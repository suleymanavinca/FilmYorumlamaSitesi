<?php
// settings.php - Kullanıcı Ayarları
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Ayarlar';
$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("
    SELECT user_id, username, email, full_name, bio, profile_image
    FROM users 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Form gönderimi kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Geçersiz istek!';
    } else {
        switch ($_POST['action']) {
            case 'update_profile':
                $full_name = sanitize($_POST['full_name'] ?? '');
                $bio = sanitize($_POST['bio'] ?? '');
                
                try {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, bio = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $bio, $user_id]);
                    $success = 'Profil bilgileriniz güncellendi!';
                    
                    // Güncel bilgileri tekrar çek
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                } catch (PDOException $e) {
                    $error = 'Güncelleme sırasında bir hata oluştu!';
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                // Mevcut şifreyi kontrol et
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $hash = $stmt->fetchColumn();
                
                if (!password_verify($current_password, $hash)) {
                    $error = 'Mevcut şifreniz yanlış!';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Yeni şifre en az 6 karakter olmalıdır!';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Yeni şifreler eşleşmiyor!';
                } else {
                    try {
                        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                        $stmt->execute([$new_hash, $user_id]);
                        $success = 'Şifreniz başarıyla değiştirildi!';
                    } catch (PDOException $e) {
                        $error = 'Şifre değiştirme sırasında bir hata oluştu!';
                    }
                }
                break;
                
            case 'change_email':
                $new_email = sanitize($_POST['new_email'] ?? '');
                $password = $_POST['password'] ?? '';
                
                if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Geçerli bir e-posta adresi giriniz!';
                } else {
                    // Şifreyi kontrol et
                    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $hash = $stmt->fetchColumn();
                    
                    if (!password_verify($password, $hash)) {
                        $error = 'Şifreniz yanlış!';
                    } else {
                        // E-posta zaten kullanılıyor mu?
                        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
                        $stmt->execute([$new_email, $user_id]);
                        
                        if ($stmt->fetch()) {
                            $error = 'Bu e-posta adresi zaten kullanılıyor!';
                        } else {
                            try {
                                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                                $stmt->execute([$new_email, $user_id]);
                                $_SESSION['email'] = $new_email;
                                $success = 'E-posta adresiniz güncellendi!';
                                
                                // Güncel bilgileri tekrar çek
                                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                                $stmt->execute([$user_id]);
                                $user = $stmt->fetch();
                            } catch (PDOException $e) {
                                $error = 'E-posta güncellenirken bir hata oluştu!';
                            }
                        }
                    }
                }
                break;
        }
    }
}

include 'header.php';
?>

<style>
    .settings-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 30px;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .alert-error {
        background: #fee;
        color: #c33;
        border: 1px solid #fcc;
    }

    .settings-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 1.5em;
        color: #764ba2;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }

    .form-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        font-family: inherit;
        transition: all 0.3s;
    }

    .form-input:focus {
        outline: none;
        border-color: #9d7cce;
        box-shadow: 0 0 0 4px rgba(157, 124, 206, 0.1);
    }

    textarea.form-input {
        min-height: 100px;
        resize: vertical;
    }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 10px;
        font-size: 1em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(118, 75, 162, 0.3);
    }

    .info-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #9d7cce;
        margin-bottom: 20px;
        color: #666;
        font-size: 0.95em;
    }

    .danger-zone {
        border: 2px solid #ef4444;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }

    .danger-title {
        color: #ef4444;
        font-size: 1.2em;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
    }

    .user-info-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #666;
        font-weight: 600;
    }

    .info-value {
        color: #333;
    }

    @media (max-width: 768px) {
        .settings-container {
            padding: 20px;
        }
    }
</style>

<div class="settings-container">
    <div class="page-header">
        <h1>⚙️ Ayarlar</h1>
        <p>Hesap bilgilerinizi ve tercihlerinizi yönetin</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Hesap Bilgileri -->
    <div class="settings-section">
        <h2 class="section-title">👤 Hesap Bilgileri</h2>
        <div class="user-info-card">
            <div class="info-row">
                <span class="info-label">Kullanıcı Adı:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">E-posta:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
        </div>
    </div>

    <!-- Profil Düzenleme -->
    <div class="settings-section">
        <h2 class="section-title">✏️ Profil Bilgileri</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label class="form-label">Ad Soyad</label>
                <input type="text" name="full_name" class="form-input" 
                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                       placeholder="Adınız ve soyadınız">
            </div>

            <div class="form-group">
                <label class="form-label">Biyografi</label>
                <textarea name="bio" class="form-input" 
                          placeholder="Kendiniz hakkında birkaç şey yazın..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-save">💾 Kaydet</button>
        </form>
    </div>

    <!-- Şifre Değiştirme -->
    <div class="settings-section">
        <h2 class="section-title">🔒 Şifre Değiştir</h2>
        <div class="info-box">
            ℹ️ Güvenliğiniz için şifrenizi düzenli olarak değiştirmenizi öneririz.
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label class="form-label">Mevcut Şifre</label>
                <input type="password" name="current_password" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Yeni Şifre</label>
                <input type="password" name="new_password" class="form-input" required minlength="6">
            </div>

            <div class="form-group">
                <label class="form-label">Yeni Şifre (Tekrar)</label>
                <input type="password" name="confirm_password" class="form-input" required minlength="6">
            </div>

            <button type="submit" class="btn-save">🔑 Şifreyi Değiştir</button>
        </form>
    </div>

    <!-- E-posta Değiştirme -->
    <div class="settings-section">
        <h2 class="section-title">📧 E-posta Değiştir</h2>
        <form method="POST">
            <input type="hidden" name="action" value="change_email">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label class="form-label">Yeni E-posta</label>
                <input type="email" name="new_email" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">Şifreniz (Doğrulama için)</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="btn-save">📧 E-postayı Değiştir</button>
        </form>
    </div>

    <!-- Tehlikeli Bölge -->
    <div class="settings-section">
        <div class="danger-zone">
            <h3 class="danger-title">⚠️ Tehlikeli Bölge</h3>
            <p style="color: #666; margin-bottom: 15px;">
                Hesabınızı silerseniz, tüm verileriniz (incelemeler, puanlar, yorumlar) kalıcı olarak silinecektir.
            </p>
            <button class="btn-danger" onclick="deleteAccount()">🗑️ Hesabımı Sil</button>
        </div>
    </div>
</div>

<script>
    function deleteAccount() {
        if (confirm('Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
            if (confirm('SON UYARI: Tüm verileriniz kalıcı olarak silinecek. Devam etmek istiyor musunuz?')) {
                // Hesap silme işlemi
                window.location.href = 'delete_account.php';
            }
        }
    }
</script>

<?php include 'footer.php'; ?>