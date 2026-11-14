<?php
require 'includes/config.php';
require_login();

$uid = $_SESSION['user_id'];

// ---------- Handle actions: add / update / delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $date = $_POST['memory_date'] ?? '';

        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($title === '' || $content === '' || !$d || $d->format('Y-m-d') !== $date) {
            flash('Please fill in the title, date and your memory.', 'error');
            redirect($id ? "memories.php?edit=$id" : 'memories.php');
        }

        if ($id) {
            $stmt = $pdo->prepare('UPDATE memories SET title = ?, content = ?, memory_date = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$title, $content, $date, $id, $uid]);
            flash('Memory updated ✏️');
        } else {
            $stmt = $pdo->prepare('INSERT INTO memories (user_id, title, content, memory_date) VALUES (?, ?, ?, ?)');
            $stmt->execute([$uid, $title, $content, $date]);
            flash('Memory saved 💚');
        }
        redirect('memories.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM memories WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
        flash('Memory deleted.');
        redirect('memories.php');
    }
}

// ---------- Load data ----------
$editing = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM memories WHERE id = ? AND user_id = ?');
    $s->execute([(int)$_GET['edit'], $uid]);
    $editing = $s->fetch() ?: null;
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $s = $pdo->prepare('SELECT * FROM memories WHERE user_id = ? AND (title LIKE ? OR content LIKE ?) ORDER BY memory_date DESC, id DESC');
    $like = '%' . $search . '%';
    $s->execute([$uid, $like, $like]);
} else {
    $s = $pdo->prepare('SELECT * FROM memories WHERE user_id = ? ORDER BY memory_date DESC, id DESC');
    $s->execute([$uid]);
}
$memories = $s->fetchAll();

$pageTitle = 'Memories';
include 'includes/header.php';
?>

<div class="page-head">
  <h1>📖 My Memories</h1>
  <p class="muted">Little moments, big feelings. Keep them safe here.</p>
</div>

<section class="panel" id="memoryForm">
  <h2><?= $editing ? '✏️ Edit memory' : '➕ Add a new memory' ?></h2>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <div class="form-row">
      <label class="grow">Title
        <input type="text" name="title" maxlength="120" placeholder="e.g. Trip to Goa with friends" value="<?= e($editing['title'] ?? '') ?>" required>
      </label>
      <label>Date
        <input type="date" name="memory_date" max="<?= date('Y-m-d') ?>" value="<?= e($editing['memory_date'] ?? date('Y-m-d')) ?>" required>
      </label>
    </div>
    <label>What happened? How did it feel?
      <textarea name="content" rows="4" placeholder="Write your memory here..." required><?= e($editing['content'] ?? '') ?></textarea>
    </label>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary"><?= $editing ? 'Update memory' : 'Save memory' ?></button>
      <?php if ($editing): ?><a href="memories.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </div>
  </form>
</section>

<form method="get" class="search-bar">
  <input type="search" name="q" value="<?= e($search) ?>" placeholder="🔍 Search your memories...">
  <button class="btn btn-ghost" type="submit">Search</button>
</form>

<?php if (!$memories): ?>
  <div class="empty">
    <div class="empty-icon">🫧</div>
    <p><?= $search !== '' ? 'No memories match your search.' : 'No memories yet. Add your first one above!' ?></p>
  </div>
<?php else: ?>
  <section class="timeline">
    <?php foreach ($memories as $m): ?>
      <article class="memory-card">
        <div class="memory-date"><?= e(date('d M Y', strtotime($m['memory_date']))) ?></div>
        <h3><?= e($m['title']) ?></h3>
        <p><?= nl2br(e($m['content'])) ?></p>
        <div class="card-actions">
          <a href="memories.php?edit=<?= (int)$m['id'] ?>#memoryForm" class="btn btn-small btn-ghost">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this memory permanently?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <button type="submit" class="btn btn-small btn-danger">Delete</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
