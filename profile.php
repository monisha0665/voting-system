<?php
// Profile page - edit info and change password
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

    if (isset($_POST['update_profile'])) {
        $name  = trim(htmlspecialchars($_POST['name']));
        $email = trim($_POST['email']);
        $stmt  = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? AND id != ?"
        );
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

// Get vote history
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

// Get vote count for badge
$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ?");
$stmt->execute([$user_id]);
$vote_count = $stmt->fetchColumn();

// Determine badge
if ($vote_count === 0) {
    $badge       = '🌱';
    $badge_name  = 'New Voter';
    $badge_color = 'bg-gray-100 text-gray-600';
    $badge_desc  = 'Cast your first vote to earn a badge!';
} elseif ($vote_count === 1) {
    $badge       = '🗳️';
    $badge_name  = 'First Timer';
    $badge_color = 'bg-blue-100 text-blue-700';
    $badge_desc  = 'You cast your first vote!';
} elseif ($vote_count <= 3) {
    $badge       = '⭐';
    $badge_name  = 'Active Voter';
    $badge_color = 'bg-yellow-100 text-yellow-700';
    $badge_desc  = 'You are an active voter!';
} elseif ($vote_count <= 6) {
    $badge       = '🏅';
    $badge_name  = 'Dedicated Voter';
    $badge_color = 'bg-orange-100 text-orange-700';
    $badge_desc  = 'You are a dedicated voter!';
} else {
    $badge       = '🏆';
    $badge_name  = 'Champion Voter';
    $badge_color = 'bg-purple-100 text-purple-700';
    $badge_desc  = 'You are a champion voter!';
}

// Badge progress
$next_level = $vote_count === 0 ? 1 :
             ($vote_count < 3  ? 3 :
             ($vote_count < 6  ? 6 : 10));
$prev_level = $vote_count === 0 ? 0 :
             ($vote_count < 3  ? 1 :
             ($vote_count < 6  ? 3 : 6));
$progress   = $next_level > $prev_level
    ? min(100, round(
        ($vote_count - $prev_level) /
        ($next_level - $prev_level) * 100
      ))
    : 100;

require 'includes/header.php';
?>

<div class="max-w-2xl mx-auto">
  <h1 class="text-2xl font-bold text-indigo-700 mb-6">👤 My Profile</h1>

  <?php if ($success): ?>
    <div class="bg-green-50 border border-green-300 text-green-700
                rounded-xl px-4 py-3 mb-5">
      ✅ <?= $success ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-300 text-red-700
                rounded-xl px-4 py-3 mb-5">
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
          <?= $user['role'] === 'admin'
              ? 'bg-yellow-100 text-yellow-700'
              : 'bg-indigo-100 text-indigo-700' ?>">
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
        <label class="block text-sm font-medium mb-1">
          Current Password
        </label>
        <input type="password" name="current_password" required
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">
          New Password
        </label>
        <input type="password" name="new_password" required minlength="6"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">
          Confirm New Password
        </label>
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

  <!-- Voting Badge Section -->
  <div class="bg-white rounded-2xl shadow p-6 mb-6">

    <!-- Current Badge -->
    <div class="flex items-center gap-4 mb-6 p-4
                bg-gradient-to-r from-indigo-50 to-purple-50
                rounded-2xl border border-indigo-100">
      <div class="text-6xl"><?= $badge ?></div>
      <div class="flex-1">
        <span class="inline-block px-3 py-1 rounded-full text-sm
                     font-bold mb-1 <?= $badge_color ?>">
          <?= $badge_name ?>
        </span>
        <p class="text-gray-600 text-sm"><?= $badge_desc ?></p>
        <p class="text-indigo-600 font-bold mt-1">
          Total votes cast: <?= $vote_count ?>
        </p>
      </div>
      <div class="text-right hidden md:block">
        <p class="text-xs text-gray-400 mb-1">Next badge at</p>
        <?php if ($vote_count >= 10): ?>
          <p class="text-sm font-bold text-purple-600">Max! 🏆</p>
        <?php else: ?>
          <p class="text-sm font-bold text-indigo-600">
            <?= $next_level ?> votes
          </p>
        <?php endif; ?>
        <div class="w-32 bg-gray-200 rounded-full h-2 mt-2">
          <div class="h-2 rounded-full bg-indigo-500 transition-all"
               style="width: <?= $progress ?>%">
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-1">
          <?= $progress ?>% to next
        </p>
      </div>
    </div>

    <!-- Badge Collection -->
    <div class="mb-6">
      <p class="text-sm font-bold text-gray-600 mb-3">
        Badge Collection:
      </p>
      <div class="flex gap-6 flex-wrap">
        <div class="text-center <?= $vote_count >= 1
            ? 'opacity-100' : 'opacity-30' ?>">
          <div class="text-4xl">🗳️</div>
          <p class="text-xs text-gray-500 mt-1">First Timer</p>
          <p class="text-xs text-gray-400">1 vote</p>
        </div>
        <div class="text-center <?= $vote_count >= 3
            ? 'opacity-100' : 'opacity-30' ?>">
          <div class="text-4xl">⭐</div>
          <p class="text-xs text-gray-500 mt-1">Active</p>
          <p class="text-xs text-gray-400">3 votes</p>
        </div>
        <div class="text-center <?= $vote_count >= 6
            ? 'opacity-100' : 'opacity-30' ?>">
          <div class="text-4xl">🏅</div>
          <p class="text-xs text-gray-500 mt-1">Dedicated</p>
          <p class="text-xs text-gray-400">6 votes</p>
        </div>
        <div class="text-center <?= $vote_count >= 10
            ? 'opacity-100' : 'opacity-30' ?>">
          <div class="text-4xl">🏆</div>
          <p class="text-xs text-gray-500 mt-1">Champion</p>
          <p class="text-xs text-gray-400">10 votes</p>
        </div>
      </div>
    </div>

    <!-- Voting History -->
    <h2 class="font-bold text-gray-700 mb-4">🗳️ My Voting History</h2>
    <?php if (empty($vote_history)): ?>
      <p class="text-gray-400 text-center py-4">
        You haven't voted in any election yet.
      </p>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($vote_history as $v): ?>
        <div class="flex items-center justify-between
                    border border-gray-100 rounded-xl p-3
                    hover:bg-gray-50 transition">
          <div>
            <p class="font-medium text-gray-800 text-sm">
              <?= htmlspecialchars($v['title']) ?>
            </p>
            <p class="text-xs text-gray-400 mt-1">
              Voted for:
              <strong class="text-indigo-600">
                <?= htmlspecialchars($v['candidate']) ?>
              </strong>
            </p>
          </div>
          <div class="text-right">
            <p class="text-xs text-gray-400">
              <?= date('M d, Y', strtotime($v['voted_at'])) ?>
            </p>
            <span class="text-xs bg-green-100 text-green-700
                         px-2 py-1 rounded-full font-semibold">
              ✅ Voted
            </span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require 'includes/footer.php'; ?>