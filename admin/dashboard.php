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
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg
              hover:bg-indigo-700 text-sm font-semibold">
      + New Election
    </a>
    <div class="flex gap-3">
  <a href="announcements.php"
     class="bg-purple-600 text-white px-4 py-2 rounded-lg
            hover:bg-purple-700 text-sm font-semibold">
    📢 Announcements
  </a>
  <a href="elections.php?action=create"
     class="bg-indigo-600 text-white px-4 py-2 rounded-lg
            hover:bg-indigo-700 text-sm font-semibold">
    + New Election
  </a>
</div>
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
            <td colspan="5"
                class="px-4 py-8 text-center text-gray-400">
              No elections yet. Create one!
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($elections as $e): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">
              <?= htmlspecialchars($e['title']) ?>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-semibold
                <?= $e['status'] === 'active'
                    ? 'bg-green-100 text-green-700'
                    : ($e['status'] === 'upcoming'
                        ? 'bg-blue-100 text-blue-700'
                        : 'bg-gray-100 text-gray-500') ?>">
                <?= ucfirst($e['status']) ?>
              </span>
            </td>
            <td class="px-4 py-3 text-gray-500"><?= $e['start_date'] ?></td>
            <td class="px-4 py-3 text-gray-500"><?= $e['end_date'] ?></td>
            <td class="px-4 py-3">
              <div class="flex gap-3">
                <a href="candidates.php?election_id=<?= $e['id'] ?>"
                   class="text-indigo-600 hover:underline font-medium">
                   Candidates
                </a>
                <a href="elections.php?action=edit&id=<?= $e['id'] ?>"
                   class="text-yellow-600 hover:underline font-medium">
                   Edit
                </a>
                <a href="elections.php?action=delete&id=<?= $e['id'] ?>"
                   onclick="return confirm('Delete this election?')"
                   class="text-red-500 hover:underline font-medium">
                   Delete
                </a>
                <a href="../results.php?id=<?= $e['id'] ?>"
                   class="text-green-600 hover:underline font-medium">
                   Results
                </a>
                <a href="../results.php?id=<?= $e['id'] ?>"
   class="text-green-600 hover:underline font-medium">
   Results
</a>
<a href="export_csv.php?election_id=<?= $e['id'] ?>&type=results"
   class="text-blue-600 hover:underline font-medium">
   📥 CSV
</a>
<a href="export_csv.php?election_id=<?= $e['id'] ?>&type=voters"
   class="text-purple-600 hover:underline font-medium">
   👥 Voters
</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- IP Address Tracking Table -->
  <div class="mt-8">
   <div class="flex items-center justify-between mb-4">
  <h2 class="text-xl font-bold text-gray-700">
    🌐 IP Address Tracking — Votes
  </h2>
  <a href="export_csv.php?type=all_votes"
     class="bg-blue-600 hover:bg-blue-700 text-white
            px-4 py-2 rounded-lg text-sm font-semibold
            transition flex items-center gap-2">
    📥 Export All Votes CSV
  </a>
