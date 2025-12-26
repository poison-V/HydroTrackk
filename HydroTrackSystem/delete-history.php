<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>HydroTrack · Deleted Logs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles.css" />
  <script src="libs/tailwindcss.js"></script>
  <script src="libs/qrcode.min.js"></script>
  <script src="libs/html5-qrcode.min.js"></script>
  <style>
    @media print {
      .no-print {
        display: none !important;
      }

      body {
        background: white;
      }
    }

    body {
      font-family: system-ui, sans-serif;
      background: linear-gradient(to bottom right, #ecfdf5, #cffafe);
      overflow-x: hidden;
    }

    .sidebar {
      position: fixed;
      top: 12vh;
      left: 0;
      width: 150px;
      height: 100vh;
      background: #f8fffc;
      border-right: 1px solid #d1fae5;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 110px;
      z-index: 60;
      transform: translateX(0);
      transition: transform 0.3s ease;
    }

    .sidebar a {
      display: block;
      width: 115px;
      text-align: center;
      margin-bottom: 6px;
      padding: 8px;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.75rem;
      transition: 0.2s;
      color: #374151;
      text-decoration: none;
    }

    .sidebar a:hover {
      transform: translateY(-1px);
    }

    .sidebar .active {
      background: #4b5563;
      color: white;
    }

    .sidebar a.active,
    .sidebar a:hover {
      background: #4b5563;
      color: white;
    }

    .main-content {
      margin-left: 150px;
      padding: 40px 20px 20px 20px;
      transition: margin-left 0.3s ease;
      margin-top: 10vh;
    }

    table thead th {
      position: sticky;
      top: 0;
      background-color: #ffffff;
      z-index: 10;
    }

    .scroll-panel {
      overflow-y: auto;
      max-height: 65vh;
      scroll-behavior: smooth;
    }

    .scroll-panel::-webkit-scrollbar {
      width: 6px;
    }

    .scroll-panel::-webkit-scrollbar-thumb {
      background-color: rgba(16, 185, 129, 0.4);
      border-radius: 3px;
    }

    .scroll-panel::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.05);
      border-radius: 3px;
    }

    .btn-clear {
      background: #dc2626;
      color: white;
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 0.85rem;
    }

    .btn-clear:hover {
      background: #b91c1c;
    }

    .btn-restore {
      background: #10b981;
      color: white;
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 0.85rem;
    }

    .btn-restore:hover {
      background: #059669;
    }

    .empty-message {
      text-align: center;
      padding: 40px 0;
      color: #6b7280;
      animation: fadeIn 0.6s ease-in-out;
    }

    .empty-message span {
      display: inline-block;
      animation: pulseIcon 1.2s infinite;
      font-size: 28px;
    }

    @keyframes pulseIcon {

      0%,
      100% {
        opacity: 0.7;
        transform: scale(1);
      }

      50% {
        opacity: 1;
        transform: scale(1.1);
      }
    }

    .badge {
      background: #eefbf3;
      color: #065f46;
      padding: 4px 8px;
      border-radius: 999px;
      font-size: 0.75rem;
    }

    .controls {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .search-input {
      width: 380px;
      max-width: 40vw;
    }

    .mobile-menu-btn {
      display: none;
      position: fixed;
      top: 28vh;
      left: 3vh;
      z-index: 65;
      background: #059669;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 1px 5px;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
      font-size: 1.2rem;
    }

    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 55;
    }

    .sidebar-overlay.active {
      display: block;
    }

    @media (max-width:768px) {
      .search-input {
        width: 100%;
      }

      .main-content {
        margin-left: 0;
        padding: 140px 10px 10px 10px;
      }

      .sidebar {
        transform: translateX(-100%);
        width: 220px;
        padding-top: 80px;
        z-index: 60;
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .sidebar a {
        width: 180px;
        font-size: 0.875rem;
        padding: 10px;
      }

      .mobile-menu-btn {
        display: block;
      }
    }
  </style>
</head>

<body>
  <!-- Mobile Menu Button -->
  <button class="mobile-menu-btn no-print" id="mobileMenuBtn" aria-label="Toggle Menu">☰</button>

  <!-- Sidebar Overlay -->
  <div class="sidebar-overlay no-print" id="sidebarOverlay"></div>

  <!-- NAVBAR -->
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm shadow-md">
    <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 flex flex-col md:flex-row items-start md:items-center gap-3">
      <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
        <div class="flex items-center gap-3">
          <span class="text-2xl"> <img src="icons/image-removebg-preview (1).png"
              class="w-12 h-12 object-contain" /></span>
          <div>
            <div class="text-emerald-700 font-bold text-lg">HydroTrack</div>
            <div class="text-xs text-gray-500 -mt-1 hidden md:block">Deleted Logs</div>
          </div>
        </div>
        <a href="logout.php"
          class="md:hidden text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Logout</a>
      </div>

      <div class="flex-1 w-full md:mx-6 md:w-auto relative">
        <input id="searchBox" type="text" placeholder="Search customers / phone / item..."
          class="block w-full rounded border px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
          autocomplete="off" />
        <div id="customerDropdown"
          class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg max-h-60 overflow-y-auto z-50 hidden">
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
        <div id="cashierBadge" class="cashier-badge flex items-center gap-2 text-sm text-gray-600">
          <img src="icons/clerk.png" class="w-5 h-5 object-contain" alt="">
          <span id="cashierNameDisplay" class="font-semibold text-emerald-700">—</span>
        </div><a href="logout.php"
          class="hidden md:inline-block text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 whitespace-nowrap">Logout</a>
      </div>
      <div
        class="max-w-6xl mx-auto px-4 md:px-6 py-1 flex flex-col md:flex-row items-start md:items-center justify-between gap-1">
        <div class="text-xs text-gray-400">Local saved cashier</div>
      </div>
  </nav>

  <!-- SIDEBAR -->
  <aside class="sidebar">

    <a href="home.php"
      class="sidebar-link btn-home flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
      <div class="sidebar-icon">
        <img src="icons/home.png" class="w-5 h-5 object-contain" alt="">
      </div>
      <span>Home</span>
    </a>

    <a href="dailysalesreport.php"
      class="sidebar-link btn-sales flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
      <div class="sidebar-icon">
        <img src="icons/sales.png" class="w-5 h-5 object-contain" alt="">
      </div>
      <span>Daily Sales</span>
    </a>

    <a href="history.php"
      class="sidebar-link btn-history flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
      <div class="sidebar-icon">
        <img src="icons/history.png" class="w-5 h-5 object-contain" alt="">
      </div>
      <span>History</span>
    </a>

    <a href="delete-history.php"
      class="sidebar-link btn-deleted flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
      <div class="sidebar-icon">
        <img src="icons/bin.png" class="w-5 h-5 object-contain" alt="">
      </div>
      <span>Deleted</span>
    </a>

    <a href="qrcodegenerator.php"
      class="sidebar-link btn-deleted flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
      <div class="sidebar-icon">
        <img src="icons/qr-code.png" class="w-5 h-5 object-contain" alt="">
      </div>
      <span>Generate QR</span>
    </a>

  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-bold text-emerald-700">Deleted Logs <span id="countBadge" class="badge"
          style="margin-left:8px">0</span></h2>
      <div style="display:flex; gap:8px; align-items:center;">
        <button id="restoreAllBtn" class="btn-restore no-print" title="Restore all (moves back to sales)">&nbsp;♻️
          Restore All&nbsp;</button>
        <button id="clearDeleted" class="btn-clear no-print">🗑️ Clear All Deleted</button>
      </div>
    </div>

    <div class="scroll-panel border border-emerald-100 rounded-lg">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-emerald-800">
            <th class="p-2 font-medium">Customer</th>
            <th class="p-2 font-medium">Phone</th>
            <th class="p-2 font-medium">Address</th>
            <th class="p-2 font-medium">Type</th>
            <th class="p-2 font-medium">Size</th>
            <th class="p-2 font-medium">Quantity</th>
            <th class="p-2 font-medium">Total</th>
            <th class="p-2 font-medium">Deleted At</th>
            <th class="p-2 font-medium">Action</th>
          </tr>
        </thead>
        <tbody id="deletedTable"></tbody>
      </table>
    </div>
  </main>

  <script>
    /*
      Connected Delete History
      - Real-time updates via BroadcastChannel 'hydrotrack_updates'
      - Supports both keys: 'hydro_deleted_sales_v1' (preferred) and 'deleteHistory' (legacy)
      - Restore/clear operations broadcast updates so other tabs/pages update instantly
    */

    const BROADCAST_CHANNEL = 'hydrotrack_updates';
    const PREFERRED_DELETED_KEY = 'hydro_deleted_sales_v1'; // used by newer files
    const LEGACY_DELETED_KEY = 'deleteHistory';              // legacy snippet used this
    const SALES_KEY = 'hydro_sales_v1';
    const CASHIER_KEY = 'hydro_cashier_v1';

    const bc = (window.BroadcastChannel) ? new BroadcastChannel(BROADCAST_CHANNEL) : null;

    /* Utility helpers */
    function safeParse(key, fallback) {
      try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch (e) { return fallback; }
    }
    function save(key, val) { localStorage.setItem(key, JSON.stringify(val)); }
    function dedupeDeleted(arr) {
      // dedupe by id if present, else by JSON string
      const seen = new Set();
      const out = [];
      arr.forEach(item => {
        const id = item && item.id ? item.id : JSON.stringify(item);
        if (!seen.has(id)) {
          seen.add(id);
          out.push(item);
        }
      });
      return out;
    }
    function getAllDeletedArrays() {
      const a = safeParse(PREFERRED_DELETED_KEY, []);
      const b = safeParse(LEGACY_DELETED_KEY, []);
      // merge and dedupe (preserve most recent deletedAt if duplicates)
      const combined = [...a, ...b];
      const deduped = dedupeDeleted(combined);
      // sort by deletedAt (fallback to date)
      deduped.sort((x, y) => {
        const dx = new Date(x.deletedAt || x.date || 0).getTime();
        const dy = new Date(y.deletedAt || y.date || 0).getTime();
        return dy - dx;
      });
      return deduped;
    }
    function persistDeletedArray(arr) {
      // Save to preferred key and also keep legacy key in sync for compatibility
      save(PREFERRED_DELETED_KEY, arr);
      save(LEGACY_DELETED_KEY, arr);
    }

    /* UI rendering */
    function renderDeletedTable() {
      const arr = getAllDeletedArrays();
      const tbody = document.getElementById('deletedTable');
      const countBadge = document.getElementById('countBadge');
      countBadge.textContent = String(arr.length);
      if (!tbody) return;

      if (arr.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9">
      <div class="empty-message"><span>🗑️</span><br>No deleted records found</div>
    </td></tr>`;
        return;
      }

      tbody.innerHTML = arr.map((d, idx) => {
        const deletedAt = new Date(d.deletedAt || d.date || Date.now()).toLocaleString();
        const total = (d.total !== undefined) ? (typeof d.total === 'number' ? '₱' + Number(d.total).toFixed(2) : d.total) : '—';
        return `<tr class="border-t" data-idx="${idx}">
      <td class="p-2">${escapeHtml(d.customer || '—')}</td>
      <td class="p-2">${escapeHtml(d.phone || '—')}</td>
      <td class="p-2">${escapeHtml(d.address || '—')}</td>
      <td class="p-2">${escapeHtml(d.type || '—')}</td>
      <td class="p-2">${escapeHtml(d.size || d.item || '—')}</td>
      <td class="p-2">${escapeHtml(String(d.qty || '—'))}</td>
      <td class="p-2">${escapeHtml(total)}</td>
      <td class="p-2">${escapeHtml(deletedAt)}</td>
      <td class="p-2">
        <button class="btn-restore" data-idx="${idx}" onclick="restoreDeleted(${idx})">♻️ Restore</button>
      </td>
    </tr>`;
      }).join('');
    }

    /* Safe HTML escape */
    function escapeHtml(s) { return String(s || '').replace(/[&<>"'`]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '`': '&#96;' })[c]); }

    /* Restore single deleted entry */
    function restoreDeleted(idx) {
      const arr = getAllDeletedArrays();
      if (idx < 0 || idx >= arr.length) return alert('Invalid item.');
      if (!confirm('Restore this deleted record back to sales?')) return;

      const item = arr.splice(idx, 1)[0];

      // push to sales
      const sales = safeParse(SALES_KEY, []);
      // ensure restored item has id; preserve original
      sales.push(item);
      save(SALES_KEY, sales);

      // persist updated deleted arrays to both keys
      persistDeletedArray(arr);

      // broadcast to other tabs/pages
      broadcast({ type: 'deleted:restored', itemId: item.id || null });

      renderDeletedTable();
      alert('Restored.');
    }

    /* Restore ALL */
    function restoreAllDeleted() {
      const arr = getAllDeletedArrays();
      if (arr.length === 0) return alert('No deleted records to restore.');
      if (!confirm(`Restore ALL (${arr.length}) deleted records back to sales?`)) return;

      const sales = safeParse(SALES_KEY, []);
      // append all
      arr.forEach(i => sales.push(i));
      save(SALES_KEY, sales);

      // clear deleted
      persistDeletedArray([]);

      // broadcast
      broadcast({ type: 'deleted:restoredAll', count: arr.length });

      renderDeletedTable();
      alert('All restored.');
    }

    /* Clear all deleted */
    function clearAllDeleted() {
      if (!confirm('Are you sure you want to clear ALL deleted logs? This cannot be undone.')) return;
      persistDeletedArray([]);
      broadcast({ type: 'deleted:cleared' });
      renderDeletedTable();
      alert('Deleted logs cleared.');
    }

    /* Broadcast helper: notify other pages */
    function broadcast(msg) {
      try {
        if (bc) bc.postMessage(msg);
        // also set a small localStorage flag to trigger storage event in old browsers/tabs
        localStorage.setItem('hydrotrack_last_event', JSON.stringify({ msg, t: Date.now() }));
      } catch (e) {
        console.warn('Broadcast failed', e);
      }
    }

    /* Listen to broadcast events */
    if (bc) {
      bc.onmessage = (ev) => {
        try {
          const data = ev.data;
          // React to events that affect deleted-sales UI
          if (data && (data.type && (
            data.type.startsWith('deleted:') ||
            data.type === 'sale:added' ||
            data.type === 'sale:deleted' ||
            data.type === 'sales:reset'
          ))) {
            // re-render to reflect any changes
            renderDeletedTable();
          }
        } catch (e) { console.warn('bc onmessage error', e); }
      };
    }

    /* Also listen to storage events (useful for older tabs) */
    window.addEventListener('storage', (ev) => {
      // respond to changes on deleted keys or hydotrack events
      if (!ev.key) return;
      if ([PREFERRED_DELETED_KEY, LEGACY_DELETED_KEY, 'hydrotrack_last_event'].includes(ev.key) || ev.key === SALES_KEY) {
        // slight debounce
        setTimeout(() => {
          renderDeletedTable();
        }, 50);
      }
    });

    /* Search */
    document.addEventListener('input', (e) => {
      if (e.target && e.target.id === 'searchBox') {
        const query = (e.target.value || '').toLowerCase();
        const rows = document.querySelectorAll('#deletedTable tr[data-idx]');
        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(query) ? '' : 'none';
        });
      }
    });

    /* Wire buttons & init */
    document.addEventListener('DOMContentLoaded', () => {
      // Mobile menu toggle
      const mobileMenuBtn = document.getElementById('mobileMenuBtn');
      const sidebar = document.querySelector('.sidebar');
      const sidebarOverlay = document.getElementById('sidebarOverlay');

      if (mobileMenuBtn && sidebar && sidebarOverlay) {
        mobileMenuBtn.addEventListener('click', () => {
          sidebar.classList.toggle('active');
          sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', () => {
          sidebar.classList.remove('active');
          sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking on a link (mobile)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
          link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
              sidebar.classList.remove('active');
              sidebarOverlay.classList.remove('active');
            }
          });
        });
      }

      // highlight sidebar
      const links = document.querySelectorAll('.sidebar-link');
      const currentPage = window.location.pathname.split("/").pop();
      links.forEach(link => link.classList.toggle('active', link.getAttribute('href') === currentPage));

      // cashier
      const cashierInputTop = document.getElementById('cashierInputTop');
      const cashierNameDisplay = document.getElementById('cashierNameDisplay');
      const saved = localStorage.getItem(CASHIER_KEY);
      if (saved) {
        if (cashierInputTop) cashierInputTop.value = saved;
        if (cashierNameDisplay) cashierNameDisplay.textContent = saved;
      }

      // Cashier management buttons
      function setCashierName(v) {
        if (!v) {
          localStorage.removeItem(CASHIER_KEY);
          if (cashierNameDisplay) cashierNameDisplay.textContent = '—';
          return;
        }
        localStorage.setItem(CASHIER_KEY, v);
        if (cashierNameDisplay) cashierNameDisplay.textContent = v;
      }

      // Sync cashier name across tabs
      window.addEventListener('storage', (e) => {
        if (e.key === CASHIER_KEY) {
          const newName = e.newValue || '—';
          const cashierNameDisplay = document.getElementById('cashierNameDisplay');
          const cashierInputTop = document.getElementById('cashierInputTop');
          if (cashierNameDisplay) cashierNameDisplay.textContent = newName;
          if (cashierInputTop) cashierInputTop.value = newName;
        }
      });

      document.getElementById('saveCashierBtn')?.addEventListener('click', () => {
        const name = cashierInputTop?.value;
        setCashierName(name);
      });

      document.getElementById('removeCashierBtn')?.addEventListener('click', () => {
        setCashierName('');
        if (cashierInputTop) cashierInputTop.value = '';
      });

      document.getElementById('clearDeleted').addEventListener('click', clearAllDeleted);
      document.getElementById('restoreAllBtn').addEventListener('click', restoreAllDeleted);

      // initial render
      renderDeletedTable();
    });

    /* Expose restoreDeleted to inline onclick handlers (global) */
    window.restoreDeleted = restoreDeleted;

    /* Also provide a small API for other pages to notify updates (optional) */
    window.hydroTrackNotify = function (e) {
      // e: { type: 'sale:deleted' | 'sale:added' | 'deleted:changed' }
      broadcast(e || { type: 'deleted:changed' });
      // local update too
      setTimeout(renderDeletedTable, 60);
    };
  </script>
</body>

</html>