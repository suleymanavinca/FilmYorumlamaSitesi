<?php
// config.php - Veritabanı Bağlantı Ayarları

// Session zaten başlatılmış mı kontrol et
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı Bilgileri
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'movie_platform');

// Site Ayarları
define('SITE_URL', 'http://localhost/movie-platform');
define('SITE_NAME', 'FilmKutusu');

// Güvenlik
define('ENCRYPTION_KEY', 'your-secret-key-here-change-this'); // Değiştirin!

// Veritabanı Bağlantısı
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Yardımcı Fonksiyonlar
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function showError($message) {
    return '<div class="alert alert-error">' . sanitize($message) . '</div>';
}

function showSuccess($message) {
    return '<div class="alert alert-success">' . sanitize($message) . '</div>';
}

// CSRF Token Oluşturma
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Doğrulama
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// JSON Response
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}
?>