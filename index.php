<?php
session_start();
require 'config/db.php';
require 'includes/update_status.php';
$page_title = 'Home - VoteApp';

update_election_statuses($pdo);

$active   = $pdo->query("SELECT * FROM elections WHERE status = 'active'  ORDER BY end_date ASC")->fetchAll();
$upcoming = $pdo->query("SELECT * FROM elections WHERE status = 'upcoming' ORDER BY start_date ASC")->fetchAll();
$closed   = $pdo->query("SELECT * FROM elections WHERE status = 'closed'  ORDER BY end_date DESC LIMIT 3")->fetchAll();

require 'includes/header.php';
?>

<style>
/* Hero gradient */
.hero-gradient {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #6366f1 100%);
  position: relative;
  overflow: hidden;
}
.hero-gradient::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
  animation: pulse-bg 4s ease-in-out infinite;
}
@keyframes pulse-bg {
  0%, 100% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.1); opacity: 1; }
}

/* Stats counter animation */
@keyframes countUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.stat-num { animation: countUp 0.6s ease forwards; }

/* Card hover */
.election-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.election-card:hover {
  transform: translateY(-4px);
}

/* Dark mode hero */
.dark .hero-gradient {
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e1b4b 100%);
}
</style>

<!-- ── Hero Banner ──────────────────────── -->
<div class="hero-gradient text-white rounded-3xl p-10 mb-8
            shadow-2xl relative">
  <div class="relative z-10 text-center">
    <div class="text-5xl mb-3">🗳️</div>
    <h1 class="text-4xl md:text-5xl font-extrabold mb-3 tracking-tight">
      Smart Voting System
    </h1>
    <p class="text-indigo-100 text-lg md:text-xl mb-6 max-w-xl mx-auto">
      Your voice matters — vote securely and transparently
    </p>

    <?php if (!isset($_SESSION['user_id'])): ?>
      <div class="flex justify-center gap-4 flex-wrap">
        <a href="signup.php"
           class="bg-white text-indigo-700 px-8 py-3 rounded-2xl
                  font-bold hover:bg-indigo-50 transition shadow-lg
                  hover:shadow-xl hover:-translate-y-0.5 transform text-base">
           🚀 Get Started
        </a>
        <a href="login.php"
           class="border-2 border-white/60 text-white px-8 py-3 rounded-2xl
                  font-bold hover:bg-white/10 transition backdrop-blur-sm
                  hover:-translate-y-0.5 transform text-base">
           🔑 Login
        </a>
      </div>
    <?php else: ?>
      <div class="inline-flex items-center gap-3 bg-white/15 backdrop-blur-sm
                  rounded-2xl px-6 py-3 border border-white/20">
        <span class="text-2xl">👋</span>
        <div class="text-left">
          <p class="text-xs text-indigo-200">Welcome back</p>
          <p class="font-bold text-lg">
            <?= htmlspecialchars($_SESSION['name']) ?>
          </p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick Stats -->
  <?php
  $stat_active   = $pdo->query("SELECT COUNT(*) FROM elections WHERE status='active'")->fetchColumn();
  $stat_votes    = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
  $stat_users    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
  ?>
  <div class="relative z-10 grid grid-cols-3 gap-4 mt-8
              bg-white/10 backdrop-blur-sm rounded-2xl p-4
              border border-white/20">
    <div class="text-center">
      <p class="text-3xl font-extrabold stat-num"><?= $stat_active ?></p>
      <p class="text-indigo-200 text-xs mt-1">Active Elections</p>
    </div>
    <div class="text-center border-x border-white/20">
      <p class="text-3xl font-extrabold stat-num"><?= $stat_votes ?></p>
      <p class="text-indigo-200 text-xs mt-1">Total Votes Cast</p>
    </div>
    <div class="text-center">
      <p class="text-3xl font-extrabold stat-num"><?= $stat_users ?></p>
      <p class="text-indigo-200 text-xs mt-1">Registered Voters</p>
    </div>
  </div>
</div>

