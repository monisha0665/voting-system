<?php
session_start();
require 'config/db.php';
$page_title = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = "Invalid email or password.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        header("Location: /voting_system/index.php");
        exit;
    }
}
require 'includes/header.php';
?>
<div class="max-w-md mx-auto mt-10 bg-white rounded-2xl shadow-lg p-8">
  <h1 class="text-2xl font-bold text-indigo-700 mb-2">Welcome back</h1>
  <p class="text-gray-400 text-sm mb-6">Login to cast your vote</p>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg px-4 py-3 mb-4">
      ⚠️ <?= $error ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block text-sm font-medium mb-1">Email Address</label>
      <input type="email" name="email" required
             class="w-full border border-gray-300 rounded-lg px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password" name="password" required
             class="w-full border border-gray-300 rounded-lg px-3 py-2
                    focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                   font-semibold py-2 rounded-lg transition">
      Login
    </button>
  </form>
  <p class="text-center text-sm text-gray-500 mt-4">
    New here?
    <a href="signup.php" class="text-indigo-600 hover:underline">Create an account</a>
  </p>
  <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
    <p class="text-xs text-gray-500 font-semibold mb-1">Admin test account:</p>
    <p class="text-xs text-gray-400">Email: admin@voteapp.com</p>
    <p class="text-xs text-gray-400">Password: Admin@123</p>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
```

---

## After Fixing Both Files

Test the site:
```
http://localhost/voting_system/index.php