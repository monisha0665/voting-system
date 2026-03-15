<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'VoteApp' ?></title>
  <meta name="theme-color" content="#4338ca">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

<nav class="bg-indigo-700 text-white shadow-md sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
    <a href="/voting_system/index.php" class="text-xl font-bold tracking-wide">🗳️ VoteApp</a>

    <!-- Desktop menu -->
    <div class="hidden md:flex items-center gap-4 text-sm">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/voting_system/index.php" class="hover:text-indigo-200">Home</a>
        <a href="/voting_system/profile.php" class="hover:text-indigo-200">My Profile</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="/voting_system/admin/dashboard.php"
             class="bg-yellow-400 text-gray-900 px-3 py-1 rounded-full font-semibold hover:bg-yellow-300">
             Admin
          </a>
        <?php endif; ?>
        <a href="/voting_system/logout.php"
           class="bg-white text-indigo-700 px-3 py-1 rounded-full font-semibold hover:bg-indigo-100">
           Logout
        </a>
      <?php else: ?>
        <a href="/voting_system/login.php" class="hover:text-indigo-200">Login</a>
        <a href="/voting_system/signup.php"
           class="bg-white text-indigo-700 px-3 py-1 rounded-full font-semibold hover:bg-indigo-100">
           Sign Up
        </a>
      <?php endif; ?>
    </div>

    <!-- Mobile hamburger button -->
    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
            class="md:hidden focus:outline-none">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  <!-- Mobile dropdown -->
  <div id="mobile-menu" class="hidden md:hidden bg-indigo-800 px-4 pb-4 space-y-2 text-sm">
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/voting_system/index.php"   class="block py-2 hover:text-indigo-200">🏠 Home</a>
      <a href="/voting_system/profile.php" class="block py-2 hover:text-indigo-200">👤 My Profile</a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="/voting_system/admin/dashboard.php"
           class="block py-2 text-yellow-300">⚙️ Admin Dashboard</a>
      <?php endif; ?>
      <a href="/voting_system/logout.php" class="block py-2 text-red-300">🚪 Logout</a>
    <?php else: ?>
      <a href="/voting_system/login.php"  class="block py-2 hover:text-indigo-200">🔑 Login</a>
      <a href="/voting_system/signup.php" class="block py-2 hover:text-indigo-200">✨ Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<main class="flex-grow max-w-5xl mx-auto px-4 py-8 w-full">"<!-- header -->" 
