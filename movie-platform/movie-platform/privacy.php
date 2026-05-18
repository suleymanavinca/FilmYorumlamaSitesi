<?php
// privacy.php - Gizlilik Politikası
require_once 'config.php';
$page_title = 'Gizlilik Politikası';
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
        <h1>🔒 Gizlilik Politikası</h1>
        <p>Son güncelleme: <?php echo date('d.m.Y'); ?></p>
    </div>

    <div class="legal-content">
        <p><strong>FilmKutusu</strong> olarak kullanıcılarımızın gizliliğine önem veririz. Bu gizlilik politikası, hangi bilgileri topladığımızı, nasıl kullandığımızı ve koruduğumuzu açıklar.</p>

        <h2>1. Toplanan Bilgiler</h2>
        <p>Sitemizi kullanırken aşağıdaki bilgileri toplayabiliriz:</p>
        <ul>
            <li><strong>Hesap Bilgileri:</strong> Kullanıcı adı, e-posta adresi, şifre</li>
            <li><strong>Profil Bilgileri:</strong> Ad soyad, biyografi, profil resmi</li>
            <li><strong>İçerik Bilgileri:</strong> Film incelemeleri, puanlar, yorumlar</li>
            <li><strong>Kullanım Bilgileri:</strong> IP adresi, tarayıcı bilgisi, ziyaret saatleri</li>
        </ul>

        <h2>2. Bilgilerin Kullanımı</h2>
        <p>Topladığımız bilgileri şu amaçlarla kullanırız:</p>
        <ul>
            <li>Hesabınızı oluşturmak ve yönetmek</li>
            <li>Platformumuzun işlevselliğini sağlamak</li>
            <li>Size özelleştirilmiş içerik sunmak</li>
            <li>Platformu geliştirmek ve iyileştirmek</li>
            <li>Güvenlik ve dolandırıcılık önleme</li>
        </ul>

        <h2>3. Bilgi Paylaşımı</h2>
        <p>Kişisel bilgilerinizi üçüncü taraflarla <strong>paylaşmayız</strong>. Ancak aşağıdaki durumlarda bilgi paylaşımı gerekebilir:</p>
        <ul>
            <li>Yasal zorunluluklar</li>
            <li>Platform güvenliğini sağlamak</li>
            <li>Açık rızanızla</li>
        </ul>

        <h2>4. Çerezler (Cookies)</h2>
        <p>Sitemiz, kullanıcı deneyimini iyileştirmek için çerezler kullanır. Tarayıcı ayarlarınızdan çerezleri devre dışı bırakabilirsiniz.</p>

        <h2>5. Veri Güvenliği</h2>
        <p>Bilgilerinizi korumak için endüstri standardı güvenlik önlemleri alıyoruz:</p>
        <ul>
            <li>Şifreler güvenli hash algoritmaları ile korunur</li>
            <li>HTTPS protokolü kullanılır</li>
            <li>Düzenli güvenlik güncellemeleri yapılır</li>
        </ul>

        <h2>6. Haklarınız</h2>
        <p>Kişisel verileriniz üzerinde aşağıdaki haklara sahipsiniz:</p>
        <ul>
            <li>Bilgilerinize erişim hakkı</li>
            <li>Bilgilerinizi düzeltme hakkı</li>
            <li>Bilgilerinizi silme hakkı (hesap kapatma)</li>
            <li>Veri taşınabilirliği hakkı</li>
        </ul>

        <h2>7. Çocukların Gizliliği</h2>
        <p>Sitemiz 13 yaşın altındaki çocuklara yönelik değildir. Bilerek 13 yaşından küçük kullanıcılardan bilgi toplamayız.</p>

        <h2>8. Değişiklikler</h2>
        <p>Bu gizlilik politikasını zaman zaman güncelleyebiliriz. Önemli değişiklikleri kullanıcılarımıza e-posta ile bildireceğiz.</p>

        <h2>9. İletişim</h2>
        <p>Gizlilik politikamız hakkında sorularınız varsa bizimle iletişime geçebilirsiniz:</p>
        <ul>
            <li><strong>E-posta:</strong> privacy@filmkutusu.com</li>
            <li><strong>İletişim:</strong> <a href="contact.php">İletişim Formu</a></li>
        </ul>

        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;">
            <p style="margin: 0; color: #666;">
                Bu politikayı kabul ederek platformumuzu kullanmaya devam edebilirsiniz.
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
