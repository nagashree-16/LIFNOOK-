<?php
require 'includes/config.php';
require_login();

$uid = $_SESSION['user_id'];
$categories = ['Personal', 'Career', 'Education', 'Health', 'Travel', 'Finance', 'Relationships', 'Hobbies'];

// Mark a goal as achieved and add it to the achievements list
function achieve_goal($pdo, $uid, $id) {
    $s = $pdo->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ? AND status = 'active'");
    $s->execute([$id, $uid]);
    $g = $s->fetch();
    if (!$g) return false;

    $pdo->prepare("UPDATE goals SET status = 'achieved', progress = 100 WHERE id = ? AND user_id = ?")->execute([$id, $uid]);
    $pdo->prepare('INSERT INTO achievements (user_id, title, description, achieved_date) VALUES (?, ?, ?, CURDATE())')
        ->execute([$uid, 'Goal achieved: ' . $g['title'], $g['description'], ]);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'save') {
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $cat = in_array($_POST['category'] ?? '', $categories, true) ? $_POST['category'] : 'Personal';
        $target = $_POST['target_date'] ?? '';
        $targetVal = null;
        if ($target !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $target);
            if ($d && $d->format('Y-m-d') === $target) $targetVal = $target;
        }

        if ($title === '') {
            flash('Please give your goal a title.', 'error');
            redirect($id ? "vision.php?edit=$id" : 'vision.php');
        }

        if ($id) {
            $pdo->prepare('UPDATE goals SET title = ?, description = ?, category = ?, target_date = ? WHERE id = ? AND user_id = ?')
                ->execute([$title, $desc, $cat, $targetVal, $id, $uid]);
            flash('Goal updated ✏️');
        } else {
            $pdo->prepare('INSERT INTO goals (user_id, title, description, category, target_date) VALUES (?, ?, ?, ?, ?)')
                ->execute([$uid, $title, $desc, $cat, $targetVal]);
            flash('Goal added to your vision board 🌠');
        }
        redirect('vision.php');
    }

    if ($action === 'progress') {
        $p = max(0, min(100, (int)($_POST['progress'] ?? 0)));
        if ($p >= 100) {
            achieve_goal($pdo, $uid, $id);
            flash('Amazing! Goal completed and added to your achievements 🎉');
        } else {
            $pdo->prepare("UPDATE goals SET progress = ? WHERE id = ? AND user_id = ? AND status = 'active'")->execute([$p, $id, $uid]);
            flash('Progress updated 📈');
        }
        redirect('vision.php');
    }

    if ($action === 'achieve') {
        if (achieve_goal($pdo, $uid, $id)) flash('Congratulations! Added to your achievements 🏆');
        redirect('vision.php');
    }

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM goals WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
        flash('Goal removed.');
        redirect('vision.php');
    }
}

$editing = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM goals WHERE id = ? AND user_id = ?');
    $s->execute([(int)$_GET['edit'], $uid]);
    $editing = $s->fetch() ?: null;
}

$s = $pdo->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY status ASC, created_at DESC");
$s->execute([$uid]);
$goals = $s->fetchAll();

$pageTitle = 'Vision Board';
include 'includes/header.php';
?>

<div class="page-head">
  <h1>🌠 My Vision Board</h1>
  <p class="muted">Dream it. Plan it. Track it. Every small step counts.</p>
</div>

<section class="panel" id="goalForm">
  <h2><?= $editing ? '✏️ Edit goal' : '➕ Add a goal or dream' ?></h2>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <div class="form-row">
      <label class="grow">Goal
        <input type="text" name="title" maxlength="120" placeholder="e.g. Learn to play guitar" value="<?= e($editing['title'] ?? '') ?>" required>
      </label>
      <label>Category
        <select name="category">
          <?php foreach ($categories as $c): ?>
            <option <?= (($editing['category'] ?? 'Personal') === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Target date
        <input type="date" name="target_date" value="<?= e($editing['target_date'] ?? '') ?>">
      </label>
    </div>
    <label>Why does it matter to you? (optional)
      <textarea name="description" rows="3" placeholder="Describe your dream..."><?= e($editing['description'] ?? '') ?></textarea>
    </label>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Update goal' : 'Add to vision board' ?></button>
      <?php if ($editing): ?><a href="vision.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </div>
  </form>
</section>

<?php if (!$goals): ?>
  <div class="empty">
    <div class="empty-icon">🌱</div>
    <p>Your vision board is empty. Add your first dream above!</p>
  </div>
<?php else: ?>
  <section class="vision-grid">
    <?php foreach ($goals as $g): $done = $g['status'] === 'achieved'; ?>
      <article class="goal-card <?= $done ? 'done' : '' ?>">
        <div class="goal-top">
          <span class="tag"><?= e($g['category']) ?></span>
          <?php if ($done): ?><span class="badge-done">✔ Achieved</span><?php endif; ?>
        </div>
        <h3><?= e($g['title']) ?></h3>
        <?php if ($g['description']): ?><p class="goal-desc"><?= nl2br(e($g['description'])) ?></p><?php endif; ?>
        <?php if ($g['target_date']): ?><p class="goal-date">🎯 Target: <?= e(date('d M Y', strtotime($g['target_date']))) ?></p><?php endif; ?>

        <div class="progress"><div class="progress-bar" style="width: <?= (int)$g['progress'] ?>%"></div></div>
        <div class="progress-label"><?= (int)$g['progress'] ?>% complete</div>

        <?php if (!$done): ?>
          <form method="post" class="progress-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="progress">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <input type="range" name="progress" min="0" max="100" step="5" value="<?= (int)$g['progress'] ?>" oninput="this.nextElementSibling.textContent = this.value + '%'">
            <output><?= (int)$g['progress'] ?>%</output>
            <button type="submit" class="btn btn-small btn-ghost">Save</button>
          </form>
        <?php endif; ?>

        <div class="card-actions">
          <?php if (!$done): ?>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="achieve">
              <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
              <button type="submit" class="btn btn-small btn-primary">Mark achieved</button>
            </form>
            <a href="vision.php?edit=<?= (int)$g['id'] ?>#goalForm" class="btn btn-small btn-ghost">Edit</a>
          <?php endif; ?>
          <form method="post" onsubmit="return confirm('Remove this goal?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <button type="submit" class="btn btn-small btn-danger">Delete</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
