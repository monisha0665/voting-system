<?php
// Vote page - one vote per user enforcement
session_start();
require 'config/db.php';
require 'includes/auth_guard.php';
require_login();

$page_title  = 'Cast Your Vote';
$election_id = intval($_GET['id'] ?? 0);
$user_id     = $_SESSION['user_id'];
$message     = '';

$stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ? AND status = 'active'");
$stmt->execute([$election_id]);
$election = $stmt->fetch();
if (!$election) {
    header("Location: index.php");
    exit;
}
$stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND election_id = ?");
$stmt->execute([$user_id, $election_id]);
$already_voted = $stmt->rowCount() > 0;

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

$stmt = $pdo->prepare("SELECT * FROM candidates WHERE election_id = ?");
$stmt->execute([$election_id]);
$candidates = $stmt->fetchAll();

require 'includes/header.php';
?>

<div class="max-w-2xl mx-auto">

  <a href="index.php" class="text-indigo-500 text-sm hover:underline">
    ← Back to Home
  </a>

  <div class="mt-2 mb-4">
    <h1 class="text-2xl font-bold text-indigo-700">
      <?= htmlspecialchars($election['title']) ?>
    </h1>
    <p class="text-gray-500 mt-1">
      <?= htmlspecialchars($election['description']) ?>
    </p>
    <p class="text-xs text-gray-400 mt-1">
      ⏰ Ends: <?= date('M d, Y h:i A', strtotime($election['end_date'])) ?>
    </p>

    <div id="countdown"
         class="mt-3 bg-indigo-50 border border-indigo-200
                rounded-xl px-4 py-3 inline-block">
      <p class="text-xs text-indigo-400 mb-1">Time remaining to vote:</p>
      <div class="flex gap-3 text-center">
        <div>
          <p id="days" class="text-2xl font-bold text-indigo-600">00</p>
          <p class="text-xs text-gray-400">Days</p>
        </div>
        <div class="text-2xl font-bold text-indigo-300 mt-1">:</div>
        <div>
          <p id="hours" class="text-2xl font-bold text-indigo-600">00</p>
          <p class="text-xs text-gray-400">Hours</p>
        </div>
        <div class="text-2xl font-bold text-indigo-300 mt-1">:</div>
        <div>
          <p id="minutes" class="text-2xl font-bold text-indigo-600">00</p>
          <p class="text-xs text-gray-400">Minutes</p>
        </div>
        <div class="text-2xl font-bold text-indigo-300 mt-1">:</div>
        <div>
          <p id="seconds" class="text-2xl font-bold text-indigo-600">00</p>
          <p class="text-xs text-gray-400">Seconds</p>
        </div>
      </div>
    </div>
  </div>

  <?php if ($message === 'success'): ?>
    <div class="bg-green-50 border border-green-300 text-green-700
                rounded-xl px-5 py-4 mb-5">
      <p class="font-semibold">✅ Your vote has been recorded successfully!</p>
      <p class="mt-2">
        <a href="results.php?id=<?= $election_id ?>"
           class="bg-green-600 text-white px-4 py-2 rounded-lg
                  hover:bg-green-700 inline-block mt-1 text-sm font-semibold">
           📊 View Live Results →
        </a>
      </p>
    </div>

  <?php elseif ($message === 'error'): ?>
    <div class="bg-red-50 border border-red-300 text-red-700
                rounded-xl px-5 py-4 mb-5">
      ❌ Something went wrong. Please try again.
    </div>
  <?php endif; ?>

  <?php if ($already_voted && $message !== 'success'): ?>
    <div class="bg-blue-50 border border-blue-300 text-blue-700
                rounded-xl px-5 py-4 mb-5">
      <p class="font-semibold">ℹ️ You have already voted in this election.</p>
      <p class="mt-2">
        <a href="results.php?id=<?= $election_id ?>"
           class="bg-blue-600 text-white px-4 py-2 rounded-lg
                  hover:bg-blue-700 inline-block mt-1 text-sm font-semibold">
           📊 View Live Results →
        </a>
      </p>
    </div>
  <?php endif; ?>

  <?php if (!$already_voted): ?>
  <div class="mb-4">
    <h2 class="font-semibold text-gray-700 mb-4 text-lg">
      🗳️ Select your candidate:
    </h2>
    <form method="POST" id="vote-form" class="space-y-4">

      <?php foreach ($candidates as $c): ?>
      <?php
      if (!empty($c['photo']) && $c['photo'] !== 'default.png') {
          $photo_src = '/voting_system/public/uploads/' . htmlspecialchars($c['photo']);
      } else {
          $photo_src = 'https://ui-avatars.com/api/?name='
                       . urlencode($c['name'])
                       . '&background=6366f1&color=fff&size=128&bold=true';
      }
      ?>
      <label class="block border-2 border-gray-200 rounded-2xl overflow-hidden
                    cursor-pointer hover:border-indigo-400 hover:shadow-md
                    transition-all duration-200">
        <div class="flex items-stretch">
          <!-- Photo -->
          <div class="w-28 h-28 shrink-0 overflow-hidden bg-gray-100">
            <img src="<?= $photo_src ?>"
                 alt="<?= htmlspecialchars($c['name']) ?>"
                 class="w-full h-full object-cover"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c['name']) ?>&background=6366f1&color=fff&size=128'">
          </div>
          <div class="flex-1 px-5 py-4 flex items-center gap-4">
            <input type="radio"
                   name="candidate_id"
                   value="<?= $c['id'] ?>"
                   required
                   class="accent-indigo-600 w-5 h-5 shrink-0">
            <div>
              <p class="font-bold text-gray-800 text-base candidate-name">
                <?= htmlspecialchars($c['name']) ?>
              </p>
              <p class="text-sm text-gray-500 mt-1">
                <?= htmlspecialchars($c['bio']) ?>
              </p>
            </div>
          </div>
        </div>
      </label>
      <?php endforeach; ?>

      <?php if (empty($candidates)): ?>
        <div class="text-center py-10 text-gray-400">
          <p class="text-4xl mb-2">👤</p>
          <p>No candidates added yet.</p>
        </div>
      <?php else: ?>

        <button type="button"
                onclick="confirmVote()"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                       font-bold py-4 rounded-2xl transition text-lg mt-2
                       shadow-md hover:shadow-lg">
          🗳️ Cast My Vote
        </button>
      <?php endif; ?>

    </form>
  </div>
  <?php endif; ?>

  <?php if (!empty($candidates)): ?>
  <div class="mt-4 text-center">
    <a href="results.php?id=<?= $election_id ?>"
       class="text-indigo-400 text-sm hover:underline">
       View current results without voting →
    </a>
  </div>
  <?php endif; ?>

