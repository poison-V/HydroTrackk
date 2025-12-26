<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>HydroTrack · Daily Sales Report</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="styles.css" />
  <script src="libs/tailwindcss.js"></script>
  <script src="libs/qrcode.min.js"></script>
  <script src="libs/html5-qrcode.min.js"></script>
  <style>
    body {
      font-family: system-ui, sans-serif;
      background: linear-gradient(to bottom right, #ecfdf5, #cffafe);
      color: #0f172a;
    }

    .layout {
      display: flex;
      min-height: 100vh;
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
      margin-bottom: 12px;
      padding: 6px;
      border-radius: 6px;
      font-weight: 500;
      font-size: 0.8rem;
      color: #374151;
      text-decoration: none;
    }

    .sidebar a.active {
      background: #4b5563;
      color: white;
    }

    .sidebar a.active,
    .sidebar a:hover {
      background: #4b5563;
      color: white;
    }

    .main {
      margin-left: 150px;
      padding: 110px 24px 24px 24px;
      flex: 1;
      transition: margin-left 0.3s ease;
    }

    .report-card {
      background: white;
      border-radius: 12px;
      padding: 14px;
      box-shadow: 0 8px 20px rgba(2, 6, 23, 0.06);
      margin-top: 10vh;
    }

    .scroll-panel {
      max-height: 48vh;
      overflow: auto;
    }

    .customer-chip {
      cursor: pointer;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: 1px solid #eef2f1;
    }

    .chip-debt {
      background: #fff;
    }

    .chip-returned {
      background: #f0fdf4;
    }

    .chip-refill {
      background: #f0f9ff;
    }

    .chip-buyed {
      background: #fef2f2;
    }

    .muted {
      color: #6b7280;
      font-size: 0.9rem;
    }

    .small {
      font-size: 0.85rem;
    }

    .legend {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .modal {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(2, 6, 23, 0.45);
      z-index: 70;
    }

    .modal.active {
      display: flex;
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

    /* Customer Dropdown */
    #customerDropdown {
      max-height: 300px;
      overflow-y: auto;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    #customerDropdown.show {
      display: block;
    }

    .customer-item {
      padding: 12px 16px;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
      transition: background 0.2s;
    }

    .customer-item:hover {
      background: #f0fdf4;
    }

    .customer-item .name {
      font-weight: 600;
      color: #065f46;
      margin-bottom: 2px;
    }

    .customer-item .details {
      font-size: 12px;
      color: #6b7280;
    }

    /* Expandable Customer Info */
    .customer-row {
      cursor: pointer;
      transition: background 0.2s;
    }

    .customer-row:hover {
      background: rgba(16, 185, 129, 0.05);
    }

    .customer-row.expanded {
      background: rgba(16, 185, 129, 0.1);
    }

    .customer-details-expand {
      display: none;
      background: #f0fdf4;
      border-left: 3px solid #10b981;
      padding: 12px;
      margin-top: 6px;
      border-radius: 6px;
      animation: slideDown 0.2s ease-out;
    }

    .customer-details-expand.show {
      display: block;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 4px 0;
      font-size: 13px;
    }

    .detail-label {
      font-weight: 600;
      color: #065f46;
    }

    .detail-value {
      color: #374151;
    }

    @media (max-width:768px) {
      .main {
        margin-left: 0;
        padding: 140px 12px 12px 12px;
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

      .summary-grid,
      .grid {
        grid-template-columns: 1fr !important;
      }

      nav .flex {
        flex-wrap: wrap;
      }

      .report-card {
        margin-bottom: 1rem;
      }
    }

    @media print {
      .no-print {
        display: none !important;
      }
    }

    table thead th {
      position: sticky;
      top: 0;
      background-color: #ecfeff;
      z-index: 10;
    }

    .text-xxs {
      font-size: 0.7rem;
    }

    .expand-area {
      background: #fbfffe;
      border-left: 4px solid #34d399;
      margin-top: 6px;
      padding: 10px;
      border-radius: 6px;
    }

    .btn {
      padding: 6px 10px;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn-return {
      background: #059669;
      color: white;
      border: none;
    }

    .btn-close {
      background: #e5e7eb;
      border: none;
    }

    .clickable-name:hover {
      text-decoration: underline;
      color: #065f46;
    }

    .text-xxs-muted {
      font-size: 0.75rem;
      color: #6b7280;
    }

    .topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 50;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(4px);
      border-bottom: 1px solid #eef2f1;
    }

    .content-wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 12px 18px;
      display: flex;
      gap: 12px;
      align-items: center;
      justify-content: space-between;
    }

    .table-refill {
      background: rgba(240, 249, 255, 0.6);
    }

    .table-borrow {
      background: rgba(255, 249, 230, 0.6);
    }

    .table-returned {
      background: rgba(240, 253, 244, 0.6);
    }

    .active-name {
      background: linear-gradient(90deg, #ecfdf5, #fbfffe);
      border-left: 4px solid #34d399;
      padding-left: 6px;
    }

    /* Low Stock Warning Toast */
    .stock-warning-toast {
      position: fixed;
      top: 80px;
      right: 20px;
      background: #FEF3C7;
      border-left: 4px solid #F59E0B;
      border-radius: 8px;
      padding: 16px 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 9999;
      max-width: 350px;
      animation: slideInRight 0.3s ease-out;
      display: none;
    }

    .stock-warning-toast.show {
      display: block;
    }

    .stock-warning-toast .close-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      background: transparent;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: #92400E;
    }

    /* Stock Management Cards */
    .stock-card {
      background: white;
      border-radius: 12px;
      padding: 12px 16px;
      border: 1px solid #e2e8f0;
      transition: all 0.2s ease;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .stock-card:hover {
      border-color: #10b981;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
      transform: translateY(-2px);
    }

    .stock-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .stock-type {
      font-weight: 700;
      color: #1e293b;
      font-size: 0.95rem;
    }

    .stock-count {
      font-size: 1.25rem;
      font-weight: 800;
      color: #059669;
    }

    .stock-bar-bg {
      height: 6px;
      background: #f1f5f9;
      border-radius: 10px;
      overflow: hidden;
    }

    .stock-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #10b981, #34d399);
      border-radius: 10px;
      transition: width 0.5s ease;
    }

    .stock-status {
      font-size: 0.75rem;
      color: #64748b;
      display: flex;
      justify-content: space-between;
    }

    .stock-card.low-stock {
      border-left: 4px solid #ef4444;
    }

    .stock-card.low-stock .stock-count {
      color: #ef4444;
    }

    .stock-card.low-stock .stock-bar-fill {
      background: #ef4444;
    }

    @keyframes slideInRight {
      from {
        transform: translateX(400px);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  </style>
</head>

<body>
  <!-- Mobile Menu Button -->
  <button class="mobile-menu-btn no-print" id="mobileMenuBtn" aria-label="Toggle Menu">☰</button>

  <!-- Sidebar Overlay -->
  <div class="sidebar-overlay no-print" id="sidebarOverlay"></div>

  <!-- Low Stock Warning Toast -->
  <div class="stock-warning-toast no-print" id="stockWarningToast">
    <button class="close-btn" onclick="document.getElementById('stockWarningToast').classList.remove('show')">✖</button>
    <div style="display:flex; align-items:start; gap:12px;">
      <div style="font-size:24px;">⚠️</div>
      <div>
        <div style="font-weight:700; color:#92400E; margin-bottom:4px;">Low Stock Alert!</div>
        <div id="stockWarningContent" style="font-size:14px; color:#78350F;"></div>
      </div>
    </div>
  </div>

  <!-- NAV -->
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm shadow-md">
    <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 flex flex-col md:flex-row items-start md:items-center gap-3">
      <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
        <div class="flex items-center gap-3">
          <span class="text-2xl"><img src="icons/image-removebg-preview (1).png"
              class="w-12 h-12 object-contain" /></span>
          <div>
            <div class="text-emerald-700 font-bold text-lg">HydroTrack</div>
            <div class="text-xs text-gray-500 -mt-1 hidden md:block">Daily Sales Report</div>
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
      class="sidebar-link active btn-sales flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
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

  <main class="main">
    <!-- Top summary -->
    <div class="grid grid-cols-5 gap-4 mb-4">
      <div class="report-card">
        <h2 class="text-emerald-700 font-bold text-lg mb-3">Refill</h2>
        <div id="refillList" class="scroll-panel"></div>
      </div>

      <div class="report-card">
        <h2 class="text-yellow-600 font-bold text-lg mb-3">Borrowed</h2>
        <div id="borrowList" class="scroll-panel"></div>
      </div>

      <div class="report-card">
        <h2 class="text-red-600 font-bold text-lg mb-3">Bought</h2>
        <div id="buyedList" class="scroll-panel"></div>
      </div>

      <div class="report-card">
        <h2 class="text-cyan-700 font-bold text-lg mb-3">Returned</h2>
        <div id="returnedList" class="scroll-panel"></div>
      </div>

      <div class="report-card">
        <h3 class="font-bold">Totals</h3>
        <div class="mt-2 space-y-2 text-sm">
          <div class="flex justify-between">
            <div class="muted">Total Sales</div>
            <div id="totalSalesDS">₱0.00</div>
          </div>
          <div class="flex justify-between">
            <div class="muted">Refill Count</div>
            <div id="refillCount">0</div>
          </div>
          <div class="flex justify-between">
            <div class="muted">Borrowed Count</div>
            <div id="borrowCount">0</div>
          </div>
          <div class="flex justify-between">
            <div class="muted">Bought Count</div>
            <div id="buyedCount">0</div>
          </div>
          <div class="flex justify-between">
            <div class="muted">Returned Count</div>
            <div id="returnedCount">0</div>
          </div>
        </div>
        <div class="mt-3 grid gap-2">
          <button id="computeAllBtn" class="btn" style="background:#059669;color:white">Compute</button>
          <button id="exportCsvBtn" class="btn" style="background:#f3f4f6">Export CSV</button>
          <button id="refreshBtn" class="btn" style="background:#06b6d4;color:white">Refresh</button>
          <button id="clearAllBtn" class="btn" style="background:#ef4444;color:white">Clear All & Reset</button>
        </div>
      </div>
    </div>

    <!-- DEBT AREA -->
    <div class="grid lg:grid-cols-4 md:grid-cols-1 gap-4 mb-4">
      <!-- Debtors -->
      <div class="report-card">
        <div class="flex justify-between items-center mb-2">
          <div>
            <h3 class="font-bold text-xl">Debtors</h3>
            <div class="text-xs muted">Customers who still owe</div>
          </div>
        </div>
        <div id="debtorsList" class="scroll-panel" aria-live="polite"></div>
        <div class="mt-3 flex gap-2">
          <button id="manualAddDebtBtn" class="btn" style="background:#f3f4f6">+ Add Debt</button>
          <button id="clearAllBtn" class="btn" style="background:#f3f4f6">Clear All</button>
        </div>
      </div>

      <!-- Not Fully Paid -->
      <div class="report-card">
        <h3 class="font-bold text-xl">Not Fully Paid</h3>
        <div class="text-xs muted mb-2">Partial payments</div>
        <div id="partialList" class="scroll-panel"></div>
      </div>

      <!-- Fully Paid -->
      <div class="report-card">
        <h3 class="font-bold text-xl">Fully Paid</h3>
        <div class="text-xs muted mb-2">Paid in full</div>
        <div id="paidList" class="scroll-panel"></div>
      </div>

      <!-- Total Balance -->
      <div class="report-card">
        <h3 class="font-bold">Total Balance</h3>
        <div class="mt-2 space-y-2 text-sm">
          <div class="flex justify-between">
            <div>Debt Balance</div>
            <div id="debtBalance">0</div>
          </div>
          <div class="flex justify-between">
            <div>Not Fully Paid Balance</div>
            <div id="partialBalance">0</div>
          </div>
          <div class="flex justify-between">
            <div>Fully Paid Balance</div>
            <div id="paidBalance">0</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Availability Section -->
    <div class="report-card mb-4">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-xl font-bold text-emerald-700">Stock Availability</h3>
          <p class="text-xs text-gray-500">Real-time stock levels from database</p>
        </div>
      </div>
      <div id="stockManagementList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stock cards will be rendered here -->
      </div>
    </div>

    <!-- All Transactions filter table -->
    <div class="report-card mb-4">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-3 gap-3">
        <div>
          <h3 class="text-lg font-bold">All Transactions (Search & Filter)</h3>
          <div class="text-xs muted">Filtered view — includes Delivery column</div>
        </div>
        <div class="flex flex-wrap gap-2 no-print w-full md:w-auto">
          <input id="searchBoxFilter" type="text" placeholder="Search name / phone / size"
            class="border rounded px-2 py-1 text-sm flex-1 md:flex-none" style="min-width:150px;" />
          <select id="typeFilter" class="border rounded px-2 py-1 text-sm flex-1 md:flex-none">
            <option value="">All Types</option>
            <option value="Refill">Refill</option>
            <option value="Borrow">Borrow</option>
            <option value="Returned">Returned</option>
          </select>
          <div class="flex gap-2 no-print">
            <button id="exportCsvBtn2" class="btn" style="background:#f3f4f6">Export CSV</button>
            <button id="refreshBtn2" class="btn" style="background:#06b6d4;color:white">Refresh</button>
          </div>
        </div>
      </div>

      <div class="scroll-panel border border-emerald-100 rounded-lg">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-emerald-800">
              <th class="p-2 font-medium">Invoice</th>
              <th class="p-2 font-medium">Customer</th>
              <th class="p-2 font-medium">Address</th>
              <th class="p-2 font-medium">Phone</th>
              <th class="p-2 font-medium">Type</th>
              <th class="p-2 font-medium">Item</th>
              <th class="p-2 font-medium">Qty</th>
              <th class="p-2 font-medium">Unit</th>
              <th class="p-2 font-medium">Total</th>
              <th class="p-2 font-medium">Payment</th>
              <th class="p-2 font-medium">Delivery</th>
              <th class="p-2 font-medium">Returned</th>
              <th class="p-2 font-medium">Date</th>
              <th class="p-2 font-medium">Time</th>
              <th class="p-2 font-medium no-print">Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody"></tbody>
        </table>
      </div>

      <div id="details" class="mt-6 bg-white rounded-xl shadow p-4 hidden">
        <h3 class="text-lg font-semibold text-emerald-700 mb-2">Customer Details</h3>
        <div id="detailsContent" class="text-sm"></div>
      </div>
    </div>

    <!-- Cash & Stock Monitoring -->
    <div class="report-card">
      <h3 class="font-bold text-xl mb-1">Cash Monitoring</h3>
      <div class="grid grid-cols-1 gap-2 mb-3">
        <div>
          <label class="text-xs">Money taken from money box</label>
          <input id="moneyOutAmount" type="number" min="0" class="w-full rounded border px-3 py-1"
            placeholder="Amount taken" />
        </div>
        <div>
          <label class="text-xs">Return to money box</label>
          <input id="moneyReturnAmount" type="number" min="0" class="w-full rounded border px-3 py-1"
            placeholder="Amount returned" />
        </div>
        <div>
          <label class="text-xs">Total cash in money box (PHP)</label>
          <input id="totalCashInput" type="number" min="0" class="w-full rounded border px-3 py-1"
            placeholder="Cash amount" />
          <div class="text-xs muted mt-1">Enter cash totals (bills/notes)</div>
        </div>
        <div>
          <label class="text-xs">Total coins (PHP)</label>
          <input id="totalCoins" type="number" min="0" class="w-full rounded border px-3 py-1"
            placeholder="Coin amount" />
          <div class="text-xs muted mt-1">Enter coin totals (5,10,1 PHP combined)</div>
        </div>
        <div class="flex gap-2">
          <button id="applyMoneyBoxBtn" class="btn" style="background:#059669;color:white">Apply</button>
          <button id="resetMoneyBoxBtn" class="btn" style="background:#f3f4f6">Reset</button>
        </div>
      </div>

      <hr class="my-2" />
      <div class="text-sm small muted">
        <div class="mt-2 space-y-2 text-sm">
          <div class="flex justify-between">
            <div>Total Sales</div>
            <div id="cardTotalSales">₱0.00</div>
          </div>
          <div class="flex justify-between">
            <div>Total Expenses</div>
            <div id="totalexpense">₱0.00</div>
          </div>
          <div class="flex justify-between">
            <div>Total Coins</div>
            <div id="totalcoins">₱0.00</div>
          </div>
          <div class="flex justify-between">
            <div>Total Cash</div>
            <div id="totalCashDisplay">₱0.00</div>
          </div>
          <div class="flex justify-between mt-1">
            <div>Net Cash</div>
            <div id="netCashDisplay">₱0.00</div>
          </div>
          <div class="flex justify-between">
            <div>Amount Paid</div>
            <div id="totalAmountPaidDisplay">₱0.00</div>
          </div>
        </div>

        <!-- Collection Records Container -->
        <div class="mt-4 pt-3 border-t border-gray-100">
          <div class="flex justify-between items-center mb-2">
            <h4 class="font-bold text-xs uppercase tracking-wider text-emerald-800">Collection Records (Today)</h4>
            <div class="text-[10px] text-gray-400" id="paymentRecordCount">0 records</div>
          </div>
          <div id="paymentLogs" class="scroll-panel rounded-lg bg-gray-50/50 p-2"
            style="max-height: 180px; min-height: 60px; font-size: 11px; border: 1px solid #f1f5f9;">
            <div class="text-center text-gray-400 italic py-4">No collections recorded yet</div>
          </div>
        </div>
      </div>
  </main>

  <!-- Payment modal -->
  <div id="paymentModal" class="modal" aria-hidden="true">
    <div class="bg-white rounded-xl p-6 shadow-xl w-11/12 max-w-md">
      <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-bold text-emerald-700">Collect Payment</h3>
        <button id="closePaymentModal" class="text-gray-600">✖</button>
      </div>

      <div id="paymentModalBody" class="text-sm"></div>

      <div class="mt-3 grid grid-cols-2 gap-2">
        <button id="applyPaymentBtn" class="bg-emerald-600 text-white py-2 rounded">Apply Payment</button>
        <button id="cancelPaymentBtn" class="bg-gray-100 py-2 rounded">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Manual debt modal -->
  <div id="manualDebtModal" class="modal" aria-hidden="true">
    <div class="bg-white rounded-xl p-6 shadow-xl w-11/12 max-w-md">
      <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-bold text-emerald-700">Add Manual Debt</h3>
        <button id="closeManualDebtModal" class="text-gray-600">✖</button>
      </div>

      <div id="manualDebtBody" class="text-sm">
        <div class="mb-2">
          <label class="text-xs">Customer name</label>
          <input id="manualDebtCustomer" class="w-full rounded border px-3 py-1" />
        </div>
        <div class="mb-2">
          <label class="text-xs">Phone</label>
          <input id="manualDebtPhone" class="w-full rounded border px-3 py-1" />
        </div>
        <div class="mb-2">
          <label class="text-xs">Amount</label>
          <input id="manualDebtAmount" type="number" min="0" class="w-full rounded border px-3 py-1" />
        </div>
        <div class="mb-2">
          <label class="text-xs">Notes (optional)</label>
          <input id="manualDebtNotes" class="w-full rounded border px-3 py-1" />
        </div>
      </div>

      <div class="mt-3 grid grid-cols-2 gap-2">
        <button id="saveManualDebtBtn" class="bg-emerald-600 text-white py-2 rounded">Save Debt</button>
        <button id="cancelManualDebtBtn" class="bg-gray-100 py-2 rounded">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    /* ---------------------------
      Daily Sales Report - hydro_ keys
      --------------------------- */

    const STOCK_KEY = 'hydro_stock_v1';
    const SALES_KEY = 'hydro_sales_v1';
    const CASHIER_KEY = 'hydro_cashier_v1';
    const EXPENSES_KEY = 'hydro_expenses_v1';
    const DEBTS_KEY = 'hydro_debts_v1';
    const PAID_DEBTS_KEY = 'hydro_paid_debts_v1';
    const MONEYBOX_KEY = 'hydro_moneybox_v1';
    const TOTAL_SALES_KEY = 'hydro_total_sales_v1';
    const DELETED_KEY = 'hydro_deleted_sales_v1';
    const HISTORY_KEY = 'hydro_history_v1';
    const PAYMENTS_KEY = 'hydro_payments_v1';

    function safeParse(key, fallback) { try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch (e) { return fallback; } }
    function save(key, val) { localStorage.setItem(key, JSON.stringify(val)); }
    function uid() { return 'id' + Date.now() + Math.floor(Math.random() * 9000); }
    function money(v) { return '₱' + (Number(v || 0)).toFixed(2); }
    function fmtDate(iso) { try { return new Date(iso).toLocaleString(); } catch (e) { return iso || ''; } }
    function escapeHtml(s) { return String(s || '').replace(/[&<>"'`]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '`': '&#96;' })[c]); }
    function getDeliveryType(sale) { return sale.deliveryType || sale.delivery || 'Pickup'; }

    /* defaults - STOCK STARTS AT 0 */
    if (!localStorage.getItem(STOCK_KEY)) save(STOCK_KEY, { '20LiterSlim': 0, '20LiterRound': 0, '10Liter': 0, '5Liter': 0 });
    if (!localStorage.getItem(SALES_KEY)) save(SALES_KEY, []);
    if (!localStorage.getItem(EXPENSES_KEY)) save(EXPENSES_KEY, []);
    if (!localStorage.getItem(DEBTS_KEY)) save(DEBTS_KEY, []);
    if (!localStorage.getItem(PAID_DEBTS_KEY)) save(PAID_DEBTS_KEY, []);
    if (!localStorage.getItem(MONEYBOX_KEY)) save(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0 });
    if (!localStorage.getItem(DELETED_KEY)) save(DELETED_KEY, []);
    if (!localStorage.getItem(PAYMENTS_KEY)) save(PAYMENTS_KEY, []);

    /* Core helpers */
    function getSales() { return safeParse(SALES_KEY, []); }
    function setSales(a) { save(SALES_KEY, a); }
    function getStock() { return safeParse(STOCK_KEY, { '20LiterSlim': 0, '20LiterRound': 0, '10Liter': 0, '5Liter': 0 }); }
    function setStock(a) { save(STOCK_KEY, a); checkLowStock(); }
    function getDebts() { return safeParse(DEBTS_KEY, []); }
    function setDebts(a) { save(DEBTS_KEY, a); }
    function getDeleted() { return safeParse(DELETED_KEY, []); }
    function pushDeleted(item) { const arr = getDeleted(); arr.push(item); save(DELETED_KEY, arr); }
    function getPayments() { return safeParse(PAYMENTS_KEY, []); }
    function setPayments(a) { save(PAYMENTS_KEY, a); }

    /* Add to History Log */
    function addHistoryLog(logEntry) {
      const history = safeParse(HISTORY_KEY, []);
      history.push(logEntry);
      save(HISTORY_KEY, history);
    }

    /* Low Stock Warning System */
    const LOW_STOCK_THRESHOLD = 5;
    const STOCK_WARNING_SHOWN_KEY = 'hydro_stock_warning_shown_v1';

    /* Toggle Customer Details Dropdown */
    let currentOpenDetails = null;

    function toggleCustomerDetails(rowId, detailsElement) {
      const row = document.getElementById(rowId);
      if (!row || !detailsElement) return;

      // If clicking the same row, toggle it
      if (currentOpenDetails === detailsElement) {
        detailsElement.classList.toggle('show');
        row.classList.toggle('expanded');
        if (!detailsElement.classList.contains('show')) {
          currentOpenDetails = null;
        }
      } else {
        // Close any other open details
        if (currentOpenDetails) {
          currentOpenDetails.classList.remove('show');
          const prevRow = currentOpenDetails.previousElementSibling;
          if (prevRow) prevRow.classList.remove('expanded');
        }
        // Open this one
        detailsElement.classList.add('show');
        row.classList.add('expanded');
        currentOpenDetails = detailsElement;
      }
    }

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
      if (!currentOpenDetails) return;

      // Check if click is outside any customer row or details
      const isClickInsideCustomer = e.target.closest('.customer-row') || e.target.closest('.customer-details-expand');

      if (!isClickInsideCustomer) {
        currentOpenDetails.classList.remove('show');
        const prevRow = currentOpenDetails.previousElementSibling;
        if (prevRow) prevRow.classList.remove('expanded');
        currentOpenDetails = null;
      }
    });

    // Close dropdown on double click of the same row
    document.addEventListener('dblclick', (e) => {
      const row = e.target.closest('.customer-row');
      if (row && currentOpenDetails) {
        const details = row.nextElementSibling;
        if (details && details.classList.contains('customer-details-expand') && details.classList.contains('show')) {
          details.classList.remove('show');
          row.classList.remove('expanded');
          currentOpenDetails = null;
        }
      }
    });

    function checkLowStock() {
      const stock = getStock();
      const lowStockItems = [];
      const labels = {
        '20LiterSlim': '20L Slim',
        '20LiterRound': '20L Round',
        '10Liter': '10L',
        '5Liter': '5L'
      };

      Object.keys(stock).forEach(key => {
        const qty = Number(stock[key] || 0);
        if (qty <= LOW_STOCK_THRESHOLD && qty > 0) {
          lowStockItems.push(`${labels[key]}: ${qty} pieces left`);
        } else if (qty === 0) {
          lowStockItems.push(`${labels[key]}: OUT OF STOCK`);
        }
      });

      if (lowStockItems.length > 0) {
        showStockWarning(lowStockItems);
      }
    }

    function showStockWarning(items) {
      const toast = document.getElementById('stockWarningToast');
      const content = document.getElementById('stockWarningContent');

      if (!toast || !content) return;

      const warningKey = items.join('|');
      const lastShown = localStorage.getItem(STOCK_WARNING_SHOWN_KEY);

      if (lastShown !== warningKey) {
        content.innerHTML = items.map(item =>
          `<div style="margin-bottom:4px;">⚠️ ${escapeHtml(item)}</div>`
        ).join('') + '<div style="margin-top:8px; font-weight:600;">Please restock soon!</div>';

        toast.classList.add('show');
        localStorage.setItem(STOCK_WARNING_SHOWN_KEY, warningKey);

        setTimeout(() => {
          toast.classList.remove('show');
        }, 10000);
      }
    }

    // Data fetching
    async function loadSalesFromServer() {
      try {
        const response = await fetch('api/get_sales.php');
        const salesData = await response.json();
        // Fallback or merge if needed, but we prioritize Server Data now
        // For partial backward compat, we could merge, but migration implies shift.
        // Let's just USE the server data as the source of truth for calculations.
        // We will override the local 'getSales' helper or just pass data to render functions.
        return salesData;
      } catch (e) {
        console.error('Failed to load sales from server', e);
        return [];
      }
    }

    /* Bridge function for existing calls */
    function renderAll() { updateUI(); }

    /* UI: stock management list (Unified with Home) */
    async function renderStockManagementList() {
      const wrap = document.getElementById('stockManagementList');
      if (!wrap) return;

      try {
        const res = await fetch('api/get_stock_summary.php');
        const result = await res.json();
        if (!result.success) throw new Error(result.message);

        const s = result.data;
        wrap.innerHTML = '';

        const items = [
          { key: '20LiterSlim', label: '20L Slim', max: 50 },
          { key: '20LiterRound', label: '20L Round', max: 50 },
          { key: '10Liter', label: '10L', max: 30 },
          { key: '5Liter', label: '5L', max: 20 }
        ];

        items.forEach(it => {
          const data = s[it.key] || { in: 0, out: 0, borrowed: 0 };
          const available = data.in || 0;
          const percentage = Math.min(100, (available / it.max) * 100);
          const isLow = available <= (it.max * 0.2);

          const card = document.createElement('div');
          card.className = `stock-card ${isLow ? 'low-stock' : ''}`;
          card.innerHTML = `
            <div class="stock-card-header">
              <span class="stock-type">${it.label}</span>
              <span class="stock-count">${available}</span>
            </div>
            <div class="stock-bar-bg">
              <div class="stock-bar-fill" style="width: ${percentage}%"></div>
            </div>
            <div class="stock-status">
              <span>Available</span>
              <span class="${isLow ? 'text-red-500 font-bold' : ''}">${isLow ? 'LOW STOCK' : 'Healthy'}</span>
            </div>
          `;
          wrap.appendChild(card);
        });

        // Sync local storage copy for legacy code if needed
        const legacyStock = {};
        items.forEach(it => legacyStock[it.key] = s[it.key]?.in || 0);
        save(STOCK_KEY, legacyStock);

      } catch (e) {
        console.error('Stock Sync Error:', e);
        wrap.innerHTML = `<div class="text-center text-red-500 py-4 text-xs font-semibold">❌ Stock Sync Failed</div>`;
      }
    }

    /* Core Update Function */
    async function updateUI() {
      // Load sales from server instead of localStorage
      // const sales = await loadSalesFromServer(); 
      // FALLBACK: Use local storage until API is ready
      const sales = getSales();

      // We also need Deleted sales, debts, etc. For now we only migrated SALES.
      // Other data (Debts, MoneyBox) still on LocalStorage for this iteration unless requested.

      const debts = getDebts();
      const paidDebts = safeParse(PAID_DEBTS_KEY, []);

      // Clear lists
      document.getElementById('refillList').innerHTML = '';
      document.getElementById('borrowList').innerHTML = '';
      document.getElementById('returnedList').innerHTML = '';
      document.getElementById('debtorsList').innerHTML = '';
      document.getElementById('partialList').innerHTML = '';
      document.getElementById('paidList').innerHTML = '';
      document.getElementById('tableBody').innerHTML = '';

      let totalSales = 0;
      let refillCount = 0;
      let borrowCount = 0;
      let buyedCount = 0;
      let returnedCount = 0;

      // ... rest of logic uses 'sales' variable ...

      // 1. Process Sales (Refill, Borrow, Returned)
      // The original renderAll filtered sales into refill, borrowed, returned lists
      // We will adapt the existing render functions to use the new 'sales' array.
      const refill = sales.filter(s => s.type === 'Refill');
      const borrowed = sales.filter(s => (s.type === 'Borrow' && !s.returned));
      const buyed = sales.filter(s => (s.type === 'Buy Gallon' && !s.returned));
      const returned = sales.filter(s => s.returned);

      renderRefillList(refill);
      renderBorrowList(borrowed);
      renderBuyedList(buyed);
      renderReturnedList(returned);
      renderTransactionsTable(sales); // This function needs to be defined or adapted if not present

      // Calculate counts for the summary cards
      refill.forEach(s => {
        totalSales += Number(s.total || 0);
        refillCount += Number(s.qty || 1);
      });
      borrowed.forEach(s => {
        totalSales += Number(s.total || 0);
        borrowCount += Number(s.qty || 1);
      });
      buyed.forEach(s => {
        totalSales += Number(s.total || 0);
        buyedCount += Number(s.qty || 1);
      });
      returned.forEach(s => {
        returnedCount += Number(s.qty || 1);
      });

      // Update counters
      if (document.getElementById('totalSalesDS')) document.getElementById('totalSalesDS').textContent = money(totalSales);
      document.getElementById('refillCount').textContent = refillCount;
      document.getElementById('borrowCount').textContent = borrowCount;
      document.getElementById('buyedCount').textContent = buyedCount;
      document.getElementById('returnedCount').textContent = returnedCount;

      /* 2. Debts & Payments (LocalStorage) - passing sales for details */
      renderDebtsLists(sales);

      // Update stock display (Unified async)
      renderStockManagementList();

      renderTable(sales);
    }

    function renderBuyedList(list) {
      const wrap = document.getElementById('buyedList');
      if (!wrap) return; wrap.innerHTML = '';
      if (!list.length) { wrap.innerHTML = '<div class="muted small">No gallon bought transactions yet</div>'; return; }

      list.slice().reverse().forEach(sale => {
        const rowId = 'buyed-' + (sale.id || Date.now() + Math.random());
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-1';

        const row = document.createElement('div');
        row.className = 'customer-chip chip-buyed customer-row';
        row.id = rowId;
        row.innerHTML = `
      <div style="flex:1">
        <div class="font-semibold">${escapeHtml(sale.customer || 'Unknown')}</div>
        <div class="text-xs muted">${escapeHtml(sale.phone || '')} • ${fmtDate(sale.date)}</div>
      </div>
      <div class="text-sm">
        <div>${escapeHtml(sale.size || sale.item || '')}</div>
        <div class="muted text-xxs">${money(sale.total)}</div>
      </div>
    `;

        const details = document.createElement('div');
        details.className = 'customer-details-expand';
        details.innerHTML = `
      <div class="detail-row"><span class="detail-label">Serial/Invoice:</span><span class="detail-value">${escapeHtml(sale.invoice || sale.id || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value">${escapeHtml(sale.customer || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">${escapeHtml(sale.phone || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Address:</span><span class="detail-value">${escapeHtml(sale.address || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Type:</span><span class="detail-value">${escapeHtml(sale.type || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Delivery:</span><span class="detail-value">${escapeHtml(sale.deliveryOption || sale.delivery || 'Pickup')}</span></div>
      <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value">${escapeHtml(sale.paymentMethod || sale.payment || 'Cash')}</span></div>
      <div class="detail-row"><span class="detail-label">Gallon Size:</span><span class="detail-value">${escapeHtml(sale.size || sale.item || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Quantity:</span><span class="detail-value">${escapeHtml(String(sale.qty || 1))}</span></div>
      <div class="detail-row"><span class="detail-label">Total:</span><span class="detail-value">${money(sale.total)}</span></div>
      <div class="mt-2 flex gap-2">
        ${!sale.returned ? `<button class="btn btn-return px-3 py-1 rounded bg-emerald-600 text-white text-xs" data-id="${sale.id}">Return</button>` : ''}
        <button class="btn-delete-detail px-3 py-1 rounded bg-red-600 text-white text-xs" data-id="${sale.id}" data-type="buyed">Delete</button>
      </div>
    `;

        row.addEventListener('click', () => toggleCustomerDetails(rowId, details));

        // Add return button handler
        const returnBtn = details.querySelector('.btn-return');
        if (returnBtn) {
          returnBtn.addEventListener('click', async (ev) => {
            ev.stopPropagation();
            if (!confirm('Mark this transaction as returned? This will add it back to stock.')) return;
            const ok = await markSaleReturned(sale.id);
            if (ok) alert('Marked returned.');
          });
        }

        // Add delete button handler
        const deleteBtn = details.querySelector('.btn-delete-detail');
        if (deleteBtn) {
          deleteBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (!confirm('Delete this transaction? It will be moved to Deleted History.')) return;
            deleteSaleById(sale.id);
            updateUI(); // Refresh the list
            alert('Deleted and archived.');
          });
        }

        wrapper.appendChild(row);
        wrapper.appendChild(details);
        wrap.appendChild(wrapper);
      });
    }

    /* The rest of rendering functions are identical to the ones in home; reusing them here: */
    function renderRefillList(list) {
      const wrap = document.getElementById('refillList');
      if (!wrap) return; wrap.innerHTML = '';
      if (!list.length) { wrap.innerHTML = '<div class="muted small">No refill transactions yet</div>'; return; }

      list.slice().reverse().forEach(sale => {
        const rowId = 'refill-' + (sale.id || Date.now() + Math.random());
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-1';

        const row = document.createElement('div');
        row.className = 'customer-chip chip-refill customer-row';
        row.id = rowId;
        row.innerHTML = `
      <div style="flex:1">
        <div class="font-semibold">${escapeHtml(sale.customer || 'Unknown')}</div>
        <div class="text-xs muted">${escapeHtml(sale.phone || '')} • ${fmtDate(sale.date)}</div>
      </div>
      <div class="text-sm">
        <div>${escapeHtml(sale.size || sale.item || '')}</div>
        <div class="muted text-xxs">${money(sale.total)}</div>
      </div>
    `;

        const details = document.createElement('div');
        details.className = 'customer-details-expand';
        details.innerHTML = `
      <div class="detail-row"><span class="detail-label">Serial/Invoice:</span><span class="detail-value">${escapeHtml(sale.invoice || sale.id || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value">${escapeHtml(sale.customer || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">${escapeHtml(sale.phone || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Address:</span><span class="detail-value">${escapeHtml(sale.address || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Type:</span><span class="detail-value">${escapeHtml(sale.type || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Delivery:</span><span class="detail-value">${escapeHtml(sale.deliveryOption || sale.delivery || 'Pickup')}</span></div>
      <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value">${escapeHtml(sale.paymentMethod || sale.payment || 'Cash')}</span></div>
      <div class="detail-row"><span class="detail-label">Gallon Size:</span><span class="detail-value">${escapeHtml(sale.size || sale.item || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Quantity:</span><span class="detail-value">${escapeHtml(String(sale.qty || 1))}</span></div>
      <div class="detail-row"><span class="detail-label">Total:</span><span class="detail-value">${money(sale.total)}</span></div>
      <div class="mt-2 flex gap-2">
        <button class="btn-delete-detail px-3 py-1 rounded bg-red-600 text-white text-xs" data-id="${sale.id}" data-type="refill">Delete</button>
      </div>
    `;

        row.addEventListener('click', () => toggleCustomerDetails(rowId, details));

        // Add delete button handler
        const deleteBtn = details.querySelector('.btn-delete-detail');
        if (deleteBtn) {
          deleteBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (!confirm('Delete this transaction? It will be moved to Deleted History.')) return;
            deleteSaleById(sale.id);
            renderAll();
            alert('Deleted and archived.');
          });
        }

        wrapper.appendChild(row);
        wrapper.appendChild(details);
        wrap.appendChild(wrapper);
      });
    }
    function renderBorrowList(list) {
      const wrap = document.getElementById('borrowList');
      if (!wrap) return; wrap.innerHTML = '';
      if (!list.length) { wrap.innerHTML = '<div class="muted small">No borrowed gallons currently</div>'; return; }

      list.slice().reverse().forEach(sale => {
        const rowId = 'borrow-' + (sale.id || Date.now() + Math.random());
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-1';

        const row = document.createElement('div');
        row.className = 'customer-chip chip-debt customer-row';
        row.id = rowId;
        row.innerHTML = `
      <div style="flex:1">
        <div class="font-semibold">${escapeHtml(sale.customer || 'Unknown')}</div>
        <div class="text-xs muted">${escapeHtml(sale.phone || '')} • ${fmtDate(sale.date)}</div>
      </div>
      <div style="text-align:right">
        <div class="font-semibold">${escapeHtml(sale.size || sale.item || '')}</div>
        <div class="text-xs muted">${money(sale.total)}</div>
      </div>
    `;

        const details = document.createElement('div');
        details.className = 'customer-details-expand';
        details.innerHTML = `
      <div class="detail-row"><span class="detail-label">Serial/Invoice:</span><span class="detail-value">${escapeHtml(sale.invoice || sale.id || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value">${escapeHtml(sale.customer || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">${escapeHtml(sale.phone || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Address:</span><span class="detail-value">${escapeHtml(sale.address || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Type:</span><span class="detail-value">${escapeHtml(sale.type || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Delivery:</span><span class="detail-value">${escapeHtml(sale.deliveryOption || sale.delivery || 'Pickup')}</span></div>
      <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value">${escapeHtml(sale.paymentMethod || sale.payment || 'Cash')}</span></div>
      <div class="detail-row"><span class="detail-label">Gallon Size:</span><span class="detail-value">${escapeHtml(sale.size || sale.item || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Quantity:</span><span class="detail-value">${escapeHtml(String(sale.qty || 1))}</span></div>
      <div class="detail-row"><span class="detail-label">Total:</span><span class="detail-value">${money(sale.total)}</span></div>
      <div class="mt-2 flex gap-2">
        ${(sale.type === 'Borrow' || sale.type === 'Buy Gallon') && !sale.returned ? `<button class="btn btn-return" data-id="${sale.id}" style="background:#059669;color:white;padding:6px 12px;border-radius:6px;font-size:12px;">Mark Returned</button>` : ''}
        <button class="btn-delete-detail px-3 py-1 rounded bg-red-600 text-white text-xs" data-id="${sale.id}" data-type="borrow">Delete</button>
      </div>
    `;

        row.addEventListener('click', () => toggleCustomerDetails(rowId, details));

        // Button handlers
        const returnBtn = details.querySelector('.btn-return');
        const delBtn = details.querySelector('.btn-delete-detail');

        if (returnBtn) {
          returnBtn.addEventListener('click', async (ev) => {
            ev.stopPropagation();
            if (!confirm('Mark this transaction as returned? This will add it back to stock.')) return;
            const ok = await markSaleReturned(sale.id);
            if (ok) alert('Marked returned.');
          });
        }

        if (delBtn) {
          delBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (!confirm('Delete this transaction? (It will be archived to Deleted History)')) return;
            deleteSaleById(sale.id);
            renderAll();
            alert('Deleted and archived.');
          });
        }

        wrapper.appendChild(row);
        wrapper.appendChild(details);
        wrap.appendChild(wrapper);
      });
    }
    function renderReturnedList(list) {
      const wrap = document.getElementById('returnedList');
      if (!wrap) return; wrap.innerHTML = '';
      if (!list.length) { wrap.innerHTML = '<div class="muted small">No returns yet</div>'; return; }

      list.slice().reverse().forEach(sale => {
        const rowId = 'returned-' + (sale.id || Date.now() + Math.random());
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-1';

        const row = document.createElement('div');
        row.className = 'customer-chip chip-returned customer-row';
        row.id = rowId;
        row.innerHTML = `
      <div style="flex:1">
        <div class="font-semibold">${escapeHtml(sale.customer || 'Unknown')}</div>
        <div class="text-xs text-emerald-600 font-medium">${escapeHtml(sale.type || '—')} • ${fmtDate(sale.returnedAt || sale.date)}</div>
      </div>
      <div class="text-sm">
        <div>${escapeHtml(sale.size || sale.item || '—')}</div>
        <div class="muted text-xxs">${money(sale.total)}</div>
      </div>
    `;

        const details = document.createElement('div');
        details.className = 'customer-details-expand';
        details.innerHTML = `
      <div class="detail-row"><span class="detail-label">Serial/Invoice:</span><span class="detail-value">${escapeHtml(sale.invoice || sale.id || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Name:</span><span class="detail-value">${escapeHtml(sale.customer || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">${escapeHtml(sale.phone || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Address:</span><span class="detail-value">${escapeHtml(sale.address || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Type:</span><span class="detail-value">${escapeHtml(sale.type || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Delivery:</span><span class="detail-value">${escapeHtml(sale.deliveryOption || sale.delivery || 'Pickup')}</span></div>
      <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value">${escapeHtml(sale.paymentMethod || sale.payment || 'Cash')}</span></div>
      <div class="detail-row"><span class="detail-label">Gallon Size:</span><span class="detail-value">${escapeHtml(sale.size || sale.item || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Quantity:</span><span class="detail-value">${escapeHtml(String(sale.qty || 1))}</span></div>
      <div class="detail-row"><span class="detail-label">Total:</span><span class="detail-value">${money(sale.total)}</span></div>
      <div class="detail-row"><span class="detail-label">Returned At:</span><span class="detail-value">${fmtDate(sale.returnedAt || '—')}</span></div>
      <div class="mt-2 flex gap-2">
        <button class="btn-delete-detail px-3 py-1 rounded bg-red-600 text-white text-xs" data-id="${sale.id}" data-type="returned">Delete</button>
      </div>
    `;

        row.addEventListener('click', () => toggleCustomerDetails(rowId, details));

        // Add delete button handler
        const deleteBtn = details.querySelector('.btn-delete-detail');
        if (deleteBtn) {
          deleteBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (!confirm('Delete this transaction? It will be moved to Deleted History.')) return;
            deleteSaleById(sale.id);
            renderAll();
            alert('Deleted and archived.');
          });
        }

        wrapper.appendChild(row);
        wrapper.appendChild(details);
        wrap.appendChild(wrapper);
      });
    }

    function renderTransactionsTable(sales) {
      const tbody = document.getElementById('transactionsBody'); if (!tbody) return; tbody.innerHTML = '';
      const ordered = (sales || []).slice().sort((a, b) => new Date(b.date) - new Date(a.date));
      ordered.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="p-2">${fmtDate(s.date)}</td>
                    <td class="p-2">${escapeHtml(s.invoice || ('INV-' + (s.id || '')))}</td>
                    <td class="p-2"><span class="click-customer" data-customer="${escapeHtml(s.customer || '')}">${escapeHtml(s.customer || '—')}</span></td>
                    <td class="p-2">${escapeHtml(s.item || s.size || '—')}</td>
                    <td class="p-2 text-right">${Number(s.qty || 0)}</td>
                    <td class="p-2 text-right">${money(s.unit)}</td>
                    <td class="p-2 text-right">${money(s.total)}</td>
                    <td class="p-2">${escapeHtml(s.paymentMethod || s.payment || 'Cash')}</td>
                    <td class="p-2">${escapeHtml(s.type || '')}</td>
                    <td class="p-2">${s.returned ? 'Yes' : 'No'}</td>`;
        tr.addEventListener('dblclick', () => { if (!confirm('Create a debt record from this transaction?')) return; createDebtFromSale(s); alert('Debt created from transaction.'); });
        tbody.appendChild(tr);
      });
      document.querySelectorAll('.click-customer').forEach(el => { el.style.cursor = 'pointer'; el.addEventListener('click', () => openCustomerPanelDS(el.getAttribute('data-customer') || el.textContent)); });
    }
    function saveExpense(item) { const arr = safeParse(EXPENSES_KEY, []); arr.push(item); save(EXPENSES_KEY, arr); }

    async function recordDebtPayment(debtId) {
      const debts = getDebts();
      const d = debts.find(x => x.id === debtId);
      if (!d) return alert('Debt record not found');

      const remaining = Number(d.amount) - Number(d.paid || 0);
      const pay = prompt(`Enter payment for ${d.customer || 'customer'}\nRemaining Balance: ${money(remaining)}`);
      const amt = Number(pay || 0);

      if (!pay) return; // Cancelled
      if (isNaN(amt) || amt <= 0) return alert('Invalid amount');

      // Update debt values
      d.paid = (Number(d.paid) || 0) + amt;
      const newRemaining = Math.max(0, Number(d.amount) - Number(d.paid));

      // 1. Record individual payment in collections log (Strictly for Monitoring)
      const logs = getPayments();
      logs.push({
        id: uid(),
        date: new Date().toISOString(),
        customer: d.customer,
        invoice: d.invoice || '—',
        amount: amt,
        remaining: newRemaining,
        status: newRemaining <= 0 ? 'Fully Paid' : 'Not Fully Paid'
      });
      setPayments(logs);

      // 2. Add to history log
      addHistoryLog({
        date: new Date().toISOString(),
        action: 'Debt/Partial Payment',
        customer: d.customer || '—',
        details: `Payment: ${money(amt)} - Remaining: ${money(newRemaining)} - Invoice: ${d.invoice || '—'}`
      });

      // 3. Move or update the debt record
      if (newRemaining <= 0) {
        // Move to fully paid
        const paidDebts = safeParse(PAID_DEBTS_KEY, []);
        paidDebts.push(Object.assign({}, d, { paidAt: new Date().toISOString(), status: 'Fully Paid' }));
        save(PAID_DEBTS_KEY, paidDebts);
        const remainingDebts = debts.filter(x => x.id !== debtId);
        save(DEBTS_KEY, remainingDebts);
      } else {
        // Update existing debt
        const updatedDebts = debts.map(x => x.id === debtId ? d : x);
        save(DEBTS_KEY, updatedDebts);
      }

      renderAll();
      alert(`✅ Payment of ${money(amt)} recorded for ${d.customer}.`);
    }

    function renderDebtsLists(sales = []) {
      const debts = getDebts();
      const paidDebts = safeParse(PAID_DEBTS_KEY, []);
      const debtorsWrap = document.getElementById('debtorsList');
      const partialWrap = document.getElementById('partialList');
      const paidWrap = document.getElementById('paidList');
      if (debtorsWrap) debtorsWrap.innerHTML = '';
      if (partialWrap) partialWrap.innerHTML = '';
      if (paidWrap) paidWrap.innerHTML = '';

      // Calculate totals
      let debtTotal = 0;
      let partialTotal = 0;
      let paidTotal = 0;

      // Render debtors (completely unpaid debts - paid = 0)
      debts.forEach(d => {
        const paid = Number(d.paid || 0);
        const remaining = Number(d.amount) - paid;
        if (remaining <= 0 || paid > 0) return; // Skip if fully paid or has partial payment

        debtTotal += remaining;

        const rowId = 'debt-' + (d.id || Date.now() + Math.random());
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-1';

        const row = document.createElement('div');
        row.className = 'customer-chip customer-row';
        row.id = rowId;
        row.innerHTML = `<div><div class="font-semibold clickable-name">${escapeHtml(d.customer)}</div><div class="text-xs muted">${escapeHtml(d.phone || '')} • ${fmtDate(d.date)}</div></div><div class="text-sm">${money(remaining)}</div>`;

        const sale = sales.find(s => s.id === d.sourceSaleId || s.invoice === d.invoice);
        const address = sale ? (sale.address || '—') : '—';
        const type = sale ? (sale.type || '—') : '—';
        const qty = sale ? (sale.qty || '—') : '—';
        const delivery = sale ? (sale.deliveryOption || sale.delivery || 'Pickup') : '—';
        const payment = sale ? (sale.paymentMethod || sale.payment || 'Cash') : '—';
        const saleAmountPaid = sale ? (sale.amountPaid || 0) : 0;
        const change = sale ? (sale.change || 0) : 0;

        const details = document.createElement('div');
        details.className = 'customer-details-expand';
        details.innerHTML = `
      <div class="detail-row"><span class="detail-label">Invoice:</span><span class="detail-value">${escapeHtml(d.invoice || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Phone Number:</span><span class="detail-value">${escapeHtml(d.phone || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Address:</span><span class="detail-value">${escapeHtml(address)}</span></div>
      <div class="detail-row"><span class="detail-label">Transaction Type:</span><span class="detail-value">${escapeHtml(type)}</span></div>
      <div class="detail-row"><span class="detail-label">Quantity:</span><span class="detail-value">${escapeHtml(String(qty))}</span></div>
      <div class="detail-row"><span class="detail-label">Delivery Option:</span><span class="detail-value">${escapeHtml(delivery)}</span></div>
      <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value">${escapeHtml(payment)}</span></div>
      <div class="detail-row"><span class="detail-label">Amount Paid:</span><span class="detail-value">${money(saleAmountPaid)}</span></div>
      <div class="detail-row"><span class="detail-label">Change:</span><span class="detail-value">${money(change)}</span></div>
      <div class="detail-row"><span class="detail-label">Remaining Debt:</span><span class="detail-value">${money(remaining)}</span></div>
      <div class="mt-2 flex gap-2">
        <button class="btn-pay-debt px-3 py-1 rounded bg-emerald-600 text-white text-xs" data-id="${d.id}">Pay</button>
        <button class="btn-delete-debt px-3 py-1 rounded bg-red-600 text-white text-xs" data-id="${d.id}">Delete</button>
      </div>
    `;

        row.addEventListener('click', () => toggleCustomerDetails(rowId, details));

        // Pay button handler
        const payBtn = details.querySelector('.btn-pay-debt');
        if (payBtn) {
          payBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            recordDebtPayment(d.id);
          });
        }

        // Delete button handler
        const delBtn = details.querySelector('.btn-delete-debt');
        if (delBtn) {
          delBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (!confirm('Delete this debt record?')) return;
            const all = getDebts().filter(x => x.id !== d.id);
            save(DEBTS_KEY, all);
            renderAll();
            alert('Debt deleted.');
          });
        }

        wrapper.appendChild(row);
        wrapper.appendChild(details);
        if (debtorsWrap) debtorsWrap.appendChild(wrapper);
      });

      // Render partial payments (debts with some payment but not fully paid)
      debts.forEach(d => {
        const remaining = Number(d.amount) - Number(d.paid || 0);
        if (remaining <= 0 || d.paid <= 0) return; // Skip if fully paid or no payment yet

        partialTotal += remaining;

        const rowId = 'partial-' + (d.id || Date.now() + Math.random());
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-1';

        const row = document.createElement('div');
        row.className = 'customer-chip customer-row';
        row.id = rowId;
        row.innerHTML = `<div><div class="font-semibold clickable-name">${escapeHtml(d.customer)}</div><div class="text-xs muted">${escapeHtml(d.phone || '')} • ${fmtDate(d.date)}</div></div><div class="text-sm">${money(remaining)}</div>`;

        const sale = sales.find(s => s.id === d.sourceSaleId || s.invoice === d.invoice);
        const address = sale ? (sale.address || '—') : '—';
        const type = sale ? (sale.type || '—') : '—';
        const qty = sale ? (sale.qty || '—') : '—';
        const delivery = sale ? (sale.deliveryOption || sale.delivery || 'Pickup') : '—';
        const payment = sale ? (sale.paymentMethod || sale.payment || 'Cash') : '—';
        const saleAmountPaid = sale ? (sale.amountPaid || 0) : 0;
        const change = sale ? (sale.change || 0) : 0;

        const details = document.createElement('div');
        details.className = 'customer-details-expand';
        details.innerHTML = `
      <div class="detail-row"><span class="detail-label">Invoice:</span><span class="detail-value">${escapeHtml(d.invoice || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Phone Number:</span><span class="detail-value">${escapeHtml(d.phone || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Address:</span><span class="detail-value">${escapeHtml(address)}</span></div>
      <div class="detail-row"><span class="detail-label">Transaction Type:</span><span class="detail-value">${escapeHtml(type)}</span></div>
      <div class="detail-row"><span class="detail-label">Quantity:</span><span class="detail-value">${escapeHtml(String(qty))}</span></div>
      <div class="detail-row"><span class="detail-label">Delivery Option:</span><span class="detail-value">${escapeHtml(delivery)}</span></div>
      <div class="detail-row"><span class="detail-label">Payment Method:</span><span class="detail-value">${escapeHtml(payment)}</span></div>
      <div class="detail-row"><span class="detail-label">Amount Paid:</span><span class="detail-value">${money(saleAmountPaid)}</span></div>
      <div class="detail-row"><span class="detail-label">Change:</span><span class="detail-value">${money(change)}</span></div>
      <div class="detail-row"><span class="detail-label">Remaining Debt:</span><span class="detail-value">${money(remaining)}</span></div>
      <div class="mt-2 flex gap-2">
        <button class="btn-pay-debt px-3 py-1 rounded bg-emerald-600 text-white text-xs" data-id="${d.id}">Pay</button>
        <button class="btn-delete-debt px-3 py-1 rounded bg-red-600 text-white text-xs" data-id="${d.id}">Delete</button>
      </div>
    `;

        row.addEventListener('click', () => toggleCustomerDetails(rowId, details));

        // Pay button handler
        const payBtn = details.querySelector('.btn-pay-debt');
        if (payBtn) {
          payBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            recordDebtPayment(d.id);
          });
        }

        const delBtn = details.querySelector('.btn-delete-debt');
        if (delBtn) {
          delBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (!confirm('Delete this debt record?')) return;
            const all = getDebts().filter(x => x.id !== d.id);
            save(DEBTS_KEY, all);
            renderAll();
            alert('Debt deleted.');
          });
        }

        wrapper.appendChild(row);
        wrapper.appendChild(details);
        // ADD TO DEBTORS WRAP INSTEAD OF PARTIAL WRAP (or both, but user specified Debtors)
        if (partialWrap) partialWrap.appendChild(wrapper);
      });
      // Render fully paid debts
      paidDebts.forEach(d => {
        paidTotal += Number(d.amount || 0);

        const rowId = 'paid-' + (d.id || Date.now() + Math.random());
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-1';

        const row = document.createElement('div');
        row.className = 'customer-chip customer-row';
        row.id = rowId;
        row.innerHTML = `<div><div class="font-semibold clickable-name">${escapeHtml(d.customer)}</div><div class="text-xs muted">${escapeHtml(d.phone || '')} • ${fmtDate(d.paidAt || d.date)}</div></div><div class="text-sm">${money(d.amount)}</div>`;

        const details = document.createElement('div');
        details.className = 'customer-details-expand';
        details.innerHTML = `
      <div class="detail-row"><span class="detail-label">Customer:</span><span class="detail-value">${escapeHtml(d.customer || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">${escapeHtml(d.phone || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Total Amount:</span><span class="detail-value">${money(d.amount)}</span></div>
      <div class="detail-row"><span class="detail-label">Paid At:</span><span class="detail-value">${fmtDate(d.paidAt || '—')}</span></div>
      <div class="detail-row"><span class="detail-label">Invoice:</span><span class="detail-value">${escapeHtml(d.invoice || '—')}</span></div>
      <div class="mt-2 flex gap-2">
        <button class="btn-delete-paid px-3 py-1 rounded bg-red-600 text-white text-xs" data-id="${d.id}">Delete</button>
      </div>
    `;

        row.addEventListener('click', () => toggleCustomerDetails(rowId, details));

        // Delete button handler
        const delBtn = details.querySelector('.btn-delete-paid');
        if (delBtn) {
          delBtn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (!confirm('Delete this paid debt record?')) return;
            const all = paidDebts.filter(x => x.id !== d.id);
            save(PAID_DEBTS_KEY, all);
            renderAll();
            alert('Paid debt deleted.');
          });
        }

        wrapper.appendChild(row);
        wrapper.appendChild(details);
        if (paidWrap) paidWrap.appendChild(wrapper);
      });

      // Update Total Balance display
      if (document.getElementById('debtBalance')) document.getElementById('debtBalance').textContent = money(debtTotal);
      if (document.getElementById('partialBalance')) document.getElementById('partialBalance').textContent = money(partialTotal);
      if (document.getElementById('paidBalance')) document.getElementById('paidBalance').textContent = money(paidTotal);
    }
    function createDebtFromSale(saleObj) {
      const debts = getDebts();
      debts.push({
        id: uid(),
        sourceSaleId: saleObj.id || null,
        date: new Date().toISOString(),
        customer: saleObj.customer || '',
        phone: saleObj.phone || '',
        amount: Number(saleObj.total || 0),
        paid: Number(saleObj.amountPaid || 0),
        initialPaid: Number(saleObj.amountPaid || 0), // Track initial payment
        notes: saleObj.notes || '',
        invoice: saleObj.invoice || ''
      });
      save(DEBTS_KEY, debts);

      // Add to history
      addHistoryLog({
        date: new Date().toISOString(),
        action: 'Debt Created',
        customer: saleObj.customer || '—',
        details: `Amount: ${money(saleObj.total || 0)} - Invoice: ${saleObj.invoice || '—'}`
      });

      renderAll();
    }
    async function markSaleReturned(id) {
      const arr = getSales();
      const idx = arr.findIndex(s => s.id === id);
      if (idx === -1) return false;
      if (arr[idx].returned) return true;

      // Check if we have serials
      const itemSerials = arr[idx].serials || [];
      if (itemSerials.length === 0) {
        if (!confirm("This transaction has no tracked serial numbers. Add back to stock as numbers-less?")) return false;
      } else {
        // Database Return
        try {
          const res = await fetch('api/inventory_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'in',
              serials: itemSerials,
              customer: arr[idx].customer
            })
          });
          const d = await res.json();
          if (!d.success) {
            alert("Failed to return items in database: " + (d.errors || []).join(', '));
            return false;
          }
        } catch (e) {
          alert("Network error processing return.");
          return false;
        }
      }

      arr[idx].returned = true;
      arr[idx].returnedAt = new Date().toISOString();
      setSales(arr);

      // Add to history
      addHistoryLog({
        date: new Date().toISOString(),
        action: 'Returned Gallons',
        customer: arr[idx].customer || '—',
        details: `${arr[idx].qty || 1}x ${arr[idx].size || '—'} - Invoice: ${arr[idx].invoice || id}`
      });

      renderAll();
      return true;
    }
    function deleteSaleById(id) {
      const arr = getSales();
      const idx = arr.findIndex(s => s.id === id);
      if (idx === -1) return false;
      const [removed] = arr.splice(idx, 1);
      setSales(arr);
      removed.deletedAt = new Date().toISOString();
      removed.deletedBy = localStorage.getItem(CASHIER_KEY) || '—';
      const deleted = getDeleted(); deleted.push(removed); save(DELETED_KEY, deleted);

      // Add to history
      addHistoryLog({
        date: new Date().toISOString(),
        action: 'Sale Deleted',
        customer: removed.customer || '—',
        details: `${removed.qty || 1}x ${removed.size || '—'} - Invoice: ${removed.invoice || id}`
      });

      recomputeTotals(); renderAll();
      return true;
    }
    async function returnGallon(id) {
      const ok = await markSaleReturned(id);
      if (ok) alert('Marked returned.');
    }
    function deleteItem(id) { deleteSaleById(id); }
    function recomputeTotals() {
      const sales = getSales();
      const deletedSales = getDeleted();

      // 1. Metrics Calculation
      let grossSalesValue = 0;       // For Total Sales (Sum of all transaction totals)
      let debtMonitoringTotal = 0;   // For "Amount Paid" in Monitoring (Debts/Partial Only)

      sales.forEach(s => {
        const total = Number(s.total || 0);
        grossSalesValue += total;
        // deliberate: we do NOT add to debtMonitoringTotal here anymore
        // to avoid mixing standard sales payments with debt tracking.
      });

      // 2. Debt Collections (TOTAL PAYMENTS COLLECTED TODAY - for Monitoring only)
      const paymentsLog = getPayments();
      const todayIso = new Date().toISOString().split('T')[0];
      let debtCollectionsToday = 0;

      paymentsLog.forEach(p => {
        if (p.date.startsWith(todayIso)) {
          debtCollectionsToday += Number(p.amount || 0);
        }
      });

      // Balances for the labels
      const debts = getDebts();
      const paidDebts = safeParse(PAID_DEBTS_KEY, []);

      let debtBalanceTotal = 0;
      let partialBalanceTotal = 0;
      let fullyPaidBalanceTotal = 0;

      debts.forEach(d => {
        const paid = Number(d.paid || 0);
        const amount = Number(d.amount || 0);
        const remaining = Math.max(0, amount - paid);

        if (paid > 0 && remaining > 0) partialBalanceTotal += remaining;
        else if (paid === 0 && remaining > 0) debtBalanceTotal += amount;
      });

      paidDebts.forEach(d => {
        fullyPaidBalanceTotal += Number(d.amount || 0);
      });

      // 3. Moneybox & Monitoring
      const mb = safeParse(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0, cash: 0 });
      const amountTaken = Number(mb.out || 0);
      const amountReturned = Number(mb.returned || 0);
      const totalCoins = Number(mb.coins || 0);
      const totalCashManual = Number(mb.cash || 0);

      // 4. Final Calculations (Daily Flow)
      // Logic: Cash Inflow = (Gross Sales) - (Debts Created today) + (Debt Collections today)

      let linkedDebtPrincipal = 0;
      const salesIds = new Set(sales.map(s => s.id));
      const sumLinked = (list) => {
        list.forEach(d => {
          if (d.sourceSaleId && salesIds.has(d.sourceSaleId)) {
            linkedDebtPrincipal += Number(d.amount || 0);
          }
        });
      };
      sumLinked(debts);
      sumLinked(paidDebts);

      const adjustedSales = grossSalesValue - linkedDebtPrincipal + debtCollectionsToday;
      const totalSales = adjustedSales - amountTaken + amountReturned;
      const netCash = totalSales;

      // 5. Update UI
      if (document.getElementById('totalSalesDS')) document.getElementById('totalSalesDS').textContent = money(totalSales);
      if (document.getElementById('cardTotalSales')) document.getElementById('cardTotalSales').textContent = money(totalSales);

      if (document.getElementById('totalexpense')) document.getElementById('totalexpense').textContent = money(amountTaken);
      if (document.getElementById('totalcoins')) document.getElementById('totalcoins').textContent = money(totalCoins);
      if (document.getElementById('totalCashDisplay')) document.getElementById('totalCashDisplay').textContent = money(totalCashManual);
      if (document.getElementById('netCashDisplay')) document.getElementById('netCashDisplay').textContent = money(netCash);

      // Amount Paid Display (Reflects ONLY Today's Debt Collections for Monitoring)
      if (document.getElementById('totalAmountPaidDisplay')) document.getElementById('totalAmountPaidDisplay').textContent = money(debtCollectionsToday);

      // Balances
      if (document.getElementById('debtBalance')) document.getElementById('debtBalance').textContent = money(debtBalanceTotal);
      if (document.getElementById('partialBalance')) document.getElementById('partialBalance').textContent = money(partialBalanceTotal);
      const paidBalances = document.querySelectorAll('#paidBalance');
      if (paidBalances.length > 0) paidBalances[0].textContent = money(fullyPaidBalanceTotal);

      save(TOTAL_SALES_KEY, totalSales);
      renderPaymentLogsDisplay();
    }

    function renderPaymentLogsDisplay() {
      const wrap = document.getElementById('paymentLogs');
      const countEl = document.getElementById('paymentRecordCount');
      if (!wrap) return;

      const payments = getPayments();
      // Filter for today
      const today = new Date().toISOString().split('T')[0];
      const todayPayments = payments.filter(p => p.date.startsWith(today)).reverse();

      if (countEl) countEl.textContent = `${todayPayments.length} record${todayPayments.length === 1 ? '' : 's'}`;

      if (todayPayments.length === 0) {
        wrap.innerHTML = '<div class="text-center text-gray-400 italic py-4">No collections recorded today</div>';
        return;
      }

      wrap.innerHTML = todayPayments.map(p => `
        <div class="flex flex-col mb-2 p-2 rounded bg-white border border-gray-100 shadow-sm relative overflow-hidden">
          <div class="absolute right-0 top-0 h-full w-1 ${p.remaining <= 0 ? 'bg-emerald-400' : 'bg-yellow-400'}"></div>
          <div class="flex justify-between items-start mb-1">
            <div class="font-bold text-emerald-800">${escapeHtml(p.customer)}</div>
            <div class="flex items-center gap-2">
              <div class="font-bold text-emerald-600">${money(p.amount)}</div>
              <button onclick="deletePaymentLog('${p.id}')" class="text-red-400 hover:text-red-600 transition-colors" title="Delete record">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <div class="flex justify-between text-[10px] text-gray-500">
            <div>Ref: ${escapeHtml(p.invoice)} • ${new Date(p.date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
            <div>Rem: ${money(p.remaining)}</div>
          </div>
        </div>
      `).join('');
    }

    function deletePaymentLog(id) {
      if (!confirm('Are you sure you want to delete this payment record? This will adjust your daily totals.')) return;

      const payments = getPayments();
      const removed = payments.find(p => p.id === id);
      if (!removed) return;

      const updated = payments.filter(p => p.id !== id);
      setPayments(updated);

      addHistoryLog({
        date: new Date().toISOString(),
        action: 'Payment Deleted',
        customer: removed.customer || '—',
        details: `Deleted collection of ${money(removed.amount)} - Ref: ${removed.invoice}`
      });

      recomputeTotals();
      alert('Record deleted and totals adjusted.');
    }
    function openCustomerPanelDS(name) {
      const sales = getSales().filter(s => String(s.customer || '').trim() === String(name || '').trim());
      if (sales.length === 0) { alert('No transactions for ' + name); return; }
      const modal = document.createElement('div'); modal.className = 'modal active'; modal.style.zIndex = 200;
      modal.innerHTML = `<div class="bg-white rounded-xl p-4 shadow-lg w-11/12 max-w-xl">
    <div class="flex justify-between items-center mb-3"><div><h3 class="text-lg font-bold">Transactions — ${escapeHtml(name)}</h3><div class="text-xs muted">${sales.length} transaction(s)</div></div><button id="closeCust" class="text-gray-600">✖</button></div>
    <div style="max-height:360px;overflow:auto;">
      ${sales.map(s => {
        return `<div class="report-card mb-2 p-3" style="display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div><strong>${escapeHtml(s.invoice || ('INV-' + s.id))}</strong> • ${escapeHtml(fmtDate(s.date))}</div>
            <div class="text-xs muted">${escapeHtml(s.size || s.item || '')} x ${s.qty || 1} • ${escapeHtml(s.paymentMethod || s.payment || 'Cash')}</div>
            <div class="text-xs">Total: ${money(s.total)}</div>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;">
            ${(s.type === 'Borrow' || s.type === 'Buy Gallon') && !s.returned ? `<button class="btn-return small px-3 py-1 rounded bg-emerald-600 text-white" data-id="${s.id}">Return</button>` : ''}
            <button class="btn-delete small px-3 py-1 rounded bg-gray-100" data-id="${s.id}">Delete</button>
          </div>
        </div>`;
      }).join('')}
    </div>
    <div class="mt-3 flex justify-end"><button id="closeCust2" class="px-3 py-1 bg-gray-100 rounded">Close</button></div>
  </div>`;
      document.body.appendChild(modal);
      modal.querySelectorAll('#closeCust,#closeCust2').forEach(b => b.addEventListener('click', () => modal.remove()));
      modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
      modal.querySelectorAll('.btn-return').forEach(b => b.addEventListener('click', async () => {
        const id = b.getAttribute('data-id');
        if (!id) return;
        if (!confirm('Mark this transaction as returned? This will add it back to stock.')) return;
        const ok = await markSaleReturned(id);
        if (ok) {
          alert('Marked returned.');
          modal.remove();
        }
      }));
      modal.querySelectorAll('.btn-delete').forEach(b => b.addEventListener('click', () => { const id = b.getAttribute('data-id'); if (!id) return; if (!confirm('Delete this transaction? It will be moved to Deleted History.')) return; deleteSaleById(id); alert('Deleted and archived.'); modal.remove(); }));
    }

    function clearAllRecordsAndResetEverythingDS() {
      if (!confirm('Clear transaction lists (Sales, Debts, History)? Stock counts will remain unchanged.')) return;
      const currentSales = getSales().slice();
      const deleted = getDeleted();
      currentSales.forEach(s => { s.deletedAt = new Date().toISOString(); s.deletedBy = localStorage.getItem(CASHIER_KEY) || '—'; deleted.push(s); });
      save(DELETED_KEY, deleted);
      save(SALES_KEY, []);
      save(DEBTS_KEY, []);
      save(PAID_DEBTS_KEY, []);
      save(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0 });
      save(TOTAL_SALES_KEY, 0);
      // Stock is intentionally NOT reset here to preserve inventory counts
      renderAll(); recomputeTotals();
      alert('Transaction lists cleared. Stock counts preserved.');
    }

    /* simple search/filter for renderTable */
    function renderTable(sales) {
      const tbody = document.getElementById('tableBody');
      if (!tbody) return;
      const search = (document.getElementById('searchBoxFilter')?.value || '').toLowerCase();
      const typeFilter = (document.getElementById('typeFilter')?.value || '');
      let rows = (sales === undefined) ? (getSales() || []) : (sales.slice ? sales.slice() : getSales());

      if (typeFilter) rows = rows.filter(r => r.type === typeFilter);
      if (search) rows = rows.filter(r =>
        (r.customer || '').toLowerCase().includes(search) ||
        (r.phone || '').toLowerCase().includes(search) ||
        (r.size || '').toLowerCase().includes(search)
      );

      tbody.innerHTML = rows.map(s => {
        const d = getDeliveryType(s);
        const typeClass = s.type === 'Refill' ? 'table-refill' : s.type === 'Borrow' ? 'table-borrow' : 'table-returned';

        // Split date into date and time
        let dateStr = '';
        let timeStr = '';
        try {
          const dateObj = new Date(s.date);
          dateStr = dateObj.toLocaleDateString();
          timeStr = dateObj.toLocaleTimeString();
        } catch (e) {
          dateStr = fmtDate(s.date);
          timeStr = '';
        }

        return `
      <tr class="border-t ${typeClass}">
        <td class="p-2">${escapeHtml(s.invoice || ('INV-' + (s.id || '')))}</td>
        <td class="p-2">${escapeHtml(s.customer || '')}</td>
        <td class="p-2">${escapeHtml(s.address || '')}</td>
        <td class="p-2">${escapeHtml(s.phone || '')}</td>
        <td class="p-2">${escapeHtml(s.type || '')}</td>
        <td class="p-2">${escapeHtml(s.size || '')}</td>
        <td class="p-2">${s.qty || ''}</td>
        <td class="p-2">₱${Number(s.unit || 0).toFixed(2)}</td>
        <td class="p-2">₱${Number(s.total || 0).toFixed(2)}</td>
        <td class="p-2">${escapeHtml(s.paymentMethod || s.payment || 'Cash')}</td>
        <td class="p-2">${escapeHtml(d)}</td>
        <td class="p-2">${s.returned ? 'Yes' : 'No'}</td>
        <td class="p-2">${dateStr}</td>
        <td class="p-2">${timeStr}</td>
        <td class="p-2 no-print">
          ${(s.type === 'Borrow' || s.type === 'Buy Gallon') && !s.returned ? `<button onclick="returnGallon('${s.id}')" class="px-2 py-1 bg-emerald-600 text-white rounded text-sm">Return</button>` : ''}
          <button onclick="deleteItem('${s.id}')" class="px-2 py-1 bg-red-600 text-white rounded text-sm">Delete</button>
        </td>
      </tr>
    `;
      }).join('');
    }

    /* DOM bindings */
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

      document.getElementById('computeAllBtn')?.addEventListener('click', () => { const sales = getSales(); let gross = 0; sales.forEach(s => gross += Number(s.total || 0)); save(TOTAL_SALES_KEY, gross); recomputeTotals(); alert('Computed totals updated.'); });
      document.querySelectorAll('#exportCsvBtn,#exportCsvBtn2').forEach(b => {
        if (b) b.addEventListener('click', () => {
          const sales = getSales();
          if (!sales || sales.length === 0) { alert('No sales to export'); return; }
          const headers = ['Invoice', 'Customer', 'Address', 'Phone', 'Type', 'Item', 'Qty', 'Unit', 'Total', 'Payment', 'Delivery', 'Returned', 'Date', 'Time', 'Actions'];
          const rows = [headers.join(',')];
          sales.forEach(s => {
            // Split date into date and time
            let dateStr = '';
            let timeStr = '';
            try {
              const dateObj = new Date(s.date);
              dateStr = dateObj.toLocaleDateString();
              timeStr = dateObj.toLocaleTimeString();
            } catch (e) {
              dateStr = s.date || '';
              timeStr = '';
            }

            const row = [
              `"${(s.invoice || '')}"`,
              `"${(s.customer || '')}"`,
              `"${(s.address || '')}"`,
              `"${(s.phone || '')}"`,
              `"${(s.type || '')}"`,
              `"${(s.item || s.size || '')}"`,
              (s.qty || 1),
              (s.unit || ''),
              (s.total || ''),
              `"${(s.paymentMethod || s.payment || 'Cash')}"`,
              `"${(s.deliveryOption || s.delivery || 'Pickup')}"`,
              (s.returned ? 'Yes' : 'No'),
              `"${dateStr}"`,
              `"${timeStr}"`,
              `""`
            ];
            rows.push(row.join(','));
          });
          const csv = rows.join('\n');
          const blob = new Blob([csv], { type: 'text/csv' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a'); a.href = url; a.download = `hydro_sales_${new Date().toISOString().slice(0, 10)}.csv`; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
        });
      });

      document.querySelectorAll('#refreshBtn,#refreshBtn2').forEach(b => { if (b) b.addEventListener('click', () => { renderAll(); recomputeTotals(); }); });

      document.getElementById('manualAddDebtBtn')?.addEventListener('click', () => document.getElementById('manualDebtModal').classList.add('active'));
      document.getElementById('closeManualDebtModal')?.addEventListener('click', () => document.getElementById('manualDebtModal').classList.remove('active'));
      document.getElementById('cancelManualDebtBtn')?.addEventListener('click', () => document.getElementById('manualDebtModal').classList.remove('active'));
      document.getElementById('saveManualDebtBtn')?.addEventListener('click', () => {
        const customer = document.getElementById('manualDebtCustomer')?.value || ''; const phone = document.getElementById('manualDebtPhone')?.value || ''; const amount = Number(document.getElementById('manualDebtAmount')?.value || 0); const notes = document.getElementById('manualDebtNotes')?.value || '';
        if (!customer || amount <= 0) { alert('Please provide customer and amount'); return; }
        const debts = getDebts(); debts.push({ id: uid(), date: new Date().toISOString(), customer, phone, amount, paid: 0, notes }); save(DEBTS_KEY, debts);

        // Add to history
        addHistoryLog({
          date: new Date().toISOString(),
          action: 'Manual Debt Added',
          customer: customer,
          details: `Amount: ${money(amount)} - Notes: ${notes}`
        });

        document.getElementById('manualDebtModal').classList.remove('active'); renderAll();
      });

      document.getElementById('resetMoneyBoxBtn')?.addEventListener('click', () => {
        if (!confirm('Are you sure you want to reset the Money Box?')) return;
        save(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0, cash: 0 });
        if (document.getElementById('moneyOutAmount')) document.getElementById('moneyOutAmount').value = '';
        if (document.getElementById('moneyReturnAmount')) document.getElementById('moneyReturnAmount').value = '';
        if (document.getElementById('totalCoins')) document.getElementById('totalCoins').value = '';
        if (document.getElementById('totalCashInput')) document.getElementById('totalCashInput').value = '';
        recomputeTotals();
        alert('Money Box reset.');
      });

      document.getElementById('clearAllBtn')?.addEventListener('click', clearAllRecordsAndResetEverythingDS);

      document.getElementById('applyFilterBtn')?.addEventListener('click', () => renderTable());
      document.getElementById('searchBoxFilter')?.addEventListener('input', () => renderTable());
      document.getElementById('typeFilter')?.addEventListener('change', () => renderTable());

      document.getElementById('applyMoneyBoxBtn')?.addEventListener('click', () => {
        const moneyOut = parseFloat(document.getElementById('moneyOutAmount')?.value) || 0;
        const moneyReturn = parseFloat(document.getElementById('moneyReturnAmount')?.value) || 0;
        const coins = parseFloat(document.getElementById('totalCoins')?.value) || 0;
        const cash = parseFloat(document.getElementById('totalCashInput')?.value) || 0;

        if (moneyOut === 0 && moneyReturn === 0 && coins === 0 && cash === 0) {
          alert('Please enter an amount');
          return;
        }

        const mb = safeParse(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0, cash: 0 });
        let actions = [];

        if (moneyOut > 0) {
          mb.out = (Number(mb.out) || 0) + moneyOut;
          actions.push(`Money Taken Out: ${money(moneyOut)}`);
          addHistoryLog({
            date: new Date().toISOString(),
            action: 'Money Taken Out',
            customer: 'Cash Management',
            details: `${money(moneyOut)} removed from money box`
          });
        }

        if (moneyReturn > 0) {
          // Logic: "Amount Returned" reduces the Total Expenses (mb.out)
          if (mb.out < moneyReturn) {
            alert('Cannot return more than total expenses taken (' + money(mb.out) + ')');
            return; // Stop processing to prevent negative/invalid state
          }
          mb.out = (Number(mb.out) || 0) - moneyReturn;

          actions.push(`Money Returned: ${money(moneyReturn)}`);
          addHistoryLog({
            date: new Date().toISOString(),
            action: 'Money Returned',
            customer: 'Cash Management',
            details: `${money(moneyReturn)} deducted from expenses (New Expenses Total: ${money(mb.out)})`
          });
        }

        if (coins > 0) {
          mb.coins = (Number(mb.coins) || 0) + coins; // Accumulate coin amounts
          actions.push(`Coins Counted: ${money(coins)}`);
          addHistoryLog({
            date: new Date().toISOString(),
            action: 'Coins Counted',
            customer: 'Cash Management',
            details: `${money(coins)} in coins recorded (New Total: ${money(mb.coins)})`
          });
        }

        if (cash > 0) {
          mb.cash = (Number(mb.cash) || 0) + cash; // Accumulate cash amounts
          actions.push(`Cash Counted: ${money(cash)}`);
          addHistoryLog({
            date: new Date().toISOString(),
            action: 'Cash Counted',
            customer: 'Cash Management',
            details: `${money(cash)} in cash recorded (New Total: ${money(mb.cash)})`
          });
        }

        save(MONEYBOX_KEY, mb);

        // Clear inputs
        if (document.getElementById('moneyOutAmount')) document.getElementById('moneyOutAmount').value = '';
        if (document.getElementById('moneyReturnAmount')) document.getElementById('moneyReturnAmount').value = '';
        if (document.getElementById('totalCoins')) document.getElementById('totalCoins').value = '';
        if (document.getElementById('totalCashInput')) document.getElementById('totalCashInput').value = '';

        recomputeTotals();
        alert('Cash management applied!\n\n' + actions.join('\n'));
      });


      const savedCashier = localStorage.getItem(CASHIER_KEY);
      if (savedCashier && document.getElementById('cashierNameDisplay')) document.getElementById('cashierNameDisplay').textContent = savedCashier;
      if (savedCashier && document.getElementById('cashierInputTop')) document.getElementById('cashierInputTop').value = savedCashier;

      // Cashier management buttons
      function setCashierName(v) {
        if (!v) {
          localStorage.removeItem(CASHIER_KEY);
          document.getElementById('cashierNameDisplay').textContent = '—';
          return;
        }
        localStorage.setItem(CASHIER_KEY, v);
        document.getElementById('cashierNameDisplay').textContent = v;
      }

      // Sync cashier name & totals across tabs
      window.addEventListener('storage', (e) => {
        if (e.key === CASHIER_KEY) {
          const newName = e.newValue || '—';
          document.getElementById('cashierNameDisplay').textContent = newName;
          const inputTop = document.getElementById('cashierInputTop');
          if (inputTop) inputTop.value = newName;
        }
        if (e.key === SALES_KEY || e.key === DEBTS_KEY || e.key === MONEYBOX_KEY || e.key === PAYMENTS_KEY) {
          renderAll();
          recomputeTotals();
        }
      });

      document.getElementById('saveCashierBtn')?.addEventListener('click', () => {
        const name = document.getElementById('cashierInputTop')?.value;
        setCashierName(name);
      });

      document.getElementById('removeCashierBtn')?.addEventListener('click', () => {
        setCashierName('');
        document.getElementById('cashierInputTop').value = '';
      });

      renderAll(); recomputeTotals();

      // Check stock on page load
      checkLowStock();

      // Periodic stock check every 5 minutes
      setInterval(checkLowStock, 300000);
    });
    /* global API */
    function pushSale(sale) {
      const arr = getSales(); arr.push(sale); setSales(arr);
      const s = getStock();
      if (s[sale.size] !== undefined) { s[sale.size] = Math.max(0, (Number(s[sale.size]) || 0) - (Number(sale.qty) || 1)); setStock(s); }
      recomputeTotals(); renderAll();
    }
    window.hydroPushSale = pushSale;
  </script>
</body>

</html>