<?php
require 'includes/config.php';
require_login();

$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $date = $_POST['achieved_date'] ?? '';
        $d = DateTime::createFromFormat('Y-m-d', $date);

        if ($title === '' || !$d || $d->format('Y-m-d') !== $date) {
            flash('Please enter a title and a valid date.', 'error');
            redirect($id ? "achievements.php?edit=$id" : 'achievements.php');
        }

        if ($id) {
            $pdo->prepare('UPDATE achievements SET title = ?, description = ?, achieved_date = ? WHERE id = ? AND user_id = ?')
                ->execute([$title, $desc, $date, $id, $uid]);
            flash('Achievement updated ✏️');
        } else {
            $pdo->prepare('INSERT INTO achievements (user_id, title, description, achieved_date) VALUES (?, ?, ?, ?)')
                ->execute([$uid, $title, $desc, $date]);
            flash('Congratulations! Achievement added 🎉');
            $_SESSION['celebrate'] = true;
        }
        redirect('achievements.php');
    }

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM achievements WHERE id = ? AND user_id = ?')->execute([(int)($_POST['id'] ?? 0), $uid]);
        flash('Achievement removed.');
        redirect('achievements.php');
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM achievements WHERE id = ? AND user_id = ?');
    $s->execute([(int)$_GET['edit'], $uid]);
    $editing = $s->fetch() ?: null;
}

$s = $pdo->prepare('SELECT * FROM achievements WHERE user_id = ? ORDER BY achieved_date DESC, id DESC');
$s->execute([$uid]);
$list = $s->fetchAll();

$celebrate = !empty($_SESSION['celebrate']);
unset($_SESSION['celebrate']);

$pageTitle = 'Achievements';
include 'includes/header.php';
?>

<div class="page-head">
  <h1>🏆 My Achievements</h1>
  <p class="muted">Every win, big or small, deserves to be celebrated.</p>
</div>

<?php if ($list): ?>
  <div class="boost-banner">
    You have <strong><?= count($list) ?></strong> achievement<?= count($list) > 1 ? 's' : '' ?>. You are doing great, keep shining! ✨
  </div>
<?php endif; ?>

<section class="panel" id="achForm">
  <h2><?= $editing ? '✏️ Edit achievement' : '➕ Add an achievement' ?></h2>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <div class="form-row">
      <label class="grow">What did you achieve?
        <input type="text" name="title" maxlength="120" placeholder="e.g. Scored 90% in exams" value="<?= e($editing['title'] ?? '') ?>" required>
      </label>
      <label>Date
        <input type="date" name="achieved_date" max="<?= date('Y-m-d') ?>" value="<?= e($editing['achieved_date'] ?? date('Y-m-d')) ?>" required>
      </label>
    </div>
    <label>Notes (optional)
      <textarea name="description" rows="3" placeholder="How did it feel? What did you learn?"><?= e($editing['description'] ?? '') ?></textarea>
    </label>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Update' : 'Add achievement' ?></button>
      <?php if ($editing): ?><a href="achievements.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </div>
  </form>
</section>

<?php if (!$list): ?>
  <div class="empty">
    <div class="empty-icon">🏅</div>
    <p>No achievements yet. Complete a goal on your vision board or add one above!</p>
  </div>
<?php else: ?>
  <section class="ach-grid">
    <?php foreach ($list as $a): ?>
      <article class="ach-card">
        <div class="ach-medal-big">🏅</div>
        <h3><?= e($a['title']) ?></h3>
        <div class="memory-date"><?= e(date('d M Y', strtotime($a['achieved_date']))) ?></div>
        <?php if ($a['description']): ?><p><?= nl2br(e($a['description'])) ?></p><?php endif; ?>
        <div class="card-actions">
          <a href="achievements.php?edit=<?= (int)$a['id'] ?>#achForm" class="btn btn-small btn-ghost">Edit</a>
          <form method="post" onsubmit="return confirm('Remove this achievement?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <button type="submit" class="btn btn-small btn-danger">Delete</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<?php if ($celebrate): ?><script>window.LIFNOOK_CELEBRATE = true;</script><?php endif; ?>
<?php include 'includes/footer.php'; ?>
