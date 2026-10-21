/* PERKOCI EATERY — Katalog customer (self-order).
 *
 * Sumber data tunggal: GET /api/produk.
 * - Produk berstatus "nonaktif" tidak pernah ditampilkan ke customer.
 * - Markup kartu identik dengan resources/views/components/customer/menu-card.blade.php
 *   (class tidak diubah, hanya sumber datanya diganti jadi dinamis).
 * - Filter kategori & pencarian dijalankan atas data hasil fetch, bukan data statis.
 */
(function () {
  'use strict';

  var list = document.getElementById('menu-list');
  if (!list) return;

  var API_URL = list.getAttribute('data-api-url') || '/api/produk';
  var ADD_URL = list.getAttribute('data-cart-add-url') || '/order/cart/add';

  var searchInput = document.getElementById('menu-search');
  var chipsBox = document.getElementById('category-chips');
  var countEl = document.getElementById('menu-count');
  var emptyEl = document.getElementById('menu-empty');
  var emptyText = document.getElementById('menu-empty-text');
  var retryBtn = document.getElementById('menu-retry');
  var loadingEl = document.getElementById('menu-loading');
  var promoCta = document.getElementById('promo-cta');

  /* ---------- util ---------- */
  var esc = function (s) {
    return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  };

  var fmtRp = function (n) {
    return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
  };

  var csrf = function () {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : '';
  };

  function postJSON(url, payload) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf(),
      },
      body: JSON.stringify(payload || {}),
    }).then(function (res) {
      return res
        .json()
        .catch(function () {
          return {};
        })
        .then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        });
    });
  }

  function toast(msg, tone) {
    var wrap = document.getElementById('order-toasts');
    if (!wrap) return;
    var styles = {
      dark: 'bg-pk-brown text-white',
      green: 'bg-pk-green text-white',
      amber: 'bg-[#9a5b14] text-white',
      red: 'bg-pk-danger text-white',
    };
    var el = document.createElement('div');
    el.className = 'pointer-events-auto rounded-xl px-4 py-3 text-center text-sm font-semibold shadow-bar ' + (styles[tone] || styles.dark);
    el.textContent = msg;
    wrap.appendChild(el);
    setTimeout(function () {
      el.style.transition = 'opacity .3s';
      el.style.opacity = '0';
      setTimeout(function () {
        el.remove();
      }, 320);
    }, 2600);
  }

  /* ---------- state ---------- */
  var items = [];
  var state = 'loading'; // loading | ready | empty | error
  var initialChip = chipsBox ? chipsBox.querySelector('[aria-selected="true"]') : null;
  var category = initialChip ? initialChip.getAttribute('data-category') || 'Semua' : 'Semua';

  // Saat masih memuat, sembunyikan empty-state yang tampil di awal.
  if (emptyEl) emptyEl.classList.add('hidden');
  if (countEl) countEl.textContent = 'Memuat menu…';

  /* ---------- map respons API -> shape kartu ---------- */
  function mapProduct(row) {
    row = row || {};
    var id = row.id_produk !== undefined && row.id_produk !== null ? row.id_produk : row.id;
    var name = row.nama_produk || row.name || 'Menu';

    // Status "nonaktif" = tidak untuk customer (difilter lagi di bawah).
    var status = String(row.status || 'aktif').toLowerCase();

    // Field stok/sold_out tidak dikirim API saat ini → aman default false.
    var soldOut = false;
    if (row.sold_out !== undefined && row.sold_out !== null) {
      soldOut = row.sold_out === true || row.sold_out === 1 || row.sold_out === '1';
    } else if (row.stok !== undefined && row.stok !== null) {
      soldOut = Number(row.stok) <= 0;
    }

    return {
      id: 'db-' + id, // selaras dengan CustomerData::findProduct()
      dbId: id,
      name: name,
      desc: row.deskripsi || row.desc || name,
      price: Number(row.harga !== undefined ? row.harga : row.price || 0),
      category: row.kategori || row.category || 'Signature Coffee',
      status: status,
      rating: row.rating || null,
      soldOut: soldOut,
    };
  }

  function toneFor(cat) {
    switch (cat) {
      case 'Signature Coffee':
        return 'bg-pk-brown text-pk-khaki';
      case 'Manual Brew':
        return 'bg-[#2c1f16] text-[#e8d9c4]';
      case 'Non-Coffee':
        return 'bg-pk-green text-white';
      case 'Makanan Utama':
        return 'bg-[#9a5b14] text-white';
      default:
        return 'bg-pk-khaki text-pk-brown';
    }
  }

  function cardHTML(p) {
    var initial = esc(String(p.name || 'P').charAt(0).toUpperCase());
    var nameAttr = esc(String(p.name + ' ' + (p.desc || '')).toLowerCase());

    var media =
      '<span class="font-heading text-5xl font-semibold">' + initial + '</span>' +
      (p.rating
        ? '<span class="absolute left-2 top-2 inline-flex items-center gap-0.5 rounded-full bg-white/90 px-2 py-0.5 text-[11px] font-bold text-pk-amber">' +
          '<svg class="!h-3 !w-3" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><use href="#i-star"></use></svg>' +
          esc(p.rating) +
          '</span>'
        : '') +
      (p.soldOut
        ? '<span class="absolute inset-x-0 bottom-0 bg-black/45 py-1 text-center text-[11px] font-bold uppercase tracking-wider text-white">Habis</span>'
        : '');

    var action;
    if (p.soldOut) {
      action =
        '<span class="rounded-lg bg-pk-sand px-2.5 py-1.5 text-xs font-bold text-pk-brown-soft">Habis</span>';
    } else {
      action =
        '<button type="button" data-quick-add="' +
        esc(p.id) +
        '" aria-label="Tambah ' +
        esc(p.name) +
        '" class="flex h-8 w-8 items-center justify-center rounded-full bg-pk-green text-lg font-bold leading-none text-white">+</button>';
    }

    return (
      '<article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm" data-menu-item data-product-id="' +
      esc(p.id) +
      '" data-name="' +
      nameAttr +
      '" data-category="' +
      esc(p.category) +
      '">' +
      '<div class="relative flex h-28 items-center justify-center ' +
      toneFor(p.category) +
      '" aria-hidden="true">' +
      media +
      '</div>' +
      '<div class="flex flex-1 flex-col p-3">' +
      '<h3 class="line-clamp-1 text-sm font-bold text-pk-ink">' +
      esc(p.name) +
      '</h3>' +
      '<p class="mt-0.5 line-clamp-2 min-h-8 text-xs text-pk-brown-soft">' +
      esc(p.desc) +
      '</p>' +
      '<div class="mt-auto flex items-center justify-between gap-1 pt-2">' +
      '<p class="font-heading text-[15px] font-semibold text-pk-brown">' +
      fmtRp(p.price) +
      '</p>' +
      action +
      '</div>' +
      '</div>' +
      '</article>'
    );
  }

  function chipHTML(cat) {
    var on = cat === category;
    return (
      '<button type="button" role="tab" data-category="' +
      esc(cat) +
      '" aria-selected="' +
      (on ? 'true' : 'false') +
      '" class="shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold shadow-sm transition ' +
      (on ? 'bg-pk-green text-white' : 'bg-white text-pk-brown-soft hover:text-pk-green') +
      '">' +
      esc(cat) +
      '</button>'
    );
  }

  /* ---------- render ---------- */
  function renderChips() {
    if (!chipsBox) return;

    var cats = ['Semua'];
    items.forEach(function (p) {
      if (p.category && cats.indexOf(p.category) === -1) cats.push(p.category);
    });
    if (cats.indexOf(category) === -1) category = 'Semua';

    chipsBox.innerHTML = cats.map(chipHTML).join('');

    Array.prototype.forEach.call(chipsBox.querySelectorAll('[data-category]'), function (chip) {
      chip.addEventListener('click', function () {
        category = chip.getAttribute('data-category');
        Array.prototype.forEach.call(chipsBox.querySelectorAll('[data-category]'), function (c) {
          var on = c === chip;
          c.setAttribute('aria-selected', on ? 'true' : 'false');
          c.classList.toggle('bg-pk-green', on);
          c.classList.toggle('text-white', on);
          c.classList.toggle('bg-white', !on);
          c.classList.toggle('text-pk-brown-soft', !on);
        });
        try {
          var url = new URL(window.location.href);
          if (category === 'Semua') url.searchParams.delete('kategori');
          else url.searchParams.set('kategori', category);
          history.replaceState(null, '', url);
        } catch (e) {
          /* URL/History bisa gagal di environment tertentu — filter tetap jalan. */
        }
        apply();
      });
    });
  }

  function render() {
    list.innerHTML = items.map(cardHTML).join('');
    renderChips();
    apply();
  }

  /* ---------- filter (kategori + pencarian) atas data hasil fetch ---------- */
  function apply() {
    if (state === 'loading') {
      if (countEl) countEl.textContent = 'Memuat menu…';
      if (emptyEl) emptyEl.classList.add('hidden');
      if (retryBtn) retryBtn.classList.add('hidden');
      return;
    }

    var q = searchInput ? String(searchInput.value || '').trim().toLowerCase() : '';
    var shown = 0;
    var cards = list.querySelectorAll('[data-menu-item]');

    for (var i = 0; i < cards.length; i++) {
      var card = cards[i];
      var okCat = category === 'Semua' || card.getAttribute('data-category') === category;
      var okQ = !q || String(card.getAttribute('data-name') || '').indexOf(q) !== -1;
      var ok = okCat && okQ;
      card.style.display = ok ? '' : 'none';
      if (ok) shown++;
    }

    if (countEl) countEl.textContent = shown + ' Menu Tersedia';

    var msg = '';
    var showRetry = false;

    if (state === 'error') {
      msg = 'Menu belum bisa dimuat. Periksa koneksi Anda lalu coba lagi.';
      showRetry = true;
    } else if (items.length === 0) {
      msg = 'Menu belum tersedia.';
    } else if (shown === 0) {
      msg = 'Tidak ada menu yang cocok.';
    }

    if (emptyEl) {
      if (msg) {
        if (emptyText) emptyText.textContent = msg;
        emptyEl.classList.remove('hidden');
      } else {
        emptyEl.classList.add('hidden');
      }
    }
    if (retryBtn) retryBtn.classList.toggle('hidden', !showRetry);
  }

  /* ---------- muat data dari API ---------- */
  function load() {
    state = 'loading';
    items = [];
    list.innerHTML = '';
    if (loadingEl) list.appendChild(loadingEl); // node dipakai ulang saat retry
    apply();

    fetch(API_URL, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (json) {
        var rows = (json && json.data) || (Array.isArray(json) ? json : []);
        items = rows
          .map(mapProduct)
          .filter(function (p) {
            return p.status !== 'nonaktif'; // jangan tampilkan menu nonaktif ke customer
          });
        state = items.length > 0 ? 'ready' : 'empty';
        render();
      })
      .catch(function () {
        items = [];
        state = 'error';
        list.innerHTML = '';
        apply();
      });
  }

  /* ---------- interaksi ---------- */

  // Tambah cepat (kartu dirender JS → pakai delegasi supaya ikut terikat).
  list.addEventListener('click', function (e) {
    var btn = e.target && e.target.closest ? e.target.closest('[data-quick-add]') : null;
    if (!btn) return;
    e.preventDefault();

    btn.disabled = true;
    postJSON(ADD_URL, { id: btn.getAttribute('data-quick-add'), qty: 1 })
      .then(function (r) {
        btn.disabled = false;
        if (r.ok && r.data && r.data.success) {
          toast(r.data.message, 'green');
          syncCartBadges(r.data.cartCount, r.data.cartTotal);
        } else {
          toast((r.data && r.data.message) || 'Gagal menambah.', 'red');
        }
      })
      .catch(function () {
        btn.disabled = false;
        toast('Gagal menambah. Periksa koneksi Anda.', 'red');
      });
  });

  function syncCartBadges(count, total) {
    var nav = document.getElementById('nav-cart-badge');
    if (nav) {
      nav.textContent = count;
      nav.style.display = count > 0 ? '' : 'none';
    }
    var barCount = document.getElementById('cart-bar-count');
    if (barCount) barCount.textContent = count + ' Item di Pesanan';
    var barTotal = document.getElementById('cart-bar-total');
    if (barTotal && total != null) barTotal.textContent = fmtRp(total);
    var barBadge = document.getElementById('cart-bar-badge');
    if (barBadge) barBadge.textContent = count;
    if (count > 0 && !document.getElementById('cart-bar')) window.location.reload();
  }

  // Pencarian: listener customer-order.js terikat lebih dulu, milik ini
  // terikat belakangan sehingga hasilnya (data hasil fetch) yang menang.
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      apply();
    });
  }

  // CTA promo: customer-order.js memfilter kartu lama; jalankan ulang filter
  // agar daftar hasil fetch tetap tampil konsisten.
  if (promoCta) {
    promoCta.addEventListener('click', function () {
      apply();
    });
  }

  if (retryBtn) retryBtn.addEventListener('click', load);

  load();
})();
