<?php
// forum_topic.php - Forum Konusu Detay
require_once 'config.php';

$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$topic_id) {
    redirect('forum.php');
}

// Konu bilgisi
$stmt = $pdo->prepare("
    SELECT 
        ft.*,
        u.username,
        fc.category_name,
        fc.category_id
    FROM forum_topics ft
    JOIN users u ON ft.user_id = u.user_id
    JOIN forum_categories fc ON ft.category_id = fc.category_id
    WHERE ft.topic_id = ?
");
$stmt->execute([$topic_id]);
$topic = $stmt->fetch();

if (!$topic) {
    redirect('forum.php');
}

$page_title = $topic['title'];

// Görüntülenme sayısını artır
$stmt = $pdo->prepare("UPDATE forum_topics SET view_count = view_count + 1 WHERE topic_id = ?");
$stmt->execute([$topic_id]);

// Yanıtları çek
$stmt = $pdo->prepare("
    SELECT 
        fr.*,
        u.username
    FROM forum_replies fr
    JOIN users u ON fr.user_id = u.user_id
    WHERE fr.topic_id = ?
    ORDER BY fr.reply_date ASC
");
$stmt->execute([$topic_id]);
$replies = $stmt->fetchAll();

// Yanıt gönder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $content = sanitize($_POST['content'] ?? '');
        
        if (!empty($content) && strlen($content) >= 5) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO forum_replies (topic_id, user_id, content, reply_date) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$topic_id, $_SESSION['user_id'], $content]);
                
                redirect('forum_topic.php?id=' . $topic_id);
            } catch (PDOException $e) {
                $error = 'Yanıt gönderilirken hata oluştu!';
            }
        }
    }
}

include 'header.php';
?>

<style>
    .topic-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 30px;
    }

    .breadcrumb {
        margin-bottom: 20px;
        color: #666;
    }

    .breadcrumb a {
        color: #764ba2;
        text-decoration: none;
    }

    .topic-post {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .post-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .post-author {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .author-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.3em;
    }

    .author-name {
        font-weight: 600;
        color: #333;
    }

    .post-date {
        color: #999;
        font-size: 0.9em;
    }

    .post-content {
        line-height: 1.8;
        color: #555;
        font-size: 1.05em;
    }

    .reply-form {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-top: 30px;
    }

    .reply-form h3 {
        color: #764ba2;
        margin-bottom: 20px;
    }

    .reply-form textarea {
        width: 100%;
        min-height: 150px;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1em;
        font-family: inherit;
        resize: vertical;
    }

    .reply-form textarea:focus {
        outline: none;
        border-color: #9d7cce;
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 15px;
    }
</style>

<div class="topic-container">
    <div class="breadcrumb">
        <a href="forum.php">Forum</a> → 
        <a href="forum_category.php?id=<?php echo $topic['category_id']; ?>">
            <?php echo htmlspecialchars($topic['category_name']); ?>
        </a> → 
        <?php echo htmlspecialchars($topic['title']); ?>
    </div>

    <div class="topic-post">
        <div class="post-header">
            <div class="post-author">
                <div class="author-avatar">
                    <?php echo strtoupper(substr($topic['username'], 0, 1)); ?>
                </div>
                <div>
                    <div class="author-name"><?php echo htmlspecialchars($topic['username']); ?></div>
                    <div class="post-date">
                        <?php echo date('d F Y, H:i', strtotime($topic['created_date'])); ?>
                    </div>
                </div>
            </div>
        </div>
        <h1 style="font-size: 1.8em; color: #333; margin-bottom: 20px;">
            <?php echo htmlspecialchars($topic['title']); ?>
        </h1>
        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
        </div>
    </div>

    <?php foreach ($replies as $reply): ?>
        <div class="topic-post">
            <div class="post-header">
                <div class="post-author">
                    <div class="author-avatar">
                        <?php echo strtoupper(substr($reply['username'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="author-name"><?php echo htmlspecialchars($reply['username']); ?></div>
                        <div class="post-date">
                            <?php echo date('d F Y, H:i', strtotime($reply['reply_date'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (isLoggedIn()): ?>
        <div class="reply-form">
            <h3>💬 Yanıt Yaz</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <textarea name="content" required minlength="5" placeholder="Yanıtınızı yazın..."></textarea>
                <button type="submit" class="btn-submit">Yanıtla</button>
            </form>
        </div>
    <?php else: ?>
        <div class="reply-form" style="text-align: center;">
            <p>Yanıt yazmak için <a href="login.php" style="color: #764ba2; font-weight: 600;">giriş yapmalısınız</a>.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>