/* PERKOCI EATERY — Panel Admin (vanilla JS). */
(function () {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const fmtRp = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

  function setModal(id, show) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.toggle('hidden', !show);
    m.classList.toggle('flex', show);
  }
  $$('[data-modal-close]').forEach((b) =>
    b.addEventListener('click', () => {
      const m = b.closest('[role="dialog"]');
      if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
    })
  );
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') $$('[role="dialog"]').forEach((m) => { m.classList.add('hidden'); m.classList.remove('flex'); });
  });

  /* ---------- Hapus menu ---------- */
  $$('[data-delete-menu]').forEach((b) =>
    b.addEventListener('click', () => {
      $('#delete-menu-name').textContent = b.dataset.menuName || '';
      $('#delete-menu-form').setAttribute('action', `${window.ADMIN_MENU_EDIT_BASE}/${b.dataset.deleteMenu}`);
      setModal('modal-delete-menu', true);
    })
  );

  /* ---------- Lihat detail menu ---------- */
  const menus = Object.fromEntries((window.ADMIN_MENUS || []).map((m) => [String(m.id_produk), m]));
  $$('[data-view-menu]').forEach((b) =>
    b.addEventListener('click', () => {
      const m = menus[String(b.dataset.viewMenu)];
      if (!m) return;
      const body = $('#view-menu-body');
      body.innerHTML = '';
      [
        ['Nama', m.nama_produk],
        ['Kategori', m.kategori],
        ['Harga Jual', fmtRp(m.harga)],
        ['Status', String(m.status).toUpperCase()],
        ['Deskripsi', m.deskripsi || '-'],
      ].forEach(([k, v]) => {
        const row = document.createElement('div');
        row.className = 'flex justify-between gap-3 rounded-xl bg-pk-sand-2 px-3 py-2';
        row.innerHTML = `<dt class="text-pk-brown-soft"></dt><dd class="text-right font-bold text-pk-brown"></dd>`;
        row.children[0].textContent = k;
        row.children[1].textContent = v;
        body.appendChild(row);
      });
      const edit = document.createElement('a');
      edit.href = `${window.ADMIN_MENU_EDIT_BASE}/${m.id_produk}/edit`;
      edit.className = 'mt-3 block rounded-xl bg-pk-brown px-4 py-2.5 text-center text-sm font-bold text-white';
      edit.textContent = 'Edit Menu Ini';
      body.appendChild(edit);
      setModal('modal-view-menu', true);
    })
  );

  /* ---------- Margin live (simulasi browser, tidak disimpan) ---------- */
  (function margin() {
    const jual = $('#harga-jual');
    if (!jual) return;
    const hpp = $('#harga-hpp');
    function calc() {
      const j = Number(jual.value || 0);
      const h = Number(hpp && hpp.value ? hpp.value : 0);
      $('#margin-jual').textContent = fmtRp(j);
      $('#margin-hpp').textContent = fmtRp(h);
      const profit = j - h;
      $('#margin-profit').textContent = `${fmtRp(Math.max(profit, 0))} / cup`;
      $('#margin-pct').textContent = j > 0 && h >= 0 ? `${((profit / j) * 100).toFixed(1)}%` : '0%';
    }
    jual.addEventListener('input', calc);
    hpp && hpp.addEventListener('input', calc);
    calc();
  })();

  /* ---------- Resep (UI siap integrasi) ---------- */
  (function recipe() {
    const list = $('#recipe-list');
    if (!list) return;
    $('#recipe-add')?.addEventListener('click', () => {
      const name = ($('#recipe-name') || {}).value || '';
      const qty = ($('#recipe-qty') || {}).value || '';
      if (!name.trim()) return;
      const li = document.createElement('li');
      li.className = 'flex items-center justify-between gap-2 rounded-xl bg-pk-sand-2 px-3 py-2 text-sm';
      li.innerHTML = `<span class="font-semibold text-pk-brown"></span><button type="button" class="text-pk-danger">Hapus</button>`;
      li.children[0].textContent = `${name.trim()} • ${qty.trim() || '-'}`;
      li.children[1].addEventListener('click', () => li.remove());
      list.appendChild(li);
      $('#recipe-name').value = '';
      $('#recipe-qty').value = '';
    });
    $$('[data-recipe-remove]', list).forEach((b) =>
      b.addEventListener('click', () => b.closest('li')?.remove())
    );
  })();

  /* ---------- Restock ---------- */
  $$('[data-restock]').forEach((b) =>
    b.addEventListener('click', () => {
      $('#restock-id').value = b.dataset.restock;
      $('#restock-name').textContent = b.dataset.restockName || '';
      setModal('modal-restock', true);
    })
  );

  /* ---------- Meja: tambah ---------- */
  $$('[data-open-add-table]').forEach((b) =>
    b.addEventListener('click', () => setModal('modal-add-table', true))
  );

  /* ---------- Meja: QR modal (QR asli dari payload, via qrserver) ---------- */
  const tables = Object.fromEntries((window.ADMIN_TABLES || []).map((t) => [String(t.id), t]));
  $$('[data-qr-view]').forEach((b) =>
    b.addEventListener('click', () => {
      const t = tables[String(b.dataset.qrView)];
      if (!t) return;
      $('#qr-title').textContent = `QR ${t.label}`;
      $('#qr-payload').textContent = t.qr;
      const imgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(t.scan_url)}`;
      const img = $('#qr-image');
      img.src = imgUrl;
      img.alt = `QR Code ${t.label}`;
      img.onerror = () => { img.style.display = 'none'; };
      img.style.display = '';
      $('#qr-download').href = imgUrl;
      $('#qr-copy').onclick = async () => {
        try { await navigator.clipboard.writeText(t.scan_url); } catch (_) { /* clipboard tak tersedia */ }
      };
      setModal('modal-qr', true);
    })
  );

  /* ---------- Meja: regenerate + hapus ---------- */
  $$('[data-qr-regen]').forEach((b) =>
    b.addEventListener('click', () => {
      $('#regen-label').textContent = b.dataset.qrLabel || '';
      $('#regen-form').setAttribute('action', `${window.ADMIN_TABLE_REGEN_BASE}/${b.dataset.qrRegen}/regenerate`);
      setModal('modal-regen', true);
    })
  );
  $$('[data-table-delete]').forEach((b) =>
    b.addEventListener('click', () => {
      $('#delete-table-label').textContent = b.dataset.tableLabel || '';
      $('#delete-table-form').setAttribute('action', `${window.ADMIN_TABLE_REGEN_BASE}/${b.dataset.tableDelete}`);
      setModal('modal-delete-table', true);
    })
  );

  /* ---------- Manajer: catat pengeluaran ---------- */
  $$('[data-open-add-keluar]').forEach((b) =>
    b.addEventListener('click', () => setModal('modal-add-keluar', true))
  );
  $$('[data-del-keluar]').forEach((b) =>
    b.addEventListener('click', () => {
      const label = $('#del-keluar-label');
      if (label) label.textContent = b.dataset.delLabel || '';
      const form = $('#del-keluar-form');
      if (form && window.MGR_KELUAR_DEL_BASE) form.setAttribute('action', `${window.MGR_KELUAR_DEL_BASE}/${b.dataset.delKeluar}`);
      setModal('modal-del-keluar', true);
    })
  );
})();
