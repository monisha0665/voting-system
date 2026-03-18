<?php
// Admin dashboard - stats and election management
session_start();
require '../config/db.php';
require '../includes/auth_guard.php';
require_admin();
$page_title = 'Admin Dashboard';

// Stats
$total_users     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$total_elections = $pdo->query("SELECT COUNT(*) FROM elections")->fetchColumn();
$total_votes     = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$active_count    = $pdo->query("SELECT COUNT(*) FROM elections WHERE status='active'")->fetchColumn();

// All elections
$elections = $pdo->query("SELECT * FROM elections ORDER BY created_at DESC")->fetchAll();

require '../includes/header.php';
?>

<div>
  <h1 class="text-2xl font-bold text-indigo-700 mb-6">⚙️ Admin Dashboard</h1>

  <!-- Stat Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-indigo-500">
      <p class="text-sm text-gray-500">Total Users</p>
      <p class="text-4xl font-bold text-indigo-600 mt-1"><?= $total_users ?></p>
    </div>
    <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-green-500">
      <p class="text-sm text-gray-500">Elections</p>
      <p class="text-4xl font-bold text-green-600 mt-1"><?= $total_elections ?></p>
    </div>
    <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-yellow-500">
      <p class="text-sm text-gray-500">Total Votes</p>
      <p class="text-4xl font-bold text-yellow-600 mt-1"><?= $total_votes ?></p>
    </div>
    <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-red-500">
      <p class="text-sm text-gray-500">Active Now</p>
      <p class="text-4xl font-bold text-red-600 mt-1"><?= $active_count ?></p>
    </div>
  </div>

  <!-- Elections Table -->
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold text-gray-700">Manage Elections</h2>
    <a href="elections.php?action=create"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-semibold">
      + New Election
    </a>
  </div>

  <div class="bg-white rounded-2xl shadow overflow-hidden mb-8">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
        <tr>
          <th class="px-4 py-3 text-left">Title</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-left">Start Date</th>
          <th class="px-4 py-3 text-left">End Date</th>
          <th class="px-4 py-3 text-left">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($elections)): ?>
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
              No elections yet. Create one!
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($elections as $e): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($e['title']) ?></td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-semibold
                <?= $e['status'] === 'active'   ? 'bg-green-100 text-green-700'  :
                   ($e['status'] === 'upcoming' ? 'bg-blue-100 text-blue-700'    :
                                                  'bg-gray-100 text-gray-500') ?>">
                <?= ucfirst($e['status']) ?>
              </span>
            </td>
            <td class="px-4 py-3 text-gray-500"><?= $e['start_date'] ?></td>
            <td class="px-4 py-3 text-gray-500"><?= $e['end_date'] ?></td>
            <td class="px-4 py-3">
              <div class="flex gap-3">
                <a href="candidates.php?election_id=<?= $e['id'] ?>"
                   class="text-indigo-600 hover:underline">Candidates</a>
                <a href="elections.php?action=edit&id=<?= $e['id'] ?>"
                   class="text-yellow-600 hover:underline">Edit</a>
                <a href="elections.php?action=delete&id=<?= $e['id'] ?>"
                   onclick="return confirm('Delete this election and all its votes?')"
                   class="text-red-500 hover:underline">Delete</a>
                <a href="../results.php?id=<?= $e['id'] ?>"
                   class="text-green-600 hover:underline">Results</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require '../includes/footer.php'; ?>