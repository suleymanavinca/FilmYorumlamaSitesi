<?php
// forum.php - Forum Ana Sayfası
require_once 'config.php';

$page_title = 'Forum';

// Forum kategorilerini çek
$stmt = $pdo->query("
    SELECT 
        fc.*,
        COUNT(DISTINCT ft.topic_id) as topic_count,
        COUNT(DISTINCT fr.reply_id) as reply_count
    FROM forum_categories fc
    LEFT JOIN forum_topics ft ON fc.category_id = ft.category_id
    LEFT JOIN forum_replies fr ON ft.topic_id = fr.topic_id
    GROUP BY fc.category_id
    ORDER BY fc.display_order ASC
");
$categories = $stmt->fetchAll();

// Son konular
$stmt = $pdo->query("
    SELECT 
        ft.*,
        u.username,
        fc.category_name,
        fc.category_id as cat_id,
        (SELECT COUNT(*) FROM forum_replies WHERE topic_id = ft.topic_id) as reply_count,
        (SELECT username FROM users WHERE user_id = 
            (SELECT user_id FROM forum_replies WHERE topic_id = ft.topic_id ORDER BY reply_date DESC LIMIT 1)
        ) as last_reply_user,
        (SELECT reply_date FROM forum_replies WHERE topic_id = ft.topic_id ORDER BY reply_date DESC LIMIT 1) as last_reply_date
    FROM forum_topics ft
    JOIN users u ON ft.user_id = u.user_id
    JOIN forum_categories fc ON ft.category_id = fc.category_id
    ORDER BY 
        ft.is_pinned DESC,
        COALESCE(last_reply_date, ft.created_date) DESC
    LIMIT 15
");
$recent_topics = $stmt->fetchAll();

// İstatistikler
$total_topics = $pdo->query("SELECT COUNT(*) FROM forum_topics")->fetchColumn();
$total_replies = $pdo->query("SELECT COUNT(*) FROM forum_replies")->fetchColumn();

include 'header.php';
?>

<style>
    .forum-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px;
    }

    .forum-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 50px;
        border-radius: 20px;
        margin-bottom: 30px;
    }

    .forum-header h1 {
        font-size: 2.5em;
        margin-bottom: 15px;
    }

    .forum-stats {
        display: flex;
        gap: 40px;
        margin-top: 20px;
    }

    .stat {
        text-align: center;
    }

    .stat-number {
        font-size: 2em;
        font-weight: bold;
        display: block;
    }

    .stat-label {
        font-size: 0.9em;
        opacity: 0.9;
    }

    .btn-new-topic {
        background: white;
        color: #764ba2;
        padding: 12px 25px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        margin-top: 20px;
    }

    .categories-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 1.8em;
        color: #764ba2;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .category-list {
        display: grid;
        gap: 15px;
    }

    .category-item {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 12px;
        display: flex;
        gap: 20px;
        align-items: center;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }

    .category-item:hover {
        background: #ede9fe;
        transform: translateX(5px);
    }

    .category-icon {
        font-size: 2.5em;
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-info {
        flex: 1;
    }

    .category-name {
        font-size: 1.3em;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .category-desc {
        color: #666;
        font-size: 0.95em;
    }

    .category-stats {
        text-align: right;
        color: #999;
        font-size: 0.9em;
    }

    .category-count {
        font-size: 1.5em;
        font-weight: bold;
        color: #764ba2;
        display: block;
    }

    .topics-list {
        display: grid;
        gap: 12px;
    }

    .topic-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        display: flex;
        gap: 15px;
        align-items: center;
        transition: all 0.3s;
        text-decoration: none;
        color: inherit;
    }

    .topic-item:hover {
        background: #ede9fe;
    }

    .topic-item.pinned {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
    }

    .topic-icon {
        font-size: 1.8em;
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .topic-content {
        flex: 1;
    }

    .topic-title {
        font-size: 1.1em;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .topic-meta {
        display: flex;
        gap: 15px;
        color: #999;
        font-size: 0.85em;
    }

    .topic-category {
        background: #764ba2;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8em;
    }

    .topic-stats {
        text-align: right;
        color: #999;
        font-size: 0.9em;
    }

    .reply-count {
        font-size: 1.2em;
        font-weight: bold;
        color: #764ba2;
        display: block;
    }

    @media (max-width: 768px) {
        .forum-stats {
            flex-direction: column;
            gap: 20px;
        }

        .category-item {
            flex-direction: column;
            text-align: center;
        }

        .category-stats {
            text-align: center;
        }

        .topic-item {
            flex-direction: column;
            text-align: center;
        }

        .topic-stats {
            text-align: center;
        }
    }
</style>

<div class="forum-container">
    <div class="forum-header">
        <h1>💬 Forum</h1>
        <p>Film severlerle buluşun, tartışın, paylaşın!</p>
        
        <div class="forum-stats">
            <div class="stat">
                <span class="stat-number"><?php echo number_format($total_topics); ?></span>
                <span class="stat-label">Konu</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?php echo number_format($total_replies); ?></span>
                <span class="stat-label">Yanıt</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?php echo count($categories); ?></span>
                <span class="stat-label">Kategori</span>
            </div>
        </div>

        <?php if (isLoggedIn()): ?>
            <a href="forum_new_topic.php" class="btn-new-topic">➕ Yeni Konu Aç</a>
        <?php endif; ?>
    </div>

    <!-- Kategoriler -->
    <div class="categories-section">
        <h2 class="section-title">📂 Kategoriler</h2>
        <div class="category-list">
            <?php foreach ($categories as $category): ?>
                <a href="forum_category.php?id=<?php echo $category['category_id']; ?>" class="category-item">
                    <div class="category-icon">
                        <?php echo $category['icon'] ?: '📁'; ?>
                    </div>
                    <div class="category-info">
                        <div class="category-name"><?php echo htmlspecialchars($category['category_name']); ?></div>
                        <div class="category-desc"><?php echo htmlspecialchars($category['description']); ?></div>
                    </div>
                    <div class="category-stats">
                        <span class="category-count"><?php echo $category['topic_count']; ?></span>
                        <span>konu</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Son Konular -->
    <div class="categories-section">
        <h2 class="section-title">🔥 Son Konular</h2>
        <?php if (empty($recent_topics)): ?>
            <div style="text-align: center; padding: 60px; color: #999;">
                <p style="font-size: 3em;">💬</p>
                <p style="font-size: 1.2em;">Henüz konu açılmamış.</p>
                <?php if (isLoggedIn()): ?>
                    <a href="forum_new_topic.php" style="margin-top: 15px; display: inline-block; color: #764ba2; font-weight: 600;">
                        İlk konuyu siz açın! →
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="topics-list">
                <?php foreach ($recent_topics as $topic): ?>
                    <a href="forum_topic.php?id=<?php echo $topic['topic_id']; ?>" 
                       class="topic-item <?php echo $topic['is_pinned'] ? 'pinned' : ''; ?>">
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
                                <?php if ($topic['is_pinned']): ?>
                                    <span style="color: #f59e0b;">📌</span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($topic['title']); ?>
                            </div>
                            <div class="topic-meta">
                                <span class="topic-category"><?php echo htmlspecialchars($topic['category_name']); ?></span>
                                <span>👤 <?php echo htmlspecialchars($topic['username']); ?></span>
                                <span>👁️ <?php echo number_format($topic['view_count']); ?></span>
                                <?php if ($topic['last_reply_user']): ?>
                                    <span>Son: <?php echo htmlspecialchars($topic['last_reply_user']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="topic-stats">
                            <span class="reply-count"><?php echo $topic['reply_count']; ?></span>
                            <span>yanıt</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>