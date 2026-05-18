<?php
require_once 'config.php';
$page_title = 'Sorun Bildir';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $type = sanitize($_POST['type'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        if (!empty($type) && !empty($description)) {
            $success = 'Raporunuz alındı! En kısa sürede incelenecektir.';
        } else {
            $error = 'Tüm alanları doldurun!';
        }
    }
}

include 'header.php';
?>

<style>
    .report-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 30px;
    }

    .report-header {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 50px 40px;
        border-radius: 20px;
        margin-bottom: 30px;
        text-align: center;
    }

    .report-form {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
    }

    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        font-family: inherit;
    }

    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }

    .btn-submit {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 14px 30px;
        border: none;
        border-radius: 10px;
        font-size: 1.1em;
        font-weight: 600;
        cursor: pointer;
    }

    .alert {
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
    }

    .alert-error {
        background: #fee;
        color: #c33;
    }
</style>

<div class="report-container">
    <div class="report-header">
        <h1>🚨 Sorun Bildir</h1>
        <p>Bir sorun mu buldunuz? Bize bildirin!</p>
    </div>

    <?php if (!isLoggedIn()): ?>
        <div class="report-form">
            <p style="text-align: center; color: #666;">
                Sorun bildirmek için <a href="login.php" style="color: #764ba2; font-weight: 600;">giriş yapmalısınız</a>.
            </p>
        </div>
    <?php else: ?>
        <div class="report-form">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label>Sorun Türü *</label>
                    <select name="type" required>
                        <option value="">Seçiniz</option>
                        <option value="bug">Teknik Hata</option>
                        <option value="abuse">Uygunsuz İçerik</option>
                        <option value="spam">Spam</option>
                        <option value="account">Hesap Sorunu</option>
                        <option value="other">Diğer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Açıklama *</label>
                    <textarea name="description" required placeholder="Sorunu detaylı açıklayın..."></textarea>
                </div>

                <button type="submit" class="btn-submit">📤 Gönder</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>