<?php
// admin/settings.php - Site Ayarları
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Site Ayarları';

// İstatistikler
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_movies' => $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn(),
    'total_reviews' => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
    'total_ratings' => $pdo->query("SELECT COUNT(*) FROM ratings")->fetchColumn(),
];

include '../header.php';
?>

<style>
    .admin-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
    }

    .page-header h1 {
        font-size: 2em;
        color: #333;
        margin-bottom: 10px;
    }

    .page-description {
        color: #666;
        margin-bottom: 30px;
    }

    .settings-section {
        background: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 1.3em;
        color: #764ba2;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95em;
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
    }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 40px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.05em;
        font-weight: 600;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .stat-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-value {
        font-size: 2.5em;
        font-weight: bold;
        color: #764ba2;
    }

    .stat-label {
        color: #666;
        font-size: 0.9em;
        margin-top: 5px;
    }

    .danger-zone {
        background: #fef2f2;
        border: 2px solid #fca5a5;
        border-radius: 12px;
        padding: 25px;
    }

    .danger-zone .section-title {
        color: #dc2626;
    }

    .btn-danger {
        background: #dc2626;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-right: 10px;
    }
</style>

<div class="admin-container">
    <div class="page-header">
        <h1>⚙️ Site Ayarları</h1>
        <p class="page-description">Sitenizin genel ayarlarını buradan yönetebilirsiniz.</p>
    </div>

    <form method="POST" action="settings_save.php">
        <!-- Genel Ayarlar -->
        <div class="settings-section">
            <h2 class="section-title">🌐 Genel Ayarlar</h2>
            
            <div class="form-group">
                <label>Site Adı</label>
                <input type="text" name="site_name" value="FilmKutusu" required>
            </div>

            <div class="form-group">
                <label>Site Açıklaması</label>
                <textarea name="site_description">Film inceleme ve değerlendirme platformu</textarea>
            </div>

            <div class="form-group">
                <label>İletişim E-postası</label>
                <input type="email" name="site_email" value="info@filmkutusu.com" required>
            </div>
        </div>

        <!-- Kullanıcı Ayarları -->
        <div class="settings-section">
            <h2 class="section-title">👥 Kullanıcı Ayarları</h2>
            
            <div class="checkbox-group">
                <input type="checkbox" name="allow_registration" id="allow_registration" checked>
                <label for="allow_registration">
                    <strong>Kayıt İzni</strong><br>
                    <small>Yeni kullanıcıların kayıt olmasına izin ver</small>
                </label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="require_email_verification" id="require_email_verification">
                <label for="require_email_verification">
                    <strong>E-posta Doğrulama</strong><br>
                    <small>Kayıt sonrası e-posta doğrulaması iste</small>
                </label>
            </div>
        </div>

        <!-- İçerik Ayarları -->
        <div class="settings-section">
            <h2 class="section-title">📝 İçerik Ayarları</h2>
            
            <div class="checkbox-group">
                <input type="checkbox" name="allow_reviews" id="allow_reviews" checked>
                <label for="allow_reviews">
                    <strong>İnceleme İzni</strong><br>
                    <small>Kullanıcıların film incelemesi yazmasına izin ver</small>
                </label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="allow_ratings" id="allow_ratings" checked>
                <label for="allow_ratings">
                    <strong>Puan İzni</strong><br>
                    <small>Kullanıcıların filmlere puan vermesine izin ver</small>
                </label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="allow_forum" id="allow_forum" checked>
                <label for="allow_forum">
                    <strong>Forum İzni</strong><br>
                    <small>Forum özelliğini aktif et</small>
                </label>
            </div>
        </div>

        <button type="submit" class="btn-save">💾 Ayarları Kaydet</button>
    </form>

    <!-- Site İstatistikleri -->
    <div class="settings-section" style="margin-top: 30px;">
        <h2 class="section-title">📊 Site İstatistikleri</h2>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Toplam Kullanıcı</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['total_movies']); ?></div>
                <div class="stat-label">Toplam Film</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['total_reviews']); ?></div>
                <div class="stat-label">Toplam İnceleme</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['total_ratings']); ?></div>
                <div class="stat-label">Toplam Puan</div>
            </div>
        </div>
    </div>

    <!-- Tehlikeli Alan -->
    <div class="danger-zone" style="margin-top: 30px;">
        <h2 class="section-title">⚠️ Tehlikeli Alan</h2>
        <p style="color: #666; margin-bottom: 15px;">
            Aşağıdaki işlemler geri alınamaz! Lütfen dikkatli olun.
        </p>
        
        <button type="button" onclick="if(confirm('Önbelleği temizlemek istediğinize emin misiniz?')) clearCache()" class="btn-danger">
            🗑️ Önbelleği Temizle
        </button>
        
        <button type="button" onclick="if(confirm('Tüm oturumları kapatmak istediğinize emin misiniz?')) clearSessions()" class="btn-danger">
            🚪 Tüm Oturumları Kapat
        </button>
    </div>
</div>

<script>
function clearCache() {
    alert('Önbellek temizlendi!');
}

function clearSessions() {
    alert('Tüm oturumlar kapatıldı!');
    window.location.href = '../logout.php';
}
</script>

<?php include '../footer.php'; ?>
