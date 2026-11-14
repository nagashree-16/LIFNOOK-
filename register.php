<?php
require 'includes/config.php';

if (is_logged_in()) redirect('dashboard.php');

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all the fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'This email is already registered. Try logging in.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            flash('Welcome to LIFNOOK, ' . $name . '! 🎉');
            redirect('mood.php');
        }
    }
}

$pageTitle = 'Create account';
$showNav = false;
include 'includes/header.php';
?>
<section class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo">✨</div>
    <h1 class="brand-title">Create your space</h1>
    <p class="tagline">It takes less than a minute.</p>

    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" id="registerForm">
      <?= csrf_field() ?>
      <label>Your name
        <input type="text" name="name" value="<?= e($name) ?>" maxlength="80" placeholder="e.g. Aarav" required>
      </label>
      <label>Email
        <input type="email" name="email" value="<?= e($email) ?>" maxlength="120" placeholder="you@example.com" required>
      </label>
      <label>Password
        <input type="password" name="password" minlength="6" placeholder="At least 6 characters" required>
      </label>
      <label>Confirm password
        <input type="password" name="confirm" minlength="6" placeholder="Repeat password" required>
      </label>
      <button type="submit" class="btn btn-primary btn-block">Sign up</button>
    </form>
    <p class="auth-switch">Already have a space? <a href="login.php">Login</a></p>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
