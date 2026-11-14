<?php
require 'includes/config.php';
require_login();

$uid = $_SESSION['user_id'];
$moods = [
    'happy' => 'Happy', 'excited' => 'Excited', 'calm' => 'Calm',
    'sad' => 'Sad', 'stressed' => 'Stressed', 'tired' => 'Tired',
];

// Save mood and pick a quote
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $mood = $_POST['mood'] ?? '';
    if (isset($moods[$mood])) {
        $pdo->prepare('INSERT INTO moods (user_id, mood) VALUES (?, ?)')->execute([$uid, $mood]);

        $q = $pdo->prepare('SELECT quote_text FROM quotes WHERE mood = ? ORDER BY RAND() LIMIT 1');
        $q->execute([$mood]);
        $quote = $q->fetchColumn();

        $_SESSION['mood'] = $mood;
        $_SESSION['quote'] = $quote ?: 'You are doing better than you think. Keep going.';
        redirect('mood.php');
    }
}

$mood = $_SESSION['mood'] ?? null;
$quote = $_SESSION['quote'] ?? null;
// "Change mood" link clears the current one
if (isset($_GET['change'])) {
    unset($_SESSION['mood'], $_SESSION['quote']);
    redirect('mood.php');
}

$pageTitle = 'How are you feeling?';
include 'includes/header.php';
?>

<?php if (!$mood): ?>
<section class="mood-section">
  <h1 class="hero-title">Hi <?= e($_SESSION['user_name']) ?> 👋</h1>
  <p class="hero-sub">How are you feeling today?</p>

  <form method="post" class="mood-grid">
    <?= csrf_field() ?>
    <?php foreach ($moods as $key => $label): ?>
      <button type="submit" name="mood" value="<?= $key ?>" class="mood-card mood-<?= $key ?>">
        <span class="mood-emoji"><?= mood_emoji($key) ?></span>
        <span class="mood-label"><?= $label ?></span>
      </button>
    <?php endforeach; ?>
  </form>
</section>

<?php else: ?>
<section class="quote-section">
  <div class="quote-card">
    <div class="quote-emoji"><?= mood_emoji($mood) ?></div>
    <p class="quote-intro">Feeling <strong><?= e($moods[$mood]) ?></strong> today? This one's for you:</p>
    <blockquote class="quote-text">&ldquo;<?= e($quote) ?>&rdquo;</blockquote>
    <div class="quote-actions">
      <a href="dashboard.php" class="btn btn-primary">Continue to my space &rarr;</a>
      <a href="mood.php?change=1" class="btn btn-ghost">Change mood</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
