<?php
session_start();

// Protektahan ang dashboard – kailangan naka-login
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Optional: kunin ang pangalan para ipakita sa UI
$cashierNameFromSession = $_SESSION['full_name'] ?? '—';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>HydroTrack · Home Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="styles.css" />
  <script src="libs/tailwindcss.js" onerror="console.error('Failed to load Tailwind CSS')"></script>
  <script src="libs/qrcode.min.js" onerror="console.error('Failed to load QRCode library')"></script>
  <script src="libs/html5-qrcode.min.js" onerror="console.error('Failed to load HTML5 QR Code scanner')"></script>
  <style>
    body {
      overflow: auto;
      font-family: system-ui, sans-serif;
      background: linear-gradient(to bottom right, #ecfdf5, #cffafe);
      color: #0f172a;
    }

    .app-height {
      height: auto;
      min-height: calc(100vh - 160px);
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    @media(min-width:768px) {
      .app-height {
        flex-direction: row;
        height: calc(100vh - 160px);
      }
    }

    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 100;
      padding: 1rem;
    }

    .modal.active {
      display: flex;
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
      color: #374151;
      text-decoration: none;
      transition: 0.2s;
    }

    .sidebar a.active,
    .sidebar a:hover {
      background: #4b5563;
      color: white;
    }

    .main-content {
      margin-left: 150px;
      padding: 35px;
      transition: margin-left 0.3s ease;
    }

    .report-card {
      background: white;
      border-radius: 12px;
      padding: 14px;
      box-shadow: 0 8px 20px rgba(2, 6, 23, 0.06);
    }

    .muted {
      color: #6b7280;
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

    @media (max-width:768px) {
      .main {
        margin-left: 0px;
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

      .total-sales-float {
        position: static !important;
        margin-bottom: 1rem;
        width: 100%;
      }

      .summary-grid {
        grid-template-columns: 1fr !important;
      }

      .app-height {
        flex-direction: column !important;
      }

      nav .flex {
        flex-wrap: wrap;
      }
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

    right: 8px;
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #92400E;
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

    .no-select {
      user-select: none;
      -webkit-user-select: none;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    @media(min-width:768px) {
      .summary-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }

    .summary-card {
      background: white;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 6px 18px rgba(2, 6, 23, 0.05);
      text-align: center;
      margin-top: 1px;
    }

    .summary-value {
      font-size: 1.4rem;
      font-weight: 700;
      margin-top: 1px;
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

    table thead th {
      position: sticky;
      top: 0;
      background-color: #ecfeff;
      z-index: 10;
    }

    @media print {
      .no-print {
        display: none !important;
      }

      body {
        background: white;
      }
    }

    /* Inline receipt preview styles (small) */
    #receiptPreview {
      background: #fff;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(2, 6, 23, 0.04);
      font-size: 12px;
    }

    #receiptPreview .brand {
      text-align: center;
      font-weight: 700;
      font-size: 12px;
    }

    #receiptPreview .line {
      display: flex;
      justify-content: space-between;
      padding: 3px 0;
      border-bottom: 1px dashed #eee;
    }

    #receiptPreview .line.total {
      font-weight: 700;
      border-top: 2px solid #000;
      border-bottom: none;
      margin-top: 6px;
      padding-top: 6px;
    }

    #receiptPreview img.qr {
      display: block;
      margin: 8px auto;
      width: 120px;
      height: 120px;
      object-fit: contain;
    }

    /* hide helper QR container */
    #printQRTemp {
      position: absolute;
      left: -9999px;
      top: -9999px;
      visibility: hidden;
      height: 1px;
      width: 1px;
      overflow: hidden;
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
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm shadow-md no-print">
    <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 flex flex-col md:flex-row items-start md:items-center gap-3">
      <div class="flex items-center justify-between md:justify-start w-full md:w-auto gap-3">

        <!-- Logo + Text -->
        <div class="flex items-center gap-3">
          <img src="icons/image-removebg-preview (1).png" class="w-12 h-12 object-contain" />

          <div>
            <div class="text-emerald-700 font-bold text-lg">HydroTrack</div>
            <div class="text-xs text-gray-500 -mt-1 hidden md:block">Home Dashboard</div>
          </div>
        </div>

        <!-- Logout (mobile only) -->
        <a href="logout.php" class="md:hidden text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
          Logout
        </a>

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
          <span id="cashierNameDisplay" class="font-semibold text-emerald-700">
            <?php echo htmlspecialchars($cashierNameFromSession); ?>
          </span>
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
      class="sidebar-link active btn-home flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
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

  <!-- Total Sales floating -->


  <!-- MAIN -->
  <main class="main ">
    <!-- SUMMARY CARDS -->
    <div class="mb-6">
      <div class="grid grid-cols-5 gap-4 mb-4">
        <div class="summary-card">
          <div class="text-xs text-gray-500">Total Sales</div>
          <div id="cardTotalSales" class="summary-value text-emerald-700">₱0.00</div>
        </div>
        <div class="summary-card">
          <div class="text-xs text-gray-500">Total Refill</div>
          <div id="cardRefillCount" class="summary-value text-cyan-700">0</div>
        </div>

        <div class="summary-card">
          <div class="text-xs text-gray-500">Total Borrowed</div>
          <div id="cardBorrowedCount" class="summary-value text-yellow-600">0</div>
        </div>

        <div class="summary-card">
          <div class="text-xs text-gray-500">Total buyed</div>
          <div id="cardBuyedCount" class="summary-value text-red-600">0</div>
        </div>

        <div class="summary-card">
          <div class="text-xs text-gray-500">Total Returned</div>
          <div id="cardReturnedCount" class="summary-value text-lime-700">0</div>
        </div>
      </div>
    </div>

    <div class="flex flex-col md:flex-row gap-4 app-height">
      <!-- LEFT: POS -->
      <section class="flex-1 bg-white rounded-xl shadow p-5 overflow-auto report-card">
        <h2 class="text-emerald-700 text-lg font-semibold mb-3 text-center">Sales Transaction</h2>

        <form id="salesForm" class="space-y-3" onsubmit="return false;">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <select id="transactionType" class="mt-1 block w-full rounded border px-3 py-2" required>
                <option value="" disabled selected>Transaction Type</option>
                <option value="Refill">Refill</option>
                <option value="Borrow">Borrow</option>
                <option value="Buy Gallon">Buy Gallon</option>
              </select>
            </div>

            <div>
              <select id="deliveryOption" class="mt-1 block w-full rounded border px-3 py-2" required>
                <option value="" disabled selected>Select option</option>
                <option value="Delivery">Delivery</option>
                <option value="Pickup">Pickup</option>
                <option value="Walk-In">Walk-In</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>

              <select id="waterSize" class="mt-1 block w-full rounded border px-3 py-2" required>
                <option value="" disabled selected>Gallon size</option>
                <option value="20LiterSlim" data-key="20LiterSlim">20L Slim</option>
                <option value="20LiterRound" data-key="20LiterRound">20L Round</option>
                <option value="10Liter" data-key="10Liter">10L</option>
                <option value="5Liter" data-key="5Liter">5L</option>
              </select>
            </div>

            <div>

              <input id="quantity" type="number" min="1" value="Quantity"
                class="mt-1 block w-full rounded border px-3 py-2" placeholder="Quantity" />
            </div>
          </div>

          <div>

            <input id="customerName" type="text" placeholder="Customer Name"
              class="mt-1 block w-full rounded border px-3 py-2" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>

              <input id="phoneNumber" type="text" placeholder="Phone Number"
                class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>

              <input id="address" type="text" placeholder="Address"
                class="mt-1 block w-full rounded border px-3 py-2" />
            </div>
            <div>

              <select id="paymentMethod" class="mt-1 block w-full rounded border px-3 py-2">
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
              <label class="block text-sm font-medium">Unit Price</label>
              <input id="unitPrice" readonly class="mt-1 block w-full rounded border px-3 py-2 bg-gray-50" />
            </div>

            <div>
              <label class="block text-sm font-medium">Total</label>
              <input id="totalPrice" readonly class="mt-1 block w-full rounded border px-3 py-2 bg-gray-50" />
            </div>

            <div>
              <label class="block text-sm font-medium">Amount Paid</label>
              <input id="amountPaid" type="number" class="mt-1 block w-full rounded border px-3 py-2" />
            </div>

            <div>
              <label class="block text-sm font-medium">Change</label>
              <input id="changeAmount" readonly class="mt-1 block w-full rounded border px-3 py-2 bg-gray-50" />
            </div>
          </div>

          <div class="flex gap-3">
            <button id="processSaleBtn" type="button"
              class="flex-1 bg-emerald-600 text-white rounded py-2 hover:bg-emerald-700">Process Transaction</button>
            <button id="clearReceiptBtn" type="button"
              class="bg-gray-100 text-gray-700 rounded py-2 px-4 hover:bg-gray-200">Clear</button>
          </div>
        </form>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="report-card">
            <h4 class="font-semibold text-gray-800 mb-2">Receipt Preview</h4>
            <div id="receiptPreview" class="min-h-[120px] text-sm bg-emerald-50 p-3 rounded overflow-auto">
              Receipt will appear here...
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2">
              <button id="receiptPrint" class="bg-gray-700 text-white py-2 rounded">🖨️ Print Receipt</button>
              <button id="downloadSalesBtn" class="bg-emerald-200 text-emerald-800 py-2 rounded">⬇️ Export
                Sales</button>
            </div>
          </div>

          <div class="report-card mb-4">
            <h4 class="font-semibold text-gray-800 mb-2">Payments</h4>
            <div id="paymentSummary" class="text-sm muted">No payments yet</div>
          </div>
        </div>

      </section>

      <!-- RIGHT: Stock Management + quick panels -->
      <aside class="w-full md:w-[320px]">
        <div class="report-card mb-4">
          <div class="flex items-center justify-between mb-2">
            <div>
              <h4 class="text-lg font-bold text-emerald-700">Stock Management</h4>
              <div class="text-xs text-gray-500">Scan gallons to track inventory</div>
            </div>
          </div>
          <div id="stockManagementList" class="space-y-2 text-sm min-h-[100px]">
            <div class="text-center text-gray-400 py-4">Scan QR codes to add gallons</div>
          </div>
          <div class="mt-3">
            <button id="scannerBtn" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Scan QR
              Code</button>
            <div class="mt-3">
              <button id="removeAllStockBtn"
                class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 font-semibold">🗑️ Remove All
                Stock</button>
            </div>
          </div>
        </div>
      </aside>
    </div>

    <!-- QR Scanner Modal -->
    <div id="scannerModal" class="modal">
      <div class="bg-white rounded-lg shadow-lg p-4 md:p-6 w-full max-w-md mx-4">
        <h3 class="text-lg md:text-xl font-semibold mb-4 text-emerald-700 text-center">Scan Gallon QR Code</h3>
        <div id="qr-reader" class="mx-auto mb-4" style="width:100%; max-width:300px;"></div>
        <div class="mb-4">
          <div class="text-sm text-gray-600 mb-2">Scanned Serial:</div>
          <div id="scannedSerial"
            class="text-base md:text-lg font-bold text-emerald-700 text-center p-2 bg-gray-50 rounded break-all">—</div>
        </div>
        <div class="flex gap-2 md:gap-3 mb-3">
          <button id="inBtn"
            class="flex-1 bg-green-600 text-white px-3 md:px-4 py-2 md:py-3 rounded hover:bg-green-700 font-semibold text-sm md:text-base">IN</button>
          <button id="outBtn"
            class="flex-1 bg-red-600 text-white px-3 md:px-4 py-2 md:py-3 rounded hover:bg-red-700 font-semibold text-sm md:text-base">OUT</button>
        </div>
        <button id="closeModal"
          class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm md:text-base">Close
          Scanner</button>
      </div>
    </div>
    </div>
  </main>
  <!-- Expenses modal -->
  <div id="expensesModal" class="modal" aria-hidden="true">
    <div class="bg-white rounded-xl p-4 md:p-6 shadow-lg w-11/12 max-w-md text-left mx-4">
      <div class="flex justify-between items-center mb-3">
        <h2 class="text-base md:text-lg font-bold text-emerald-700">Expenses</h2>
        <button id="closeExpensesModal" class="text-gray-600 hover:text-emerald-700 text-xl">✖</button>
      </div>
      <label class="text-xs font-medium">Description (Optional for Return)</label>
      <input id="expenseDesc" class="w-full border rounded px-2 py-2 mb-2 text-sm"
        placeholder="e.g. Snacks, Gas, Return change" />
      <div class="grid grid-cols-2 gap-2 mb-3">
        <div>
          <label class="text-xs font-medium">Amount Taken</label>
          <input id="expenseAmount" type="number" step="any" class="w-full border rounded px-2 py-2 text-sm"
            placeholder="0.00" />
        </div>
        <div>
          <label class="text-xs font-medium">Amount Returned</label>
          <input id="expenseReturnAmount" type="number" step="any" class="w-full border rounded px-2 py-2 text-sm"
            placeholder="0.00" />
        </div>
      </div>
      <div class="flex gap-2">
        <button id="saveExpense"
          class="flex-1 bg-emerald-600 text-white rounded py-2 text-sm md:text-base">Save</button>
        <button id="cancelExpense" class="flex-1 bg-gray-100 rounded py-2 text-sm md:text-base">Cancel</button>
      </div>
    </div>
  </div>
  </div>

  <!-- hidden helper for QR generation -->
  <div id="printQRTemp"></div>

  <script>
    function startScanner() {
      const scanner = document.getElementById('qr-reader');
      if (!scanner) return;

      if (typeof Html5Qrcode === 'undefined') {
        alert('QR Scanner library hindi na-load. Please refresh ang page.');
        console.error('Html5Qrcode library is not loaded');
        return;
      }

      if (html5QrcodeScanner) {
        // Already running
        return;
      }

      html5QrcodeScanner = new Html5Qrcode("qr-reader");
      html5QrcodeScanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        async (decodedText) => {
          // Prevent rapid firing if same
          if (currentScannedSerial === decodedText && currentScannedSerials.length === 0) return;

          // Parse Payload
          let isBatch = false;
          let batchSerials = [];

          try {
            const json = JSON.parse(decodedText);
            // Check for Receipt Payload
            if (json.items && Array.isArray(json.items)) {
              isBatch = true;
              json.items.forEach(i => {
                if (i.serial_numbers && Array.isArray(i.serial_numbers)) {
                  batchSerials.push(...i.serial_numbers);
                }
              });
            } else if (json.serial) {
              // Single structured
              decodedText = json.serial;
            } else if (Array.isArray(json)) {
              // Flat array
              isBatch = true;
              batchSerials = json;
            }
          } catch (e) { }

          if (isBatch) {
            await handleBatchScan(batchSerials);
            return;
          }

          // Single Serial Logic
          currentScannedSerial = decodedText;
          currentScannedSerials = []; // Clear batch
          let displaySerial = decodedText;

          document.getElementById('scannedSerial').innerHTML = `
            <div class="font-bold text-lg">${displaySerial}</div>
            <div class="text-sm text-gray-500">Validating...</div>
          `;

          // API Validate Single
          try {
            const res = await fetch('api/scan_validate.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ serial: currentScannedSerial })
            });
            const valData = await res.json();

            const statusColors = {
              'in': 'text-green-600',
              'out': 'text-red-600',
              'borrowed': 'text-yellow-600',
              'damaged': 'text-gray-600'
            };

            if (valData.success && valData.exists) {
              const item = valData.data;
              const colorClass = statusColors[(item.status || '').toLowerCase()] || 'text-gray-800';
              document.getElementById('scannedSerial').innerHTML = `
                    <div class="font-bold text-lg text-emerald-700">${item.serial_number}</div>
                    <div class="text-sm text-gray-600">${item.product_name} (${item.size})</div>
                    <div class="font-bold ${colorClass}">Status: ${item.status.toUpperCase()}</div>
                  `;
            } else {
              // Check if it's a system-formatted serial but not yet in DB
              const prefix = (currentScannedSerial || '').substring(0, 3);
              const validPrefixes = ['SLM', 'RND', '10L', '05L'];

              if (validPrefixes.includes(prefix)) {
                document.getElementById('scannedSerial').innerHTML = `
                      <div class="font-bold text-lg text-blue-600">${displaySerial}</div>
                      <div class="bg-blue-50 text-blue-700 px-3 py-2 rounded-lg border border-blue-200 mt-2">
                        <div class="font-bold">✨ New Gallon Detected</div>
                        <div class="text-xs">This QR is not yet in your system.</div>
                      </div>
                      <div class="text-xs text-gray-500 mt-2">Click <strong>IN</strong> to register it into stock.</div>
                    `;
              } else {
                document.getElementById('scannedSerial').innerHTML = `
                      <div class="font-bold text-red-600">${displaySerial}</div>
                      <div class="text-xs text-red-500">❌ Invalid / Not in Database</div>
                    `;
              }
            }

          } catch (e) {
            console.error(e);
            document.getElementById('scannedSerial').textContent = "Server Error";
          }
        },
        (errorMessage) => { }
      ).catch(err => {
        console.error('Scanner error:', err);
        alert('Cannot access camera. Please allow camera permissions.');
      });
    }

    async function handleBatchScan(serials) {
      if (!serials || serials.length === 0) return;

      // Validate Batch
      try {
        currentScannedSerials = serials; // Store as array
        currentScannedSerial = ''; // Clear single global

        const res = await fetch('api/scan_validate.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ serials: serials })
        });
        const data = await res.json();

        if (data.success && data.batch) {
          const total = data.results.length;
          const existsCount = data.results.filter(r => r.exists).length;
          const inCount = data.results.filter(r => r.exists && (r.status || '').toLowerCase() === 'in').length;
          const outCount = data.results.filter(r => r.exists && (r.status || '').toLowerCase() === 'out').length;

          document.getElementById('scannedSerial').innerHTML = `
                    <div class="font-bold text-lg text-blue-700">Batch Scanned</div>
                    <div class="text-sm text-gray-600">Total Items: ${total}</div>
                    <div class="flex gap-2 justify-center mt-2 text-xs">
                        <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded">In: ${inCount}</span>
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded">Out: ${outCount}</span>
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">Unknown: ${total - existsCount}</span>
                    </div>
                 `;
        }
      } catch (e) { console.error(e); document.getElementById('scannedSerial').innerText = "Batch Validation Error"; }
    }

    /* IN button - Add gallon to stock (Batch or Single) */
    document.getElementById('inBtn')?.addEventListener('click', async () => {
      // Determine what we are processing
      let itemsToProcess = [];
      if (currentScannedSerials.length > 0) itemsToProcess = currentScannedSerials;
      else if (currentScannedSerial) itemsToProcess = [currentScannedSerial];

      if (itemsToProcess.length === 0) return alert('Please scan a QR code first!');

      try {
        const res = await fetch('api/inventory_update.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            serials: itemsToProcess,
            action: 'in'
          })
        });
        const data = await res.json();

        if (data.success) {
          // Automatic Customer Update Logic
          const sale = findSaleBySerials(itemsToProcess);
          if (sale) {
            const arr = getSales();
            const idx = arr.findIndex(s => s.id === sale.id);
            if (idx !== -1) {
              arr[idx].returned = true;
              arr[idx].returnedAt = new Date().toISOString();
              setSales(arr);

              // Add to history for traceability
              const history = safeParse(HISTORY_KEY, []);
              history.push({
                date: new Date().toISOString(),
                action: 'Returned Gallons (Auto)',
                customer: sale.customer || '—',
                details: `${sale.qty || 1}x ${sale.size || '—'} - Returned via QR Scan`
              });
              save(HISTORY_KEY, history);

              addLog(`Auto-returned for ${sale.customer}: ${sale.invoice || sale.id}`, 'text-emerald-600');
            }
          }

          alert(`✅ Successfully Processed: ${itemsToProcess.length} item(s) are now IN stock.`);
          syncAllDisplays();
          document.getElementById('scannedSerial').innerHTML = '—';
          currentScannedSerial = '';
          currentScannedSerials = [];
        } else {
          alert(`❌ Failed: ${data.message || (data.errors || []).join('\n')}`);
        }
      } catch (e) {
        alert(`❌ System Error: ${e.message}`);
      }
    });

    function findSaleBySerials(serials) {
      if (!serials || serials.length === 0) return null;
      const sales = getSales();
      // Find matching un-returned transactions
      const matches = sales.filter(s =>
        (s.type === 'Borrow' || s.type === 'Buy Gallon') &&
        !s.returned &&
        s.serials && s.serials.some(r => serials.includes(r))
      );
      // Sort by date descending to get the most recent one if duplicates exist
      matches.sort((a, b) => new Date(b.date) - new Date(a.date));
      return matches[0] || null;
    }

    /* OUT button - Remove from stock (Batch or Single) */
    document.getElementById('outBtn')?.addEventListener('click', async () => {
      let itemsToProcess = [];
      if (currentScannedSerials.length > 0) itemsToProcess = currentScannedSerials;
      else if (currentScannedSerial) itemsToProcess = [currentScannedSerial];

      if (itemsToProcess.length === 0) return alert('Please scan a QR code first!');

      try {
        const res = await fetch('api/inventory_update.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            serials: itemsToProcess,
            action: 'out'
          })
        });
        const data = await res.json();

        if (data.success) {
          alert(`✅ Successfully Removed (OUT): ${itemsToProcess.length} items.`);
          syncAllDisplays();
          document.getElementById('scannedSerial').innerHTML = '—';
          currentScannedSerial = '';
          currentScannedSerials = [];
        } else {
          alert(`❌ Failed: ${data.message || (data.errors || []).join('\n')}`);
        }
      } catch (e) {
        alert(`❌ System Error: ${e.message}`);
      }
    });

    // Legacy function removed. See renderStockManagementList definition below.

    // ---- Globals & storage helpers (ADD) ----
    let currentScannedSerial = '';
    let html5QrcodeScanner = null;
    let currentAmountPaid = ''; // Stable input storage

    const GALLONS_KEY = 'hydro_gallons_v1';
    const USED_QR_KEY = 'hydro_used_qr_v1';
    const PAYMENTS_KEY = 'hydro_payments_v1';
    function getGallons() { try { return JSON.parse(localStorage.getItem(GALLONS_KEY)) || []; } catch (e) { return []; } }
    function setGallons(arr) { localStorage.setItem(GALLONS_KEY, JSON.stringify(arr || [])); }
    function getUsedQRCodes() { try { return JSON.parse(localStorage.getItem(USED_QR_KEY)) || []; } catch (e) { return []; } }
    function setUsedQRCodes(arr) { localStorage.setItem(USED_QR_KEY, JSON.stringify(arr || [])); }
    function isQRCodeUsed(serial) { return getUsedQRCodes().includes(serial); }
    function markQRCodeAsUsed(serial) { const used = getUsedQRCodes(); if (!used.includes(serial)) { used.push(serial); setUsedQRCodes(used); } }
    function getPayments() { try { return JSON.parse(localStorage.getItem(PAYMENTS_KEY)) || []; } catch (e) { return []; } }
    function setPayments(arr) { localStorage.setItem(PAYMENTS_KEY, JSON.stringify(arr || [])); }

    // Sync cards + lists in one call
    function syncAllDisplays() {
      recomputeTotalsAndPayments();
      renderTransactionsTable();
      renderStockManagementList();
      updateStockDisplayAll();
    }

    // ---- Scanner open/close wiring (ADD) ----
    document.addEventListener('DOMContentLoaded', () => {
      const scannerBtn = document.getElementById('scannerBtn');
      const scannerModal = document.getElementById('scannerModal');
      const closeModalBtn = document.getElementById('closeModal');

      scannerBtn?.addEventListener('click', () => {
        scannerModal?.classList.add('active');
        // small timeout para sure na visible ang container bago i-start
        setTimeout(startScanner, 100);
      });

      closeModalBtn?.addEventListener('click', async () => {
        scannerModal?.classList.remove('active');
        try {
          if (html5QrcodeScanner) {
            await html5QrcodeScanner.stop();
            html5QrcodeScanner = null;
          }
        } catch (e) { }
        currentScannedSerial = '';
        const scannedEl = document.getElementById('scannedSerial');
        if (scannedEl) scannedEl.textContent = '—';
      });

      // Close modal kapag nag-click sa backdrop
      scannerModal?.addEventListener('click', async (e) => {
        if (e.target === scannerModal) {
          scannerModal.classList.remove('active');
          try {
            if (html5QrcodeScanner) {
              await html5QrcodeScanner.stop();
              html5QrcodeScanner = null;
            }
          } catch (e) { }
          currentScannedSerial = '';
          const scannedEl = document.getElementById('scannedSerial');
          if (scannedEl) scannedEl.textContent = '—';
        }
      });
    });

    // Helper function to get cashier name
    function _getCashier() { return localStorage.getItem(CASHIER_KEY) || '—'; }

    // ---- Consolidated renderStockManagementList (Database Driven) ----
    async function renderStockManagementList() {
      const wrap = document.getElementById('stockManagementList');
      if (!wrap) return;

      try {
        const res = await fetch('api/get_stock_details.php');
        const json = await res.json();

        if (!json.success) {
          console.error('Failed to load stock');
          return;
        }

        const details = json.details;
        const summary = json.summary;

        wrap.innerHTML = '';
        const labels = {
          '20LiterSlim': '20L Slim',
          '20LiterRound': '20L Round',
          '10Liter': '10L',
          '5Liter': '5L'
        };

        const keys = ['20LiterSlim', '20LiterRound', '10Liter', '5Liter'];

        keys.forEach(key => {
          const info = summary[key] || { in: 0, out: 0, borrowed: 0 };
          const items = details[key] || [];

          // Container
          const container = document.createElement('div');
          container.className = 'bg-gray-50 rounded mb-2 overflow-hidden border border-gray-200';

          // Header (Click to toggle)
          const header = document.createElement('div');
          header.className = 'flex items-center justify-between p-3 cursor-pointer hover:bg-gray-100 transition-colors';
          header.onclick = () => {
            const body = container.querySelector('.stock-body');
            const icon = container.querySelector('.toggle-icon');
            if (body.style.display === 'none') {
              body.style.display = 'block';
              icon.textContent = '▼';
            } else {
              body.style.display = 'none';
              icon.textContent = '▶';
            }
          };

          header.innerHTML = `
                <div>
                  <div class="flex items-center gap-2">
                    <span class="toggle-icon text-xs text-gray-400">▶</span>
                    <span class="font-medium text-gray-800">${labels[key]}</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1 pl-5">
                    In: <span class="text-emerald-600 font-bold">${info.in}</span> | 
                    Out: <span class="text-red-500 font-bold">${info.out + info.borrowed}</span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-lg text-emerald-700">${info.in}</div>
                  <div class="text-[10px] uppercase text-gray-400 font-semibold tracking-wider">Available</div>
                </div>
            `;

          // Body
          const body = document.createElement('div');
          body.className = 'stock-body bg-white border-t border-gray-100 p-2 max-h-[200px] overflow-y-auto';
          body.style.display = 'none';

          // Filter to show only IN items
          const visibleItems = items.filter(item => (item.status || '').toLowerCase() === 'in');

          if (visibleItems.length === 0) {
            body.innerHTML = '<div class="text-xs text-gray-400 text-center italic py-2">No available stock</div>';
          } else {
            visibleItems.forEach(item => {
              const status = (item.status || '').toLowerCase();
              const isIn = status === 'in';
              const colorClass = 'text-emerald-600 bg-emerald-50 border-emerald-100';
              const statusLabel = 'IN';

              const row = document.createElement('div');
              row.className = `flex justify-between items-center text-xs p-2 mb-1 rounded border ${colorClass}`;
              row.innerHTML = `
                          <span class="font-mono font-semibold">${item.serial}</span>
                          <span class="font-bold text-[10px] px-1.5 py-0.5 rounded uppercase tracking-wide bg-white bg-opacity-60 border border-opacity-20 shadow-sm">${statusLabel}</span>
                      `;
              body.appendChild(row);
            });
          }

          container.appendChild(header);
          container.appendChild(body);
          wrap.appendChild(container);

          if (document.getElementById('stock_' + key)) {
            document.getElementById('stock_' + key).textContent = info.in;
          }
        });

        checkLowStock(summary);

      } catch (e) {
        console.error(e);
        wrap.innerHTML = '<div class="text-xs text-red-400">Connection error</div>';
      }
    }

    // ---- Standardize money (OPTIONAL) ----
    function money(v) { return '₱' + (Number(v || 0)).toFixed(2); }

    // Removed duplicate inBtn/outBtn listeners to avoid conflicts
    // Main listeners are defined above.












    /* ---------------------------
       home.html JS (uses hydro_ keys)
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

    function safeParse(key, fallback) { try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch (e) { return fallback; } }
    function save(key, val) { localStorage.setItem(key, JSON.stringify(val)); }
    function uid() { return 'id' + Date.now() + Math.floor(Math.random() * 9000); }
    function money(v) { return '₱' + (Number(v || 0)).toFixed(2); }
    function fmtDate(iso) { try { return new Date(iso).toLocaleString(); } catch (e) { return iso || ''; } }
    function escapeHtml(s) { return String(s || '').replace(/[&<>"'`]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '`': '&#96;' })[c]); }

    /* defaults */
    if (!localStorage.getItem(STOCK_KEY)) save(STOCK_KEY, { '20LiterSlim': 0, '20LiterRound': 0, '10Liter': 0, '5Liter': 0 });
    if (!localStorage.getItem(SALES_KEY)) save(SALES_KEY, []);
    if (!localStorage.getItem(EXPENSES_KEY)) save(EXPENSES_KEY, []);
    if (!localStorage.getItem(DEBTS_KEY)) save(DEBTS_KEY, []);
    if (!localStorage.getItem(PAID_DEBTS_KEY)) save(PAID_DEBTS_KEY, []);
    if (!localStorage.getItem(MONEYBOX_KEY)) save(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0 });
    if (!localStorage.getItem(DELETED_KEY)) save(DELETED_KEY, []);
    if (!localStorage.getItem(HISTORY_KEY)) save(HISTORY_KEY, []);

    /* Helpers */
    function getSales() { return safeParse(SALES_KEY, []); }
    function setSales(a) { save(SALES_KEY, a); }
    function getStock() { return safeParse(STOCK_KEY, { '20LiterSlim': 0, '20LiterRound': 0, '10Liter': 0, '5Liter': 0 }); }
    function setStock(s) { save(STOCK_KEY, s); checkLowStock(); }
    function getExpenses() { return safeParse(EXPENSES_KEY, []); }
    function getDebts() { return safeParse(DEBTS_KEY, []); }
    function setDebts(a) { save(DEBTS_KEY, a); }
    function getDeleted() { return safeParse(DELETED_KEY, []); }
    function pushDeleted(item) { const d = getDeleted(); d.push(item); save(DELETED_KEY, d); }

    /**
     * API: Log action
     */
    async function logActionToServer(action, customer, details) {
      try {
        const payload = {
          action: action,
          customer: customer || '—',
          details: details || '',
          date: new Date().toISOString().slice(0, 19).replace('T', ' ') // 'YYYY-MM-DD HH:MM:SS' for MySQL
        };

        await fetch('api/log_action.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
      } catch (e) {
        console.error('Failed to log to server:', e);
      }
    }

    /* API: Save Sale */
    async function saveSaleToServer(sale) {
      try {
        await fetch('api/save_sale.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(sale)
        });
      } catch (e) { console.error('Failed to save sale to server:', e); }
    }

    /* Low Stock Warning System */
    const LOW_STOCK_THRESHOLD = 5;
    const STOCK_WARNING_SHOWN_KEY = 'hydro_stock_warning_shown_v1';

    function checkLowStock(data) {
      if (!data) return; // Should be passed from renderStockManagementList

      const lowStockItems = [];
      const labels = {
        '20LiterSlim': '20L Slim',
        '20LiterRound': '20L Round',
        '10Liter': '10L',
        '5Liter': '5L'
      };

      Object.keys(data).forEach(key => {
        const qty = data[key]?.in || 0;
        if (qty <= LOW_STOCK_THRESHOLD && qty > 0) {
          lowStockItems.push(`${labels[key]}: ${qty} left`);
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

      // Only show if different from last warning or after 30 minutes
      if (lastShown !== warningKey) {
        content.innerHTML = items.map(item =>
          `<div style="margin-bottom:4px;">⚠️ ${escapeHtml(item)}</div>`
        ).join('') + '<div style="margin-top:8px; font-weight:600;">Please restock soon!</div>';

        toast.classList.add('show');
        localStorage.setItem(STOCK_WARNING_SHOWN_KEY, warningKey);

        // Auto-hide after 10 seconds
        setTimeout(() => {
          toast.classList.remove('show');
        }, 10000);
      }
    }

    /* UI updates */
    function updateStockDisplayAll() {
      // Alias to new DB-driven renderer to support legacy calls
      if (typeof renderStockManagementList === 'function') renderStockManagementList();
    }

    /* totals & payments + summary cards */
    /* totals & payments + summary cards */
    function recomputeTotalsAndPayments() {
      const sales = getSales();
      let payments = {};
      let grossSalesValue = 0;
      let refillGallons = 0, borrowGallons = 0, buyedGallons = 0, returnedGallons = 0;

      sales.forEach(s => {
        const amountPaid = Number(s.amountPaid || 0);
        grossSalesValue += Number(s.total || 0);

        // Card Counts
        const qty = Number(s.qty || 1);
        if (s.type === 'Refill') refillGallons += qty;
        if (s.type === 'Borrow' && !s.returned) borrowGallons += qty;
        if (s.type === 'Buy Gallon') buyedGallons += qty;
        if (s.returned) returnedGallons += qty;

        // Payment Summary
        const pm = s.paymentMethod || s.payment || 'Cash';
        if (amountPaid > 0) {
          payments[pm] = (payments[pm] || 0) + amountPaid;
        }
      });

      // 2. Calculate Debt & Partial Payments
      const debts = getDebts();
      const paidDebts = safeParse(PAID_DEBTS_KEY, []);

      let debtPaymentTotal = 0; // All cash from debts (initial + subsequent)
      let debtBalanceTotal = 0;
      let partialBalanceTotal = 0;
      let fullyPaidTotal = 0;

      debts.forEach(d => {
        const paid = Number(d.paid || 0);
        const amount = Number(d.amount || 0);
        const remaining = Math.max(0, amount - paid);
        debtPaymentTotal += paid;
        if (paid > 0 && remaining > 0) partialBalanceTotal += remaining;
        else if (paid === 0 && remaining > 0) debtBalanceTotal += amount;
      });

      paidDebts.forEach(d => {
        const amount = Number(d.amount || 0);
        const paid = Number(d.paid || amount);
        fullyPaidTotal += amount;
      });

      // 2.5 Debt Collections Today (Source: PAYMENTS_KEY)
      const paymentsLog = getPayments();
      const todayIso = new Date().toISOString().split('T')[0];
      let debtCollectionsToday = 0;
      paymentsLog.forEach(p => {
        if (p.date && p.date.startsWith(todayIso)) {
          debtCollectionsToday += Number(p.amount || 0);
        }
      });

      // 3. Moneybox & Monitoring
      const mb = safeParse(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0 });
      const amountTaken = Number(mb.out || 0);
      const amountReturned = Number(mb.returned || 0);
      const totalCoins = Number(mb.coins || 0);

      // 4. Synchronization Formula (Match dailysalesreport)
      // We need Linked Debt Principal to calculate Adjusted Sales
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

      // Adjusted Cash Sales = Gross - Debt value + Payments received today
      const adjustedSales = grossSalesValue - linkedDebtPrincipal + debtCollectionsToday;
      const finalTotalSales = adjustedSales - amountTaken + amountReturned;

      // Net Cash matches Total Sales per requirement
      const netCash = finalTotalSales;

      // Save Source of Truth
      save(TOTAL_SALES_KEY, Number(finalTotalSales));

      // Update DOM
      if (document.getElementById('totalSalesDisplay')) document.getElementById('totalSalesDisplay').textContent = finalTotalSales.toFixed(2);
      if (document.getElementById('cardTotalSales')) document.getElementById('cardTotalSales').textContent = money(finalTotalSales);

      // Cash Monitoring
      if (document.getElementById('totalexpense')) document.getElementById('totalexpense').textContent = money(amountTaken);
      if (document.getElementById('totalcoins')) document.getElementById('totalcoins').textContent = money(totalCoins);
      if (document.getElementById('netCashDisplay')) document.getElementById('netCashDisplay').textContent = money(netCash);

      // Display 'totalCashDisplay' as Adjusted Sales or Total Cash depending on UI expectation
      // Here we set it to follow dailysalesreport's lead if applicable
      const totalCashInDrawer = adjustedSales + totalCoins; // Total physical cash before expenses
      if (document.getElementById('totalCashDisplay')) document.getElementById('totalCashDisplay').textContent = money(totalCashInDrawer);

      if (document.getElementById('paidBalance')) document.getElementById('paidBalance').textContent = money(debtPaymentTotal);
      // Note: User asked "Payments entered in Amount Paid... recordings". 
      // 'paidBalance' ID was used for "Amount Paid" in the breakdown.
      // Let's assume 'paidBalance' here represents the Total Collected from Debts/Partials as per context.

      // Update Balances (Right side / Debtors view)
      // These IDs might not exist in home.php but good to have consistency if blocks are added
      if (document.getElementById('debtBalance')) document.getElementById('debtBalance').textContent = money(debtBalanceTotal);
      if (document.getElementById('partialBalance')) document.getElementById('partialBalance').textContent = money(partialBalanceTotal);
      // 'paidBalance' in Debtors card usually means "Value of debts that were fully paid".
      // But in Cash Monitoring, "Amount Paid" likely means Cash In.
      // We will leave 'paidBalance' as debtPaymentTotal for the Cash Monitoring Section if ID duplicates.
      // If there are separate sections, we needed separate IDs. 
      // unique IDs in home.php: 'paidBalance' is in Cash Monitoring.

      // Card Counts
      if (document.getElementById('cardRefillCount')) document.getElementById('cardRefillCount').textContent = refillGallons;
      if (document.getElementById('cardBorrowedCount')) document.getElementById('cardBorrowedCount').textContent = borrowGallons;
      if (document.getElementById('cardBuyedCount')) document.getElementById('cardBuyedCount').textContent = buyedGallons;
      if (document.getElementById('cardReturnedCount')) document.getElementById('cardReturnedCount').textContent = returnedGallons;

      // Payment Summary
      const ps = document.getElementById('paymentSummary');
      if (ps) {
        ps.innerHTML = '';
        if (Object.keys(payments).length === 0) ps.innerHTML = '<div class="muted">No payments recorded</div>';
        else { Object.keys(payments).forEach(k => { const row = document.createElement('div'); row.className = 'flex justify-between'; row.innerHTML = `<div>${escapeHtml(k)}</div><div>${money(payments[k])}</div>`; ps.appendChild(row); }); }
      }

      updateStockDisplayAll();
      renderTransactionsTable();
    }

    /* pricing & helpers */
    function getUnitPrice(tx, size) {
      if (tx === 'Refill') { if (size === '5Liter') return 5; if (size === '10Liter') return 10; return 20; }
      if (tx === 'Borrow') return 200;
      if (tx === 'Buy Gallon') {
        if (size === '20LiterSlim' || size === '20LiterRound') return 200;
        return 0; // Other sizes must be entered manually
      }
      return 0;
    }

    function updateUnitAndTotal() {
      const tx = document.getElementById('transactionType')?.value;
      const size = document.getElementById('waterSize')?.value;
      const qty = Number(document.getElementById('quantity')?.value || 1);
      const unitInput = document.getElementById('unitPrice');
      const totalInput = document.getElementById('totalPrice');

      let unit = getUnitPrice(tx, size);

      // Allow manual entry if unit price is 0 (or unknown)
      if (unitInput) {
        if (unit > 0) {
          unitInput.value = unit.toFixed(2);
          unitInput.readOnly = true;
          unitInput.classList.add('bg-gray-50');
        } else {
          // If 0, user might want to enter it manually
          unitInput.readOnly = false;
          unitInput.classList.remove('bg-gray-50');
          // Don't auto-clear if user already typed something different? 
          // Actually, if they change Type/Size, we probably should reset or recalculate.
          // For now, if system says 0, we default to what was there or 0? 
          // Let's set it to 0.00 or empty to encourage typing.
          // But wait, if I type 50, then change Quantity, I don't want Unit Price to reset to 0 if I didn't change Type/Size.
          // The 'input' event on quantity calls this.
          // We need to differentiate WHO called this.

          // Simplified approach: If system returns 0, we don't force a value unless it was previously system-set? 
          // Actually, let's just use the current value of the input if it's not readonly?
          // No, safer to just set to 0.00 if it's a fresh detection requiring manual input.
          // BUT this runs on quantity change too.
          // If I entered 150, changed qty to 2, getUnitPrice returns 0. I shouldn't overwrite 150.

          if (unit === 0) {
            // If the field is currently readonly, it means we entered a new state where we should unlock it.
            // We can assume if it's readonly, we should reset it. If not, preserve user input.
            if (unitInput.readOnly) {
              unitInput.value = ''; // Clear it for manual entry
            }
            // If it's already editable, leave it alone (user might be typing)
          }
        }
      }

      // Re-read unit price from input (in case it was manual)
      const currentUnit = parseFloat(unitInput?.value || 0);
      if (totalInput) totalInput.value = (currentUnit * qty).toFixed(2);
      updateChange();
    }

    function updateChange() {
      const total = parseFloat(document.getElementById('totalPrice')?.value || 0);
      // Use stable variable for calculation to prevent input glitches
      const paid = parseFloat(currentAmountPaid || 0);
      const change = paid - total;
      if (document.getElementById('changeAmount')) document.getElementById('changeAmount').value = change >= 0 ? change.toFixed(2) : '';
    }

    /* restock/reset */
    function restock(key) { const s = getStock(); s[key] = (Number(s[key] || 0) + 10); setStock(s); recomputeTotalsAndPayments(); addLog(`+10 ${key} (manual)`); renderStockManagementList(); }
    function resetStock(key) { const s = getStock(); s[key] = 50; setStock(s); recomputeTotalsAndPayments(); addLog(`Reset ${key} to 50`); renderStockManagementList(); }
    async function removeAllStock() {
      if (!confirm('Are you sure you want to remove ALL stock? This will mark all gallons in the database as OUT.')) return;

      try {
        const res = await fetch('api/inventory_remove_all.php', { method: 'POST' });
        const data = await res.json();

        if (data.success) {
          // Update local copy for legacy code
          const s = getStock();
          Object.keys(s).forEach(key => s[key] = 0);
          setStock(s);

          // Update history log in UI
          const history = safeParse(HISTORY_KEY, []);
          history.push({
            date: new Date().toISOString(),
            action: 'Bulk Stock Removal',
            customer: '—',
            details: 'All inventory items marked as OUT via system control.'
          });
          save(HISTORY_KEY, history);

          syncAllDisplays();
          addLog('Removed all stock (Sync with DB)', 'text-red-600');
          alert('✅ All stock has been marked as OUT in the database.');
        } else {
          alert('❌ Failed: ' + data.message);
        }
      } catch (e) {
        alert('❌ System Error: ' + e.message);
      }
    }

    /* log */
    function addLog(message, colorClass = "text-gray-700") { const p = document.createElement('p'); p.className = colorClass + " text-xs"; p.textContent = `${new Date().toLocaleTimeString()} — ${message}`; const log = document.getElementById('logContent'); if (log) { log.prepend(p); if (log.children.length > 80) log.removeChild(log.lastChild); } }

    /* render table */
    function renderTransactionsTable() {
      const tbody = document.getElementById('transactionsBody');
      if (!tbody) return;
      tbody.innerHTML = '';
      const sales = getSales().slice().sort((a, b) => new Date(b.date) - new Date(a.date));
      sales.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="p-2">${escapeHtml(fmtDate(s.date))}</td>
                    <td class="p-2">${escapeHtml(s.invoice || ('INV-' + (s.id || '')))}</td>
                    <td class="p-2"><span class="click-customer" data-customer="${escapeHtml(s.customer || '')}">${escapeHtml(s.customer || '—')}</span></td>
                    <td class="p-2">${escapeHtml(s.size || s.item || '—')}</td>
                    <td class="p-2 text-right">${Number(s.qty || 0)}</td>
                    <td class="p-2 text-right">₱${Number(s.unit || 0).toFixed(2)}</td>
                    <td class="p-2 text-right">₱${Number(s.total || 0).toFixed(2)}</td>
                    <td class="p-2">${escapeHtml(s.paymentMethod || s.payment || 'Cash')}</td>
                    <td class="p-2">${escapeHtml(s.type || '')}</td>`;
        tr.addEventListener('dblclick', () => { if (!confirm('Create a debt record from this transaction?')) return; createDebtFromSale(s); alert('Debt created.'); });
        tbody.appendChild(tr);
      });
      document.querySelectorAll('.click-customer').forEach(el => {
        el.style.cursor = 'pointer';
        el.addEventListener('click', () => {
          const name = el.getAttribute('data-customer') || el.textContent;
          openCustomerPanel(name);
        });
      });
    }

    /* customer panel */
    function openCustomerPanel(name) {
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

    /* create debt from sale */
    function createDebtFromSale(saleObj) {
      const debts = getDebts();
      // FIX: Initialize paid with the amountPaid from the sale to capture the initial payment
      debts.push({
        id: uid(),
        sourceSaleId: saleObj.id || null,
        date: new Date().toISOString(),
        customer: saleObj.customer || '',
        phone: saleObj.phone || '',
        amount: Number(saleObj.total || 0),
        paid: Number(saleObj.amountPaid || 0),
        notes: '',
        invoice: saleObj.invoice || ''
      });
      setDebts(debts);

      // Add to history
      const history = safeParse(HISTORY_KEY, []);
      history.push({
        date: new Date().toISOString(),
        action: 'Debt Created',
        customer: saleObj.customer || '—',
        details: `Amount: ${money(saleObj.total || 0)} - Invoice: ${saleObj.invoice || '—'}`
      });
      save(HISTORY_KEY, history);

      // Log to server
      logActionToServer('Debt Created', saleObj.customer || '—', `Amount: ${money(saleObj.total || 0)} - Invoice: ${saleObj.invoice || '—'}`);
    }

    /* mark returned (DB Integrated) */
    async function markSaleReturned(id) {
      const arr = getSales();
      const idx = arr.findIndex(s => s.id === id);
      if (idx === -1) return false;
      if (arr[idx].returned) return true;

      // Check if we have serials
      const itemSerials = arr[idx].serials || [];
      if (itemSerials.length === 0) {
        // Fallback logic if no serials stored (legacy data)
        // If we cannot identify serials, we cannot easily return them to 'in' safely in DB without scanning.
        // Prompt user to use scanner?
        if (!confirm("This transaction has no tracked serial numbers. Do you want to try an unconditional return (might not update database stock properly)? Suggestion: Use the 'Scan QR' feature instead.")) return;
        // If verified, proceed with local update only? Or error?
        // Let's just do local logic for legacy.
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
            return false; // Abort
          }
        } catch (e) {
          alert("Network error processing return.");
          return false;
        }
      }

      arr[idx].returned = true;
      arr[idx].returnedAt = new Date().toISOString();

      // Legacy local update (visual only, list relies on DB now)
      // const st = getStock(); const key = arr[idx].size;
      // if (key && st[key] !== undefined) { st[key] = (Number(st[key]) || 0) + (Number(arr[idx].qty) || 1); setStock(st); }

      setSales(arr);

      // Add to history
      const history = safeParse(HISTORY_KEY, []);
      history.push({
        date: new Date().toISOString(),
        action: 'Returned Gallons',
        customer: arr[idx].customer || '—',
        details: `${arr[idx].qty || 1}x ${arr[idx].size || '—'} - Invoice: ${arr[idx].invoice || id}`
      });
      save(HISTORY_KEY, history);

      // Log to server
      logActionToServer('Returned Gallons', arr[idx].customer || '—', `${arr[idx].qty || 1}x ${arr[idx].size || '—'} - Invoice: ${arr[idx].invoice || id}`);

      syncAllDisplays();
      addLog(`Sale returned: ${arr[idx].invoice || idx}`);
      return true;
    }

    /* delete sale */
    function deleteSaleById(id) {
      const arr = getSales();
      const idx = arr.findIndex(s => s.id === id);
      if (idx === -1) return false;
      const [removed] = arr.splice(idx, 1);
      setSales(arr);
      removed.deletedAt = new Date().toISOString();
      removed.deletedBy = localStorage.getItem(CASHIER_KEY) || '—';
      pushDeleted(removed);
      recomputeTotalsAndPayments();

      // Add to history
      const history = safeParse(HISTORY_KEY, []);
      history.push({
        date: new Date().toISOString(),
        action: 'Sale Deleted',
        customer: removed.customer || '—',
        details: `${removed.qty || 1}x ${removed.size || '—'} - Invoice: ${removed.invoice || id}`
      });
      save(HISTORY_KEY, history);

      // Log to server
      logActionToServer('Sale Deleted', removed.customer || '—', `${removed.qty || 1}x ${removed.size || '—'} - Invoice: ${removed.invoice || id}`);

      addLog(`Deleted sale: ${removed.invoice || removed.id}`);
      return true;
    }

    /* clear all */
    function clearAllRecordsAndResetEverything() {
      if (!confirm('Clear ALL records and reset stock, debts, moneybox, and totals? This will archive current sales to Deleted History.')) return;
      const currentSales = getSales().slice();
      const deleted = getDeleted();
      currentSales.forEach(s => { s.deletedAt = new Date().toISOString(); s.deletedBy = localStorage.getItem(CASHIER_KEY) || '—'; deleted.push(s); });
      save(DELETED_KEY, deleted);
      save(SALES_KEY, []);
      save(DEBTS_KEY, []); save(PAID_DEBTS_KEY, []);
      save(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0 });
      save(TOTAL_SALES_KEY, 0);
      save(STOCK_KEY, { '20LiterSlim': 0, '20LiterRound': 0, '10Liter': 0, '5Liter': 0 });
      recomputeTotalsAndPayments();
      addLog('Cleared all records & reset system.');
      alert('All records cleared and system reset.');
    }

    /* export CSV */
    function exportSalesCSV() {
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
    }

    /* UI: stock management list (DB Driven) */
    async function renderStockManagementList() {
      const wrap = document.getElementById('stockManagementList');
      if (!wrap) return;

      // Show small loader if it's the first load or takes time
      if (wrap.innerHTML === '' || wrap.innerText.includes('Scan QR')) {
        wrap.innerHTML = '<div class="text-center py-6"><div class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-emerald-600 border-r-2 border-transparent"></div><div class="text-xs text-gray-500 mt-2">Syncing with database...</div></div>';
      }

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
        wrap.innerHTML = `<div class="text-center text-red-500 py-4 text-xs font-semibold">❌ Sync Failed<br><span class="text-[10px] opacity-70">${e.message}</span></div>`;
      }
    }

    /*     --- Receipt generation & printing (Format B) --- */

    /**
     * buildReceiptHtml(transaction, qrDataURL)
     * returns HTML string for thermal 58mm receipt (Format B)
     */
    function buildReceiptHtml(tx, qrDataUrl) {
      // tx expected fields: invoice, date, customer, phone, address, type, size, qty, unit, total, paymentMethod, cashier, amountPaid, change
      const dt = tx.date ? new Date(tx.date).toLocaleString() : new Date().toLocaleString();
      const totalStr = money(tx.total || 0);
      const paidStr = money(tx.amountPaid || 0);
      const changeStr = money(tx.change || 0);
      const returnedLine = tx.returned ? `<div style="font-size:10px;margin-top:4px;">Returned: Yes</div>` : '';

      // Calculate Debt / Remaining Balance
      const totalVal = Number(tx.total || 0);
      const paidVal = Number(tx.amountPaid || 0);
      const debtVal = Math.max(0, totalVal - paidVal);
      const debtLine = debtVal > 0.01
        ? `<div class="line"><div><strong>Remaining Debt</strong></div><div><strong>${money(debtVal)}</strong></div></div>`
        : '';

      // 1. Get serial numbers from tx (source of truth)
      const serialNumbers = tx.serials || [];
      const serialsDisplay = serialNumbers.length > 0
        ? `<div class="sep"></div><div class="small"><strong>Serial Numbers:</strong></div>${serialNumbers.map(s => `<div class="small">• ${escapeHtml(s)}</div>`).join('')}`
        : '';

      const html = `
  <!doctype html>
  <html>
  <head>
    <meta charset="utf-8" />
    <title>Receipt - ${escapeHtml(tx.invoice || '')}</title>
    <style>
      :root{ --width:58mm; }
      body{ font-family: Arial, Helvetica, sans-serif; margin:0; padding:6px; width:var(--width); -webkit-print-color-adjust: exact; color:#111; }
      .brand{ text-align:center; font-weight:800; font-size:13px; }
      .muted{ font-size:10px; color:#444; text-align:center; margin-bottom:6px; }
      .line{ display:flex; justify-content:space-between; font-size:11px; padding:4px 0; }
      .sep{ border-top:1px dashed #333; margin:8px 0; }
      .total{ display:flex; justify-content:space-between; font-weight:800; font-size:12px; padding-top:6px; }
      .meta{ font-size:10px; text-align:center; margin-top:8px; }
      .qr-container{ border: 2px solid #000; padding: 10px; display: inline-block; background: white; margin-top: 8px; border-radius: 8px; }
      img.qr{ display:block; width:160px; height:160px; object-fit:contain; image-rendering: pixelated; }
      .small{ font-size:10px; }
      @media print{
        @page{ size:58mm auto; margin:0; }
        body{ margin:0; }
      }
    </style>
  </head>
  <body>
    <div class="brand">HydroTrack Water Refilling Station</div>
    <div class="muted">1392 L. Barrios St. Poblacion Kalibo, Aklan<br>O94 6272 8011</div>
    <div class="sep"></div>

    <div class="small"><strong>Customer:</strong> ${escapeHtml(tx.customer || '—')}</div>
    <div class="small"><strong>Phone:</strong> ${escapeHtml(tx.phone || '—')}</div>
    <div class="small"><strong>Address:</strong> ${escapeHtml(tx.address || '—')}</div>

    <div class="sep"></div>

    <div class="line"><div>Transaction</div><div>${escapeHtml(tx.type || '—')}</div></div>
    <div class="line"><div>Delivery</div><div>${escapeHtml(tx.deliveryOption || tx.delivery || 'Pickup')}</div></div>
    <div class="line"><div>Gallon</div><div>${escapeHtml(tx.size || '—')} (x${Number(tx.qty || 1)})</div></div>
    <div class="line"><div>Payment</div><div>${escapeHtml(tx.paymentMethod || 'Cash')}</div></div>

    ${serialsDisplay}

    <div class="sep"></div>

    <div class="line"><div>Total</div><div>${totalStr}</div></div>
    <div class="line"><div>Paid</div><div>${paidStr}</div></div>
    <div class="line"><div>Change</div><div>${changeStr}</div></div>
    ${debtLine}

    <div class="sep"></div>

    <div style="text-align:center;margin-top:6px;">
      <div class="small">Cashier: ${escapeHtml(tx.cashier || _getCashier())}</div>
      ${qrDataUrl ? `
        <div style="margin-top:8px;">
          <div class="qr-container">
            <img class="qr" src="${qrDataUrl}" alt="QR">
          </div>
          <div class="small" style="margin-top:4px; font-weight:bold;">SCAN TO RETURN</div>
        </div>` : ''}
      <div class="small" style="margin-top:8px;">Date: ${escapeHtml(dt)}</div>
      ${returnedLine}
    </div>

    <div class="meta small">Thank you for your patronage!</div>
  </body>
  </html>
  `;
      return html;
    }

    /**
     * generateQRCodeDataUrl(text) -> Promise<string> (dataURL)
     * Uses QRCode lib to create a temporary element then extract dataURL from img/canvas.
     */
    function generateQRCodeDataUrl(text, size = 400) {
      return new Promise((resolve, reject) => {
        const holder = document.getElementById('printQRTemp');
        if (!holder) {
          console.error('printQRTemp element not found');
          resolve(null);
          return;
        }

        // Check if QRCode library is loaded
        if (typeof QRCode === 'undefined') {
          console.error('QRCode library is not loaded');
          resolve(null);
          return;
        }

        holder.innerHTML = '';
        try {
          const qrDiv = document.createElement('div');
          holder.appendChild(qrDiv);
          // High-contrast, clean QR code. Medium error correction is a good balance.
          const qr = new QRCode(qrDiv, {
            text: text,
            width: size,
            height: size,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
          });
          // small delay to allow library to render
          setTimeout(() => {
            // try img first
            const img = qrDiv.querySelector('img');
            if (img && img.src) {
              resolve(img.src);
              holder.innerHTML = '';
              return;
            }
            const canvas = qrDiv.querySelector('canvas');
            if (canvas) {
              try {
                const data = canvas.toDataURL('image/png');
                resolve(data);
                holder.innerHTML = '';
                return;
              } catch (e) {
                // fallback
              }
            }
            // if none available, fallback to reject
            resolve(null);
            holder.innerHTML = '';
          }, 120);
        } catch (e) {
          console.error('QR generation error:', e);
          holder.innerHTML = '';
          resolve(null);
        }
      });
    }

    /**
     * printReceipt(tx)
     * opens a new window with receipt content and calls print
     */
    async function printReceipt(tx) {
      // Rule: Do NOT generate QR code for Refill transactions.
      let qrPayloadStr = '';

      if (tx.type === 'Borrow' || tx.type === 'Buy Gallon') {
        const serials = tx.serials || [];
        if (serials.length > 0) {
          // Compact payload: Array of serials is the most readable and easy to scan
          qrPayloadStr = JSON.stringify(serials);
        }
      }

      const qrDataUrl = qrPayloadStr ? await generateQRCodeDataUrl(qrPayloadStr, 300) : null;
      const receiptHtml = buildReceiptHtml(tx, qrDataUrl);

      const w = window.open('', '_blank', 'width=420,height=700');
      if (!w) { alert('Popup blocked. Allow popups for this site to print receipts.'); return; }
      w.document.open();
      w.document.write(receiptHtml);
      w.document.close();
      // wait a bit for resources to load (image)
      setTimeout(() => {
        try {
          w.focus();
          w.print();
        } catch (e) { }
        // close window after print dialog opened (give user time)
        setTimeout(() => { try { w.close(); } catch (e) { } }, 1200);
      }, 400);
    }

    /* render receipt preview (in-page) */
    async function renderReceiptPreview(tx) {
      const preview = document.getElementById('receiptPreview');
      if (!preview) return;
      preview.innerHTML = ''; // build simplified HTML for preview
      const dt = tx.date ? new Date(tx.date).toLocaleString() : new Date().toLocaleString();

      // Get available serial numbers for the gallon size
      const gallons = getGallons();
      // If tx.serials is already attached (from processSale), use it. otherwise fallback to lookup
      let serialNumbers = tx.serials || [];
      if (serialNumbers.length === 0 && tx.type !== 'Refill') { // Only fetch if not refill
        const availableGallons = gallons.filter(g => g.size === tx.size && g.status === 'In');
        serialNumbers = availableGallons.map(g => g.serial).slice(0, Number(tx.qty || 1));
      }

      const serialsDisplay = serialNumbers.length > 0
        ? `<div style="margin-top:6px;font-size:11px;"><strong>Serial Numbers:</strong></div>${serialNumbers.map(s => `<div style="font-size:10px;">• ${escapeHtml(s)}</div>`).join('')}`
        : '';

      // Calculate Debt for Preview
      const totalVal = Number(tx.total || 0);
      const paidVal = Number(tx.amountPaid || 0);
      const debtVal = Math.max(0, totalVal - paidVal);
      const debtLine = debtVal > 0.01
        ? `<div class="line"><div><strong>Remaining Debt</strong></div><div><strong>${money(debtVal)}</strong></div></div>`
        : '';

      // QR Generation
      let qrSrc = null;
      if ((tx.type === 'Borrow' || tx.type === 'Buy Gallon') && serialNumbers.length > 0) {
        try {
          // Use compact format for preview too
          qrSrc = await generateQRCodeDataUrl(JSON.stringify(serialNumbers), 240);
        } catch (e) { qrSrc = null; }
      }

      const html = `
    <div class="brand">HydroTrack Water Refilling Station</div>
    <div class="muted small">1392 L. Barrios St. Poblacion Kalibo, Aklan<br>O94 6272 8011</div>
    <div style="font-size:11px;"><strong>Customer:</strong> ${escapeHtml(tx.customer || '—')}</div>
    <div style="font-size:11px;"><strong>Phone:</strong> ${escapeHtml(tx.phone || '—')}</div>
    <div style="font-size:11px;"><strong>Address:</strong> ${escapeHtml(tx.address || '—')}</div>
    <div style="margin-top:6px" class="line"><div>Transaction</div><div>${escapeHtml(tx.type || '—')}</div></div>
    <div class="line"><div>Delivery</div><div>${escapeHtml(tx.deliveryOption || tx.delivery || 'Pickup')}</div></div>
    <div class="line"><div>Gallon</div><div>${escapeHtml(tx.size || '—')} (x${Number(tx.qty || 1)})</div></div>
    ${serialsDisplay}
    <div class="line"><div>Total</div><div>${money(tx.total || 0)}</div></div>
    <div class="line"><div>Paid</div><div>${money(tx.amountPaid || 0)}</div></div>
    <div class="line"><div>Change</div><div>${money(tx.change || 0)}</div></div>
    ${debtLine}
    <div class="line"><div>Payment</div><div>${escapeHtml(tx.paymentMethod || 'Cash')}</div></div>
    <div style="text-align:center; margin-top:10px;">
      <div style="font-size:12px;">Cashier: ${escapeHtml(tx.cashier || _getCashier())}</div>
      ${qrSrc ? `<div style="margin-top:10px;">
          <div style="border:2px solid #000; padding:8px; display:inline-block; background:white; border-radius:8px;">
            <img class="qr" src="${qrSrc}" alt="QR" style="display:block; width:140px; height:140px; object-fit:contain; image-rendering:pixelated;">
          </div>
          <div class="small" style="margin-top:4px; font-weight:bold; font-size:9px;">SCAN TO RETURN</div>
        </div>` : ''}
      <div style="font-size:10px; margin-top:10px;">${escapeHtml(dt)}</div>
    </div>
  `;
      preview.innerHTML = html;
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

      // Customer Autocomplete System
      const searchBox = document.getElementById('searchBox');
      const customerDropdown = document.getElementById('customerDropdown');

      function getUniqueCustomers() {
        const sales = getSales();
        const customersMap = new Map();

        sales.forEach(sale => {
          const name = sale.customer?.trim();
          const phone = sale.phone?.trim();
          const address = sale.address?.trim();

          if (name && name !== '—') {
            if (!customersMap.has(name)) {
              customersMap.set(name, {
                name: name,
                phone: phone || '',
                address: address || '',
                lastDate: sale.date
              });
            } else {
              // Update with most recent info
              const existing = customersMap.get(name);
              if (new Date(sale.date) > new Date(existing.lastDate)) {
                customersMap.set(name, {
                  name: name,
                  phone: phone || existing.phone,
                  address: address || existing.address,
                  lastDate: sale.date
                });
              }
            }
          }
        });

        return Array.from(customersMap.values()).sort((a, b) =>
          new Date(b.lastDate) - new Date(a.lastDate)
        );
      }

      function showCustomerDropdown(query) {
        const customers = getUniqueCustomers();
        const filtered = customers.filter(c => {
          const searchTerm = query.toLowerCase();
          return c.name.toLowerCase().includes(searchTerm) ||
            (c.phone && c.phone.includes(searchTerm)) ||
            (c.address && c.address.toLowerCase().includes(searchTerm));
        });

        if (filtered.length === 0) {
          customerDropdown.classList.remove('show');
          return;
        }

        customerDropdown.innerHTML = filtered.slice(0, 10).map(customer => `
      <div class="customer-item" data-name="${escapeHtml(customer.name)}" data-phone="${escapeHtml(customer.phone)}" data-address="${escapeHtml(customer.address)}">
        <div class="name">${escapeHtml(customer.name)}</div>
        <div class="details">
          ${customer.phone ? `📞 ${escapeHtml(customer.phone)}` : ''} 
          ${customer.address ? `• 📍 ${escapeHtml(customer.address)}` : ''}
        </div>
      </div>
    `).join('');

        customerDropdown.classList.add('show');

        // Add click handlers to items
        customerDropdown.querySelectorAll('.customer-item').forEach(item => {
          item.addEventListener('click', () => {
            const name = item.getAttribute('data-name');
            const phone = item.getAttribute('data-phone');
            const address = item.getAttribute('data-address');

            // Fill form fields
            if (document.getElementById('customerName')) document.getElementById('customerName').value = name;
            if (document.getElementById('phoneNumber')) document.getElementById('phoneNumber').value = phone;
            if (document.getElementById('address')) document.getElementById('address').value = address;

            // Clear search and hide dropdown
            searchBox.value = '';
            customerDropdown.classList.remove('show');

            // Focus on next field
            const nextField = document.getElementById('transactionType');
            if (nextField) nextField.focus();
          });
        });
      }

      if (searchBox && customerDropdown) {
        let searchTimeout;
        searchBox.addEventListener('input', (e) => {
          clearTimeout(searchTimeout);
          const query = e.target.value.trim();

          if (query.length < 2) {
            customerDropdown.classList.remove('show');
            return;
          }

          searchTimeout = setTimeout(() => {
            showCustomerDropdown(query);
          }, 200);
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', (e) => {
          if (!searchBox.contains(e.target) && !customerDropdown.contains(e.target)) {
            customerDropdown.classList.remove('show');
          }
        });

        // Show all customers when focusing empty search
        searchBox.addEventListener('focus', () => {
          if (searchBox.value.trim().length === 0) {
            const customers = getUniqueCustomers();
            if (customers.length > 0) {
              showCustomerDropdown('');
            }
          }
        });
      }

      document.getElementById('transactionType')?.addEventListener('input', updateUnitAndTotal);
      document.getElementById('waterSize')?.addEventListener('input', updateUnitAndTotal);
      document.getElementById('quantity')?.addEventListener('input', updateUnitAndTotal);
      document.getElementById('amountPaid')?.addEventListener('input', (e) => {
        currentAmountPaid = e.target.value; // Store raw input
        updateChange();
        updateProcessButtonState();
      });

      // Function to enable/disable Process Transaction button based on Amount Paid
      function updateProcessButtonState() {
        const amountPaid = document.getElementById('amountPaid')?.value || '';
        const processBtn = document.getElementById('processSaleBtn');
        if (processBtn) {
          if (!amountPaid || Number(amountPaid) <= 0) {
            processBtn.disabled = true;
            processBtn.style.opacity = '0.5';
            processBtn.style.cursor = 'not-allowed';
          } else {
            processBtn.disabled = false;
            processBtn.style.opacity = '1';
            processBtn.style.cursor = 'pointer';
          }
        }
      }

      // Initialize button state
      updateProcessButtonState();

      // Ensure stable variable is synced on load
      if (document.getElementById('amountPaid')) currentAmountPaid = document.getElementById('amountPaid').value;

      function setCashierName(v) {
        if (!v) { localStorage.removeItem(CASHIER_KEY); document.getElementById('cashierNameDisplay').textContent = '—'; return; }
        localStorage.setItem(CASHIER_KEY, v); document.getElementById('cashierNameDisplay').textContent = v;
      }
      document.getElementById('saveCashierBtn')?.addEventListener('click', () => setCashierName(document.getElementById('cashierInputTop')?.value || document.getElementById('cashierInput')?.value));
      document.getElementById('saveCashierBtnTop')?.addEventListener('click', () => setCashierName(document.getElementById('cashierInput')?.value || document.getElementById('cashierInputTop')?.value));
      document.getElementById('removeCashierBtn')?.addEventListener('click', () => { setCashierName(''); document.getElementById('cashierInputTop').value = ''; document.getElementById('cashierInput').value = ''; });
      document.getElementById('removeCashierBtnTop')?.addEventListener('click', () => { setCashierName(''); document.getElementById('cashierInputTop').value = ''; document.getElementById('cashierInput').value = ''; });


      // LOGIC FIX: Prioritize PHP Session Name as Source of Truth
      const renderedName = document.getElementById('cashierNameDisplay')?.innerText?.trim();

      if (renderedName && renderedName !== '—') {
        // We have a session name -> Update LocalStorage and inputs to match
        localStorage.setItem(CASHIER_KEY, renderedName);
        if (document.getElementById('cashierInput')) document.getElementById('cashierInput').value = renderedName;
        if (document.getElementById('cashierInputTop')) document.getElementById('cashierInputTop').value = renderedName;
      } else {
        // No session name (or '—'), fallback to localStorage
        const savedCashier = localStorage.getItem(CASHIER_KEY);
        if (savedCashier) {
          if (document.getElementById('cashierInput')) document.getElementById('cashierInput').value = savedCashier;
          if (document.getElementById('cashierInputTop')) document.getElementById('cashierInputTop').value = savedCashier;
          if (document.getElementById('cashierNameDisplay')) document.getElementById('cashierNameDisplay').textContent = savedCashier;
        }
      }

      // Sync cashier name and totals across tabs
      window.addEventListener('storage', (e) => {
        if (e.key === CASHIER_KEY) {
          const newName = e.newValue || '—';
          document.getElementById('cashierNameDisplay').textContent = newName;
          const inputTop = document.getElementById('cashierInputTop');
          if (inputTop) inputTop.value = newName;
        }
        // Recompute totals if sales, debts, or moneybox changes in another tab
        if ([SALES_KEY, DEBTS_KEY, PAID_DEBTS_KEY, MONEYBOX_KEY].includes(e.key)) {
          recomputeTotalsAndPayments();
        }
      });

      document.getElementById('processSaleBtn')?.addEventListener('click', async () => {
        const tx = document.getElementById('transactionType')?.value;
        const size = document.getElementById('waterSize')?.value;
        const qty = Number(document.getElementById('quantity')?.value || 1);
        const customer = document.getElementById('customerName')?.value || '';
        const phone = document.getElementById('phoneNumber')?.value || '';
        const address = document.getElementById('address')?.value || '';
        const deliveryOption = document.getElementById('deliveryOption')?.value || 'Pickup';
        const paymentMethod = document.getElementById('paymentMethod')?.value || 'Cash';
        const unit = Number(document.getElementById('unitPrice')?.value || 0);
        const total = Number(document.getElementById('totalPrice')?.value || 0);
        const amountPaid = Number(document.getElementById('amountPaid')?.value || 0);
        const change = amountPaid - total;
        if (!tx || !size) { alert('Select transaction type and size'); return; }

        let usedSerials = [];

        const processBtn = document.getElementById('processSaleBtn');
        if (processBtn) { processBtn.disabled = true; processBtn.textContent = 'Processing...'; }

        // DB Check for Stock (Borrow/Buy)
        if (tx === 'Borrow' || tx === 'Buy Gallon') {
          try {
            const res = await fetch('api/get_available_serials.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ size: size, qty: qty })
            });
            const data = await res.json();

            if (!data.success) {
              alert(`⚠️ Transaction Blocked: ${data.message}\nAvailable: ${data.available || 0}, Requested: ${qty}`);
              if (processBtn) { processBtn.disabled = false; processBtn.textContent = 'Process Transaction'; }
              return;
            }
            usedSerials = data.serials;
          } catch (e) {
            console.error(e);
            alert('Error connecting to database to check stock.');
            if (processBtn) { processBtn.disabled = false; processBtn.textContent = 'Process Transaction'; }
            return;
          }
        }

        const sale = { id: uid(), date: new Date().toISOString(), type: tx, size: size, qty: qty, customer, phone, address, deliveryOption, delivery: deliveryOption, paymentMethod, unit, total, amountPaid: amountPaid, change: change, payment: paymentMethod, invoice: 'INV-' + Date.now(), cashier: _getCashier(), serials: usedSerials };

        // Commit Stock Change to DB
        if (usedSerials.length > 0) {
          try {
            // Determine status based on transaction type
            const targetStatus = (tx === 'Borrow') ? 'borrowed' : 'out';

            const res = await fetch('api/inventory_update.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                action: 'out',
                serials: usedSerials,
                customer: customer,
                status: targetStatus
              })
            });
            const d = await res.json();
            if (!d.success) {
              alert('Failed to update stock in database. Sale cancelled.\n' + (d.errors || []).join('\n'));
              if (processBtn) { processBtn.disabled = false; processBtn.textContent = 'Process Transaction'; }
              return;
            }
            addLog(`Stock decreased (DB): ${qty}x ${size} (${tx})`, 'text-red-600');
          } catch (e) {
            alert('Critical Error: Failed to update stock. Please report.');
            if (processBtn) { processBtn.disabled = false; processBtn.textContent = 'Process Transaction'; }
            return;
          }
        } else if (tx === 'Refill') {
          addLog(`Refill transaction: ${qty}x ${size} (No stock change)`, 'text-blue-600');
        }

        // Save Sale to Database (including serials)
        await saveSaleToServer(sale);

        if (processBtn) { processBtn.disabled = false; processBtn.textContent = 'Process Transaction'; }

        // Auto-create debt if amount paid is insufficient
        if (amountPaid < total) {
          const debtAmount = total - amountPaid;
          const debts = getDebts();
          debts.push({
            id: uid(),
            sourceSaleId: sale.id,
            date: new Date().toISOString(),
            customer: customer,
            phone: phone,
            amount: Number(total), // Total value of the transaction
            paid: Number(amountPaid), // Initial amount paid
            initialPaid: Number(amountPaid), // Track for cash flow
            notes: `Incomplete payment for ${sale.invoice}. Total: ${money(total)}, Paid: ${money(amountPaid)}`,
            invoice: sale.invoice
          });
          setDebts(debts);
          addLog(`Debt created automatically: ${customer} - ${money(debtAmount)} remaining`, 'text-orange-600');

          // Record initial payment in payments log if > 0 (Added back for monitoring)
          if (amountPaid > 0) {
            const payments = getPayments();
            payments.push({
              id: uid(),
              date: new Date().toISOString(),
              customer: customer,
              invoice: sale.invoice || '—',
              amount: Number(amountPaid),
              remaining: Number(debtAmount),
              status: 'Not Fully Paid (Initial)'
            });
            setPayments(payments);
          }

          // Log separate debt creation event to history (New)
          try {
            const history = JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];
            history.push({
              date: new Date().toISOString(),
              action: 'Debt Created',
              customer: customer || '—',
              details: `Auto-created from Sale INV-${sale.invoice || ''} - Amount: ${money(debtAmount)}`
            });
            localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
          } catch (e) { console.error(e); }

          // Log to server (Auto Debt)
          logActionToServer('Debt Created (Auto)', customer || '—', `Auto-created from Sale INV-${sale.invoice || ''} - Amount: ${money(debtAmount)}`);
        }

        // Add to history
        const history = safeParse(HISTORY_KEY, []);
        history.push({
          date: new Date().toISOString(),
          action: tx === 'Refill' ? 'Customer Refilled' : 'Borrowed Gallons',
          customer: customer,
          details: `${qty}x ${size} - Amount Paid: ${money(amountPaid)}`
        });
        save(HISTORY_KEY, history);

        // Log to server (Main Transaction)
        logActionToServer(
          tx === 'Refill' ? 'Customer Refilled' : (tx === 'Borrow' ? 'Borrowed Gallons' : 'Buy Gallon'),
          customer,
          `${qty}x ${size} - Amount Paid: ${money(amountPaid)}`
        );

        // Save Sale to Database (New)
        saveSaleToServer(sale);

        // save sale
        const arr = getSales(); arr.push(sale); setSales(arr); recomputeTotalsAndPayments(); renderStockManagementList();
        // fill receipt preview and attach lastTx for printing
        const cashierName = localStorage.getItem(CASHIER_KEY) || (document.getElementById('cashierInput')?.value) || '—';
        const txForReceipt = {
          id: sale.id, invoice: sale.invoice, date: sale.date, customer, phone, address, deliveryOption, type: tx, size, qty, unit, total, paymentMethod, cashier: cashierName,
          amountPaid: amountPaid, change: change, serials: sale.serials
        };
        // store last receipt in temp
        window._lastReceiptTx = txForReceipt;
        renderReceiptPreview(txForReceipt);
        if (document.getElementById('receipt')) document.getElementById('receipt').innerHTML = `<div><strong>Invoice:</strong> ${escapeHtml(sale.invoice)}</div><div><strong>Customer:</strong> ${escapeHtml(customer)}</div><div><strong>Item:</strong> ${escapeHtml(size)} x${qty}</div><div><strong>Total:</strong> ${money(total)}</div>`;
        if (window.QRCode && document.getElementById('qrcode')) { document.getElementById('qrcode').innerHTML = ''; try { new QRCode(document.getElementById('qrcode'), { text: sale.invoice, width: 120, height: 120 }); } catch (e) { } }
        addLog(`Sale processed: ${sale.invoice} ${customer} ${size} x${qty} (${tx})`);

        // Clear all inputs in Sales Transaction section
        if (document.getElementById('customerName')) document.getElementById('customerName').value = '';
        if (document.getElementById('phoneNumber')) document.getElementById('phoneNumber').value = '';
        if (document.getElementById('address')) document.getElementById('address').value = '';
        if (document.getElementById('quantity')) document.getElementById('quantity').value = '1';
        if (document.getElementById('unitPrice')) document.getElementById('unitPrice').value = '';
        if (document.getElementById('totalPrice')) document.getElementById('totalPrice').value = '';
        if (document.getElementById('amountPaid')) {
          document.getElementById('amountPaid').value = '';
          currentAmountPaid = ''; // Reset stable variable
        }
        if (document.getElementById('changeAmount')) document.getElementById('changeAmount').value = '';
        updateProcessButtonState();
        syncAllDisplays();
        // optional: auto-print small thermal if you want -- currently only preview
      });

      document.getElementById('clearReceiptBtn')?.addEventListener('click', () => { if (document.getElementById('receipt')) document.getElementById('receipt').innerHTML = 'Receipt will appear here...'; if (document.getElementById('qrcode')) document.getElementById('qrcode').innerHTML = ''; if (document.getElementById('receiptPreview')) document.getElementById('receiptPreview').innerHTML = 'Receipt will appear here...'; window._lastReceiptTx = null; });

      document.querySelectorAll('#exportAllCsv, #downloadSalesBtn, #exportAllCsv').forEach(b => { if (b) b.addEventListener('click', exportSalesCSV); });

      renderStockManagementList();

      document.getElementById('openStockAdjustBtn')?.addEventListener('click', () => alert('Open stock adjust modal (not included).'));

      // Helper function for logging to history
      function addHistoryLog(logEntry) {
        const history = safeParse(HISTORY_KEY, []);
        history.push(logEntry);
        save(HISTORY_KEY, history);
      }

      document.getElementById('openExpensesBtn')?.addEventListener('click', () => document.getElementById('expensesModal').classList.add('active'));
      document.getElementById('closeExpensesModal')?.addEventListener('click', () => document.getElementById('expensesModal').classList.remove('active'));
      document.getElementById('cancelExpense')?.addEventListener('click', () => document.getElementById('expensesModal').classList.remove('active'));
      document.getElementById('saveExpense')?.addEventListener('click', () => {
        const desc = document.getElementById('expenseDesc').value || 'Unspecified';
        const amt = Number(document.getElementById('expenseAmount').value || 0);
        const ret = Number(document.getElementById('expenseReturnAmount').value || 0);

        if (amt === 0 && ret === 0) { alert('Provide an amount to Take or Return'); return; }

        const mb = safeParse(MONEYBOX_KEY, { out: 0, returned: 0, coins: 0 });

        if (amt > 0) {
          const ex = getExpenses();
          ex.push({ id: uid(), desc, amount: amt, date: new Date().toISOString() });
          save(EXPENSES_KEY, ex);

          mb.out = (Number(mb.out) || 0) + amt;

          addHistoryLog({
            date: new Date().toISOString(),
            action: 'Amount Taken / Expense',
            customer: '—',
            details: `${desc} - Amount: ${money(amt)}`
          });
          logActionToServer('Amount Taken', '—', `${desc} - Amount: ${money(amt)}`);
        }

        if (ret > 0) {
          // Following dailysalesreport logic: Return reduces the 'out' total
          mb.out = Math.max(0, (Number(mb.out) || 0) - ret);

          addHistoryLog({
            date: new Date().toISOString(),
            action: 'Amount Returned',
            customer: '—',
            details: `${desc} - Returned: ${money(ret)}`
          });
          logActionToServer('Amount Returned', '—', `${desc} - Returned: ${money(ret)}`);
        }

        save(MONEYBOX_KEY, mb);

        document.getElementById('expenseDesc').value = '';
        document.getElementById('expenseAmount').value = '';
        document.getElementById('expenseReturnAmount').value = '';

        document.getElementById('expensesModal').classList.remove('active');
        recomputeTotalsAndPayments();
      });

      document.querySelectorAll('#refreshStockBtn, #refreshReportBtn').forEach(b => b?.addEventListener('click', () => { syncAllDisplays(); }));

      document.getElementById('receiptPrint')?.addEventListener('click', () => {
        const tx = window._lastReceiptTx;
        if (!tx) { alert('Walang receipt data. Process a transaction muna.'); return; }
        printReceipt(tx);
      });

      document.getElementById('printBtn')?.addEventListener('click', () => window.print());

      document.getElementById('clearAllBtn')?.addEventListener('click', clearAllRecordsAndResetEverything);

      document.getElementById('removeAllStockBtn')?.addEventListener('click', removeAllStock);

      document.getElementById('searchBox')?.addEventListener('input', () => {
        const q = (document.getElementById('searchBox')?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#transactionsBody tr');
        rows.forEach(row => {
          const txt = row.textContent.toLowerCase();
          row.style.display = txt.includes(q) ? '' : 'none';
        });
      }

      );

      recomputeTotalsAndPayments();
      renderTransactionsTable();
      updateStockDisplayAll();
      renderStockManagementList();

      // Check for low stock on page load
      checkLowStock();

      // Periodic stock check every 5 minutes
      setInterval(checkLowStock, 300000);
    });

    /* API for other pages */
    function pushSale(sale) {
      const arr = getSales(); arr.push(sale); setSales(arr);
      const s = getStock();
      if (s[sale.size] !== undefined) { s[sale.size] = Math.max(0, (Number(s[sale.size]) || 0) - (Number(sale.qty) || 1)); setStock(s); }
      recomputeTotalsAndPayments(); renderTransactionsTable(); renderStockManagementList();
    }
    window.hydroPushSale = pushSale;
  </script>
</body>

</html>