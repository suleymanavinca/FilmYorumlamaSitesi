<?php
// auth.php - Kullanıcı Kimlik Doğrulama İşlemleri

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CSRF Token Kontrolü
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        jsonResponse(false, 'Geçersiz istek!');
    }
    
    switch ($action) {
        case 'register':
            handleRegister();
            break;
        case 'login':
            handleLogin();
            break;
        case 'logout':
            handleLogout();
            break;
        case 'forgot_password':
            handleForgotPassword();
            break;
        default:
            jsonResponse(false, 'Geçersiz işlem!');
    }
}

// Kayıt İşlemi
function handleRegister() {
    global $pdo;
    
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    
    // Validasyon
    $errors = [];
    
    if (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'Kullanıcı adı 3-20 karakter arasında olmalıdır.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Şifre en az 6 karakter olmalıdır.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Şifreler eşleşmiyor.';
    }
    
    if (!empty($errors)) {
        jsonResponse(false, implode(' ', $errors));
    }
    
    // Kullanıcı adı kontrolü
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        jsonResponse(false, 'Bu kullanıcı adı veya e-posta zaten kullanılıyor.');
    }
    
    // Şifreyi hashle
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Kullanıcıyı kaydet
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, full_name, registration_date) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$username, $email, $password_hash, $full_name]);
        
        $user_id = $pdo->lastInsertId();
        
        // Otomatik giriş yap
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['is_admin'] = 0;
        
        // Aktivite kaydı
        logActivity($user_id, 'login', 'Kullanıcı kayıt oldu ve giriş yaptı');
        
        jsonResponse(true, 'Kayıt başarılı! Yönlendiriliyorsunuz...', ['redirect' => 'index.php']);
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.');
    }
}

// Giriş İşlemi
function handleLogin() {
    global $pdo;
    
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        jsonResponse(false, 'Kullanıcı adı ve şifre gereklidir.');
    }
    
    // Kullanıcıyı bul
    $stmt = $pdo->prepare("
        SELECT user_id, username, email, password_hash, is_admin, is_active 
        FROM users 
        WHERE username = ? OR email = ?
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(false, 'Kullanıcı adı veya şifre hatalı.');
    }
    
    if (!$user['is_active']) {
        jsonResponse(false, 'Hesabınız devre dışı bırakılmış.');
    }
    
    // Şifre kontrolü
    if (!password_verify($password, $user['password_hash'])) {
        jsonResponse(false, 'Kullanıcı adı veya şifre hatalı.');
    }
    
    // Session oluştur
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];
    
    // Son giriş zamanını güncelle
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    // Remember me cookie (isteğe bağlı)
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 gün
    }
    
    // Aktivite kaydı
    logActivity($user['user_id'], 'login', 'Kullanıcı giriş yaptı');
    
    jsonResponse(true, 'Giriş başarılı! Yönlendiriliyorsunuz...', ['redirect' => 'index.php']);
}

// Çıkış İşlemi
function handleLogout() {
    session_destroy();
    setcookie('remember_token', '', time() - 3600, "/");
    jsonResponse(true, 'Çıkış yapıldı.', ['redirect' => 'login.php']);
}

// Şifremi Unuttum
function handleForgotPassword() {
    global $pdo;
    
    $email = sanitize($_POST['email'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Geçerli bir e-posta adresi giriniz.');
    }
    
    // Kullanıcıyı bul
    $stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Güvenlik nedeniyle her zaman başarılı mesajı göster
        jsonResponse(true, 'Eğer bu e-posta kayıtlıysa, şifre sıfırlama bağlantısı gönderildi.');
    }
    
    // Reset token oluştur
    $reset_token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmt = $pdo->prepare("
        UPDATE users 
        SET reset_token = ?, reset_token_expiry = ? 
        WHERE user_id = ?
    ");
    $stmt->execute([$reset_token, $expiry, $user['user_id']]);
    
    // E-posta gönder (burada mail() fonksiyonu kullanılabilir)
    $reset_link = SITE_URL . "/reset_password.php?token=" . $reset_token;
    
    // Mail gönderme kodu buraya eklenebilir
    // mail($email, "Şifre Sıfırlama", "Şifrenizi sıfırlamak için: " . $reset_link);
    
    jsonResponse(true, 'Eğer bu e-posta kayıtlıysa, şifre sıfırlama bağlantısı gönderildi.');
}

// Aktivite Logla
function logActivity($user_id, $type, $description) {
    global $pdo;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, activity_type, activity_description, ip_address) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $type, $description, $ip]);
}
?>