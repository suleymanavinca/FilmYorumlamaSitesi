<?php
// forum_new_topic.php - Yeni Forum Konusu Oluştur
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Yeni Konu Aç';

// Kategorileri çek
$stmt = $pdo->query("SELECT * FROM forum_categories ORDER BY display_order ASC");
$categories = $stmt->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Geçersiz istek!';
    } else {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $content = sanitize($_POST['content'] ?? '');
        
        if (empty($title) || empty($content) || !$category_id) {
            $error = 'Tüm alanları doldurun!';
        } elseif (strlen($title) < 5) {
            $error = 'Başlık en az 5 karakter olmalıdır!';
        } elseif (strlen($content) < 10) {
            $error = 'İçerik en az 10 karakter olmalıdır!';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO forum_topics (category_id, user_id, title, content, created_date) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$category_id, $_SESSION['user_id'], $title, $content]);
                
                $topic_id = $pdo->lastInsertId();
                redirect('forum_topic.php?id=' . $topic_id);
                
            } catch (PDOException $e) {
                $error = 'Konu oluşturulurken bir hata oluştu!';
            }
        }
    }
}

include 'header.php';
?>

<style>
    .new-topic-container {
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
        text-align: center;
    }

    .page-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .topic-form {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
        font-size: 1.05em;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1em;
        font-family: inherit;
        transition: all 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #9d7cce;
        box-shadow: 0 0 0 4px rgba(157, 124, 206, 0.1);
    }

    .form-group textarea {
        min-height: 200px;
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

    .btn-cancel {
        background: #6c757d;
        color: white;
        padding: 14px 35px;
        border: none;
        border-radius: 10px;
        font-size: 1.1em;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        margin-left: 10px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .alert-error {
        background: #fee;
        color: #c33;
        border: 1px solid #fcc;
    }

    .alert-success {
        background: #efe;
        color: #3c3;
        border: 1px solid #cfc;
    }

    .info-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #9d7cce;
        margin-bottom: 25px;
        color: #666;
    }
</style>

<div class="new-topic-container">
    <div class="page-header">
        <h1>✍️ Yeni Konu Aç</h1>
        <p>Düşüncelerinizi paylaşın, tartışmaya başlayın!</p>
    </div>

    <div class="topic-form">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="info-box">
            <strong>📌 İpucu:</strong> Açık ve anlaşılır bir başlık seçin. İçeriğinizi detaylı açıklayın ve saygılı olun!
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="form-group">
                <label>Kategori *</label>
                <select name="category_id" required>
                    <option value="">Kategori seçin</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Başlık *</label>
                <input type="text" name="title" required minlength="5" maxlength="200" 
                       placeholder="Konunuzun başlığını yazın...">
            </div>

            <div class="form-group">
                <label>İçerik *</label>
                <textarea name="content" required minlength="10" 
                          placeholder="Konunuzu detaylı açıklayın..."></textarea>
            </div>

            <div>
                <button type="submit" class="btn-submit">📤 Konuyu Yayınla</button>
                <a href="forum.php" class="btn-cancel">İptal</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>