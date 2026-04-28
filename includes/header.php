<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'VoteApp' ?></title>
  <meta name="theme-color" content="#4338ca">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Apply saved theme immediately to prevent flash -->
  <script>
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>

  <style>
    /* Dark mode styles */
    .dark body             { background-color: #111827 !important; color: #f9fafb !important; }
    .dark nav              { background-color: #1e1b4b !important; }
    .dark main             { background-color: #111827 !important; }
    .dark .bg-white        { background-color: #1f2937 !important; }
    .dark .bg-gray-50      { background-color: #111827 !important; }
    .dark .bg-gray-100     { background-color: #374151 !important; }
    .dark .bg-indigo-50    { background-color: #1e1b4b !important; }
    .dark .text-gray-800   { color: #f9fafb !important; }
    .dark .text-gray-700   { color: #e5e7eb !important; }
    .dark .text-gray-600   { color: #d1d5db !important; }
    .dark .text-gray-500   { color: #9ca3af !important; }
    .dark .text-gray-400   { color: #6b7280 !important; }
    .dark .border-gray-200 { border-color: #374151 !important; }
    .dark .border-gray-300 { border-color: #4b5563 !important; }
    .dark .border-gray-100 { border-color: #374151 !important; }
    .dark .divide-gray-100 > * { border-color: #374151 !important; }
    .dark .shadow          { box-shadow: 0 1px 3px rgba(0,0,0,0.5) !important; }
    .dark footer           { border-color: #374151 !important; color: #6b7280 !important; }
    .dark input,
    .dark textarea,
    .dark select {
        background-color: #374151 !important;
        color: #f9fafb !important;
        border-color: #4b5563 !important;
    }
    .dark .hover\:bg-gray-50:hover  { background-color: #374151 !important; }
    .dark .hover\:bg-gray-200:hover { background-color: #4b5563 !important; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<nav class="bg-indigo-700 text-white shadow-md sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="/voting_system/index.php"
       class="text-xl font-bold tracking-wide">
       🗳️ VoteApp
    </a>

    <!-- Desktop menu -->
    <div class="hidden md:flex items-center gap-3 text-sm">

      <!-- Dark Mode Toggle -->
      <button onclick="toggleDarkMode()"
              id="theme-toggle"
              class="text-white hover:text-yellow-300 text-xl transition"
              title="Toggle dark mode">
        🌙
      </button>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Home -->
        <a href="/voting_system/index.php"
           class="bg-purple-500 hover:bg-purple-400 text-white px-4 py-2
                  rounded-full font-bold transition shadow-md">
           🏠 Home
        </a>

        <!-- My Profile -->
        <a href="/voting_system/profile.php"
           class="bg-green-500 hover:bg-green-400 text-white px-4 py-2
                  rounded-full font-bold transition shadow-md">
           👤 My Profile
        </a>

        <!-- Contact -->
        <a href="/voting_system/contact.php"
           class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2
                  rounded-full font-bold transition shadow-md">
           📬 Contact
        </a>

        <!-- Admin Button -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="/voting_system/admin/dashboard.php"
             class="bg-yellow-400 hover:bg-yellow-300 text-gray-900
                    px-4 py-2 rounded-full font-bold transition shadow-md">
             ⚙️ Admin
          </a>
        <?php endif; ?>

        <!-- Logout -->
        <a href="/voting_system/logout.php"
           class="bg-red-500 hover:bg-red-600 text-white px-4 py-2
                  rounded-full font-bold transition shadow-md
                  border-2 border-red-400 hover:border-red-500">
           🚪 Logout
        </a>

      <?php else: ?>
        <!-- Contact for guests too -->
        <a href="/voting_system/contact.php"
           class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2
                  rounded-full font-bold transition shadow-md">
           📬 Contact
        </a>

        <!-- Login -->
        <a href="/voting_system/login.php"
           class="bg-indigo-500 hover:bg-indigo-400 text-white
                  px-4 py-2 rounded-full font-bold transition">
           🔑 Login
        </a>

        <!-- Sign Up -->
        <a href="/voting_system/signup.php"
           class="bg-yellow-400 hover:bg-yellow-300 text-gray-900
                  px-4 py-2 rounded-full font-bold transition shadow-md">
           ✨ Sign Up
        </a>
      <?php endif; ?>
    </div>

    <!-- Mobile hamburger -->
    <button onclick="document.getElementById('mobile-menu')
                     .classList.toggle('hidden')"
            class="md:hidden focus:outline-none">
      <svg class="w-6 h-6" fill="none" stroke="currentColor"
           viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  <!-- Mobile dropdown -->
  <div id="mobile-menu"
       class="hidden md:hidden bg-indigo-800 px-4 pb-4 space-y-2 text-sm">

    <!-- Dark Mode Toggle -->
    <button onclick="toggleDarkMode()"
            class="block py-2 w-full text-left hover:text-indigo-200">
      🌙 Toggle Dark Mode
    </button>

    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/voting_system/index.php"
         class="block py-2 hover:text-indigo-200">
         🏠 Home
      </a>
      <a href="/voting_system/profile.php"
         class="block py-2 hover:text-indigo-200">
         👤 My Profile
      </a>
      <a href="/voting_system/contact.php"
         class="block py-2 hover:text-indigo-200">
         📬 Contact Us
      </a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="/voting_system/admin/dashboard.php"
           class="block py-2 text-yellow-300">
           ⚙️ Admin Dashboard
        </a>
      <?php endif; ?>
      <a href="/voting_system/logout.php"
         class="block py-2 px-3 mt-2 bg-red-500 hover:bg-red-600
                text-white rounded-xl font-bold text-center transition">
         🚪 Logout
      </a>
    <?php else: ?>
      <a href="/voting_system/contact.php"
         class="block py-2 hover:text-indigo-200">
         📬 Contact Us
      </a>
      <a href="/voting_system/login.php"
         class="block py-2 hover:text-indigo-200">
         🔑 Login
      </a>
      <a href="/voting_system/signup.php"
         class="block py-2 hover:text-indigo-200">
         ✨ Sign Up
      </a>
    <?php endif; ?>
  </div>
</nav>

<main class="flex-grow max-w-5xl mx-auto px-4 py-8 w-full">

<script>
function toggleDarkMode() {
    const html   = document.documentElement;
    const btn    = document.getElementById('theme-toggle');
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    if (btn) btn.textContent = isDark ? '☀️' : '🌙';
}

window.addEventListener('load', function() {
    const btn = document.getElementById('theme-toggle');
    if (btn) {
        btn.textContent =
            localStorage.getItem('theme') === 'dark' ? '☀️' : '🌙';
    }
});
</script>