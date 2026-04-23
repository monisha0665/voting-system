<?php
session_start();
require 'config/db.php';
require 'includes/get_ip.php';
$page_title = 'Sign Up';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim(htmlspecialchars($_POST['name']));
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "This email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $token  = bin2hex(random_bytes(32));

            $ip = get_ip_address();
            $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, verify_token, is_verified, ip_address)
              VALUES (?, ?, ?, ?, 1, ?)"
            );
            $stmt->execute([$name, $email, $hashed, $token, $ip]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['name']    = $name;
            $_SESSION['role']    = 'user';

            header("Location: index.php");
            exit;
        }
    }
}
require 'includes/header.php';
?>
<div class="max-w-md mx-auto mt-10 bg-white rounded-2xl shadow-lg p-8">
  <h1 class="text-2xl font-bold text-indigo-700 mb-2">Create your account</h1>
  <p class="text-gray-400 text-sm mb-6">Join VoteApp and make your voice heard</p>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg px-4 py-3 mb-4">
      ⚠️ <?= $error ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block text-sm font-medium mb-1">Full Name</label>
      <input type="text" name="name" required
             class="w-full border border-gray-300 rounded-lg px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Email Address</label>
      <input type="email" name="email" required
             class="w-full border border-gray-300 rounded-lg px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password" name="password" required minlength="6"
             class="w-full border border-gray-300 rounded-lg px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-indigo-400">
      <p class="text-xs text-gray-400 mt-1">Minimum 6 characters</p>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Confirm Password</label>
      <input type="password" name="confirm_password" required
             class="w-full border border-gray-300 rounded-lg px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                   font-semibold py-2 rounded-lg transition">
      Create Account
    </button>
  </form>
  <p class="text-center text-sm text-gray-500 mt-4">
    Already have an account?
    <a href="login.php" class="text-indigo-600 hover:underline">Login</a>
  </p>
</div>
<?php require 'includes/footer.php'; ?>