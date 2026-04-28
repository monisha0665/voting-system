<?php
// Contact us page
session_start();
require 'config/db.php';
$page_title = 'Contact Us';
$success    = '';
$error      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim(htmlspecialchars($_POST['name']));
    $email   = trim($_POST['email']);
    $subject = trim(htmlspecialchars($_POST['subject']));
    $message = trim(htmlspecialchars($_POST['message']));

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } else {
        // Save to database
        $pdo->prepare("
            INSERT INTO contacts (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ")->execute([$name, $email, $subject, $message]);
        $success = "Thank you! Your message has been sent successfully.";
    }
}

require 'includes/header.php';
?>

<div class="max-w-4xl mx-auto">

  <!-- Page Header -->
  <div class="text-center mb-10">
    <h1 class="text-3xl font-bold text-indigo-700 mb-2">
      📬 Contact Us
    </h1>
    <p class="text-gray-500">
      Have a question or feedback? We would love to hear from you!
    </p>
  </div>

  <div class="grid md:grid-cols-2 gap-8">

    <!-- Contact Form -->
    <div class="bg-white rounded-2xl shadow p-6">
      <h2 class="font-bold text-gray-700 mb-4 text-lg">
        Send us a Message
      </h2>

      <?php if ($success): ?>
        <div class="bg-green-50 border border-green-300 text-green-700
                    rounded-xl px-4 py-3 mb-4">
          ✅ <?= $success ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="bg-red-50 border border-red-300 text-red-700
                    rounded-xl px-4 py-3 mb-4">
          ⚠️ <?= $error ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">
            Full Name <span class="text-red-500">*</span>
          </label>
          <input type="text" name="name" required
                 placeholder="Your full name"
                 value="<?= isset($_SESSION['name'])
                     ? htmlspecialchars($_SESSION['name']) : '' ?>"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2
                        focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">
            Email Address <span class="text-red-500">*</span>
          </label>
          <input type="email" name="email" required
                 placeholder="your@email.com"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2
                        focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Subject</label>
          <select name="subject"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2
                         focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="General Inquiry">General Inquiry</option>
            <option value="Technical Issue">Technical Issue</option>
            <option value="Voting Problem">Voting Problem</option>
            <option value="Account Issue">Account Issue</option>
            <option value="Feedback">Feedback</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">
            Message <span class="text-red-500">*</span>
          </label>
          <textarea name="message" rows="5" required
                    placeholder="Write your message here..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </textarea>
        </div>
        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white
                       font-bold py-3 rounded-xl transition shadow-md">
          📬 Send Message
        </button>
      </form>
    </div>

    <!-- Contact Info -->
    <div class="space-y-4">

      <!-- Info Cards -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="font-bold text-gray-700 mb-4 text-lg">
          Get in Touch
        </h2>
        <div class="space-y-4">
          <div class="flex items-start gap-4">
            <div class="bg-indigo-100 text-indigo-600 p-3
                        rounded-xl text-xl shrink-0">
              📧
            </div>
            <div>
              <p class="font-semibold text-gray-700">Email</p>
              <p class="text-gray-500 text-sm">admin@voteapp.com</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="bg-green-100 text-green-600 p-3
                        rounded-xl text-xl shrink-0">
              🌐
            </div>
            <div>
              <p class="font-semibold text-gray-700">Website</p>
              <p class="text-gray-500 text-sm">
                localhost/voting_system
              </p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="bg-purple-100 text-purple-600 p-3
                        rounded-xl text-xl shrink-0">
              🏫
            </div>
            <div>
              <p class="font-semibold text-gray-700">Institution</p>
              <p class="text-gray-500 text-sm">
                Al Emdad College, Sylhet
              </p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="bg-yellow-100 text-yellow-600 p-3
                        rounded-xl text-xl shrink-0">
              ⏰
            </div>
            <div>
              <p class="font-semibold text-gray-700">Response Time</p>
              <p class="text-gray-500 text-sm">
                Within 24 hours
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ Section -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="font-bold text-gray-700 mb-4 text-lg">
          ❓ Quick FAQ
        </h2>
        <div class="space-y-3">
          <div class="border border-gray-100 rounded-xl p-3">
            <p class="font-semibold text-gray-700 text-sm">
              Can I change my vote?
            </p>
            <p class="text-gray-500 text-xs mt-1">
              No. Once submitted your vote is final
              and cannot be changed.
            </p>
          </div>
          <div class="border border-gray-100 rounded-xl p-3">
            <p class="font-semibold text-gray-700 text-sm">
              How many times can I vote?
            </p>
            <p class="text-gray-500 text-xs mt-1">
              Only once per election. Our system
              enforces this automatically.
            </p>
          </div>
          <div class="border border-gray-100 rounded-xl p-3">
            <p class="font-semibold text-gray-700 text-sm">
              Is my vote anonymous?
            </p>
            <p class="text-gray-500 text-xs mt-1">
              Your vote is securely recorded.
              Only admins can see voting records.
            </p>
          </div>
          <div class="border border-gray-100 rounded-xl p-3">
            <p class="font-semibold text-gray-700 text-sm">
              When are results available?
            </p>
            <p class="text-gray-500 text-xs mt-1">
              Results are available live during
              and after the election.
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>