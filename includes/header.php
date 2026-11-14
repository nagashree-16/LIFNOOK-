<?php
// Shared page header. Set $pageTitle and optionally $showNav before including.
$pageTitle = $pageTitle ?? 'LIFNOOK';
$showNav = $showNav ?? true;
$flash = get_flash();
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | LIFNOOK</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="bg-blob blob1"></div>
<div class="bg-blob blob2"></div>

<?php if ($showNav && is_logged_in()): ?>
<header class="navbar">
  <a href="dashboard.php" class="brand">🌿 LIFNOOK</a>
  <button class="nav-toggle" aria-label="Menu" onclick="document.querySelector('.nav-links').classList.toggle('open')">☰</button>
  <nav class="nav-links">
    <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Home</a>
    <a href="memories.php" class="<?= $current === 'memories.php' ? 'active' : '' ?>">Memories</a>
    <a href="vision.php" class="<?= $current === 'vision.php' ? 'active' : '' ?>">Vision Board</a>
    <a href="achievements.php" class="<?= $current === 'achievements.php' ? 'active' : '' ?>">Achievements</a>
    <a href="mood.php" class="<?= $current === 'mood.php' ? 'active' : '' ?>">Mood</a>
    <a href="logout.php" class="logout">Logout</a>
  </nav>
</header>
<?php endif; ?>

<main class="container">
<?php if ($flash): ?>
  <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
