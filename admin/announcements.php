<?php
// Admin announcements - send notices to all users
session_start();
require '../config/db.php';
require '../includes/auth_guard.php';
require_admin();
$page_title = 'Manage Announcements';

$success = '';
$error   = '';

// Delete announcement
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM announcements WHERE id = ?")
        ->execute([$del_id]);
    header("Location: announcements.php");
    exit;
}

// Toggle active/inactive
if (isset($_GET['toggle'])) {
    $tog_id = intval($_GET['toggle']);
    $pdo->prepare("
        UPDATE announcements
        SET is_active = !is_active
        WHERE id = ?
    ")->execute([$tog_id]);
    header("Location: announcements.php");
    exit;
}

// Add new announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']);
    $message = trim($_POST['message']);
    $type    = $_POST['type'];

    if (empty($title) || empty($message)) {
        $error = "Title and message are required.";
    } else {
        $pdo->prepare("
            INSERT INTO announcements (title, message, type, created_by)
            VALUES (?, ?, ?, ?)
        ")->execute([$title, $message, $type, $_SESSION['user_id']]);
        $success = "Announcement sent successfully!";
    }
}

// Get all announcements
$announcements = $pdo->query("
    SELECT a.*, u.name as admin_name
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    ORDER BY a.created_at DESC
")->fetchAll();

require '../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <a href="dashboard.php"
         class="text-indigo-500 text-sm hover:underline">
        ← Back to Dashboard
      </a>
      <h1 class="text-2xl font-bold text-indigo-700 mt-1">
        📢 Manage Announcements
      </h1>
    </div>
  </div>

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

  <!-- Add Announcement Form -->
  <div class="bg-white rounded-2xl shadow p-6 mb-8">
    <h2 class="font-bold text-gray-700 mb-4">
      ➕ Send New Announcement
    </h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">
          Announcement Title
        </label>
        <input type="text" name="title" required
               placeholder="e.g. Election Results Announced!"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Message</label>
        <textarea name="message" rows="4" required
                  placeholder="Write your announcement message here..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2
                         focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </textarea>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">
          Announcement Type
        </label>
        <select name="type"
                class="w-full border border-gray-300 rounded-lg px-3 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
          <option value="info">ℹ️ Info (Blue)</option>
          <option value="success">✅ Success (Green)</option>
          <option value="warning">⚠️ Warning (Yellow)</option>
          <option value="danger">🚨 Important (Red)</option>
        </select>
      </div>
      <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                     font-bold py-3 rounded-xl transition">
        📢 Send Announcement
      </button>
    </form>
  </div>

  <!-- All Announcements -->
  <h2 class="text-lg font-bold text-gray-700 mb-4">
    All Announcements
    <span class="ml-2 bg-indigo-100 text-indigo-700 text-xs
                 px-2 py-1 rounded-full">
      <?= count($announcements) ?>
    </span>
  </h2>

  <?php if (empty($announcements)): ?>
    <div class="bg-white rounded-2xl shadow p-8 text-center text-gray-400">
      <p class="text-4xl mb-2">📢</p>
      <p>No announcements yet. Send one above!</p>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($announcements as $a): ?>
      <?php
      // Type styles
      $type_styles = [
          'info'    => 'border-blue-400 bg-blue-50',
          'success' => 'border-green-400 bg-green-50',
          'warning' => 'border-yellow-400 bg-yellow-50',
          'danger'  => 'border-red-400 bg-red-50',
      ];
      $type_icons = [
          'info'    => 'ℹ️',
          'success' => '✅',
          'warning' => '⚠️',
          'danger'  => '🚨',
      ];
      $style = $type_styles[$a['type']] ?? 'border-gray-300 bg-gray-50';
      $icon  = $type_icons[$a['type']] ?? 'ℹ️';
      ?>
      <div class="bg-white rounded-2xl shadow border-l-4
                  <?= $style ?> p-5
                  <?= !$a['is_active'] ? 'opacity-50' : '' ?>">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xl"><?= $icon ?></span>
              <h3 class="font-bold text-gray-800 text-lg">
                <?= htmlspecialchars($a['title']) ?>
              </h3>
              <?php if (!$a['is_active']): ?>
                <span class="bg-gray-200 text-gray-500 text-xs
                             px-2 py-1 rounded-full">
                  Hidden
                </span>
              <?php else: ?>
                <span class="bg-green-100 text-green-700 text-xs
                             px-2 py-1 rounded-full">
                  Live
                </span>
              <?php endif; ?>
            </div>
            <p class="text-gray-600 text-sm mb-2">
              <?= htmlspecialchars($a['message']) ?>
            </p>
            <p class="text-xs text-gray-400">
              Sent by <strong><?= htmlspecialchars($a['admin_name']) ?></strong>
              on <?= date('M d, Y h:i A', strtotime($a['created_at'])) ?>
            </p>
          </div>
          <div class="flex gap-2 ml-4 shrink-0">
            <!-- Toggle Active -->
            <a href="announcements.php?toggle=<?= $a['id'] ?>"
               class="<?= $a['is_active']
                   ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                   : 'bg-green-100 text-green-700 hover:bg-green-200' ?>
                      px-3 py-1 rounded-lg text-xs font-semibold transition">
              <?= $a['is_active'] ? 'Hide' : 'Show' ?>
            </a>
            <!-- Delete -->
            <a href="announcements.php?delete=<?= $a['id'] ?>"
               onclick="return confirm('Delete this announcement?')"
               class="bg-red-100 text-red-600 hover:bg-red-200
                      px-3 py-1 rounded-lg text-xs font-semibold transition">
              Delete
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>