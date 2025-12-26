<?php
session_start();

// Kung naka-login na, pwede nang i-redirect sa home kung gusto mo
if (isset($_SESSION['user_id'])) {
  header('Location: home.php');
  exit;
}

require_once __DIR__ . '/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullName = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['password_confirm'] ?? '';

  if ($fullName === '' || $email === '' || $password === '' || $confirm === '') {
    $error = 'Please fill in all fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
    $error = 'Please enter a valid Gmail address.';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters.';
  } elseif ($password !== $confirm) {
    $error = 'Passwords do not match.';
  } else {
    // Check kung existing na ang email
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = 'This Gmail address is already registered.';
    } else {
      $stmt->close();
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
      $stmt->bind_param('sss', $fullName, $email, $hash);

      if ($stmt->execute()) {
        // Optional: mag-set ng flash message sa login
        $_SESSION['signup_success'] = 'Account created successfully. You can now log in.';
        header('Location: login.php');
        exit;
      } else {
        $error = 'Failed to create account. Please try again later.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HydroTrack · Create Account</title>
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

    @media (max-width: 640px) {
      .signup-box {
        margin: 16px !important;
        padding: 24px !important;
      }
    }
  </style>
</head>

<body class="bg-gradient-to-br from-emerald-100 to-cyan-100 min-h-screen flex flex-col items-center justify-center">

  <div class="signup-box bg-white shadow-lg rounded-2xl p-8 w-80vh max-w-md mx-4">
    <h1 class="text-2xl font-semibold text-center text-emerald-700 mb-4">Create HydroTrack Account</h1>
    <p class="text-center text-sm text-gray-500 mb-6">Set up your secure administrator account</p>

    <?php if ($error): ?>
      <div class="mb-4 text-sm text-red-600 text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form id="signupForm" method="POST" class="space-y-4" novalidate>
      <div>

        <input id="fullName" name="full_name" type="text" required
          value="<?php echo isset($fullName) ? htmlspecialchars($fullName) : ''; ?>"
          class="mt-1 w-full px-3 py-2 border rounded focus:ring focus:ring-emerald-300"
          placeholder="Enter your full name" />
      </div>

      <div>

        <input id="gmail" name="email" type="email" required
          value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
          class="mt-1 w-full px-3 py-2 border rounded focus:ring focus:ring-emerald-300"
          placeholder="example@gmail.com" />
      </div>

      <div>

        <input id="newPassword" name="password" type="password" required minlength="6"
          class="mt-1 w-full px-3 py-2 border rounded focus:ring focus:ring-emerald-300"
          placeholder="Create password" />

      </div>

      <div>

        <input id="confirmPassword" name="password_confirm" type="password" required minlength="6"
          class="mt-1 w-full px-3 py-2 border rounded focus:ring focus:ring-emerald-300"
          placeholder="Confirm Password" />
        <div class="mt-2 text-sm flex items-center justify-between">
          <div><input id="showPass" type="checkbox" class="mr-2" /> <span>Show Password</span></div>
        </div>
      </div>

      <button type="submit" id="signupBtn"
        class="w-full bg-emerald-600 text-white py-2 rounded hover:bg-emerald-700 transition flex items-center justify-center gap-2">
        <span>Create Account</span>
      </button>
      <a href="login.php" class="block text-center border py-2 rounded hover:bg-gray-50 transition">Back to Login</a>

      <p class="text-xs text-gray-400 mt-2 text-center">
        Note: Account details will be stored in the database configured in <code>config.php</code>.
      </p>
    </form>

    <!-- Loading Spinner (optional, controlled sa JS kung gusto mong gamitin) -->
    <div id="loadingSpinner" class="hidden mt-4">
      <div class="spinner"></div>
      <p class="text-center text-gray-600 mt-2">Saving your account...</p>
    </div>
  </div>

  <script>
    // Optional: simple front-end validation + spinner
    const form = document.getElementById('signupForm');
    const spinner = document.getElementById('loadingSpinner');
    const btn = document.getElementById('signupBtn');

    form.addEventListener('submit', function () {
      const gmail = document.getElementById('gmail').value.trim();
      const pass = document.getElementById('newPassword').value.trim();
      const confirm = document.getElementById('confirmPassword').value.trim();

      if (!gmail.toLowerCase().endsWith('@gmail.com')) {
        alert('⚠️ Please enter a valid Gmail address.');
        event.preventDefault();
        return;
      }

      if (pass !== confirm) {
        alert('⚠️ Passwords do not match!');
        event.preventDefault();
        return;
      }

      spinner.classList.remove('hidden');
      btn.disabled = true;
    });
  </script>
</body>

</html>