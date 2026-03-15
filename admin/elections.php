<?php
session_start();
require '../config/db.php';
require '../includes/auth_guard.php';
require_admin();
$page_title = 'Manage Elections';

$action = $_GET['action'] ?? 'list';
$id     = intval($_GET['id'] ?? 0);
$error  = '';
$success = '';

// DELETE
if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM elections WHERE id = ?")->execute([$id]);
    header("Location: dashboard.php");
    exit;
}

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date  = $_POST['start_date'];
    $end_date    = $_POST['end_date'];
    $status      = $_POST['status'];

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $pdo->prepare("
            INSERT INTO elections (title, description, start_date, end_date, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$title, $description, $start_date, $end_date, $status, $_SESSION['user_id']]);

        header("Location: dashboard.php");
        exit;
    }
}

// EDIT — fetch existing
$election = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
    $stmt->execute([$id]);
    $election = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title       = trim($_POST['title']);
        $description = trim($_POST['description']);
        $start_date  = $_POST['start_date'];
        $end_date    = $_POST['end_date'];
        $status      = $_POST['status'];

        $pdo->prepare("
            UPDATE elections
            SET title=?, description=?, start_date=?, end_date=?, status=?
            WHERE id=?
        ")->execute([$title, $description, $start_date, $end_date, $status, $id]);

        header("Location: dashboard.php");
        exit;
    }
}

require '../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
  <a href="dashboard.php" class="text-indigo-500 text-sm hover:underline">← Back to Dashboard</a>
  <h1 class="text-2xl font-bold text-indigo-700 mt-2 mb-6">
    <?= $action === 'edit' ? '✏️ Edit Election' : '➕ Create New Election' ?>
  </h1>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-300 text-red-700 rounded-xl px-4 py-3 mb-5">
      ⚠️ <?= $error ?>
    </div>
  <?php endif; ?>

  <div class="bg-white rounded-2xl shadow p-6">
    <form method="POST" class="space-y-4">

      <div>
        <label class="block text-sm font-medium mb-1">Election Title</label>
        <input type="text" name="title" required
               value="<?= htmlspecialchars($election['title'] ?? '') ?>"
               placeholder="e.g. Student Council President 2025"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3"
                  placeholder="Describe what this election is about..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2
                         focus:outline-none focus:ring-2 focus:ring-indigo-400"
        ><?= htmlspecialchars($election['description'] ?? '') ?></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Start Date</label>
          <input type="datetime-local" name="start_date" required
                 value="<?= isset($election['start_date']) ? date('Y-m-d\TH:i', strtotime($election['start_date'])) : '' ?>"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2
                        focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">End Date</label>
          <input type="datetime-local" name="end_date" required
                 value="<?= isset($election['end_date']) ? date('Y-m-d\TH:i', strtotime($election['end_date'])) : '' ?>"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2
                        focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status"
                class="w-full border border-gray-300 rounded-lg px-3 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
          <option value="upcoming" <?= (($election['status'] ?? '') === 'upcoming') ? 'selected' : '' ?>>
            Upcoming
          </option>
          <option value="active" <?= (($election['status'] ?? '') === 'active') ? 'selected' : '' ?>>
            Active
          </option>
          <option value="closed" <?= (($election['status'] ?? '') === 'closed') ? 'selected' : '' ?>>
            Closed
          </option>
        </select>
      </div>

      <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                     font-bold py-3 rounded-xl transition">
        <?= $action === 'edit' ? '💾 Save Changes' : '🚀 Create Election' ?>
      </button>
    </form>
  </div>
</div>

<?php require '../includes/footer.php'; ?>