<!-- ── Search Box ────────────────────────── -->
<div class="mb-8">
  <div class="relative max-w-2xl mx-auto">
    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg">
      🔍
    </span>
    <input type="text"
           id="search-input"
           placeholder="Search elections..."
           oninput="searchElections()"
           class="w-full border border-gray-200 rounded-2xl
                  pl-12 pr-12 py-4 text-base
                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                  bg-white shadow-sm font-medium">
    <button onclick="clearSearch()"
            id="clear-btn"
            class="hidden absolute right-4 top-1/2 -translate-y-1/2
                   text-gray-400 hover:text-gray-600 text-lg
                   w-7 h-7 flex items-center justify-center
                   rounded-full hover:bg-gray-100 transition">
      ✕
    </button>
  </div>
</div>

<!-- No results -->
<div id="no-results"
     class="hidden bg-white rounded-2xl shadow p-10
            text-center text-gray-400 mb-8 border border-gray-100">
  <p class="text-5xl mb-3">🔍</p>
  <p class="font-semibold text-lg">No elections found</p>
  <p class="text-sm mt-1">Try a different search term</p>
</div>

<!-- ── Active Elections ──────────────────── -->
<div class="flex items-center gap-3 mb-5">
  <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
  <h2 class="text-xl font-bold text-gray-800">Active Elections</h2>
  <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-1
               rounded-full font-bold">
    <?= count($active) ?> live
  </span>
</div>

<?php if (empty($active)): ?>
  <div class="bg-white rounded-2xl shadow-sm p-10 text-center
              text-gray-400 mb-8 border border-gray-100">
    <p class="text-5xl mb-3">🗓️</p>
    <p class="font-semibold text-lg">No active elections right now</p>
    <p class="text-sm mt-1">Check back soon!</p>
  </div>
<?php else: ?>
  <div id="elections-grid" class="grid md:grid-cols-2 gap-5 mb-10">
    <?php foreach ($active as $e): ?>
    <div class="election-card bg-white rounded-2xl shadow-sm
                border border-gray-100 overflow-hidden
                hover:shadow-xl hover:border-indigo-200"
         data-title="<?= strtolower(htmlspecialchars($e['title'])) ?>">

      <!-- Card top accent -->
      <div class="h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>

      <div class="p-6">
        <div class="flex items-start justify-between mb-3">
          <h3 class="font-bold text-gray-900 text-lg leading-tight pr-2">
            <?= htmlspecialchars($e['title']) ?>
          </h3>
          <span class="bg-emerald-100 text-emerald-700 text-xs px-3 py-1
                       rounded-full font-bold shrink-0 flex items-center gap-1">
            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full
                         animate-pulse inline-block"></span>
            Active
          </span>
        </div>

        <?php if ($e['description']): ?>
        <p class="text-gray-500 text-sm mb-4 leading-relaxed">
          <?= htmlspecialchars($e['description']) ?>
        </p>
        <?php endif; ?>

        <div class="flex items-center gap-2 text-xs text-gray-400 mb-5">
          <span>⏰</span>
          <span>Ends <?= date('M d, Y h:i A', strtotime($e['end_date'])) ?></span>
        </div>

        <div class="flex gap-2">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="vote.php?id=<?= $e['id'] ?>"
               class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600
                      hover:from-indigo-700 hover:to-purple-700 text-white
                      text-center py-2.5 rounded-xl text-sm font-bold
                      transition shadow-md hover:shadow-indigo-200
                      hover:shadow-lg transform hover:-translate-y-0.5">
               🗳️ Vote Now
            </a>
            <a href="results.php?id=<?= $e['id'] ?>"
               class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-700
                      text-center py-2.5 rounded-xl text-sm font-bold
                      transition border border-gray-200 hover:border-gray-300
                      transform hover:-translate-y-0.5">
               📊 Results
            </a>
          <?php else: ?>
            <a href="login.php"
               class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600
                      text-white text-center py-2.5 rounded-xl
                      text-sm font-bold transition shadow-md">
               🔑 Login to Vote
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- ── Upcoming Elections ────────────────── -->
<?php if (!empty($upcoming)): ?>
<div class="flex items-center gap-3 mb-5">
  <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
  <h2 class="text-xl font-bold text-gray-800">Upcoming Elections</h2>
  <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1
               rounded-full font-bold">
    <?= count($upcoming) ?> soon
  </span>
