<?php
// 404 - friendly error page
$page_title = 'Page Not Found';
require 'includes/header.php';
?>
<div class="text-center mt-20">
  <p class="text-8xl mb-4">🗳️</p>
  <h1 class="text-5xl font-bold text-indigo-700 mb-4">404</h1>
  <p class="text-gray-500 text-lg mb-6">Oops! This page does not exist.</p>
  <a href="/voting_system/index.php"
     class="bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 font-semibold">
     Go Back Home
  </a>
</div>
<?php require 'includes/footer.php'; ?>