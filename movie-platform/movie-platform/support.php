<?php
require_once 'config.php';
$page_title = 'Destek';
include 'header.php';
?>

<style>
    .support-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 30px;
    }

    .support-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 40px;
        border-radius: 20px;
        margin-bottom: 40px;
        text-align: center;
    }

    .support-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .support-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .support-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        text-align: center;
        transition: all 0.3s;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .support-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(118, 75, 162, 0.2);
    }

    .support-icon {
        font-size: 3em;
        margin-bottom: 15px;
    }

    .support-title {
        font-size: 1.3em;
        font-weight: 600;
        color: #764ba2;
        margin-bottom: 10px;
    }

    .support-desc {
        color: #666;
        line-height: 1.6;
    }

    .quick-links {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .quick-links h2 {
        color: #764ba2;
        margin-bottom: 20px;
    }

    .quick-links ul {
        list-style: none;
        padding: 0;
    }

    .quick-links li {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .quick-links li:last-child {
        border-bottom: none;
    }

    .quick-links a {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quick-links a:hover {
        color: #764ba2;
    }
</style>

<div class="support-container">
    <div class="support-header">
        <h1>🆘 Yardım & Destek</h1>
        <p>Size nasıl yardımcı olabiliriz?</p>
    </div>

    <div class="support-grid">
        <a href="faq.php" class="support-card">
            <div class="support-icon">❓</div>
            <div class="support-title">Sık Sorulan Sorular</div>
            <div class="support-desc">En çok sorulan soruların yanıtları</div>
        </a>

        <a href="contact.php" class="support-card">
            <div class="support-icon">📧</div>
            <div class="support-title">Bize Ulaşın</div>
            <div class="support-desc">Mesaj gönderin, size yardımcı olalım</div>
        </a>

        <a href="about.php" class="support-card">
            <div class="support-icon">ℹ️</div>
            <div class="support-title">Hakkımızda</div>
            <div class="support-desc">FilmKutusu'nu tanıyın</div>
        </a>

        <a href="terms.php" class="support-card">
            <div class="support-icon">📜</div>
            <div class="support-title">Kullanım Şartları</div>
            <div class="support-desc">Platform kuralları ve şartlar</div>
        </a>
    </div>

    <div class="quick-links">
        <h2>🔗 Hızlı Linkler</h2>
        <ul>
            <li><a href="faq.php">❓ Nasıl üye olabilirim?</a></li>
            <li><a href="faq.php">📝 İnceleme nasıl yazarım?</a></li>
            <li><a href="faq.php">⭐ Puanlama sistemi nasıl çalışır?</a></li>
            <li><a href="faq.php">🔒 Şifremi unuttum</a></li>
            <li><a href="settings.php">⚙️ Hesap ayarlarım</a></li>
            <li><a href="privacy.php">🔐 Gizlilik politikası</a></li>
            <li><a href="contact.php">📞 İletişim bilgileri</a></li>
        </ul>
    </div>
</div>

<?php include 'footer.php'; ?>