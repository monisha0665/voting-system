<?php
session_start();
require '../config/db.php';
require '../includes/auth_guard.php';
require_admin();
$page_title = 'Manage Candidates';

$election_id = intval($_GET['election_id'] ?? 0);
$error   = '';
$success = '';

// Get election info
$stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$election = $stmt->fetch();
if (!$election) {
    header("Location: dashboard.php");
    exit;
}

// DELETE candidate
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM candidates WHERE id = ? AND election_id = ?")
        ->execute([intval($_GET['delete']), $election_id]);
    header("Location: candidates.php?election_id=$election_id");
    exit;
}

// ADD candidate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $bio  = trim($_POST['bio']);

    if (empty($name)) {
        $error = "Candidate name is required.";
    } else {
        $pdo->prepare("INSERT INTO candidates (election_id, name, bio) VALUES (?, ?, ?)")
            ->execute([$election_id, $name, $bio]);
        $success = "Candidate added successfully!";
    }
}

// Get all candidates for this election
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(v.id) as vote_count
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id
    WHERE c.election_id = ?
    GROUP BY c.id
");
$stmt->execute([$election_id]);
$candidates = $stmt->fetchAll();

require '../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
  <a href="dashboard.php" class="text-indigo-500 text-sm hover:underline">← Back to Dashboard</a>
  <h1 class="text-2xl font-bold text-indigo-700 mt-2 mb-1">👥 Manage Candidates</h1>
  <p class="text-gray-500 text-sm mb-6">
    Election: <strong><?= htmlspecialchars($election['title']) ?></strong>
    <span class="ml-2 px-2 py-1 rounded-full text-xs font-semibold
      <?= $election['status'] === 'active' ? 'bg-green-100 text-green-700' :
         ($election['status'] === 'upcoming' ? 'bg-blue-100 text-blue-700' :
                                               'bg-gray-100 text-gray-500') ?>">
      <?= ucfirst($election['status']) ?>
    </span>
  </p>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-300 text-red-700 rounded-xl px-4 py-3 mb-4">
      ⚠️ <?= $error ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="bg-green-50 border border-green-300 text-green-700 rounded-xl px-4 py-3 mb-4">
      ✅ <?= $success ?>
    </div>
  <?php endif; ?>

  <!-- Add Candidate Form -->
  <div class="bg-white rounded-2xl shadow p-6 mb-6">
    <h2 class="font-bold text-gray-700 mb-4">➕ Add New Candidate</h2>
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Candidate Name</label>
        <input type="text" name="name" required
               placeholder="e.g. John Smith"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Bio / Description</label>
        <textarea name="bio" rows="2"
                  placeholder="Short description about this candidate..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2
                         focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </textarea>
      </div>
      <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                     font-semibold py-2 rounded-xl transition">
        Add Candidate
      </button>
    </form>
  </div>

  <!-- Candidates List -->
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h2 class="font-bold text-gray-700">
        Current Candidates
        <span class="ml-2 bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full">
          <?= count($candidates) ?>
        </span>
      </h2>
    </div>

    <?php if (empty($candidates)): ?>
      <div class="px-6 py-8 text-center text-gray-400">
        <p class="text-3xl mb-2">👤</p>
        <p>No candidates yet. Add one above!</p>
      </div>
    <?php else: ?>
      <div class="divide-y divide-gray-100">
        <?php foreach ($candidates as $c): ?>
        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
          <div>
            <p class="font-semibold text-gray-800"><?= htmlspecialchars($c['name']) ?></p>
            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($c['bio']) ?></p>
          </div>
          <div class="flex items-center gap-4 ml-4">
            <div class="text-center">
              <p class="text-2xl font-bold text-indigo-600"><?= $c['vote_count'] ?></p>
              <p class="text-xs text-gray-400">votes</p>
            </div>
            <a href="candidates.php?election_id=<?= $election_id ?>&delete=<?= $c['id'] ?>"
               onclick="return confirm('Remove this candidate?')"
               class="text-red-500 hover:text-red-700 text-sm font-semibold">
               Remove
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick Links -->
  <div class="mt-6 flex gap-3">
    <a href="../vote.php?id=<?= $election_id ?>"
       class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center
              py-2 rounded-lg text-sm font-semibold transition">
       🗳️ Preview Vote Page
    </a>
    <a href="../results.php?id=<?= $election_id ?>"
       class="flex-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-center
              py-2 rounded-lg text-sm font-semibold transition">
       📊 View Results
    </a>
  </div>
</div>

<?php require '../includes/footer.php'; ?>