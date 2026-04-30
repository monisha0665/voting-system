</main>

<!-- ── Footer ───────────────────────────────── -->
<footer class="border-t mt-auto py-8"
        style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%)">
  <div class="max-w-6xl mx-auto px-4">
    <div class="grid md:grid-cols-3 gap-6 mb-6">

      <!-- Brand -->
      <div>
        <div class="flex items-center gap-2 mb-3">
          <span class="text-2xl">🗳️</span>
          <span class="text-white font-extrabold text-lg">VoteApp</span>
        </div>
        <p class="text-indigo-200 text-sm leading-relaxed">
          A smart, secure and transparent online voting system
          built with PHP and MySQL.
        </p>
      </div>

      <!-- Quick Links -->
      <div>
        <h3 class="text-white font-bold mb-3 text-sm uppercase tracking-wide">
          Quick Links
        </h3>
        <div class="space-y-2">
          <a href="/voting_system/index.php"
             class="block text-indigo-200 hover:text-white text-sm transition">
            🏠 Home
          </a>
          <a href="/voting_system/contact.php"
             class="block text-indigo-200 hover:text-white text-sm transition">
            📬 Contact Us
          </a>
          <a href="/voting_system/login.php"
             class="block text-indigo-200 hover:text-white text-sm transition">
            🔑 Login
          </a>
          <a href="/voting_system/signup.php"
             class="block text-indigo-200 hover:text-white text-sm transition">
            ✨ Sign Up
          </a>
        </div>
      </div>

      <!-- Info -->
      <div>
        <h3 class="text-white font-bold mb-3 text-sm uppercase tracking-wide">
          About
        </h3>
        <div class="space-y-2 text-sm text-indigo-200">
          <p>🏫 Al Emdad College, Sylhet</p>
          <p>👩‍💻 Monisha Hossain</p>
          <p>📧 admin@voteapp.com</p>
          <p>🔒 Secure & Transparent Voting</p>
        </div>
      </div>

    </div>

    <!-- Bottom bar -->
    <div class="border-t border-indigo-700 pt-4 flex flex-col md:flex-row
                items-center justify-between gap-2">
      <p class="text-indigo-300 text-xs">
        &copy; <?= date('Y') ?> VoteApp — Built with PHP + MySQL + Tailwind CSS
      </p>
      <div class="flex items-center gap-4 text-xs text-indigo-300">
        <span>🔐 Secured with bcrypt</span>
        <span>🛡️ SQL Injection Protected</span>
        <span>📱 Mobile Responsive</span>
      </div>
    </div>
  </div>
</footer>

</body>
</html>