<?php
require 'includes/config.php';

if (is_logged_in()) redirect('dashboard.php');

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        unset($_SESSION['mood'], $_SESSION['quote']);
        redirect('mood.php');
    }
    $error = 'Incorrect email or password. Please try again.';
}

$pageTitle = 'Login';
$showNav = false;
include 'includes/header.php';
?>
<section class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">🌿</div>
    <h1 class="brand-title">LIFNOOK</h1>
    <p class="tagline">Your personal space for memories, goals and growth.</p>

    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" autocomplete="on">
      <?= csrf_field() ?>
      <label>Email
        <input type="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" required>
      </label>
      <label>Password
        <input type="password" name="password" placeholder="Your password" required>
      </label>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <p class="auth-switch">New here? <a href="register.php">Create your space</a></p>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
