<?php
session_start();
require 'config/db.php';
require 'includes/update_status.php';
$page_title = 'Home - VoteApp';

// Auto update election statuses
update_election_statuses($pdo);

$active   = $pdo->query("SELECT * FROM elections WHERE status = 'active'  ORDER BY end_date ASC")->fetchAll();
$upcoming = $pdo->query("SELECT * FROM elections WHERE status = 'upcoming' ORDER BY start_date ASC")->fetchAll();
$closed   = $pdo->query("SELECT * FROM elections WHERE status = 'closed'  ORDER BY end_date DESC LIMIT 3")->fetchAll();

require 'includes/header.php';
?>

<!-- Hero Banner -->
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl p-8 mb-8 text-center shadow-lg">
  <h1 class="text-3xl font-bold mb-2">🗳️ Smart Voting System</h1>
  <p class="text-indigo-100 text-lg">Your voice matters — vote securely and transparently</p>
  <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="mt-6 flex justify-center gap-4">
      <a href="signup.php"
         class="bg-white text-indigo-700 px-6 py-2 rounded-full font-bold hover:bg-indigo-50">
         Get Started
      </a>
      <a href="login.php"
         class="border border-white text-white px-6 py-2 rounded-full font-bold hover:bg-indigo-700">
         Login
      </a>
    </div>
  <?php else: ?>
    <p class="mt-4 text-indigo-200">
      Welcome back, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>! 👋
    </p>
  <?php endif; ?>
</div>

<!-- Search Box -->
<div class="mb-6">
  <div class="relative">
    <input type="text"
           id="search-input"
           placeholder="🔍 Search elections..."
           oninput="searchElections()"
           class="w-full border border-gray-300 rounded-xl px-4 py-3
                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                  bg-white shadow-sm">
    <button onclick="clearSearch()"
            id="clear-btn"
            class="hidden absolute right-3 top-3 text-gray-400
                   hover:text-gray-600 text-xl font-bold">
      ✕
    </button>
  </div>
</div>

<!-- No Search Results -->
<div id="no-results"
     class="hidden bg-white rounded-2xl shadow p-8 text-center text-gray-400 mb-8">
  <p class="text-4xl mb-2">🔍</p>
  <p>No elections found matching your search.</p>
</div>

<h2 class="text-xl font-bold text-gray-700 mb-4">🟢 Active Elections</h2>

<?php if (empty($active)): ?>
  <div class="bg-white rounded-2xl shadow p-8 text-center text-gray-400 mb-8">
    <p class="text-4xl mb-2">🗓️</p>
    <p>No active elections right now. Check back soon!</p>
  </div>
<?php else: ?>
  <div id="elections-grid" class="grid md:grid-cols-2 gap-4 mb-8">
    <?php foreach ($active as $e): ?>
    <div class="election-card bg-white rounded-2xl shadow hover:shadow-md
                transition p-6 border-l-4 border-green-500"
         data-title="<?= strtolower(htmlspecialchars($e['title'])) ?>">
      <div class="flex items-start justify-between mb-2">
        <h3 class="font-bold text-gray-800 text-lg">
          <?= htmlspecialchars($e['title']) ?>
        </h3>
        <span class="bg-green-100 text-green-700 text-xs px-2 py-1
                     rounded-full font-semibold">
          Active
        </span>
      </div>
      <p class="text-gray-500 text-sm mb-4">
        <?= htmlspecialchars($e['description']) ?>
      </p>
      <p class="text-xs text-gray-400 mb-4">
        ⏰ Ends: <?= date('M d, Y h:i A', strtotime($e['end_date'])) ?>
      </p>
      <div class="flex gap-2">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="vote.php?id=<?= $e['id'] ?>"
             class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white
                    text-center py-2 rounded-lg text-sm font-semibold transition">
             🗳️ Vote Now
          </a>
          <a href="results.php?id=<?= $e['id'] ?>"
             class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700
                    text-center py-2 rounded-lg text-sm font-semibold transition">
             📊 Results
          </a>
        <?php else: ?>
          <a href="login.php"
             class="flex-1 bg-indigo-600 text-white text-center
                    py-2 rounded-lg text-sm font-semibold">
             Login to Vote
          </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($upcoming)): ?>
<h2 class="text-xl font-bold text-gray-700 mb-4">🔵 Upcoming Elections</h2>
<div class="grid md:grid-cols-2 gap-4 mb-8">
  <?php foreach ($upcoming as $e): ?>
  <div class="election-card bg-white rounded-2xl shadow p-6
              border-l-4 border-blue-400 opacity-80"
       data-title="<?= strtolower(htmlspecialchars($e['title'])) ?>">
    <div class="flex items-start justify-between mb-2">
      <h3 class="font-bold text-gray-800">
        <?= htmlspecialchars($e['title']) ?>
      </h3>
      <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1
                   rounded-full font-semibold">
        Upcoming
      </span>
    </div>
    <p class="text-gray-500 text-sm mb-2">
      <?= htmlspecialchars($e['description']) ?>
    </p>
    <p class="text-xs text-gray-400">
      📅 Starts: <?= date('M d, Y h:i A', strtotime($e['start_date'])) ?>
    </p>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($closed)): ?>
<h2 class="text-xl font-bold text-gray-700 mb-4">⚫ Recently Closed</h2>
<div class="grid md:grid-cols-2 gap-4 mb-8">
  <?php foreach ($closed as $e): ?>
  <div class="election-card bg-white rounded-2xl shadow p-6
              border-l-4 border-gray-300 opacity-70"
       data-title="<?= strtolower(htmlspecialchars($e['title'])) ?>">
    <div class="flex items-start justify-between mb-2">
      <h3 class="font-bold text-gray-800">
        <?= htmlspecialchars($e['title']) ?>
      </h3>
      <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1
                   rounded-full font-semibold">
        Closed
      </span>
    </div>
    <p class="text-gray-500 text-sm mb-2">
      <?= htmlspecialchars($e['description']) ?>
    </p>
    <a href="results.php?id=<?= $e['id'] ?>"
       class="text-indigo-500 text-sm hover:underline">
       📊 View Final Results →
    </a>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function searchElections() {
    const query    = document.getElementById('search-input').value.toLowerCase();
    const cards    = document.querySelectorAll('.election-card');
    const clearBtn = document.getElementById('clear-btn');
    const noResults = document.getElementById('no-results');
    let visibleCount = 0;

    clearBtn.classList.toggle('hidden', query === '');

    cards.forEach(card => {
        const title = card.getAttribute('data-title');
        if (title.includes(query)) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    noResults.classList.toggle('hidden', visibleCount > 0 || query === '');
}

function clearSearch() {
    document.getElementById('search-input').value = '';
    searchElections();
}
</script>

<?php require 'includes/footer.php'; ?>