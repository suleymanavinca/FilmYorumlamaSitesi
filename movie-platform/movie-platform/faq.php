<?php
require_once 'config.php';
$page_title = 'Sık Sorulan Sorular';
include 'header.php';
?>

<style>
    .faq-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 30px;
    }

    .faq-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 40px;
        border-radius: 20px;
        margin-bottom: 40px;
        text-align: center;
    }

    .faq-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .faq-list {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .faq-item {
        margin-bottom: 25px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 25px;
    }

    .faq-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    .faq-question {
        font-size: 1.2em;
        font-weight: 600;
        color: #764ba2;
        margin-bottom: 10px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .faq-question:hover {
        color: #667eea;
    }

    .faq-answer {
        color: #666;
        line-height: 1.8;
        padding-left: 20px;
    }
</style>

<div class="faq-container">
    <div class="faq-header">
        <h1>❓ Sık Sorulan Sorular</h1>
        <p>Merak ettiklerinizin yanıtları burada</p>
    </div>

    <div class="faq-list">
        <div class="faq-item">
            <div class="faq-question">🎬 FilmKutusu nedir?</div>
            <div class="faq-answer">
                FilmKutusu, film severler için tasarlanmış bir topluluk platformudur. Film incelemeleri yazabilir, puanlayabilir, tartışabilir ve keşifler yapabilirsiniz.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">👤 Nasıl üye olabilirim?</div>
            <div class="faq-answer">
                Sağ üstteki "Giriş Yap" butonuna tıklayın, ardından "Kayıt Ol" sekmesine geçerek formu doldurun. E-posta doğrulaması gerektirmez, hemen kullanmaya başlayabilirsiniz!
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">🔒 Şifremi unuttum, ne yapmalıyım?</div>
            <div class="faq-answer">
                Giriş sayfasında "Şifremi Unuttum" linkine tıklayın. E-posta adresinizi girin, size şifre sıfırlama bağlantısı gönderilecektir.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">📝 Nasıl inceleme yazabilirim?</div>
            <div class="faq-answer">
                Bir filme tıklayın, aşağı kaydırın ve "İncelemenizi Yazın" bölümünden incelemenizi paylaşabilirsiniz. Başlık isteğe bağlıdır, spoiler içeriyorsa işaretleyin.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">⭐ Puanlama sistemi nasıl çalışır?</div>
            <div class="faq-answer">
                Her film için 1'den 5'e kadar yıldız verebilirsiniz. Puanınız anında kaydedilir ve film detay sayfasında "Puanla" butonuna tıklayarak değiştirebilirsiniz.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">📋 İzleme listesi ne işe yarar?</div>
            <div class="faq-answer">
                İzlemek istediğiniz filmleri izleme listenize ekleyebilirsiniz. Film detay sayfasında "+ İzleme Listesi" butonuna tıklayın. Listenize "İzleme Listem" menüsünden ulaşabilirsiniz.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">✏️ İncelememi düzenleyebilir miyim?</div>
            <div class="faq-answer">
                Evet! Profil sayfanızdaki "İncelemelerim" bölümünden incelemenizi bulun ve düzenle butonuna tıklayın. İstediğiniz zaman silebilir veya güncelleyebilirsiniz.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">🎥 Yeni film nasıl ekleyebilirim?</div>
            <div class="faq-answer">
                Film ekleme yetkisi sadece admin kullanıcılara aittir. Film önerisi için iletişim sayfasından bize ulaşabilir veya forumda konu açabilirsiniz.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">💬 Forum nasıl kullanılır?</div>
            <div class="faq-answer">
                Forum menüsünden istediğiniz kategoriye girin ve "Yeni Konu Aç" butonuna tıklayın. Film hakkında tartışma başlatabilir, öneri paylaşabilir veya soru sorabilirsiniz.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">🗑️ Hesabımı silebilir miyim?</div>
            <div class="faq-answer">
                Evet. Ayarlar → Tehlikeli Bölge → "Hesabımı Sil" butonuna tıklayın. Hesabınız ve tüm verileriniz kalıcı olarak silinecektir.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">🔔 Bildirimleri nasıl yönetebilirim?</div>
            <div class="faq-answer">
                Ayarlar sayfasından bildirim tercihlerinizi yönetebilirsiniz. Hangi olaylar için bildirim almak istediğinizi seçebilirsiniz.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">📧 Size nasıl ulaşabilirim?</div>
            <div class="faq-answer">
                İletişim sayfasından mesaj gönderebilir veya support@filmkutusu.com adresine e-posta atabilirsiniz. 1-2 iş günü içinde yanıt vermeye çalışıyoruz.
            </div>
        </div>

        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;">
            <p style="margin: 0; color: #666;">
                Sorunuz burada yok mu? 
                <a href="contact.php" style="color: #764ba2; font-weight: 600; text-decoration: none;">Bize yazın!</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>