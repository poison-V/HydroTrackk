<!doctype html>
<html lang="tl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Gallon Sticker QR Generator · System Connected</title>
  <link rel="stylesheet" href="styles.css" />
  <script src="libs/tailwindcss.js"></script>
  <script src="libs/qrcode.min.js"></script>
  <script src="libs/html5-qrcode.min.js"></script>

  <style>
    /* ===== GLOBAL STYLES ===== */
    body {
      font-family: system-ui, sans-serif;
      background: linear-gradient(to bottom right, #ecfdf5, #cffafe);
      color: #0f172a;
      margin: 0;
      padding: 0;
    }

    a {
      text-decoration: none;
    }

    input,
    select,
    button {
      font-family: inherit;
    }

    /* ===== SIDEBAR ===== */
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

    .sidebar a.active,
    .sidebar a:hover {
      background: #4b5563;
      color: white;
    }

    /* ===== MAIN CONTENT ===== */
    .main {
      margin-left: 150px;
      margin-top: 40px;
      padding: 59px 20px 20px 20px;
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
      justify-content: flex-start;
      transition: margin-left 0.3s ease;
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
      .main {
        margin-left: 0;
        padding: 140px 12px 12px 12px;
        flex-direction: column;
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

      .box {
        width: 100% !important;
        max-width: 100% !important;
      }

      .payload-card {
        width: 100% !important;
      }
    }

    .box {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      flex: 1 1 350px;
      max-width: 1000px;
      min-height: 610px;
      display: flex;
      flex-direction: column;
      margin-top: 27px;
    }

    h2 {
      text-align: center;
      color: #065f46;
      margin-bottom: 20px;
    }

    label {
      font-weight: 600;
      display: block;
      margin-top: 12px;
      color: #065f46;
    }

    select,
    input {
      width: 97%;
      padding: 10px;
      margin-top: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .controls {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 16px;
    }

    button {
      padding: 12px 18px;
      border: none;
      border-radius: 8px;
      background: #059669;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: 0.2s;
    }

    button:hover {
      background: #059669;
      color: white;
    }

    button.secondary {

      color: #040404ff;
      padding: 4px;
    }

    button.secondary:hover {
      background: #059669;
      color: white;
    }

    /* ===== STICKER CARD ===== */
    .sticker-card {
      background: white;
      width: 240px;
      padding: 1px;
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      margin: auto;
    }

    .sticker-card div {
      margin-top: 1px;
      font-size: 14px;
      color: #374151;
      font-weight: 500;
      text-align: center;
    }

    /* ===== PAYLOAD CARD ===== */
    .payload-card {
      background: #ffffff;
      border-radius: 12px;
      padding: 5px;
      width: 560px;
      overflow-x: auto;
    }

    pre {
      background: #ffffff;
      padding: 1px;
      border-radius: 8px;
      font-size: 12px;
      overflow-x: auto;
    }

    @media print {
      body * {
        visibility: hidden;
      }

      .sticker-card,
      .sticker-card * {
        visibility: visible;
      }

      .sticker-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }

      @page {
        size: 57mm auto;
        margin: 0;
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
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm shadow-md no-print">
    <div class="max-w-6xl mx-auto px-4 md:px-6 py-4 flex flex-col md:flex-row items-start md:items-center gap-3">
      <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
        <div class="flex items-center gap-3">
          <span class="text-2xl"> <img src="icons/image-removebg-preview (1).png"
              class="w-12 h-12 object-contain" /></span>
          <div>
            <div class="text-emerald-700 font-bold text-lg">HydroTrack</div>
            <div class="text-xs text-gray-500 -mt-1 hidden md:block">Generate QR</div>
          </div>
        </div>
        <a href="logout.php"
          class="md:hidden text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Logout</a>
      </div>


      <div class="flex-1 w-full md:mx-6 md:w-auto relative">
        <input id="searchBox" type="text" placeholder="Search customers / phone / item..."
          class="block w-full rounded border px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" />
      </div>

      <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
        <div id="cashierBadge" class="cashier-badge flex items-center gap-2 text-sm text-gray-600">
          <img src="icons/clerk.png" class="w-5 h-5 object-contain" alt="">
          <span id="cashierNameDisplay" class="font-semibold text-emerald-700">—</span>
        </div>
        <a href="logout.php"
          class="hidden md:inline-block text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 whitespace-nowrap">Logout</a>
      </div>
      <div
        class="max-w-6xl mx-auto px-4 md:px-6 py-1 flex flex-col md:flex-row items-start md:items-center justify-between gap-1">
        <div class="text-xs text-gray-400">Local saved cashier</div>
      </div>
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
      class="sidebar-link active btn-deleted flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
      <div class="sidebar-icon">
        <img src="icons/qr-code.png" class="w-5 h-5 object-contain" alt="">
      </div>
      <span>Generate QR</span>
    </a>

  </aside>



  <!-- MAIN CONTENT -->
  <div class="main">

    <!-- Gallon Sticker QR Generator Box -->
    <div class="box">
      <h2>Gallon Sticker QR Generator</h2>

      <label>Gallon Type</label>
      <select id="gType" style="padding:5px">
        <option value="20S">20L Slim</option>
        <option value="20R">20L Round</option>
        <option value="10L">10 Liter</option>
        <option value="05L">5 Liter</option>
      </select>

      <label>Size</label>
      <input id="sizeOutput" readonly style="padding:5px" />

      <label>Type (Category)</label>
      <input id="typeOutput" readonly style="padding:5px" />

      <label>Serial Number (Auto)</label>
      <input id="serialOutput" readonly placeholder="Click Generate" style="padding:5px" />

      <div class="mt-2 text-xs text-gray-500">
        Generated for this type: <span id="sessionCountDisplay" class="font-bold text-emerald-700">0</span>
      </div>

      <div class="controls">
        <button id="generate" style="padding:5px">Generate QR</button>
        <button id="print" class="secondary" style="padding:5px">Print QR</button>
        <button id="savePNG" class="secondary" style="padding:5px">Download</button>
        <button id="resetGenerator" class="secondary" style="padding:5px">Reset</button>
      </div>
    </div>

    <!-- QR Payload Box -->
    <div class="box">
      <h2>QR Payload</h2>
      <div class="payload-card">
        <pre id="payload"></pre>
      </div>

      <div class="sticker-card" id="sticker">
        <div id="qrcode"></div>
        <div style="margin-top:12px;">
          <div id="t1"></div>
          <div id="t2"></div>
          <div id="t3"></div>
          <div id="t4"></div>
        </div>
      </div>
    </div>

  </div>

  <script>
    const sizeMap = {
      "20S": "20LiterSlim",
      "20R": "20LiterRound",
      "10L": "10Liter",
      "05L": "5Liter"
    };
    const typeMap = {
      "20S": "Gallon",
      "20R": "Gallon",
      "10L": "Gallon",
      "05L": "Gallon"
    };

    const gType = document.getElementById("gType");
    const sizeOutput = document.getElementById("sizeOutput");
    const typeOutput = document.getElementById("typeOutput");
    const serialOutput = document.getElementById("serialOutput");
    const generateBtn = document.getElementById("generate");

    function updateTypeSize() {
      const code = gType.value;
      sizeOutput.value = sizeMap[code];
      typeOutput.value = typeMap[code];
    }

    gType.addEventListener("change", () => {
      updateTypeSize();
      serialOutput.value = "";
      document.getElementById("qrcode").innerHTML = "";
      updateSessionDisplay();
    });
    updateTypeSize();

    async function generateNewSerial() {
      const code = gType.value;

      // Disable all controls to prevent race conditions (e.g. Resetting while generating)
      const cachedText = generateBtn.textContent;
      toggleControls(false);
      generateBtn.textContent = "Generating...";

      try {
        const currentCount = getSessionCount();
        const res = await fetch('api/generate_serial.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            typeCode: code,
            seq: (getSessionCount(code) + 1),
            force: true // Always validly honor the local session counter user sees
          })
        });
        const data = await res.json();

        if (data.success) {
          serialOutput.value = data.serial;
          // Sync session count with what was actually used
          setSessionCount(code, data.seq);
          renderQR(data.serial, code);
        } else {
          alert("Error: " + data.message);
        }
      } catch (e) {
        alert("Server error: " + e.message);
        console.error(e);
      } finally {
        toggleControls(true);
        generateBtn.textContent = cachedText;
      }
    }

    function toggleControls(enable) {
      const btns = document.querySelectorAll('.controls button');
      btns.forEach(b => b.disabled = !enable);
      gType.disabled = !enable;
    }

    function renderQR(serial, code) {
      if (!serial) return;

      const payloadObj = {
        serial: serial,
        size: sizeMap[code],
        type: typeMap[code]
      };

      document.getElementById("payload").textContent = JSON.stringify(payloadObj, null, 2);

      const qrContainer = document.getElementById("qrcode");
      qrContainer.innerHTML = "";
      new QRCode(qrContainer, {
        text: JSON.stringify(payloadObj),
        width: 240,
        height: 240,
        correctLevel: QRCode.CorrectLevel.H
      });

      document.getElementById("t1").textContent = `Serial: ${payloadObj.serial}`;
      document.getElementById("t2").textContent = `Size: ${payloadObj.size}`;
      document.getElementById("t3").textContent = `Type: ${payloadObj.type}`;

      const d = new Date();
      const today = d.toISOString().split('T')[0];
      document.getElementById("t4").textContent = `Issued: ${today}`;
    }

    generateBtn.addEventListener("click", generateNewSerial);

    // Allow manual QR rendering for testing only if needed, but per specs we prefer system generated
    // keeping it mainly for read-only display
    serialOutput.addEventListener("input", () => {
      // Optional: block manual changes? 
      // serialOutput is set to readonly in HTML above
    });

    document.getElementById("print").addEventListener("click", () => { window.print(); });

    document.getElementById("savePNG").addEventListener("click", () => {
      const qrImg = document.querySelector("#qrcode img") || document.querySelector("#qrcode canvas");
      if (!qrImg) return alert("Generate QR first!");
      const link = document.createElement("a");
      link.href = qrImg.tagName === "IMG" ? qrImg.src : qrImg.toDataURL();
      link.download = serialOutput.value + ".png";
      link.click();
    });

    const QR_SESSION_KEY_PREFIX = 'hydro_qr_session_v2_';

    function getSessionCount(type) {
      if (!type) type = gType.value;
      const key = QR_SESSION_KEY_PREFIX + type;
      const val = localStorage.getItem(key);
      return val ? parseInt(val) : 0;
    }

    function setSessionCount(type, val) {
      localStorage.setItem(QR_SESSION_KEY_PREFIX + type, val);
      updateSessionDisplay();
    }

    function updateSessionDisplay() {
      const display = document.getElementById('sessionCountDisplay');
      if (display) display.textContent = getSessionCount(gType.value);
    }

    document.getElementById("resetGenerator").addEventListener("click", () => {
      if (!confirm("Are you sure you want to reset the QR generator? This will reset counters for ALL types and clear current preview. (Database records will NOT be affected)")) return;

      // Clear UI
      serialOutput.value = "";
      document.getElementById("qrcode").innerHTML = "";
      document.getElementById("payload").textContent = "";
      document.getElementById("t1").textContent = "";
      document.getElementById("t2").textContent = "";
      document.getElementById("t3").textContent = "";
      document.getElementById("t4").textContent = "";

      // Reset ALL Session Counters
      Object.keys(sizeMap).forEach(code => {
        localStorage.removeItem(QR_SESSION_KEY_PREFIX + code);
      });
      updateSessionDisplay();

      // Show Toast Notification
      showToast("Generator counters have been reset back to 0001.");
    });

    function showToast(msg) {
      // Create toast element
      const toast = document.createElement("div");
      toast.className = "fixed bottom-5 right-5 bg-gray-800 text-white px-4 py-2 rounded shadow-lg z-50 text-sm transition-opacity duration-300";
      toast.textContent = msg;
      document.body.appendChild(toast);

      // Remove after 3 seconds
      setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Initialize display
    updateSessionDisplay();

    // Cashier management
    document.addEventListener('DOMContentLoaded', () => {
      // Mobile menu
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
    });

    const CASHIER_KEY = 'hydro_cashier_v1';
    function setCashierName(v) {
      if (!v) {
        localStorage.removeItem(CASHIER_KEY);
        document.getElementById('cashierNameDisplay').textContent = '—';
        return;
      }
      localStorage.setItem(CASHIER_KEY, v);
      document.getElementById('cashierNameDisplay').textContent = v;
    }
    const savedCashier = localStorage.getItem(CASHIER_KEY);
    if (savedCashier && document.getElementById('cashierNameDisplay')) {
      document.getElementById('cashierNameDisplay').textContent = savedCashier;
    }
  </script>

</body>

</html>