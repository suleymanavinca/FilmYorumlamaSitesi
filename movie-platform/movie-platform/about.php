<?php
// about.php - Hakkımızda
require_once 'config.php';
$page_title = 'Hakkımızda';
include 'header.php';
?>

<style>
    .about-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 30px;
    }

    .about-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 60px;
        border-radius: 20px;
        margin-bottom: 50px;
        text-align: center;
    }

    .about-header h1 {
        font-size: 3em;
        margin-bottom: 20px;
    }

    .about-header p {
        font-size: 1.3em;
        opacity: 0.95;
        max-width: 700px;
        margin: 0 auto;
    }

    .content-section {
        background: white;
        padding: 50px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .content-section h2 {
        color: #764ba2;
        font-size: 2em;
        margin-bottom: 20px;
    }

    .content-section p {
        line-height: 1.8;
        color: #555;
        margin-bottom: 15px;
        font-size: 1.05em;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .feature-card {
        text-align: center;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 15px;
        transition: all 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(118, 75, 162, 0.2);
    }

    .feature-icon {
        font-size: 3.5em;
        margin-bottom: 15px;
    }

    .feature-title {
        font-size: 1.3em;
        font-weight: 600;
        color: #764ba2;
        margin-bottom: 10px;
    }

    .feature-desc {
        color: #666;
        line-height: 1.6;
    }

    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
        margin: 40px 0;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        text-align: center;
    }

    .stat-number {
        font-size: 3em;
        font-weight: bold;
        display: block;
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 1.1em;
        opacity: 0.9;
    }

    .team-section {
        margin-top: 50px;
    }

    .values-list {
        list-style: none;
        padding: 0;
    }

    .values-list li {
        padding: 20px;
        margin-bottom: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #764ba2;
    }

    .values-list li strong {
        color: #764ba2;
        font-size: 1.1em;
    }
</style>

<div class="about-container">
    <div class="about-header">
        <h1>🎬 FilmKutusu Hakkında</h1>
        <p>Film severler için tasarlanmış, tutkulu bir topluluk platformu</p>
    </div>

    <div class="content-section">
        <h2>📖 Hikayemiz</h2>
        <p>FilmKutusu, 2025 yılında film tutkunlarının bir araya gelerek fikir alışverişi yapabileceği, keşifler yapabileceği ve tutkularını paylaşabileceği bir platform olarak hayata geçirildi.</p>
        <p>Amacımız, her film severın sesini duyurabileceği, içten ve samimi eleştirilerin yapılabildiği, algoritmaların değil insanların önerilerinin öne çıktığı bir ortam yaratmaktı.</p>
        <p>Bugün binlerce film severin bir araya geldiği, günlük yüzlerce incelemenin paylaşıldığı canlı bir topluluk olduk.</p>
    </div>

    <div class="stats-section">
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn()); ?>+</span>
            <span class="stat-label">Film</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM users")->fetchColumn()); ?>+</span>
            <span class="stat-label">Kullanıcı</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn()); ?>+</span>
            <span class="stat-label">İnceleme</span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($pdo->query("SELECT COUNT(*) FROM forum_topics")->fetchColumn()); ?>+</span>
            <span class="stat-label">Forum Konusu</span>
        </div>
    </div>

    <div class="content-section">
        <h2>🎯 Misyonumuz</h2>
        <p>Film kültürünü zenginleştirmek, sinema sevgisini yaymak ve her film severın düşüncelerini özgürce paylaşabileceği bir platform olmak.</p>
        
        <h2 style="margin-top: 40px;">💡 Vizyonumuz</h2>
        <p>Türkiye'nin en büyük ve en güvenilir film topluluk platformu olmak, her film hakkında en içten ve dürüst görüşlerin bulunduğu adres olmak.</p>
    </div>

    <div class="content-section">
        <h2>⭐ Özelliklerimiz</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <div class="feature-title">Detaylı İncelemeler</div>
                <div class="feature-desc">Filmleri detaylıca analiz edin, düşüncelerinizi paylaşın</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <div class="feature-title">Puanlama Sistemi</div>
                <div class="feature-desc">5 yıldızlı puanlama ile favorilerinizi belirleyin</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <div class="feature-title">Aktif Forum</div>
                <div class="feature-desc">Film tutkunlarıyla tartışın, sohbet edin</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <div class="feature-title">İzleme Listesi</div>
                <div class="feature-desc">İzlemek istediğiniz filmleri kaydedin</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <div class="feature-title">Gelişmiş Arama</div>
                <div class="feature-desc">Tür, yıl, yönetmen ve daha fazlasına göre filtreleyin</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎭</div>
                <div class="feature-title">Geniş Tür Seçenekleri</div>
                <div class="feature-desc">Her türden filme ulaşın</div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2>💎 Değerlerimiz</h2>
        <ul class="values-list">
            <li>
                <strong>🤝 Topluluk:</strong> 
                <p style="margin: 5px 0 0 0; color: #666;">Film severler için film severler tarafından oluşturulmuş bir platform</p>
            </li>
            <li>
                <strong>🎯 Dürüstlük:</strong> 
                <p style="margin: 5px 0 0 0; color: #666;">Samimi ve içten eleştiriler, manipülasyon yok</p>
            </li>
            <li>
                <strong>🔒 Gizlilik:</strong> 
                <p style="margin: 5px 0 0 0; color: #666;">Verileriniz bizim için değerli ve korunuyor</p>
            </li>
            <li>
                <strong>🌈 Çeşitlilik:</strong> 
                <p style="margin: 5px 0 0 0; color: #666;">Her türden filme ve her görüşe saygı</p>
            </li>
            <li>
                <strong>📚 Öğrenme:</strong> 
                <p style="margin: 5px 0 0 0; color: #666;">Sinema kültürünü geliştirmek ve yaymak</p>
            </li>
        </ul>
    </div>

    <div class="content-section" style="text-align: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <h2>🚀 Bize Katılın!</h2>
        <p style="font-size: 1.2em; margin-bottom: 25px;">Film tutkusu taşıyan herkes burada!</p>
        <?php if (!isLoggedIn()): ?>
            <a href="login.php" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 1.1em;">
                Hemen Üye Ol
            </a>
        <?php else: ?>
            <a href="movies.php" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 1.1em;">
                Filmleri Keşfet
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>