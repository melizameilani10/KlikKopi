/* POS Kasir PERKOCI EATERY — interaksi UI (vanilla JS, tanpa build). */
(function () {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const fmtRp = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
  const parseRp = (s) => Number(String(s || '').replace(/[^0-9]/g, '') || 0);

  /* ---------- Toast ---------- */
  function toast(msg, tone = 'dark') {
    const wrap = $('#pos-toasts');
    if (!wrap) return;
    const el = document.createElement('div');
    const styles = {
      dark: 'bg-pk-brown text-white',
      green: 'bg-pk-green text-white',
      amber: 'bg-[#9a5b14] text-white',
      red: 'bg-pk-danger text-white',
    };
    el.className = `pointer-events-auto flex items-start gap-2 rounded-xl px-4 py-3 text-sm font-semibold shadow-card ${styles[tone] || styles.dark}`;
    el.textContent = msg;
    wrap.appendChild(el);
    setTimeout(() => {
      el.style.transition = 'opacity .3s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 320);
    }, 2600);
  }

  /* ---------- Sidebar drawer (mobile) ---------- */
  (function sidebar() {
    const btn = $('#pos-menu-btn');
    const aside = $('#pos-sidebar');
    const overlay = $('#pos-overlay');
    if (!btn || !aside) return;
    const open = () => {
      aside.classList.remove('-translate-x-full');
      overlay && overlay.classList.remove('hidden');
    };
    const close = () => {
      if (window.innerWidth >= 1024) return;
      aside.classList.add('-translate-x-full');
      overlay && overlay.classList.add('hidden');
    };
    btn.addEventListener('click', () => {
      aside.classList.contains('-translate-x-full') ? open() : close();
    });
    overlay && overlay.addEventListener('click', close);
  })();

  /* ---------- Quick actions (dashboard) ---------- */
  $$('[data-quick-action]').forEach((b) =>
    b.addEventListener('click', () => {
      const a = b.dataset.quickAction;
      if (a === 'open-drawer') toast('Cash drawer dibuka (kick).', 'green');
      else if (a === 'split-bill') toast('Pilih tiket di Daftar Antrean untuk split bill.');
      else if (a === 'manual-order') toast('Pesanan manual: pilih tiket baru di Daftar Antrean.');
      else toast('Aksi dijalankan.');
    })
  );
  document.addEventListener('keydown', (e) => {
    if (e.key === 'F1') { e.preventDefault(); toast('Pesanan manual / takeaway (F1).'); }
    if (e.key === 'F2') { e.preventDefault(); toast('Cash drawer dibuka (F2).', 'green'); }
    if (e.key === 'F3') { e.preventDefault(); toast('Split bill & cetak ulang (F3).'); }
  });

  /* ---------- Orders page ---------- */
  (function orders() {
    const list = $('#ticket-list');
    if (!list) return;
    const tickets = window.POS_TICKETS || [];
    const byCode = Object.fromEntries(tickets.map((t) => [String(t.code).toUpperCase(), t]));
    const search = $('#ticket-search');
    const areaSel = $('#area-filter');
    const tabs = $$('#filter-tabs .filter-tab');
    const count = $('#queue-count');
    const empty = $('#ticket-empty');
    let activeFilter = 'all';

    const STATUS_MATCH = {
      all: () => true,
      waiting_payment: (t) => t.status === 'waiting_payment' || t.status === 'new_qr',
      cooking: (t) => t.status === 'cooking',
      ready: (t) => t.status === 'ready',
      done: () => false,
      cancelled: () => false,
    };

    function applyFilter() {
      const q = (search ? search.value : '').trim().toLowerCase();
      const area = areaSel ? areaSel.value : '';
      const match = STATUS_MATCH[activeFilter] || STATUS_MATCH.all;
      let shown = 0;
      $$('#ticket-list > *', document).forEach((card) => {
        const code = (card.dataset.ticket || '').toUpperCase();
        const t = byCode[code];
        if (!t) { card.style.display = 'none'; return; }
        const hay = `${t.code} ${t.customer} ${t.table}`.toLowerCase();
        const ok =
          match(t) &&
          (!q || hay.includes(q)) &&
          (!area || t.area === area);
        card.style.display = ok ? '' : 'none';
        if (ok) shown++;
      });
      if (empty) empty.classList.toggle('hidden', shown > 0);
      if (count) count.textContent = `Menampilkan ${shown} dari 18 tiket antrean aktif`;
    }

    tabs.forEach((tab) =>
      tab.addEventListener('click', () => {
        activeFilter = tab.dataset.filter;
        tabs.forEach((t) => {
          const on = t === tab;
          t.setAttribute('aria-selected', on ? 'true' : 'false');
          t.className = `filter-tab inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold ${on ? 'border-pk-green bg-pk-green text-white' : 'border-pk-brown/15 bg-white text-pk-brown hover:border-pk-green'}`;
        });
        applyFilter();
      })
    );
    search && search.addEventListener('input', applyFilter);
    areaSel && areaSel.addEventListener('change', applyFilter);

    // Klik tiket → swap detail tanpa reload (fallback link tetap ada)
    list.addEventListener('click', (e) => {
      const link = e.target.closest('[data-ticket-link]');
      if (!link) return;
      const code = (link.dataset.ticket || '').toUpperCase();
      const t = byCode[code];
      if (!t) return;
      e.preventDefault();
      selectTicket(t);
      const url = new URL(window.location.href);
      url.searchParams.set('ticket', t.code);
      history.replaceState(null, '', url);
    });

    function selectTicket(t) {
      $$('#ticket-list > *').forEach((c) => {
        const on = (c.dataset.ticket || '').toUpperCase() === String(t.code).toUpperCase();
        c.classList.toggle('border-pk-green', on);
        c.classList.toggle('ring-1', on);
        c.classList.toggle('ring-pk-green', on);
      });
      const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
      set('d-code', '#' + t.code);
      set('d-customer', t.customer);
      set('d-phone', t.phone);
      set('d-table', `${t.table} • ${t.area}`);
      set('d-time', t.time);
      set('d-ago', t.ago);
      set('d-count', t.items_count ?? (t.items || []).length);
      set('d-subtotal', fmtRp(t.subtotal));
      set('d-pb1', fmtRp(t.pb1));
      set('d-service', fmtRp(t.service));
      set('d-total', fmtRp(t.total));
      const ul = $('#d-items');
      if (ul) {
        ul.innerHTML = '';
        (t.items || []).forEach((it) => {
          const li = document.createElement('li');
          li.className = 'flex items-start justify-between gap-3 py-2';
          li.innerHTML = `<div class="min-w-0"><p class="truncate text-sm font-bold text-pk-ink"></p>${it.note ? `<p class="text-xs text-pk-brown-soft"></p>` : ''}</div><p class="shrink-0 text-sm font-bold text-pk-brown"></p>`;
          li.children[0].children[0].textContent = `${it.qty}× ${it.name}`;
          if (it.note) li.children[0].children[1].textContent = it.note;
          li.children[1].textContent = fmtRp(it.price * it.qty);
          ul.appendChild(li);
        });
      }
      const badge = $('#d-badge');
      if (badge) {
        badge.textContent = '';
        const dot = document.createElement('span');
        dot.className = 'h-1.5 w-1.5 rounded-full bg-current';
        badge.appendChild(dot);
        badge.appendChild(document.createTextNode(t.status_label));
      }
      const pay = $('#btn-pay');
      if (pay) pay.setAttribute('href', `/kasir/payment/${t.code}`);
      const mc = $('#modal-cancel-code');
      if (mc) mc.textContent = '#' + t.code;
      window.POS_ACTIVE = t.code;
    }

    // Refresh
    const refresh = $('#queue-refresh');
    refresh && refresh.addEventListener('click', () => {
      refresh.disabled = true;
      toast('Memperbarui antrean…');
      setTimeout(() => { refresh.disabled = false; applyFilter(); toast('Antrean diperbarui.', 'green'); }, 700);
    });

    // Aksi dapur / barista
    const kitchen = $('#btn-kitchen');
    kitchen && kitchen.addEventListener('click', () => toast(`Tiket dapur #${window.POS_ACTIVE || ''} dikirim.`, 'green'));
    const barista = $('#btn-barista');
    barista && barista.addEventListener('click', () => toast('Barista dipanggil ke kasir.'));

    // Modal helper
    function wireModal(openBtn, modal, closeBtn, confirmBtn, onConfirm) {
      const m = $(modal);
      if (!m) return;
      const show = (v) => { m.classList.toggle('hidden', !v); m.classList.toggle('flex', v); };
      openBtn && $(openBtn) && $(openBtn).addEventListener('click', () => show(true));
      closeBtn && $(closeBtn) && $(closeBtn).addEventListener('click', () => show(false));
      m.addEventListener('click', (e) => { if (e.target === m) show(false); });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') show(false); });
      confirmBtn && $(confirmBtn) && $(confirmBtn).addEventListener('click', () => { show(false); onConfirm && onConfirm(); });
    }
    wireModal('#btn-cancel', '#modal-cancel', '#modal-cancel-no', '#modal-cancel-yes', () => {
      const badge = $('#d-badge');
      if (badge) { badge.textContent = ''; badge.appendChild(document.createTextNode('Dibatalkan')); }
      toast(`Pesanan #${window.POS_ACTIVE || ''} dibatalkan.`, 'red');
    });
    wireModal('#btn-note', '#modal-note', '#modal-note-no', '#modal-note-yes', () => {
      const v = ($('#note-input') || {}).value || '';
      const extra = $('#d-note-extra');
      if (v.trim() && extra) {
        extra.textContent = 'Catatan: ' + v.trim();
        extra.classList.remove('hidden');
        toast('Catatan disimpan.', 'green');
      }
    });

    applyFilter();
  })();

  /* ---------- Payment page ---------- */
  (function payment() {
    const total = Number(window.POS_PAYMENT_TOTAL || 0);
    if (!total && !$('#cash-received')) return;
    const methods = $$('.pay-method');
    const panels = { cash: $('#panel-cash'), noncash: $('#panel-noncash'), split: $('#panel-split') };
    const titles = {
      qris: `Scan QRIS dinamis ${fmtRp(total)}`,
      debit: `Gesek / tap EDC sebesar ${fmtRp(total)}`,
      split: 'Split bill',
    };
    let current = 'cash';

    methods.forEach((b) =>
      b.addEventListener('click', () => {
        current = b.dataset.method;
        methods.forEach((x) => {
          const on = x === b;
          x.setAttribute('aria-checked', on ? 'true' : 'false');
          x.className = `pay-method flex flex-col items-center gap-1 rounded-xl border px-3 py-3 text-sm font-bold ${on ? 'border-pk-green bg-[#eef5ea] text-pk-green' : 'border-pk-brown/15 bg-white text-pk-brown hover:border-pk-green'}`;
        });
        if (panels.cash) panels.cash.classList.toggle('hidden', current !== 'cash');
        if (panels.split) panels.split.classList.toggle('hidden', current !== 'split');
        if (panels.noncash) {
          const showNc = current === 'qris' || current === 'debit';
          panels.noncash.classList.toggle('hidden', !showNc);
          if (showNc) $('#noncash-title').textContent = titles[current];
        }
      })
    );

    const input = $('#cash-received');
    const changeEl = $('#cash-change');
    const err = $('#cash-error');
    function recalc() {
      const got = parseRp(input ? input.value : 0);
      if (input) input.value = got ? fmtRp(got) : '';
      if (changeEl) {
        const diff = got - total;
        changeEl.textContent = fmtRp(Math.max(diff, 0));
        changeEl.parentElement.classList.toggle('bg-pk-danger', got > 0 && diff < 0);
        changeEl.parentElement.classList.toggle('bg-pk-green', !(got > 0 && diff < 0));
      }
      if (err) err.classList.add('hidden');
      return parseRp(input ? input.value : 0);
    }
    input && input.addEventListener('input', recalc);
    $$('[data-cash]').forEach((b) =>
      b.addEventListener('click', () => {
        const v = b.dataset.cash;
        if (input) input.value = v === 'exact' ? fmtRp(total) : fmtRp(Number(v));
        recalc();
        input && input.focus();
      })
    );

    const confirm = $('#btn-confirm-pay');
    confirm && confirm.addEventListener('click', () => {
      if (current === 'cash') {
        const got = recalc();
        if (!got || got < total) {
          if (err) {
            err.textContent = `Uang diterima kurang ${fmtRp(total - got)}. Minimal ${fmtRp(total)}.`;
            err.classList.remove('hidden');
          }
          toast('Nominal cash kurang.', 'red');
          return;
        }
        toast(`Pembayaran ${fmtRp(total)} lunas. Kembalian ${fmtRp(got - total)}.`, 'green');
      } else if (current === 'split') {
        toast('Split bill dicatat. Selesaikan tiap bill di kasir.');
      } else {
        toast(`Menunggu konfirmasi ${current.toUpperCase()} ${fmtRp(total)}…`, 'amber');
      }
    });

    const printer = $('#btn-print');
    printer && printer.addEventListener('click', () => {
      toast('Mengirim ke Epson TM-T82…', 'amber');
      document.body.classList.add('printing-receipt');
      setTimeout(() => window.print(), 500);
    });
    window.addEventListener('afterprint', () => document.body.classList.remove('printing-receipt'));
    const wa = $('#btn-wa');
    wa && wa.addEventListener('click', () => {
      const text = encodeURIComponent(`Halo, struk PERKOCI #pembayaran ${fmtRp(total)}. Terima kasih!`);
      window.open(`https://wa.me/?text=${text}`, '_blank');
      toast('E-Receipt disiapkan untuk WhatsApp.', 'green');
    });
    const drawer = $('#btn-drawer');
    drawer && drawer.addEventListener('click', () => toast('Cash drawer dibuka.', 'green'));
  })();

  /* ---------- Settings page ---------- */
  (function settings() {
    const btn = $('#btn-pin');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const v = ($('#pin-new') || {}).value || '';
      const msg = $('#pin-msg');
      const ok = /^[0-9]{6}$/.test(v.trim());
      if (msg) {
        msg.classList.remove('hidden');
        msg.textContent = ok
          ? 'PIN berhasil diperbarui (mode demo — hubungkan ke backend untuk menyimpan permanen).'
          : 'PIN harus 6 digit angka.';
        msg.className = `rounded-xl p-3 text-xs font-bold ${ok ? 'bg-[#e7eee5] text-pk-green' : 'bg-[#fbe9e7] text-pk-danger'}`;
      }
      toast(ok ? 'PIN diperbarui.' : 'PIN tidak valid.', ok ? 'green' : 'red');
    });
  })();
})();
