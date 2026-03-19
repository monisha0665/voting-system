<?php
// Admin candidates - manage candidates per election
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
// DELETE candidate
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);

    // Get photo to delete file
    $stmt = $pdo->prepare("SELECT photo FROM candidates WHERE id = ?");
    $stmt->execute([$del_id]);
    $candidate = $stmt->fetch();

    // Delete photo file if exists
    if ($candidate && $candidate['photo'] !== 'default.png') {
        $photo_path = '../public/uploads/' . $candidate['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    // Delete votes for this candidate first
    $pdo->prepare("DELETE FROM votes WHERE candidate_id = ?")
        ->execute([$del_id]);

    // Now safe to delete candidate
    $pdo->prepare("DELETE FROM candidates WHERE id = ? AND election_id = ?")
        ->execute([$del_id, $election_id]);

    header("Location: candidates.php?election_id=$election_id");
    exit;
}

// ADD candidate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $bio   = trim($_POST['bio']);
    $photo = 'default.png';

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed    = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size   = 2 * 1024 * 1024; // 2MB
        $file       = $_FILES['photo'];
        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $upload_dir = '../public/uploads/';

        // Create folder if it does not exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (!in_array($ext, $allowed)) {
            $error = "Only JPG PNG GIF and WEBP images are allowed.";
        } elseif ($file['size'] > $max_size) {
            $error = "Image must be smaller than 2MB.";
        } else {
            $new_filename = 'candidate_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_path  = $upload_dir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $photo = $new_filename;
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        }
    }

    if (empty($name)) {
        $error = "Candidate name is required.";
    }

    if (empty($error)) {
        $pdo->prepare("
            INSERT INTO candidates (election_id, name, bio, photo)
            VALUES (?, ?, ?, ?)
        ")->execute([$election_id, $name, $bio, $photo]);
        $success = "Candidate added successfully!";
    }
}

// Get all candidates
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
  <a href="dashboard.php" class="text-indigo-500 text-sm hover:underline">
    ← Back to Dashboard
  </a>
  <h1 class="text-2xl font-bold text-indigo-700 mt-2 mb-1">👥 Manage Candidates</h1>
  <p class="text-gray-500 text-sm mb-6">
    Election: <strong><?= htmlspecialchars($election['title']) ?></strong>
    <span class="ml-2 px-2 py-1 rounded-full text-xs font-semibold
      <?= $election['status'] === 'active'   ? 'bg-green-100 text-green-700'  :
         ($election['status'] === 'upcoming' ? 'bg-blue-100 text-blue-700'    :
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
    <form method="POST" enctype="multipart/form-data" class="space-y-4">

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

      <div>
        <label class="block text-sm font-medium mb-1">
          Candidate Photo
          <span class="text-gray-400 font-normal">
            (optional — JPG PNG WEBP max 2MB)
          </span>
        </label>
        <input type="file" name="photo" accept="image/*"
               id="photo-input"
               class="w-full border border-gray-300 rounded-lg px-3 py-2
                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                      file:mr-4 file:py-1 file:px-4 file:rounded-full
                      file:border-0 file:text-sm file:font-semibold
                      file:bg-indigo-50 file:text-indigo-700
                      hover:file:bg-indigo-100">

        <!-- Photo Preview -->
        <div id="preview-wrap" class="hidden mt-3 flex items-center gap-3">
          <img id="photo-preview"
               class="w-20 h-20 rounded-full object-cover border-2 border-indigo-300">
          <p class="text-sm text-gray-500">Photo preview</p>
        </div>
      </div>

      <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                     font-semibold py-3 rounded-xl transition">
        Add Candidate
      </button>
    </form>
  </div>

  <!-- Candidates List -->
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <h2 class="font-bold text-gray-700">
        Current Candidates
        <span class="ml-2 bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full">
          <?= count($candidates) ?>
        </span>
      </h2>
    </div>

    <?php if (empty($candidates)): ?>
      <div class="px-6 py-10 text-center text-gray-400">
        <p class="text-4xl mb-2">👤</p>
        <p>No candidates yet. Add one above!</p>
      </div>
    <?php else: ?>
      <div class="divide-y divide-gray-100">
        <?php foreach ($candidates as $c): ?>
        <?php
        if (!empty($c['photo']) && $c['photo'] !== 'default.png') {
            $photo_src = '/voting_system/public/uploads/' . htmlspecialchars($c['photo']);
        } else {
            $photo_src = 'https://ui-avatars.com/api/?name='
                         . urlencode($c['name'])
                         . '&background=6366f1&color=fff&size=80&bold=true';
        }
        ?>
        <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
          <div class="flex items-center gap-4">

            <!-- Candidate Photo -->
            <img src="<?= $photo_src ?>"
                 alt="<?= htmlspecialchars($c['name']) ?>"
                 class="w-14 h-14 rounded-full object-cover border-2 border-gray-200"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c['name']) ?>&background=6366f1&color=fff&size=80'">

            <!-- Candidate Info -->
            <div>
              <p class="font-semibold text-gray-800">
                <?= htmlspecialchars($c['name']) ?>
              </p>
              <p class="text-sm text-gray-500 mt-1">
                <?= htmlspecialchars($c['bio']) ?>
              </p>
            </div>
          </div>

          <!-- Vote Count + Remove -->
          <div class="flex items-center gap-4 ml-4 shrink-0">
            <div class="text-center">
              <p class="text-2xl font-bold text-indigo-600">
                <?= $c['vote_count'] ?>
              </p>
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

<!-- Photo Preview Script -->
<script>
document.getElementById('photo-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo-preview').src = e.target.result;
            document.getElementById('preview-wrap').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require '../includes/footer.php'; ?>
