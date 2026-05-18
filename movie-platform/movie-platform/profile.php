<?php
// profile.php - Kullanıcı Profil Sayfası
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Profilim';
$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("
    SELECT * FROM users WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// İstatistikler
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
$stmt->execute([$user_id]);
$review_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM ratings WHERE user_id = ?");
$stmt->execute([$user_id]);
$rating_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ?");
$stmt->execute([$user_id]);
$watchlist_count = $stmt->fetchColumn();

include 'header.php';
?>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
    }

    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3em;
        font-weight: bold;
        color: #764ba2;
    }

    .profile-info h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stat-icon {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 2em;
        font-weight: bold;
        color: #764ba2;
    }

    .stat-label {
        color: #666;
        margin-top: 5px;
    }

    .profile-section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 1.5em;
        color: #764ba2;
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #666;
        width: 150px;
    }

    .info-value {
        color: #333;
    }

    .btn-edit {
        display: inline-block;
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
    }
</style>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['username']); ?></h1>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <?php if ($user['bio']): ?>
                <p style="margin-top: 10px; opacity: 0.9;">
                    <?php echo htmlspecialchars($user['bio']); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-value"><?php echo $review_count; ?></div>
            <div class="stat-label">İnceleme</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-value"><?php echo $rating_count; ?></div>
            <div class="stat-label">Puan Verdiğim</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-value"><?php echo $watchlist_count; ?></div>
            <div class="stat-label">İzleme Listesi</div>
        </div>
    </div>

    <div class="profile-section">
        <h2 class="section-title">👤 Hesap Bilgileri</h2>
        <div class="info-row">
            <div class="info-label">Kullanıcı Adı:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">E-posta:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Kayıt Tarihi:</div>
            <div class="info-value"><?php echo date('d.m.Y', strtotime($user['registration_date'])); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Son Giriş:</div>
            <div class="info-value">
                <?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Bilgi yok'; ?>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="settings.php" class="btn-edit">⚙️ Profili Düzenle</a>
            <a href="my-reviews.php" class="btn-edit" style="background: #10b981; margin-left: 10px;">
                📝 İncelemelerim
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
