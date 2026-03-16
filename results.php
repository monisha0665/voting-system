<?php
// Results page - live chart with auto refresh
session_start();
require 'config/db.php';
$page_title  = 'Live Results';
$election_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->execute([$election_id]);
$election = $stmt->fetch();
if (!$election) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE election_id = ?");
$stmt->execute([$election_id]);
$total_votes = $stmt->fetchColumn();

require 'includes/header.php';
?>

<div class="max-w-3xl mx-auto">

  <a href="index.php" class="text-indigo-500 text-sm hover:underline">← Back to Home</a>

  <div class="flex items-center justify-between mt-2 mb-1">
    <h1 class="text-2xl font-bold text-indigo-700">
      <?= htmlspecialchars($election['title']) ?>
    </h1>
    <span class="text-xs bg-red-100 text-red-600 px-3 py-1 rounded-full font-semibold flex items-center gap-1">
      <span class="w-2 h-2 bg-red-500 rounded-full inline-block animate-pulse"></span>
      Live
    </span>
  </div>

  <p class="text-gray-400 text-sm mb-6">
    Auto-updates every 10 seconds •
    Total votes cast:
    <strong id="total-votes"><?= $total_votes ?></strong>
  </p>

  <!-- Loading -->
  <div id="loading" class="text-center py-10 text-gray-400">
    <div class="text-4xl mb-2">📊</div>
    <p>Loading results...</p>
  </div>

  <!-- Chart -->
  <div id="chart-wrap" class="bg-white rounded-2xl shadow p-6 mb-6 hidden">
    <canvas id="resultsChart" height="120"></canvas>
  </div>

  <!-- Summary Cards -->
  <div id="summary" class="grid grid-cols-2 md:grid-cols-3 gap-4 hidden"></div>

  <!-- Vote Button -->
  <?php
  $already_voted = false;
  if (isset($_SESSION['user_id'])) {
      $stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ? AND election_id = ?");
      $stmt->execute([$_SESSION['user_id'], $election_id]);
      $already_voted = $stmt->rowCount() > 0;
  }
  ?>
  <div class="mt-6 text-center">
    <?php if ($election['status'] === 'active' && isset($_SESSION['user_id']) && !$already_voted): ?>
      <a href="vote.php?id=<?= $election_id ?>"
         class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3
                rounded-xl font-semibold transition inline-block">
         🗳️ Go Vote Now
      </a>
    <?php elseif ($already_voted): ?>
      <p class="text-green-600 font-semibold">✅ You have already voted in this election</p>
    <?php elseif (!isset($_SESSION['user_id'])): ?>
      <a href="login.php"
         class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3
                rounded-xl font-semibold transition inline-block">
         Login to Vote
      </a>
    <?php endif; ?>
  </div>

</div>

<script>
let chart = null;
const colors = ['#6366f1','#22c55e','#f59e0b','#ef4444','#14b8a6','#a855f7'];

async function loadResults() {
  try {
    const res = await fetch('/voting_system/api/results_data.php?id=<?= $election_id ?>');

    if (!res.ok) {
      throw new Error('Network error');
    }

    const data = await res.json();

    if (!data || data.length === 0) {
      document.getElementById('loading').innerHTML =
        '<p class="text-gray-400">No candidates found.</p>';
      return;
    }

    document.getElementById('loading').classList.add('hidden');
    document.getElementById('chart-wrap').classList.remove('hidden');
    document.getElementById('summary').classList.remove('hidden');

    const labels = data.map(r => r.name);
    const counts = data.map(r => parseInt(r.votes));
    const total  = counts.reduce((a, b) => a + b, 0);

    document.getElementById('total-votes').textContent = total;

    if (!chart) {
      chart = new Chart(document.getElementById('resultsChart'), {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Votes',
            data: counts,
            backgroundColor: colors.slice(0, labels.length),
            borderRadius: 8,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const val = context.parsed.y;
                  const pct = total > 0 ? Math.round(val / total * 100) : 0;
                  return ` ${val} votes (${pct}%)`;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 }
            }
          }
        }
      });
    } else {
      chart.data.labels = labels;
      chart.data.datasets[0].data = counts;
      chart.update();
    }

    document.getElementById('summary').innerHTML = data.map((r, i) => `
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
        <p class="font-semibold text-gray-700 text-sm">${r.name}</p>
        <p class="text-3xl font-bold mt-1" style="color:${colors[i]}">${r.votes}</p>
        <p class="text-xs text-gray-400 mt-1">
          ${total > 0 ? Math.round(r.votes / total * 100) : 0}% of votes
        </p>
      </div>
    `).join('');

  } catch (e) {
    console.error('Error:', e);
    document.getElementById('loading').innerHTML =
      '<p class="text-red-400">❌ Could not load results. Please refresh.</p>';
  }
}

loadResults();
setInterval(loadResults, 10000);
</script>

<?php require 'includes/footer.php'; ?>