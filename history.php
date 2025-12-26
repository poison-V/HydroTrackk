<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>HydroTrack · History Logs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
      padding: 110px 20px 20px 20px;
      transition: margin-left 0.3s ease;
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
      .main-content {
        margin-left: 0;
        padding: 140px 12px 12px 12px;
        margin-top: 10vh;
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
            <div class="text-xs text-gray-500 -mt-1 hidden md:block">History Logs</div>
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
      <h2 class="text-lg font-bold text-emerald-700">History Logs</h2>
      <button id="clearHistory" class="btn-clear no-print">🗑️ Clear All Logs</button>
    </div>

    <div class="scroll-panel border border-emerald-100 rounded-lg">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-emerald-800">
            <th class="p-2 font-medium">Date</th>
            <th class="p-2 font-medium">Action</th>
            <th class="p-2 font-medium">Customer</th>
            <th class="p-2 font-medium">Details</th>
          </tr>
        </thead>
        <tbody id="historyTable"></tbody>
      </table>
    </div>
  </main>

  <script>
    const HISTORY_KEY = 'hydro_history_v1';
    const SALES_KEY = 'hydro_sales_v1';
    const DELETE_KEY = 'hydro_deleted_sales_v1';

    // Sidebar active highlight
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

      const links = document.querySelectorAll('.sidebar-link');
      const currentPage = window.location.pathname.split("/").pop();
      links.forEach(link => {
        link.classList.toggle('active', link.getAttribute('href') === currentPage);
      });

      // Cashier sync
      const CASHIER_KEY = 'hydro_cashier_v1';
      const cashierInputTop = document.getElementById('cashierInputTop');
      const cashierDisplay = document.getElementById('cashierNameDisplay');
      const saved = localStorage.getItem(CASHIER_KEY);
      if (saved) {
        if (cashierInputTop) cashierInputTop.value = saved;
        if (cashierDisplay) cashierDisplay.textContent = saved;
      }

      // Cashier management buttons
      function setCashierName(v) {
        if (!v) {
          localStorage.removeItem(CASHIER_KEY);
          if (cashierDisplay) cashierDisplay.textContent = '—';
          return;
        }
        if (cashierDisplay) cashierDisplay.textContent = v;
      }

      // Sync cashier name across tabs
      window.addEventListener('storage', (e) => {
        if (e.key === CASHIER_KEY) {
          const newName = e.newValue || '—';
          const cashierDisplay = document.getElementById('cashierNameDisplay');
          const cashierInputTop = document.getElementById('cashierInputTop');
          if (cashierDisplay) cashierDisplay.textContent = newName;
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
    });

    // Load History Logs
    function loadHistory() {
      const history = JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];
      const deleted = JSON.parse(localStorage.getItem(DELETE_KEY)) || [];
      const sales = JSON.parse(localStorage.getItem(SALES_KEY)) || [];
      const combined = [];

      sales.forEach(s => {
        combined.push({ date: s.date, action: s.type, customer: s.customer, details: `${s.qty || ''} ${s.size || ''}` });
      });
      deleted.forEach(d => {
        combined.push({ date: d.deletedAt || d.date, action: 'Deleted', customer: d.customer, details: `${d.qty || ''} ${d.size || ''}` });
      });
      history.forEach(h => combined.push(h));

      combined.sort((a, b) => new Date(b.date) - new Date(a.date));

      const tbody = document.getElementById('historyTable');
      tbody.innerHTML = combined.map(h => `
      <tr class="border-t hover:bg-emerald-50 transition">
        <td class="p-2">${new Date(h.date).toLocaleString()}</td>
        <td class="p-2">${h.action || '—'}</td>
        <td class="p-2">${h.customer || '—'}</td>
        <td class="p-2">${h.details || '—'}</td>
      </tr>
  `).join('');
    }

    // Clear history
    document.getElementById('clearHistory').addEventListener('click', () => {
      if (!confirm('Are you sure you want to clear all history logs?')) return;
      localStorage.removeItem(HISTORY_KEY);
      loadHistory();
    });

    // Real-time update listener
    window.addEventListener('storage', e => {
      if ([HISTORY_KEY, SALES_KEY, DELETE_KEY].includes(e.key)) {
        loadHistory();
      }
    });

    // Initial load + auto-refresh every 2 seconds
    document.addEventListener('DOMContentLoaded', () => {
      loadHistory();
      setInterval(loadHistory, 2000);
    });
  </script>

</body>

</html>