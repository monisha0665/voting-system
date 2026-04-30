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

  <script>
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>

  <style>
    /* ── Google Font ───────────────────────── */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    * { font-family: 'Inter', sans-serif; }

    /* ── Smooth transitions ────────────────── */
    body, nav, main, .bg-white, .bg-gray-50,
    input, textarea, select, button, a {
      transition: background-color 0.3s ease,
                  color 0.3s ease,
                  border-color 0.3s ease;
    }

    /* ── Scrollbar ─────────────────────────── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 3px; }

    /* ══════════════════════════════════════════
       LIGHT THEME — already looks great
    ══════════════════════════════════════════ */
    body {
      background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
      color: #1e293b;
    }

    /* Cards */
    .card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.06),
                  0 4px 16px rgba(99,102,241,0.08);
      border: 1px solid rgba(99,102,241,0.1);
    }

    /* ══════════════════════════════════════════
       DARK THEME — Deep Navy + Cyan
    ══════════════════════════════════════════ */

    /* Background */
    .dark body {
      background: linear-gradient(135deg, #0a0f1e 0%, #0d1526 100%) !important;
      color: #e2e8f0 !important;
    }

    /* Navbar */
    .dark nav {
      background: linear-gradient(90deg, #0f172a 0%, #1e1b4b 100%) !important;
      border-bottom: 1px solid rgba(99,102,241,0.3) !important;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4) !important;
    }

    /* Main content area */
    .dark main {
      background: transparent !important;
    }

    /* All white cards → deep navy */
    .dark .bg-white {
      background: #0f1e3d !important;
      border: 1px solid rgba(99,102,241,0.2) !important;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3) !important;
    }

    /* Gray backgrounds */
    .dark .bg-gray-50 { background: #0a0f1e !important; }
    .dark .bg-gray-100 { background: #132040 !important; }
    .dark .bg-gray-200 { background: #1a2f55 !important; }

    /* Text colors — ALL visible */
    .dark .text-gray-900 { color: #f1f5f9 !important; }
    .dark .text-gray-800 { color: #e2e8f0 !important; }
    .dark .text-gray-700 { color: #cbd5e1 !important; }
    .dark .text-gray-600 { color: #94a3b8 !important; }
    .dark .text-gray-500 { color: #64748b !important; }
    .dark .text-gray-400 { color: #475569 !important; }

    /* Indigo text stays visible */
    .dark .text-indigo-700 { color: #818cf8 !important; }
    .dark .text-indigo-600 { color: #6366f1 !important; }

    /* Borders */
    .dark .border-gray-100 { border-color: rgba(99,102,241,0.15) !important; }
    .dark .border-gray-200 { border-color: rgba(99,102,241,0.2) !important; }
    .dark .border-gray-300 { border-color: rgba(99,102,241,0.25) !important; }
    .dark .divide-gray-100 > * { border-color: rgba(99,102,241,0.15) !important; }

    /* Inputs */
    .dark input,
    .dark textarea,
    .dark select {
      background: #132040 !important;
      color: #e2e8f0 !important;
      border-color: rgba(99,102,241,0.3) !important;
    }
    .dark input::placeholder,
    .dark textarea::placeholder {
      color: #475569 !important;
    }
    .dark input:focus,
    .dark textarea:focus,
    .dark select:focus {
      border-color: #6366f1 !important;
      box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
      outline: none !important;
    }

    /* Table headers */
    .dark .bg-gray-50.text-gray-500 {
      background: #0d1a35 !important;
      color: #94a3b8 !important;
    }
    .dark thead { background: #0d1a35 !important; }
    .dark thead th { color: #94a3b8 !important; }

    /* Table rows hover */
    .dark .hover\:bg-gray-50:hover { background: #132040 !important; }
    .dark .hover\:bg-gray-200:hover { background: #1a2f55 !important; }

    /* Indigo backgrounds */
    .dark .bg-indigo-50 { background: rgba(99,102,241,0.1) !important; }
    .dark .bg-indigo-100 { background: rgba(99,102,241,0.15) !important; }
    .dark .bg-indigo-600 { background: #4f46e5 !important; }

    /* Green backgrounds */
    .dark .bg-green-50 { background: rgba(16,185,129,0.1) !important; }
    .dark .bg-green-100 { background: rgba(16,185,129,0.15) !important; }
    .dark .text-green-700 { color: #34d399 !important; }
    .dark .text-green-600 { color: #10b981 !important; }

    /* Blue backgrounds */
    .dark .bg-blue-50 { background: rgba(59,130,246,0.1) !important; }
    .dark .bg-blue-100 { background: rgba(59,130,246,0.15) !important; }
    .dark .text-blue-700 { color: #60a5fa !important; }

    /* Yellow backgrounds */
    .dark .bg-yellow-50 { background: rgba(245,158,11,0.1) !important; }
    .dark .bg-yellow-100 { background: rgba(245,158,11,0.15) !important; }
    .dark .text-yellow-700 { color: #fbbf24 !important; }

    /* Purple backgrounds */
    .dark .bg-purple-50 { background: rgba(139,92,246,0.1) !important; }
    .dark .bg-purple-100 { background: rgba(139,92,246,0.15) !important; }
    .dark .text-purple-700 { color: #a78bfa !important; }

    /* Teal */
    .dark .bg-teal-50 { background: rgba(20,184,166,0.1) !important; }

    /* Pink */
    .dark .bg-pink-50 { background: rgba(236,72,153,0.08) !important; }

    /* Footer */
    .dark footer {
      background: #0a0f1e !important;
      border-color: rgba(99,102,241,0.2) !important;
      color: #475569 !important;
    }

    /* Shadow */
    .dark .shadow,
    .dark .shadow-sm,
    .dark .shadow-md,
    .dark .shadow-lg {
      box-shadow: 0 4px 20px rgba(0,0,0,0.4) !important;
    }

    /* Gradient hero in dark mode */
    .dark .bg-gradient-to-r {
      opacity: 0.95;
    }

    /* Stat cards in dark */
    .dark .border-l-4 {
      background: #0f1e3d !important;
    }

    /* Rounded cards in dark */
    .dark .rounded-2xl {
      background: #0f1e3d;
    }

    /* Glassmorphism effect for cards in dark */
    .dark .bg-white.rounded-2xl {
      background: rgba(15, 30, 61, 0.95) !important;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(99,102,241,0.2) !important;
    }

    /* Cyan accent glow for active elements */
    .dark .animate-pulse {
      box-shadow: 0 0 8px rgba(6,182,212,0.6);
    }

    /* Navigation active state */
    .dark nav a:hover {
      text-shadow: 0 0 8px rgba(99,102,241,0.5);
    }

    /* ── Modern button styles ──────────────── */
    .btn-primary {
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
      color: white;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      box-shadow: 0 4px 12px rgba(99,102,241,0.3);
    }
    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(99,102,241,0.4);
    }

    /* ── Election cards modern style ──────── */
    .election-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .election-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(99,102,241,0.15) !important;
    }

    /* ── Navbar glow effect ────────────────── */
    nav {
      box-shadow: 0 2px 20px rgba(79,70,229,0.2);
    }

    /* ── Form inputs modern ────────────────── */
    input, textarea, select {
      border-radius: 10px !important;
      transition: all 0.2s ease !important;
    }
    input:focus, textarea:focus, select:focus {
      transform: none;
      box-shadow: 0 0 0 3px rgba(99,102,241,0.15) !important;
    }

    /* Print styles */
    @media print {
      nav, footer, button, .no-print { display: none !important; }
      body { background: white !important; color: black !important; }
    }
  </style>
</head>

<body class="min-h-screen flex flex-col">

<!-- ── Navbar ───────────────────────────────── -->
<nav class="bg-indigo-700 text-white sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

    <!-- Logo -->
    <a href="/voting_system/index.php"
       class="flex items-center gap-2 text-xl font-bold tracking-wide">
      <span class="text-2xl">🗳️</span>
      <span class="bg-gradient-to-r from-white to-indigo-200
                   bg-clip-text text-transparent font-extrabold">
        VoteApp
      </span>
    </a>

    <!-- Desktop Menu -->
    <div class="hidden md:flex items-center gap-2 text-sm">

      <!-- Dark Mode Toggle -->
      <button onclick="toggleDarkMode()"
              id="theme-toggle"
              class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20
                     flex items-center justify-center text-lg transition
                     border border-white/20"
              title="Toggle dark mode">
        🌙
      </button>

      <?php if (isset($_SESSION['user_id'])): ?>

        <a href="/voting_system/index.php"
           class="flex items-center gap-1 bg-purple-500 hover:bg-purple-400
                  text-white px-4 py-2 rounded-xl font-semibold
                  transition shadow-md hover:shadow-lg
                  hover:-translate-y-0.5 transform">
          🏠 <span>Home</span>
        </a>

        <a href="/voting_system/profile.php"
           class="flex items-center gap-1 bg-emerald-500 hover:bg-emerald-400
                  text-white px-4 py-2 rounded-xl font-semibold
                  transition shadow-md hover:shadow-lg
                  hover:-translate-y-0.5 transform">
          👤 <span>Profile</span>
        </a>

        <a href="/voting_system/contact.php"
           class="flex items-center gap-1 bg-sky-500 hover:bg-sky-400
                  text-white px-4 py-2 rounded-xl font-semibold
                  transition shadow-md hover:shadow-lg
                  hover:-translate-y-0.5 transform">
          📬 <span>Contact</span>
        </a>

        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="/voting_system/admin/dashboard.php"
             class="flex items-center gap-1 bg-amber-400 hover:bg-amber-300
                    text-gray-900 px-4 py-2 rounded-xl font-bold
                    transition shadow-md hover:shadow-lg
                    hover:-translate-y-0.5 transform">
            ⚙️ <span>Admin</span>
          </a>
        <?php endif; ?>

        <a href="/voting_system/logout.php"
           class="flex items-center gap-1 bg-slate-600 hover:bg-slate-500
                  text-white px-4 py-2 rounded-xl font-semibold
                  transition shadow-md hover:shadow-lg
                  hover:-translate-y-0.5 transform border border-slate-500">
          🚪 <span>Logout</span>
        </a>

      <?php else: ?>

        <a href="/voting_system/contact.php"
           class="flex items-center gap-1 bg-sky-500 hover:bg-sky-400
                  text-white px-4 py-2 rounded-xl font-semibold transition
                  hover:-translate-y-0.5 transform">
          📬 <span>Contact</span>
        </a>

        <a href="/voting_system/login.php"
           class="flex items-center gap-1 bg-white/10 hover:bg-white/20
                  text-white px-4 py-2 rounded-xl font-semibold transition
                  border border-white/30 hover:-translate-y-0.5 transform">
          🔑 <span>Login</span>
        </a>

        <a href="/voting_system/signup.php"
           class="flex items-center gap-1 bg-amber-400 hover:bg-amber-300
                  text-gray-900 px-4 py-2 rounded-xl font-bold transition
                  shadow-md hover:-translate-y-0.5 transform">
          ✨ <span>Sign Up</span>
        </a>

      <?php endif; ?>
    </div>

    <!-- Mobile Hamburger -->
    <button onclick="toggleMobileMenu()"
            id="hamburger"
            class="md:hidden w-9 h-9 rounded-xl bg-white/10
                   hover:bg-white/20 flex items-center justify-center
                   transition border border-white/20">
      <svg class="w-5 h-5" fill="none" stroke="currentColor"
           viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu"
       class="hidden md:hidden border-t border-white/10
              bg-indigo-800/95 backdrop-blur-sm px-4 py-3 space-y-1">

    <button onclick="toggleDarkMode()"
            class="w-full text-left px-4 py-2 rounded-xl
                   hover:bg-white/10 transition text-sm font-medium">
      🌙 Toggle Dark Mode
    </button>

    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/voting_system/index.php"
         class="flex items-center gap-2 px-4 py-2 rounded-xl
                hover:bg-white/10 transition text-sm font-medium">
        🏠 Home
      </a>
      <a href="/voting_system/profile.php"
         class="flex items-center gap-2 px-4 py-2 rounded-xl
                hover:bg-white/10 transition text-sm font-medium">
        👤 My Profile
      </a>
      <a href="/voting_system/contact.php"
         class="flex items-center gap-2 px-4 py-2 rounded-xl
                hover:bg-white/10 transition text-sm font-medium">
        📬 Contact Us
      </a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="/voting_system/admin/dashboard.php"
           class="flex items-center gap-2 px-4 py-2 rounded-xl
                  bg-amber-400/20 text-amber-300 transition text-sm font-medium">
          ⚙️ Admin Dashboard
        </a>
      <?php endif; ?>
      <a href="/voting_system/logout.php"
         class="flex items-center gap-2 px-4 py-2 rounded-xl
                bg-slate-600/50 text-white transition text-sm font-medium mt-2">
        🚪 Logout
      </a>
    <?php else: ?>
      <a href="/voting_system/contact.php"
         class="flex items-center gap-2 px-4 py-2 rounded-xl
                hover:bg-white/10 transition text-sm font-medium">
        📬 Contact Us
      </a>
      <a href="/voting_system/login.php"
         class="flex items-center gap-2 px-4 py-2 rounded-xl
                hover:bg-white/10 transition text-sm font-medium">
        🔑 Login
      </a>
      <a href="/voting_system/signup.php"
         class="flex items-center gap-2 px-4 py-2 rounded-xl
                bg-amber-400/20 text-amber-300 transition text-sm font-medium">
        ✨ Sign Up
      </a>
    <?php endif; ?>
  </div>
</nav>

<!-- ── Main Content ──────────────────────────── -->
<main class="flex-grow max-w-6xl mx-auto px-4 py-8 w-full">

<script>
// Dark mode toggle
function toggleDarkMode() {
    const html   = document.documentElement;
    const btn    = document.getElementById('theme-toggle');
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    if (btn) btn.textContent = isDark ? '☀️' : '🌙';
}

// Mobile menu toggle
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

// Set correct icon on load
window.addEventListener('load', function() {
    const btn = document.getElementById('theme-toggle');
    if (btn) {
        btn.textContent =
            localStorage.getItem('theme') === 'dark' ? '☀️' : '🌙';
    }
});

// Close mobile menu on outside click
document.addEventListener('click', function(e) {
    const menu = document.getElementById('mobile-menu');
    const hamburger = document.getElementById('hamburger');
    if (menu && hamburger &&
        !menu.contains(e.target) &&
        !hamburger.contains(e.target)) {
        menu.classList.add('hidden');
    }
});
</script>