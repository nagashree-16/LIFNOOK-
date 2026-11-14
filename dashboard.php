<?php
require 'includes/config.php';
require_login();

$uid = $_SESSION['user_id'];

// If the user has not picked a mood this session, ask first
if (empty($_SESSION['mood'])) redirect('mood.php');

function count_rows($pdo, $sql, $uid) {
    $s = $pdo->prepare($sql);
    $s->execute([$uid]);
    return (int)$s->fetchColumn();
}

$memCount = count_rows($pdo, 'SELECT COUNT(*) FROM memories WHERE user_id = ?', $uid);
$goalActive = count_rows($pdo, "SELECT COUNT(*) FROM goals WHERE user_id = ? AND status = 'active'", $uid);
$achCount = count_rows($pdo, 'SELECT COUNT(*) FROM achievements WHERE user_id = ?', $uid);

$recent = $pdo->prepare('SELECT * FROM achievements WHERE user_id = ? ORDER BY achieved_date DESC, id DESC LIMIT 3');
$recent->execute([$uid]);
$recentAch = $recent->fetchAll();

$hour = (int)date('G');
$greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

$pageTitle = 'Home';
include 'includes/header.php';
?>

<section class="hero">
  <h1 class="hero-title"><?= $greet ?>, <?= e($_SESSION['user_name']) ?> <?= mood_emoji($_SESSION['mood']) ?></h1>
  <p class="hero-sub">What would you like to do today?</p>
</section>

<section class="stats">
  <div class="stat"><span class="stat-num"><?= $memCount ?></span><span class="stat-label">Memories</span></div>
  <div class="stat"><span class="stat-num"><?= $goalActive ?></span><span class="stat-label">Active goals</span></div>
  <div class="stat"><span class="stat-num"><?= $achCount ?></span><span class="stat-label">Achievements</span></div>
</section>

<section class="choice-grid">
  <a href="memories.php" class="choice-card choice-memories">
    <span class="choice-icon">📖</span>
    <h2>My Memories</h2>
    <p>Write down moments you never want to forget. Edit them whenever you like.</p>
    <span class="choice-cta">Open memories &rarr;</span>
  </a>
  <a href="vision.php" class="choice-card choice-vision">
    <span class="choice-icon">🌠</span>
    <h2>Vision Board</h2>
    <p>Add your goals and dreams, and watch your progress grow.</p>
    <span class="choice-cta">Open vision board &rarr;</span>
  </a>
</section>

<section class="panel">
  <div class="panel-head">
    <h2>🏆 Your achievements</h2>
    <a href="achievements.php" class="link">See all</a>
  </div>
  <?php if (!$recentAch): ?>
    <p class="muted">Your wins will show up here. Complete a goal or add an achievement to get started!</p>
  <?php else: ?>
    <div class="ach-strip">
      <?php foreach ($recentAch as $a): ?>
        <div class="ach-mini">
          <span class="ach-medal">🏅</span>
          <div>
            <strong><?= e($a['title']) ?></strong>
            <small><?= e(date('d M Y', strtotime($a['achieved_date']))) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="boost">Look how far you have come. Keep going! 💪</p>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
