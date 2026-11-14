<?php
// LIFNOOK - configuration, database connection and helper functions
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'lifnook');
define('DB_USER', 'root');   // XAMPP default
define('DB_PASS', '');       // XAMPP default is empty

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Start MySQL in XAMPP and import database.sql, then check includes/config.php.');
}

// Escape output to prevent XSS
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

// CSRF protection for all forms
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check() {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(403);
        die('Invalid request. Please go back and try again.');
    }
}

// One-time messages shown after redirects
function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Emoji for each mood
function mood_emoji($mood) {
    $map = [
        'happy' => '😊', 'excited' => '🤩', 'calm' => '😌',
        'sad' => '😢', 'stressed' => '😣', 'tired' => '😴',
    ];
    return $map[$mood] ?? '🙂';
}