</div>

<div id="confirm-modal"
     class="hidden fixed inset-0 bg-black bg-opacity-50
            flex items-center justify-center z-50 px-4">
  <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center">
    <div class="text-5xl mb-4">🗳️</div>
    <h2 class="text-xl font-bold text-gray-800 mb-2">Confirm Your Vote</h2>
    <p class="text-gray-500 mb-2">You are about to vote for:</p>
    <p id="selected-candidate"
       class="text-lg font-bold text-indigo-600 mb-4 px-4 py-2
              bg-indigo-50 rounded-xl">
    </p>
    <p class="text-sm text-red-400 mb-6">
      ⚠️ This action cannot be undone!
    </p>
    <div class="flex gap-3">
      <button onclick="cancelVote()"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                     font-semibold py-3 rounded-xl transition">
        Cancel
      </button>
      <button onclick="submitVote()"
              class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white
                     font-semibold py-3 rounded-xl transition">
        Yes, Vote!
      </button>
    </div>
  </div>
</div>

<script>
// ── Confirmation Popup ──────────────────────────────
function confirmVote() {
    const selected = document.querySelector('input[name="candidate_id"]:checked');
    if (!selected) {
        alert('Please select a candidate first!');
        return;
    }
    const label = selected.closest('label');
    const name  = label.querySelector('.candidate-name').textContent.trim();
    document.getElementById('selected-candidate').textContent = name;
    document.getElementById('confirm-modal').classList.remove('hidden');
}

function cancelVote() {
    document.getElementById('confirm-modal').classList.add('hidden');
}

function submitVote() {
    document.getElementById('vote-form').submit();
}

document.getElementById('confirm-modal').addEventListener('click', function(e) {
    if (e.target === this) cancelVote();
});

// ── Countdown Timer ─────────────────────────────────
const endDate = new Date("<?= $election['end_date'] ?>");

function updateCountdown() {
    const now  = new Date();
    const diff = endDate - now;

    if (diff <= 0) {
        document.getElementById('countdown').innerHTML =
            '<p class="text-red-500 font-semibold text-sm">⛔ Voting has ended!</p>';
        return;
    }

    const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    document.getElementById('days').textContent    = String(days).padStart(2, '0');
    document.getElementById('hours').textContent   = String(hours).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
}

updateCountdown();
setInterval(updateCountdown, 1000);
</script>

<?php require 'includes/footer.php'; ?>