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

<style>
@media print {
  nav, footer, button, #loading,
  .no-print { display: none !important; }
  body      { background: white !important; }
  .shadow   { box-shadow: none !important; }
  #chart-wrap { border: 1px solid #e5e7eb !important; }
}
</style>

<div class="max-w-3xl mx-auto">

  <a href="index.php"
     class="text-indigo-500 text-sm hover:underline no-print">
    ← Back to Home
  </a>

  <!-- Title + Buttons -->
  <div class="flex items-center justify-between mt-2 mb-1">
    <h1 class="text-2xl font-bold text-indigo-700">
      <?= htmlspecialchars($election['title']) ?>
    </h1>
    <div class="flex items-center gap-2 no-print">
      <button onclick="window.print()"
              class="bg-gray-100 hover:bg-gray-200 text-gray-700
                     px-3 py-2 rounded-lg text-sm font-semibold
                     transition flex items-center gap-1">
        🖨️ Print
      </button>
      <span class="text-xs bg-red-100 text-red-600 px-3 py-2
                   rounded-full font-semibold flex items-center gap-1">
        <span class="w-2 h-2 bg-red-500 rounded-full
                     inline-block animate-pulse"></span>
        Live
      </span>
    </div>
  </div>

  <p class="text-gray-400 text-sm mb-6">
    Auto-updates every 10 seconds •
    Total votes cast:
    <strong id="total-votes"><?= $total_votes ?></strong>
  </p>

  <div id="loading" class="text-center py-10 text-gray-400">
    <div class="text-4xl mb-2">📊</div>
    <p>Loading results...</p>
  </div>

  <div id="chart-wrap"
       class="bg-white rounded-2xl shadow-lg p-6 mb-6
              border border-gray-200 hidden">
    <canvas id="resultsChart"></canvas>
  </div>

  <div id="summary"
       class="grid grid-cols-2 md:grid-cols-3 gap-4 hidden mb-6">
  </div>

  <?php
  $already_voted = false;
  if (isset($_SESSION['user_id'])) {
      $stmt = $pdo->prepare(
          "SELECT id FROM votes WHERE user_id = ? AND election_id = ?"
      );
      $stmt->execute([$_SESSION['user_id'], $election_id]);
      $already_voted = $stmt->rowCount() > 0;
  }
  ?>
  <div class="mt-4 text-center no-print">
    <?php if ($election['status'] === 'active'
              && isset($_SESSION['user_id'])
              && !$already_voted): ?>
      <a href="vote.php?id=<?= $election_id ?>"
         class="bg-indigo-600 hover:bg-indigo-700 text-white
                px-8 py-3 rounded-xl font-semibold transition
                inline-block shadow-md">
         🗳️ Go Vote Now
      </a>
    <?php elseif ($already_voted): ?>
      <div class="bg-green-50 border border-green-200 text-green-700
                  rounded-xl px-5 py-3 inline-block">
        ✅ You have already voted in this election
      </div>
    <?php elseif (!isset($_SESSION['user_id'])): ?>
      <a href="login.php"
         class="bg-indigo-600 hover:bg-indigo-700 text-white
                px-8 py-3 rounded-xl font-semibold transition inline-block">
         🔑 Login to Vote
      </a>
    <?php endif; ?>
  </div>

</div>

<script>
let chart = null;
const colors = [
    '#4f46e5',
    '#16a34a',
    '#d97706',
    '#dc2626',
    '#0891b2',
    '#9333ea',
    '#ea580c',
    '#0284c7',
];
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

async function loadResults() {
  try {
    const res = await fetch(
        '/voting_system/api/results_data.php?id=<?= $election_id ?>'
    );
    if (!res.ok) throw new Error('Network error');
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

    // Dark mode aware colors
    const dark        = isDarkMode();
    const tickColor   = dark ? '#e5e7eb' : '#374151';
    const gridColor   = dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';
    const bgColor     = dark ? '#1f2937' : '#ffffff';

    if (!chart) {
      chart = new Chart(document.getElementById('resultsChart'), {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Votes',
            data: counts,
            backgroundColor: colors.slice(0, labels.length),
            borderColor: colors.slice(0, labels.length),
            borderWidth: 2,
            borderRadius: 10,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          layout: {
            padding: {
              bottom: 20
            }
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(0,0,0,0.85)',
              titleColor: '#ffffff',
              bodyColor: '#ffffff',
              padding: 12,
              cornerRadius: 8,
              callbacks: {
                label: function(context) {
                  const val = context.parsed.y;
                  const pct = total > 0
                      ? Math.round(val / total * 100) : 0;
                  return ` ${val} votes (${pct}%)`;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1,
                color: tickColor,
                font: {
                  size: 13,
                  weight: 'bold'
                }
              },
              grid: {
                color: gridColor,
                lineWidth: 1,
              },
              border: {
                color: gridColor
              }
            },
            x: {
              ticks: {
                color: tickColor,
                font: {
                  size: 12,
                  weight: 'bold'
                },
          
                maxRotation: 0,
                minRotation: 0,
            
                callback: function(value, index) {
                  const name = labels[index];
                  // Split name into 2 lines if too long
                  const words = name.split(' ');
                  if (words.length <= 2) return name;
                  const mid = Math.ceil(words.length / 2);
                  return [
                    words.slice(0, mid).join(' '),
                    words.slice(mid).join(' ')
                  ];
                }
              },
              grid: {
                display: false
              },
              border: {
                color: gridColor
              }
            }
          }
        }
      });
    } else {
      // Update existing chart
      chart.data.labels = labels;
      chart.data.datasets[0].data = counts;
      chart.data.datasets[0].backgroundColor = colors.slice(0, labels.length);
      chart.data.datasets[0].borderColor = colors.slice(0, labels.length);
      chart.options.scales.y.ticks.color = tickColor;
      chart.options.scales.x.ticks.color = tickColor;
      chart.options.scales.y.grid.color  = gridColor;
      chart.update();
    }

    document.getElementById('summary').innerHTML =
      data.map((r, i) => `
        <div class="bg-white rounded-xl p-4 shadow-sm text-center
                    border-2 transition-all hover:shadow-md"
             style="border-color: ${colors[i % colors.length]}">

          <!-- Color dot indicator -->
          <div class="w-4 h-4 rounded-full mx-auto mb-2"
               style="background-color: ${colors[i % colors.length]}">
          </div>

          <p class="font-semibold text-gray-700 text-sm mb-1 leading-tight">
            ${r.name}
          </p>

          <p class="text-4xl font-bold my-2"
             style="color: ${colors[i % colors.length]}">
            ${r.votes}
          </p>

          <!-- Progress bar -->
          <div class="w-full bg-gray-200 rounded-full h-3 mt-2 mb-1">
            <div class="h-3 rounded-full transition-all duration-700"
                 style="width: ${total > 0
                     ? Math.round(r.votes / total * 100) : 0}%;
                        background-color: ${colors[i % colors.length]}">
            </div>
          </div>

          <p class="text-xs text-gray-400 mt-1 font-semibold">
            ${total > 0
                ? Math.round(r.votes / total * 100) : 0}% of votes
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