</div>
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-indigo-600 text-white text-sm">
          <tr>
            <th class="px-5 py-4 text-left font-bold">Voter Name</th>
            <th class="px-5 py-4 text-left font-bold">Email</th>
            <th class="px-5 py-4 text-left font-bold">Election</th>
            <th class="px-5 py-4 text-left font-bold">Candidate</th>
            <th class="px-5 py-4 text-left font-bold">IP Address</th>
            <th class="px-5 py-4 text-left font-bold">Voted At</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $votes_ip = $pdo->query("
              SELECT v.*, u.name as voter_name, u.email,
                     e.title as election_title,
                     c.name as candidate_name,
                     v.ip_address
              FROM votes v
              JOIN users u ON v.user_id = u.id
              JOIN elections e ON v.election_id = e.id
              JOIN candidates c ON v.candidate_id = c.id
              ORDER BY v.voted_at DESC
          ")->fetchAll();

          $row_colors = [
              'bg-purple-50',
              'bg-green-50',
              'bg-blue-50',
              'bg-pink-50',
              'bg-yellow-50',
              'bg-teal-50',
          ];
          $i = 0;
          ?>
          <?php if (empty($votes_ip)): ?>
            <tr>
              <td colspan="6"
                  class="px-5 py-10 text-center text-gray-400 text-base">
                No votes cast yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($votes_ip as $v): ?>
            <tr class="<?= $row_colors[$i % count($row_colors)] ?>
                       border-b border-gray-100 hover:brightness-95 transition">
              <td class="px-5 py-4 font-bold text-gray-800 text-sm">
                <?= htmlspecialchars($v['voter_name']) ?>
              </td>
              <td class="px-5 py-4 text-gray-600 text-sm">
                <?= htmlspecialchars($v['email']) ?>
              </td>
              <td class="px-5 py-4 text-sm">
                <span class="bg-indigo-100 text-indigo-800 px-3 py-1
                             rounded-full font-semibold text-xs">
                  <?= htmlspecialchars($v['election_title']) ?>
                </span>
              </td>
              <td class="px-5 py-4 font-semibold text-gray-700 text-sm">
                <?= htmlspecialchars($v['candidate_name']) ?>
              </td>
              <td class="px-5 py-4">
                <span class="bg-indigo-600 text-white px-3 py-2
                             rounded-lg text-sm font-bold tracking-wide">
                  🌐 <?= htmlspecialchars($v['ip_address'] ?? 'Unknown') ?>
                </span>
              </td>
              <td class="px-5 py-4 text-gray-600 text-sm font-medium">
                <?= date('M d, Y', strtotime($v['voted_at'])) ?>
                <br>
                <span class="text-xs text-gray-400">
                  <?= date('h:i A', strtotime($v['voted_at'])) ?>
                </span>
              </td>
            </tr>
            <?php $i++; endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- User Registration IP Tracking -->
  <div class="mt-8 mb-8">
    <h2 class="text-xl font-bold text-gray-700 mb-4">
      👥 User Registration IP Tracking
    </h2>
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-purple-600 text-white text-sm">
          <tr>
            <th class="px-5 py-4 text-left font-bold">Name</th>
            <th class="px-5 py-4 text-left font-bold">Email</th>
            <th class="px-5 py-4 text-left font-bold">Role</th>
            <th class="px-5 py-4 text-left font-bold">IP Address</th>
            <th class="px-5 py-4 text-left font-bold">Registered At</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $users_ip = $pdo->query("
              SELECT * FROM users ORDER BY created_at DESC
          ")->fetchAll();

          $user_colors = [
              'bg-purple-50',
              'bg-green-50',
              'bg-blue-50',
              'bg-pink-50',
              'bg-teal-50',
              'bg-yellow-50',
          ];
          $j = 0;
          ?>
          <?php foreach ($users_ip as $u): ?>
          <tr class="<?= $user_colors[$j % count($user_colors)] ?>
                     border-b border-gray-100 hover:brightness-95 transition">
            <td class="px-5 py-4 font-bold text-gray-800 text-sm">
              <?= htmlspecialchars($u['name']) ?>
            </td>
            <td class="px-5 py-4 text-gray-600 text-sm">
              <?= htmlspecialchars($u['email']) ?>
            </td>
            <td class="px-5 py-4">
              <span class="px-3 py-1 rounded-full text-sm font-bold
                <?= $u['role'] === 'admin'
                    ? 'bg-yellow-400 text-yellow-900'
                    : 'bg-green-400 text-green-900' ?>">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td class="px-5 py-4">
              <span class="bg-purple-600 text-white px-3 py-2
                           rounded-lg text-sm font-bold tracking-wide">
                🌐 <?= htmlspecialchars($u['ip_address'] ?? 'Unknown') ?>
              </span>
            </td>
            <td class="px-5 py-4 text-gray-600 text-sm font-medium">
              <?= date('M d, Y', strtotime($u['created_at'])) ?>
              <br>
              <span class="text-xs text-gray-400">
                <?= date('h:i A', strtotime($u['created_at'])) ?>
              </span>
            </td>
          </tr>
          <?php $j++; endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<!-- Contact Messages -->
<div class="mt-8 mb-8">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">
      📬 Contact Messages
      <?php
      $unread = $pdo->query(
          "SELECT COUNT(*) FROM contacts WHERE is_read = 0"
      )->fetchColumn();
      if ($unread > 0): ?>
        <span class="ml-2 bg-red-500 text-white text-xs
                     px-2 py-1 rounded-full">
          <?= $unread ?> new
        </span>
      <?php endif; ?>
    </h2>
  </div>

  <?php
  $contacts = $pdo->query("
      SELECT * FROM contacts ORDER BY created_at DESC LIMIT 10
  ")->fetchAll();

  // Mark all as read
  $pdo->query("UPDATE contacts SET is_read = 1");
  ?>

  <?php if (empty($contacts)): ?>
    <div class="bg-white rounded-2xl shadow p-8 text-center text-gray-400">
      <p class="text-4xl mb-2">📬</p>
      <p>No contact messages yet.</p>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-indigo-600 text-white">
          <tr>
            <th class="px-5 py-4 text-left font-bold">Name</th>
            <th class="px-5 py-4 text-left font-bold">Email</th>
            <th class="px-5 py-4 text-left font-bold">Subject</th>
            <th class="px-5 py-4 text-left font-bold">Message</th>
            <th class="px-5 py-4 text-left font-bold">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($contacts as $c): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-5 py-4 font-bold text-gray-800">
              <?= htmlspecialchars($c['name']) ?>
            </td>
            <td class="px-5 py-4 text-gray-600">
              <?= htmlspecialchars($c['email']) ?>
            </td>
            <td class="px-5 py-4">
              <span class="bg-indigo-100 text-indigo-700
                           px-2 py-1 rounded-full text-xs font-semibold">
                <?= htmlspecialchars($c['subject']) ?>
              </span>
            </td>
            <td class="px-5 py-4 text-gray-600 max-w-xs">
              <p class="truncate">
                <?= htmlspecialchars($c['message']) ?>
              </p>
            </td>
            <td class="px-5 py-4 text-gray-500 text-xs">
              <?= date('M d, Y', strtotime($c['created_at'])) ?>
              <br>
              <span class="text-gray-400">
                <?= date('h:i A', strtotime($c['created_at'])) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</div>

<?php require '../includes/footer.php'; ?>