</div>
<div class="grid md:grid-cols-2 gap-5 mb-10">
  <?php foreach ($upcoming as $e): ?>
  <div class="election-card bg-white rounded-2xl shadow-sm
              border border-gray-100 overflow-hidden opacity-80"
       data-title="<?= strtolower(htmlspecialchars($e['title'])) ?>">
    <div class="h-1 bg-gradient-to-r from-blue-400 to-cyan-400"></div>
    <div class="p-6">
      <div class="flex items-start justify-between mb-3">
        <h3 class="font-bold text-gray-900 text-lg pr-2">
          <?= htmlspecialchars($e['title']) ?>
        </h3>
        <span class="bg-blue-100 text-blue-700 text-xs px-3 py-1
                     rounded-full font-bold shrink-0">
          Upcoming
        </span>
      </div>
      <?php if ($e['description']): ?>
      <p class="text-gray-500 text-sm mb-4">
        <?= htmlspecialchars($e['description']) ?>
      </p>
      <?php endif; ?>
      <div class="flex items-center gap-2 text-xs text-gray-400">
        <span>📅</span>
        <span>Starts <?= date('M d, Y h:i A', strtotime($e['start_date'])) ?></span>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Closed Elections ──────────────────── -->
<?php if (!empty($closed)): ?>
<div class="flex items-center gap-3 mb-5">
  <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
  <h2 class="text-xl font-bold text-gray-800">Recently Closed</h2>
</div>
<div class="grid md:grid-cols-3 gap-4 mb-8">
  <?php foreach ($closed as $e): ?>
  <div class="election-card bg-white rounded-2xl shadow-sm
              border border-gray-100 overflow-hidden opacity-70"
       data-title="<?= strtolower(htmlspecialchars($e['title'])) ?>">
    <div class="h-1 bg-gradient-to-r from-gray-300 to-gray-400"></div>
    <div class="p-5">
      <div class="flex items-start justify-between mb-2">
        <h3 class="font-bold text-gray-700 text-sm pr-2">
          <?= htmlspecialchars($e['title']) ?>
        </h3>
        <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5
                     rounded-full font-semibold shrink-0">
          Closed
        </span>
      </div>
      <a href="results.php?id=<?= $e['id'] ?>"
         class="text-indigo-500 text-xs hover:underline font-semibold
                flex items-center gap-1 mt-3">
        📊 View Final Results →
      </a>
    </div>
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
<!-- ── Announcements ─────────────────────── -->
<?php
$announcements = $pdo->query("
    SELECT * FROM announcements
    WHERE is_active = 1
    ORDER BY created_at DESC
")->fetchAll();

$ann_styles = [
    'info'    => 'bg-blue-50 border-blue-400 text-blue-900',
    'success' => 'bg-emerald-50 border-emerald-400 text-emerald-900',
    'warning' => 'bg-amber-50 border-amber-400 text-amber-900',
    'danger'  => 'bg-slate-100 border-slate-400 text-slate-900',
];
$ann_dark = [
    'info'    => 'rgba(59,130,246,0.12)',
    'success' => 'rgba(16,185,129,0.12)',
    'warning' => 'rgba(245,158,11,0.12)',
    'danger'  => 'rgba(100,116,139,0.15)',
];
$ann_icons = [
    'info'    => 'ℹ️',
    'success' => '✅',
    'warning' => '⚠️',
    'danger'  => '🔔',
];

if (!empty($announcements)): ?>
<div class="mb-6 space-y-3">
  <?php foreach ($announcements as $ann): ?>
  <?php
  $style = $ann_styles[$ann['type']] ?? $ann_styles['info'];
  $icon  = $ann_icons[$ann['type']]  ?? 'ℹ️';
  ?>
  <div class="border-l-4 rounded-2xl px-5 py-4 <?= $style ?>
              flex items-start gap-3 shadow-sm announcement-item">
    <span class="text-2xl shrink-0 mt-0.5"><?= $icon ?></span>
    <div class="flex-1 min-w-0">
      <p class="font-bold text-base">
        <?= htmlspecialchars($ann['title']) ?>
      </p>
      <p class="text-sm mt-1 opacity-80">
        <?= htmlspecialchars($ann['message']) ?>
      </p>
      <p class="text-xs opacity-50 mt-1">
        <?= date('M d, Y h:i A', strtotime($ann['created_at'])) ?>
      </p>
    </div>
    <button onclick="this.closest('.announcement-item').style.display='none'"
            class="text-lg opacity-40 hover:opacity-80 shrink-0 transition
                   w-7 h-7 flex items-center justify-center rounded-full
                   hover:bg-black/10">
      ✕
    </button>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>