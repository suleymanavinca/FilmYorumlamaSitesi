<?php
// terms.php - Kullanım Şartları
require_once 'config.php';
$page_title = 'Kullanım Şartları';
include 'header.php';
?>

<style>
    .legal-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 30px;
    }

    .legal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 50px;
        border-radius: 20px;
        margin-bottom: 40px;
        text-align: center;
    }

    .legal-content {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        line-height: 1.8;
    }

    .legal-content h2 {
        color: #764ba2;
        margin-top: 30px;
        margin-bottom: 15px;
    }

    .legal-content p {
        margin-bottom: 15px;
        color: #555;
    }

    .legal-content ul {
        margin: 15px 0 15px 30px;
        color: #555;
    }

    .legal-content li {
        margin-bottom: 10px;
    }
</style>

<div class="legal-container">
    <div class="legal-header">
        <h1>📜 Kullanım Şartları</h1>
        <p>Yürürlük tarihi: <?php echo date('d.m.Y'); ?></p>
    </div>

    <div class="legal-content">
        <p><strong>FilmKutusu</strong> platformunu kullanarak aşağıdaki şart ve koşulları kabul etmiş olursunuz. Lütfen dikkatlice okuyunuz.</p>

        <h2>1. Hizmet Tanımı</h2>
        <p>FilmKutusu, kullanıcıların film incelemesi yazabileceği, puanlayabileceği ve diğer film severlerle etkileşime geçebileceği bir platformdur.</p>

        <h2>2. Hesap Oluşturma</h2>
        <p>Platformumuzu kullanmak için:</p>
        <ul>
            <li>En az 13 yaşında olmalısınız</li>
            <li>Doğru ve güncel bilgiler sağlamalısınız</li>
            <li>Hesap bilgilerinizi güvende tutmalısınız</li>
            <li>Hesabınızda gerçekleşen tüm faaliyetlerden siz sorumlusunuz</li>
        </ul>

        <h2>3. Kullanıcı Davranışları</h2>
        <p>Platformumuzu kullanırken şunları <strong>YAPAMAZSINIZ</strong>:</p>
        <ul>
            <li>Uygunsuz, küfürlü veya hakaret içeren içerik paylaşma</li>
            <li>Spam veya yanıltıcı içerik oluşturma</li>
            <li>Telif hakkı ihlali içeren materyal yayınlama</li>
            <li>Diğer kullanıcıları taciz etme</li>
            <li>Platformun güvenliğini tehlikeye atma</li>
            <li>Sahte hesap oluşturma</li>
            <li>Otomatik araçlar (bot) kullanma</li>
        </ul>

        <h2>4. İçerik Politikası</h2>
        <p>Paylaştığınız içerikler için <strong>siz sorumlusunuz</strong>. Platform yönetimi:</p>
        <ul>
            <li>Uygunsuz içerikleri kaldırma hakkına sahiptir</li>
            <li>Kurallara uymayan hesapları askıya alabilir veya kapatabilir</li>
            <li>İçerikleri düzenleme veya kaldırma hakkını saklı tutar</li>
        </ul>

        <h2>5. Fikri Mülkiyet</h2>
        <p>Yazdığınız incelemeler ve yorumlar size aittir, ancak FilmKutusu'na şunları verirsiniz:</p>
        <ul>
            <li>İçeriği platformda yayınlama hakkı</li>
            <li>İçeriği paylaşma ve dağıtma hakkı</li>
            <li>İçeriği tanıtım amaçlı kullanma hakkı</li>
        </ul>

        <h2>6. Hizmet Değişiklikleri</h2>
        <p>FilmKutusu:</p>
        <ul>
            <li>Hizmetlerini istediği zaman değiştirebilir veya sonlandırabilir</li>
            <li>Özellik ekleyebilir veya kaldırabilir</li>
            <li>Bu şartları güncelleme hakkını saklı tutar</li>
        </ul>

        <h2>7. Sorumluluk Reddi</h2>
        <p>FilmKutusu:</p>
        <ul>
            <li>Kullanıcı içeriğinin doğruluğunu garanti etmez</li>
            <li>Hizmetin kesintisiz olacağını garanti etmez</li>
            <li>Kullanıcı içeriğinden kaynaklanan zararlardan sorumlu değildir</li>
        </ul>

        <h2>8. Hesap Kapatma</h2>
        <p>Hesabınızı istediğiniz zaman kapatabilirsiniz:</p>
        <ul>
            <li>Ayarlar → Hesabımı Sil seçeneğini kullanın</li>
            <li>Hesap kapatıldığında tüm verileriniz silinir</li>
            <li>Bu işlem geri alınamaz</li>
        </ul>

        <h2>9. İhlal Bildirimi</h2>
        <p>Kurallara aykırı içerik gördüğünüzde:</p>
        <ul>
            <li>Sorun Bildir formunu kullanın</li>
            <li>report@filmkutusu.com adresine e-posta gönderin</li>
            <li>İnceleme altındaki "Bildir" butonunu kullanın</li>
        </ul>

        <h2>10. Uyuşmazlık Çözümü</h2>
        <p>Bu şartlarla ilgili uyuşmazlıklar Türkiye Cumhuriyeti yasalarına tabiidir ve İzmir mahkemeleri yetkilidir.</p>

        <h2>11. İletişim</h2>
        <p>Kullanım şartları hakkında sorularınız için:</p>
        <ul>
            <li><strong>E-posta:</strong> legal@filmkutusu.com</li>
            <li><strong>İletişim:</strong> <a href="contact.php">İletişim Formu</a></li>
        </ul>

        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;">
            <p style="margin: 0; color: #666;">
                Bu şartları kabul ederek, FilmKutusu topluluğuna katılmış olursunuz. 🎬
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
