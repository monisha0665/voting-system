<?php
session_start();
require 'config/db.php';
require 'includes/auth_guard.php';
require_login();

$page_title  = 'Cast Your Vote';
$election_id = intval($_GET['id'] ?? 0);
$user_id     = $_SESSION['user_id'];
$message     = '';

// Get active election
$stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ? AND status = 'active'");
$stmt->execute([$election_id]);
$election = $stmt->fetch();
if (!$election) {
    header("Location: index.php");
    exit;
}

// Check already voted
$stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND election_id = ?");
$stmt->execute([$user_id, $election_id]);
$already_voted = $stmt->rowCount() > 0;

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_voted) {
    $candidate_id = intval($_POST['candidate_id']);
    try {
        $pdo->prepare("INSERT INTO votes (user_id, election_id, candidate_id) VALUES (?,?,?)")
            ->execute([$user_id, $election_id, $candidate_id]);
        $already_voted = true;
        $message = 'success';
    } catch (PDOException $e) {
        $message = 'error';
    }
}

// Get candidates
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE election_id = ?");
$stmt->execute([$election_id]);
$candidates = $stmt->fetchAll();

require 'includes/header.php';
?>

<div class="max-w-2xl mx-auto">

  <!-- Back Button -->
  <a href="index.php" class="text-indigo-500 text-sm hover:underline">← Back to Home</a>

  <!-- Election Title -->
  <div class="mt-2 mb-6">
    <h1 class="text-2xl font-bold text-indigo-700">
      <?= htmlspecialchars($election['title']) ?>
    </h1>
    <p class="text-gray-500 mt-1">
      <?= htmlspecialchars($election['description']) ?>
    </p>
    <p class="text-xs text-gray-400 mt-1">
      ⏰ Ends: <?= date('M d, Y h:i A', strtotime($election['end_date'])) ?>
    </p>
  </div>

  <!-- Success Message -->
  <?php if ($message === 'success'): ?>
    <div class="bg-green-50 border border-green-300 text-green-700 rounded-xl px-5 py-4 mb-5">
      <p class="font-semibold">✅ Your vote has been recorded successfully!</p>
      <p class="mt-2">
        <a href="results.php?id=<?= $election_id ?>"
           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 inline-block mt-1">
           📊 View Live Results →
        </a>
      </p>
    </div>

  <!-- Error Message -->
  <?php elseif ($message === 'error'): ?>
    <div class="bg-red-50 border border-red-300 text-red-700 rounded-xl px-5 py-4 mb-5">
      ❌ Something went wrong. Please try again.
    </div>
  <?php endif; ?>

  <!-- Already Voted Notice -->
  <?php if ($already_voted && $message !== 'success'): ?>
    <div class="bg-blue-50 border border-blue-300 text-blue-700 rounded-xl px-5 py-4 mb-5">
      <p class="font-semibold">ℹ️ You have already voted in this election.</p>
      <p class="mt-2">
        <a href="results.php?id=<?= $election_id ?>"
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 inline-block mt-1">
           📊 View Live Results →
        </a>
      </p>
    </div>
  <?php endif; ?>

  <!-- Voting Form -->
  <?php if (!$already_voted): ?>
  <div class="bg-white rounded-2xl shadow p-6">
    <h2 class="font-semibold text-gray-700 mb-4">
      Select your candidate:
    </h2>
    <form method="POST" class="space-y-3">
      <?php foreach ($candidates as $c): ?>
      <label class="flex items-start gap-4 border border-gray-200 rounded-xl p-4
                    cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition">
        <input type="radio" name="candidate_id"
               value="<?= $c['id'] ?>"
               required class="mt-1 accent-indigo-600 w-4 h-4">
        <div>
          <p class="font-semibold text-gray-800">
            <?= htmlspecialchars($c['name']) ?>
          </p>
          <p class="text-sm text-gray-500 mt-1">
            <?= htmlspecialchars($c['bio']) ?>
          </p>
        </div>
      </label>
      <?php endforeach; ?>

      <?php if (empty($candidates)): ?>
        <p class="text-gray-400 text-center py-4">
          No candidates added yet.
        </p>
      <?php else: ?>
        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                       font-bold py-3 rounded-xl transition text-lg mt-2">
          🗳️ Cast My Vote
        </button>
      <?php endif; ?>
    </form>
  </div>
  <?php endif; ?>

  <!-- Results Preview Link (always visible) -->
  <?php if (!empty($candidates)): ?>
  <div class="mt-4 text-center">
    <a href="results.php?id=<?= $election_id ?>"
       class="text-indigo-500 text-sm hover:underline">
       View current results without voting →
    </a>
  </div>
  <?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>