<?php
session_start();
require 'config/db.php';
require 'includes/auth_guard.php';
require_login();
$page_title = 'My Profile';
$user_id    = $_SESSION['user_id'];
$success    = '';
$error      = '';

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update info
    if (isset($_POST['update_profile'])) {
        $name  = trim(htmlspecialchars($_POST['name']));
        $email = trim($_POST['email']);

        // Check email not taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);

        if ($stmt->rowCount() > 0) {
            $error = "This email is already used by another account.";
        } else {
            $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?")
                ->execute([$name, $email, $user_id]);
            $_SESSION['name'] = $name;
            $success = "Profile updated successfully!";
            $user['name']  = $name;
            $user['email'] = $email;
        }
    }

    // Change password
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if (!password_verify($current, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif (strlen($new) < 6) {
            $error = "New password must be at least 6 characters.";
        } elseif ($new !== $confirm) {
            $error = "New passwords do not match.";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
                ->execute([$hashed, $user_id]);
            $success = "Password changed successfully!";
        }
    }
}

// Get user vote history
$stmt = $pdo->prepare("
    SELECT e.title, c.name AS candidate, v.voted_at
    FROM votes v
    JOIN elections e ON v.election_id = e.id
    JOIN candidates c ON v.candidate_id = c.id
    WHERE v.user_id = ?
    ORDER BY v.voted_at DESC
");
$stmt->execute([$user_id]);
$vote_history = $stmt->fetchAll();

require 'includes/header.php';
?>

<div class="max-w-2xl mx-auto">
  <h1 class="text-2xl font-bold text-indigo-700 mb-6">👤 My Profile</h1>

  <?php if ($success): ?>
    <div class="bg-green-50 border border-green-300 text-green-700 rounded-xl px-4 py-3 mb-5">
      ✅ <?= $success ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-300 text-red-700 rounded-xl px-4 py-3 mb-5">
      ⚠️ <?= $error ?>
    </div>
  <?php endif; ?>

  <!-- Profile Info -->
  <div class="bg-white rounded-2xl shadow p-6 mb-6">
    <h2 class="font-bold text-gray-700 mb-4">Edit Profile Info</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Full Name</label>
        <input type="text" name="name" required
               value="<?= htmlspecialchars($user['name']) ?>"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email Address</label>
        <input type="email" name="email" required
               value="<?= htmlspecialchars($user['email']) ?>"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <span class="text-xs text-gray-400">Role:</span>
        <span class="ml-2 text-xs font-semibold px-2 py-1 rounded-full
          <?= $user['role'] === 'admin' ? 'bg-yellow-100 text-yellow-700' : 'bg-indigo-100 text-indigo-700' ?>">
          <?= ucfirst($user['role']) ?>
        </span>
      </div>
      <button name="update_profile"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                     font-semibold py-2 rounded-lg transition">
        Save Changes
      </button>
    </form>
  </div>

  <!-- Change Password -->
  <div class="bg-white rounded-2xl shadow p-6 mb-6">
    <h2 class="font-bold text-gray-700 mb-4">🔒 Change Password</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Current Password</label>
        <input type="password" name="current_password" required
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">New Password</label>
        <input type="password" name="new_password" required minlength="6"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Confirm New Password</label>
        <input type="password" name="confirm_password" required
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <button name="change_password"
              class="w-full bg-gray-700 hover:bg-gray-800 text-white
                     font-semibold py-2 rounded-lg transition">
        Change Password
      </button>
    </form>
  </div>

  <!-- Vote History -->
  <div class="bg-white rounded-2xl shadow p-6">
    <h2 class="font-bold text-gray-700 mb-4">🗳️ My Voting History</h2>
    <?php if (empty($vote_history)): ?>
      <p class="text-gray-400 text-center py-4">You haven't voted in any election yet.</p>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($vote_history as $v): ?>
        <div class="flex items-center justify-between border border-gray-100 rounded-xl p-3">
          <div>
            <p class="font-medium text-gray-800 text-sm"><?= htmlspecialchars($v['title']) ?></p>
            <p class="text-xs text-gray-400">Voted for: <strong><?= htmlspecialchars($v['candidate']) ?></strong></p>
          </div>
          <p class="text-xs text-gray-400"><?= date('M d, Y', strtotime($v['voted_at'])) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require 'includes/footer.php'; ?>