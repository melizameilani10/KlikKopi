/* PERKOCI EATERY — Customer Self-Order (vanilla JS, mobile-first). */
(function () {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const fmtRp = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

  function toast(msg, tone = 'dark') {
    const wrap = $('#order-toasts');
    if (!wrap) return;
    const styles = { dark: 'bg-pk-brown text-white', green: 'bg-pk-green text-white', amber: 'bg-[#9a5b14] text-white', red: 'bg-pk-danger text-white' };
    const el = document.createElement('div');
    el.className = `pointer-events-auto rounded-xl px-4 py-3 text-center text-sm font-semibold shadow-bar ${styles[tone] || styles.dark}`;
    el.textContent = msg;
    wrap.appendChild(el);
    setTimeout(() => { el.style.transition = 'opacity .3s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 320); }, 2600);
  }

  async function postJSON(url, payload) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
      body: JSON.stringify(payload || {}),
    });
    let data = {};
    try { data = await res.json(); } catch (_) { /* non-JSON */ }
    return { ok: res.ok, status: res.status, data };
  }

  function setModal(id, show) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.toggle('hidden', !show);
    m.classList.toggle('flex', show);
  }

  /* ---------- Modal generik ---------- */
  $$('[data-modal-close]').forEach((b) =>
    b.addEventListener('click', () => b.closest('[role="dialog"]') && (b.closest('[role="dialog"]').classList.add('hidden'), b.closest('[role="dialog"]').classList.remove('flex')))
  );
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') $$('[role="dialog"]').forEach((m) => { m.classList.add('hidden'); m.classList.remove('flex'); });
  });

  /* ---------- Panggil waiter (dengan cooldown anti-spam) ---------- */
  let waiterCooldownUntil = 0;
  const waiterBtn = $('#btn-waiter');
  waiterBtn && waiterBtn.addEventListener('click', () => {
    if (Date.now() < waiterCooldownUntil) {
      toast(`Pelayan sudah dipanggil. Tunggu ${Math.ceil((waiterCooldownUntil - Date.now()) / 1000)} detik.`, 'amber');
      return;
    }
    setModal('modal-waiter', true);
  });
  const waiterConfirm = $('#waiter-confirm');
  waiterConfirm && waiterConfirm.addEventListener('click', async () => {
    waiterConfirm.disabled = true;
    const { ok, data } = await postJSON('/order/waiter', {});
    waiterConfirm.disabled = false;
    setModal('modal-waiter', false);
    if (ok && data.success) {
      waiterCooldownUntil = Date.now() + 60000;
      toast(data.message, 'green');
    } else {
      if (data.cooldown) waiterCooldownUntil = Date.now() + data.cooldown * 1000;
      toast(data.message || 'Gagal memanggil pelayan.', 'red');
    }
  });

  /* ---------- Katalog: search + kategori (instan, tanpa reload) ---------- */
  (function catalog() {
    const list = $('#menu-list');
    if (!list) return;
    const search = $('#menu-search');
    const chips = $$('#category-chips [data-category]');
    let active = (chips.find((c) => c.getAttribute('aria-selected') === 'true') || {}).dataset?.category || 'Semua';
    let promoOnly = null; // daftar product id promo saat CTA promo diklik

    function apply() {
      const q = (search ? search.value : '').trim().toLowerCase();
      let shown = 0;
      $$('[data-menu-item]', list).forEach((card) => {
        const okPromo = !promoOnly || promoOnly.includes(card.dataset.productId);
        const okCat = active === 'Semua' || card.dataset.category === active;
        const okQ = !q || (card.dataset.name || '').includes(q);
        const ok = okPromo && okCat && okQ;
        card.style.display = ok ? '' : 'none';
        if (ok) shown++;
      });
      const empty = $('#menu-empty');
      if (empty) empty.classList.toggle('hidden', shown > 0);
      const count = $('#menu-count');
      if (count) count.textContent = promoOnly ? `${shown} Menu Promo` : `${shown} Menu Tersedia`;
    }

    // CTA promo → tampilkan hanya menu promo + scroll ke daftar
    $('#promo-cta')?.addEventListener('click', () => {
      promoOnly = ($('#promo-cta').dataset.promoItems || '').split(' ').filter(Boolean);
      if (search) search.value = '';
      active = 'Semua';
      chips.forEach((c) => {
        const on = c.dataset.category === 'Semua';
        c.setAttribute('aria-selected', on ? 'true' : 'false');
        c.classList.toggle('bg-pk-green', on);
        c.classList.toggle('text-white', on);
        c.classList.toggle('bg-white', !on);
        c.classList.toggle('text-pk-brown-soft', !on);
      });
      apply();
      $('#menu-list')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      toast('Menampilkan menu promo.', 'green');
    });

    chips.forEach((chip) =>
      chip.addEventListener('click', () => {
        active = chip.dataset.category;
        promoOnly = null;
        chips.forEach((c) => {
          const on = c === chip;
          c.setAttribute('aria-selected', on ? 'true' : 'false');
          c.classList.toggle('bg-pk-green', on);
          c.classList.toggle('text-white', on);
          c.classList.toggle('bg-white', !on);
          c.classList.toggle('text-pk-brown-soft', !on);
        });
        const url = new URL(window.location.href);
        active === 'Semua' ? url.searchParams.delete('kategori') : url.searchParams.set('kategori', active);
        history.replaceState(null, '', url);
        apply();
      })
    );
    search && search.addEventListener('input', () => { promoOnly = null; apply(); });
    apply();
  })();

  /* ---------- Quick add (produk tanpa kustomisasi) ---------- */
  $$('[data-quick-add]').forEach((btn) =>
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      const { ok, data } = await postJSON('/order/cart/add', { id: btn.dataset.quickAdd, qty: 1 });
      btn.disabled = false;
      if (ok && data.success) {
        toast(data.message, 'green');
        syncCartBadges(data.cartCount, data.cartTotal);
      } else {
        toast(data.message || 'Gagal menambah.', 'red');
      }
    })
  );

  function syncCartBadges(count, total) {
    const nav = $('#nav-cart-badge');
    if (nav) { nav.textContent = count; nav.style.display = count > 0 ? '' : 'none'; }
    const barCount = $('#cart-bar-count');
    if (barCount) barCount.textContent = `${count} Item di Pesanan`;
    const barTotal = $('#cart-bar-total');
    if (barTotal && total != null) barTotal.textContent = fmtRp(total);
    const barBadge = $('#cart-bar-badge');
    if (barBadge) barBadge.textContent = count;
    if (count > 0 && !$('#cart-bar')) {
      // Bar belum ada di halaman ini (mis. katalog dimuat saat kosong) → reload ringan agar bar muncul.
      window.location.reload();
    }
  }

  /* ---------- Halaman produk: kalkulasi + tambah ---------- */
  (function productPage() {
    const page = $('[data-product-page]');
    if (!page) return;
    const base = Number(page.dataset.basePrice || 0);
    let qty = 1;

    const qtyEl = $('#pdt-qty');
    const qtyLabel = $('#pdt-qty-label');
    const totalEl = $('#pdt-total');
    const errEl = $('#product-error');
    const note = $('#barista-note');
    const noteCount = $('#note-count');

    function selections() {
      const out = {};
      $$('[data-option-group]', page).forEach((g) => {
        const key = g.dataset.optionGroup;
        const checked = $$('input:checked', g).map((i) => i.value);
        out[key] = g.dataset.optionType === 'radio' ? (checked[0] || null) : checked;
      });
      return out;
    }

    function recalc() {
      let unit = base;
      $$('[data-option-group] input:checked', page).forEach((i) => { unit += Number(i.dataset.delta || 0); });
      if (qtyEl) qtyEl.textContent = qty;
      if (qtyLabel) qtyLabel.textContent = qty;
      if (totalEl) totalEl.textContent = fmtRp(unit * qty);
      if (errEl) errEl.classList.add('hidden');
    }

    $$('[data-option-group] input', page).forEach((i) => i.addEventListener('change', recalc));
    $('#pdt-inc')?.addEventListener('click', () => { qty = Math.min(20, qty + 1); recalc(); });
    $('#pdt-dec')?.addEventListener('click', () => { qty = Math.max(1, qty - 1); recalc(); });
    note?.addEventListener('input', () => { if (noteCount) noteCount.textContent = note.value.length; });

    $('#pdt-add')?.addEventListener('click', async (e) => {
      const btn = e.currentTarget;
      btn.disabled = true;
      const { ok, data } = await postJSON('/order/cart/add', {
        id: btn.dataset.productId,
        qty,
        selections: selections(),
        note: note ? note.value : '',
      });
      btn.disabled = false;
      if (ok && data.success) {
        toast(data.message, 'green');
        setTimeout(() => { window.location.href = '/order/cart'; }, 600);
      } else {
        if (errEl) { errEl.textContent = data.message || 'Gagal menambah.'; errEl.classList.remove('hidden'); }
        toast(data.message || 'Gagal menambah.', 'red');
      }
    });

    recalc();
  })();

  /* ---------- Keranjang: qty + hapus (live, tanpa reload) ---------- */
  (function cartPage() {
    const list = $('#cart-list');
    if (!list) return;

    function refreshSummary(totals) {
      if (!totals) return;
      $$('[data-order-summary] [data-total]').forEach((dd) => {
        const k = dd.dataset.total;
        if (totals[k] != null) dd.textContent = fmtRp(totals[k]);
      });
      const checkoutBtn = $('a[href$="/checkout"]');
      if (checkoutBtn && totals.total != null) checkoutBtn.textContent = `Lanjut ke Pembayaran • ${fmtRp(totals.total)}`;
    }

    async function changeQty(key, qty) {
      const { ok, data } = await postJSON('/order/cart/update', { key, qty });
      if (!ok || !data.success) { toast(data.message || 'Gagal memperbarui.', 'red'); return; }
      const line = list.querySelector(`[data-cart-line="${key}"]`);
      if (qty === 0 || !line) {
        line && line.remove();
        if (!list.children.length) { window.location.reload(); return; }
      } else {
        const qv = line.querySelector('[data-qty-val]');
        if (qv) qv.textContent = qty;
        const lq = line.querySelector('[data-line-qty]');
        if (lq) lq.textContent = `${qty}×`;
        const lt = line.querySelector('[data-line-total]');
        if (lt) lt.textContent = fmtRp(data.lineTotal);
      }
      const nav = $('#nav-cart-badge');
      if (nav) { nav.textContent = data.cartCount; nav.style.display = data.cartCount > 0 ? '' : 'none'; }
      refreshSummary(data.totals);
    }

    list.addEventListener('click', async (e) => {
      const inc = e.target.closest('[data-qty-inc]');
      const dec = e.target.closest('[data-qty-dec]');
      const rm = e.target.closest('[data-cart-remove]');
      if (inc) {
        const key = inc.dataset.qtyInc;
        const cur = Number(list.querySelector(`[data-qty-val="${key}"]`)?.textContent || 1);
        await changeQty(key, Math.min(20, cur + 1));
      } else if (dec) {
        const key = dec.dataset.qtyDec;
        const cur = Number(list.querySelector(`[data-qty-val="${key}"]`)?.textContent || 1);
        await changeQty(key, cur - 1);
      } else if (rm) {
        const key = rm.dataset.cartRemove;
        const { ok, data } = await postJSON('/order/cart/remove', { key });
        if (ok && data.success) {
          document.querySelector(`[data-cart-line="${key}"]`)?.remove();
          toast('Item dihapus.', 'amber');
          if (!list.children.length) { window.location.reload(); return; }
          const nav = $('#nav-cart-badge');
          if (nav) { nav.textContent = data.cartCount; nav.style.display = data.cartCount > 0 ? '' : 'none'; }
          refreshSummary(data.totals);
        }
      }
    });
  })();

  /* ---------- E-Receipt ---------- */
  $('#btn-receipt-print')?.addEventListener('click', () => {
    document.body.classList.add('printing-receipt');
    window.print();
  });
  window.addEventListener('afterprint', () => document.body.classList.remove('printing-receipt'));
  $('#btn-receipt-wa')?.addEventListener('click', (e) => {
    const code = e.currentTarget.dataset.code || '';
    const text = encodeURIComponent(`Halo PERKOCI EATERY, berikut e-receipt saya ${code}. Terima kasih!`);
    window.open(`https://wa.me/?text=${text}`, '_blank');
    toast('E-Receipt disiapkan untuk WhatsApp.', 'green');
  });
})();
