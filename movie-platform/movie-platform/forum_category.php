<?php
// forum_category.php - Forum Kategorisi Detay
require_once 'config.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$category_id) {
    redirect('forum.php');
}

// Kategori bilgisi
$stmt = $pdo->prepare("SELECT * FROM forum_categories WHERE category_id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    redirect('forum.php');
}

$page_title = $category['category_name'];

// Kategorideki konular
$stmt = $pdo->prepare("
    SELECT 
        ft.*,
        u.username,
        (SELECT COUNT(*) FROM forum_replies WHERE topic_id = ft.topic_id) as reply_count,
        (SELECT username FROM users WHERE user_id = 
            (SELECT user_id FROM forum_replies WHERE topic_id = ft.topic_id ORDER BY reply_date DESC LIMIT 1)
        ) as last_reply_user,
        (SELECT reply_date FROM forum_replies WHERE topic_id = ft.topic_id ORDER BY reply_date DESC LIMIT 1) as last_reply_date
    FROM forum_topics ft
    JOIN users u ON ft.user_id = u.user_id
    WHERE ft.category_id = ?
    ORDER BY 
        ft.is_pinned DESC,
        COALESCE(last_reply_date, ft.created_date) DESC
");
$stmt->execute([$category_id]);
$topics = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .category-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
    }

    .category-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 50px 40px;
        border-radius: 20px;
        margin-bottom: 30px;
    }

    .category-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .btn-new-topic {
        background: white;
        color: #764ba2;
        padding: 12px 25px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        margin-top: 15px;
    }

    .topics-list {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .topic-item {
        display: flex;
        gap: 20px;
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .topic-item:hover {
        background: #f8f9fa;
    }

    .topic-item:last-child {
        border-bottom: none;
    }

    .topic-icon {
        font-size: 2em;
        width: 60px;
        height: 60px;
        background: #f8f9fa;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .topic-content {
        flex: 1;
    }

    .topic-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .topic-meta {
        display: flex;
        gap: 15px;
        color: #999;
        font-size: 0.9em;
    }

    .topic-stats {
        text-align: right;
        color: #999;
        font-size: 0.9em;
    }

    .reply-count {
        font-size: 1.5em;
        font-weight: bold;
        color: #764ba2;
        display: block;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #999;
    }
</style>

<div class="category-container">
    <div class="category-header">
        <h1>
            <span><?php echo $category['icon'] ?: '📁'; ?></span>
            <?php echo htmlspecialchars($category['category_name']); ?>
        </h1>
        <p><?php echo htmlspecialchars($category['description']); ?></p>
        <?php if (isLoggedIn()): ?>
            <a href="forum_new_topic.php?category=<?php echo $category_id; ?>" class="btn-new-topic">
                ➕ Yeni Konu Aç
            </a>
        <?php endif; ?>
    </div>

    <div class="topics-list">
        <?php if (empty($topics)): ?>
            <div class="empty-state">
                <p style="font-size: 3em;">💬</p>
                <h2>Henüz konu yok</h2>
                <p>İlk konuyu açan siz olun!</p>
                <?php if (isLoggedIn()): ?>
                    <a href="forum_new_topic.php?category=<?php echo $category_id; ?>" 
                       style="display: inline-block; margin-top: 20px; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 600;">
                        İlk Konuyu Aç
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($topics as $topic): ?>
                <a href="forum_topic.php?id=<?php echo $topic['topic_id']; ?>" class="topic-item">
                    <div class="topic-icon">
                        <?php if ($topic['is_pinned']): ?>
                            📌
                        <?php elseif ($topic['is_locked']): ?>
                            🔒
                        <?php else: ?>
                            💬
                        <?php endif; ?>
                    </div>
                    <div class="topic-content">
                        <div class="topic-title">
                            <?php echo htmlspecialchars($topic['title']); ?>
                        </div>
                        <div class="topic-meta">
                            <span>👤 <?php echo htmlspecialchars($topic['username']); ?></span>
                            <span>📅 <?php echo date('d.m.Y H:i', strtotime($topic['created_date'])); ?></span>
                            <span>👁️ <?php echo number_format($topic['view_count']); ?></span>
                        </div>
                    </div>
                    <div class="topic-stats">
                        <span class="reply-count"><?php echo $topic['reply_count']; ?></span>
                        <span>yanıt</span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>