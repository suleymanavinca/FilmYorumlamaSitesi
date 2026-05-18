<?php
// contact.php - İletişim
require_once 'config.php';
$page_title = 'İletişim';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Geçersiz istek!';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = 'Tüm alanları doldurunuz!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Geçerli bir e-posta adresi giriniz!';
        } else {
            // Burada e-posta gönderilebilir veya veritabanına kaydedilebilir
            $success = 'Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.';
        }
    }
}

include 'header.php';
?>

<style>
    .contact-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 30px;
    }

    .contact-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 40px;
        border-radius: 20px;
        margin-bottom: 40px;
        text-align: center;
    }

    .contact-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }

    .contact-form {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .contact-form h2 {
        color: #764ba2;
        margin-bottom: 25px;
        font-size: 1.8em;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #555;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #9d7cce;
    }

    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 14px 35px;
        border: none;
        border-radius: 10px;
        font-size: 1.1em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(118, 75, 162, 0.3);
    }

    .contact-info {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .contact-info h2 {
        color: #764ba2;
        margin-bottom: 25px;
        font-size: 1.8em;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 25px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .info-icon {
        font-size: 2em;
        flex-shrink: 0;
    }

    .info-content h3 {
        color: #333;
        margin-bottom: 5px;
    }

    .info-content p {
        color: #666;
        line-height: 1.6;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
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

    .faq-section {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .faq-section h2 {
        color: #764ba2;
        margin-bottom: 25px;
        font-size: 1.8em;
    }

    .faq-item {
        margin-bottom: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .faq-question {
        color: #333;
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 1.05em;
    }

    .faq-answer {
        color: #666;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="contact-container">
    <div class="contact-header">
        <h1>📧 İletişim</h1>
        <p>Sorularınız, önerileriniz veya geri bildirimleriniz için bize ulaşın</p>
    </div>

    <div class="contact-grid">
        <div class="contact-form">
            <h2>Mesaj Gönderin</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label>Adınız *</label>
                    <input type="text" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>E-posta *</label>
                    <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Konu *</label>
                    <select name="subject" required>
                        <option value="">Seçiniz</option>
                        <option value="Genel Soru">Genel Soru</option>
                        <option value="Teknik Destek">Teknik Destek</option>
                        <option value="Hesap Sorunu">Hesap Sorunu</option>
                        <option value="Öneri">Öneri</option>
                        <option value="Şikayet">Şikayet</option>
                        <option value="İş Birliği">İş Birliği</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mesajınız *</label>
                    <textarea name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn-submit">📤 Gönder</button>
            </form>
        </div>

        <div class="contact-info">
            <h2>İletişim Bilgileri</h2>

            <div class="info-item">
                <div class="info-icon">📧</div>
                <div class="info-content">
                    <h3>E-posta</h3>
                    <p>support@filmkutusu.com<br>info@filmkutusu.com</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">📍</div>
                <div class="info-content">
                    <h3>Adres</h3>
                    <p>Balçova, İzmir<br>Türkiye</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">⏰</div>
                <div class="info-content">
                    <h3>Çalışma Saatleri</h3>
                    <p>Pazartesi - Cuma: 09:00 - 18:00<br>Hafta sonu: Kapalı</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">💬</div>
                <div class="info-content">
                    <h3>Sosyal Medya</h3>
                    <p>
                        <a href="#" style="color: #764ba2; text-decoration: none;">Facebook</a> • 
                        <a href="#" style="color: #764ba2; text-decoration: none;">Twitter</a> • 
                        <a href="#" style="color: #764ba2; text-decoration: none;">Instagram</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="faq-section">
        <h2>❓ Sık Sorulan Sorular</h2>

        <div class="faq-item">
            <div class="faq-question">Hesap nasıl oluşturabilirim?</div>
            <div class="faq-answer">Sağ üstteki "Giriş Yap" butonuna tıklayın, ardından "Kayıt Ol" sekmesine geçerek formu doldurun.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">Şifremi unuttum, ne yapmalıyım?</div>
            <div class="faq-answer">Giriş sayfasında "Şifremi Unuttum" linkine tıklayın. E-posta adresinize şifre sıfırlama bağlantısı gönderilecektir.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">İncelememi nasıl düzenleyebilirim?</div>
            <div class="faq-answer">Profil sayfanızdaki "İncelemelerim" bölümünden incelemenizi bulun ve düzenle butonuna tıklayın.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">Hesabımı silebilir miyim?</div>
            <div class="faq-answer">Evet, Ayarlar sayfasındaki "Hesabımı Sil" bölümünden hesabınızı kalıcı olarak silebilirsiniz.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">Film nasıl ekleyebilirim?</div>
            <div class="faq-answer">Film ekleme yetkisi yalnızca admin kullanıcılara aittir. Film önerisi için bize iletişim formundan ulaşabilirsiniz.</div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>