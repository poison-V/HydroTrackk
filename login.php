<?php
session_start();

// Kung logged-in na, diretso na sa home
if (isset($_SESSION['user_id'])) {
  header('Location: home.php');
  exit;
}

require_once __DIR__ . '/config.php';

$error = '';
$success = '';

if (!empty($_SESSION['signup_success'])) {
  $success = $_SESSION['signup_success'];
  unset($_SESSION['signup_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $error = 'Please enter your Gmail and password.';
  } else {
    $stmt = $conn->prepare('SELECT id, full_name, email, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['full_name'] = $user['full_name'];
      $_SESSION['email'] = $user['email'];

      header('Location: home.php');
      exit;
    } else {
      $error = 'Invalid Gmail or password.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HydroTrack · Secure Login</title>
  <link rel="stylesheet" href="styles.css" />
  <script src="libs/tailwindcss.js"></script>
  <style>
    .spinner {
      border: 4px solid #e0f2f1;
      border-top: 4px solid #059669;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 50;
    }

    body {
      scroll-behavior: smooth;
    }

    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 100;
    }

    .modal.active {
      display: flex;
    }

    @media (max-width: 640px) {
      .navbar {
        padding: 12px 16px !important;
      }

      .navbar h1 {
        font-size: 1.25rem !important;
      }

      .navbar .space-x-4 {
        gap: 8px;
      }

      .login-box {
        margin: 16px 16px 16px 16px !important;
        padding: 24px !important;
      }
    }
  </style>
</head>

<body class="bg-gradient-to-br from-emerald-100 to-cyan-100 min-h-screen flex flex-col items-center justify-center">


  <!-- Login Box -->
  <div class="login-box mt-1 bg-white shadow-lg rounded-2xl p-8 w-80vh max-w-md mx-4">
    <div class="flex justify-center mb-4">
      <img src="icons/image-removebg-preview (1).png" class="w-20 h-20 object-contain" />
    </div>
    <h1 class="text-2xl font-semibold text-center text-emerald-700 mb-4">HydroTrack POS Login</h1>
    <p class="text-center text-sm text-gray-500 mb-2">Secure access for authorized users only</p>

    <?php if ($success): ?>
      <div class="mb-2 text-sm text-emerald-700 text-center"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="mb-2 text-sm text-red-600 text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form id="authForm" method="POST" class="space-y-4" novalidate>
      <div>
        <label class="block text-sm font-medium text-gray-700">Gmail Address</label>
        <input id="gmail" name="email" type="email" required placeholder="example@gmail.com"
          value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
          class="mt-1 w-full px-3 py-2 border rounded focus:ring focus:ring-emerald-300" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Password</label>
        <input id="password" name="password" type="password" required placeholder="Enter your password"
          class="mt-1 w-full px-3 py-2 border rounded focus:ring focus:ring-emerald-300" />
        <div class="mt-2 text-sm flex items-center justify-between">
          <div><input id="showPass" type="checkbox" class="mr-2" /> <span>Show Password</span></div>
          <!-- Forgot password flow (front-end lang muna; backend pwede idagdag later) -->
        </div>
      </div>

      <button type="submit" id="loginBtn"
        class="w-full bg-emerald-600 text-white py-2 rounded hover:bg-emerald-700 transition flex items-center justify-center gap-2">
        <span>Login</span>
      </button>
      <a href="signup.php" class="block text-center border py-2 rounded hover:bg-gray-50 transition">Sign Up</a>

      <div class="text-center text-xs text-gray-400 mt-2">
        Users are authenticated from the database configured in <code>config.php</code>.
      </div>
    </form>

    <!-- Spinner -->
    <div id="loadingSpinner" class="hidden mt-4">
      <div class="spinner"></div>
      <p class="text-center text-gray-600 mt-2">Logging in, please wait...</p>
    </div>

    <div id="errorMsg" class="text-center text-red-600 text-sm mt-3"></div>
  </div>

  <!-- Forgot Password Modal (UI only for now) -->
  <div id="forgotModal" class="modal">
    <div class="bg-white rounded-lg p-6 shadow-lg w-11/12 max-w-md relative">
      <h2 class="text-xl font-bold text-emerald-700 mb-3 text-center">Reset Password</h2>
      <p class="text-sm text-gray-600 mb-3">
        Backend for password reset via email is not yet configured. Please contact the administrator to reset password
        manually in the database.
      </p>
      <button id="cancelReset" class="w-full mt-3 bg-gray-200 py-2 rounded hover:bg-gray-300">Close</button>
    </div>
  </div>

  <!-- About Modal -->
  <div id="aboutModal" class="modal">
    <div class="bg-white rounded-lg p-6 shadow-lg w-11/12 max-w-md relative text-center">
      <h2 class="text-xl font-bold text-emerald-700 mb-3">About HydroTrack</h2>
      <p class="text-gray-600 mb-4">
        HydroTrack is a secure POS system for managing water refill stations. Track sales, gallons, and customer
        information easily.
      </p>
      <button class="closeModal w-full mt-3 bg-gray-200 py-2 rounded hover:bg-gray-300">Close</button>
    </div>
  </div>

  <!-- Contact Modal -->
  <div id="contactModal" class="modal">
    <div class="bg-white rounded-lg p-6 shadow-lg w-11/12 max-w-md relative text-center">
      <h2 class="text-xl font-bold text-emerald-700 mb-3">Contact Us</h2>
      <p class="text-gray-600 mb-4">
        Email: support@hydrotrack.com<br>
        Phone: +63 912 345 6789
      </p>
      <button class="closeModal w-full mt-3 bg-gray-200 py-2 rounded hover:bg-gray-300">Close</button>
    </div>
  </div>

  <script>
    // Show/hide password
    document.getElementById('showPass').addEventListener('change', e => {
      document.getElementById('password').type = e.target.checked ? 'text' : 'password';
    });

    // Simple spinner on submit (actual auth handled by PHP backend)
    const authForm = document.getElementById('authForm');
    const spinner = document.getElementById('loadingSpinner');
    const loginBtn = document.getElementById('loginBtn');

    authForm.addEventListener('submit', function () {
      spinner.classList.remove('hidden');
      loginBtn.disabled = true;
    });

    // ====== Forgot Password Modal (UI only) ======
    const forgotModal = document.getElementById('forgotModal');
    document.getElementById('forgotPassBtn').addEventListener('click', () => {
      forgotModal.classList.add('active');
    });
    document.getElementById('cancelReset').addEventListener('click', () => forgotModal.classList.remove('active'));

    // ===== About & Contact Modals =====
    const aboutModal = document.getElementById('aboutModal');
    const contactModal = document.getElementById('contactModal');

    document.getElementById('aboutBtn').addEventListener('click', () => aboutModal.classList.add('active'));
    document.getElementById('contactBtn').addEventListener('click', () => contactModal.classList.add('active'));

    document.querySelectorAll('.closeModal').forEach(btn => {
      btn.addEventListener('click', e => {
        e.target.closest('.modal').classList.remove('active');
      });
    });

    document.querySelectorAll('.modal').forEach(modal => {
      modal.addEventListener('click', e => {
        if (e.target === modal) modal.classList.remove('active');
      });
    });
  </script>
</body>

</html>