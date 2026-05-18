<?php
// admin/forum.php - Forum Yönetimi (DÜZELTİLMİŞ)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Forum Yönetimi';

// Forum konularını çek
try {
    $stmt = $pdo->query("
        SELECT t.*, u.username, 
               (SELECT COUNT(*) FROM forum_replies WHERE topic_id = t.topic_id) as reply_count
        FROM forum_topics t
        JOIN users u ON t.user_id = u.user_id
        ORDER BY t.created_date DESC
        LIMIT 50
    ");
    $topics = $stmt->fetchAll();
} catch (PDOException $e) {
    $topics = [];
    $error = "Veritabanı hatası: " . $e->getMessage();
}

include '../header.php';
?>

<style>
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px;
    }

    .page-header h1 {
        font-size: 2em;
        color: #333;
        margin-bottom: 30px;
    }

    .topics-list {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .topic-item {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .topic-item:hover {
        background: #f8f9fa;
    }

    .topic-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .topic-title {
        font-size: 1.2em;
        font-weight: bold;
        color: #333;
        margin-bottom: 8px;
    }

    .topic-badge {
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75em;
        font-weight: 600;
        margin-left: 10px;
    }

    .badge-pinned {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-locked {
        background: #fee2e2;
        color: #991b1b;
    }

    .topic-meta {
        display: flex;
        gap: 20px;
        font-size: 0.9em;
        color: #666;
    }

    .topic-actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        color: white;
    }

    .btn-delete {
        background: #dc2626;
    }

    .error-message {
        background: #fee;
        color: #c33;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
</style>

<div class="admin-container">
    <div class="page-header">
        <h1>💬 Forum Yönetimi</h1>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="topics-list">
        <?php if (empty($topics)): ?>
            <div style="text-align: center; padding: 60px; color: #999;">
                <p style="font-size: 3em;">💬</p>
                <h2>Henüz forum konusu yok</h2>
            </div>
        <?php else: ?>
            <?php foreach ($topics as $topic): ?>
            <div class="topic-item">
                <div class="topic-header">
                    <div>
                        <div class="topic-title">
                            <?php echo htmlspecialchars($topic['title']); ?>
                            <?php if ($topic['is_pinned']): ?>
                                <span class="topic-badge badge-pinned">📌 Sabit</span>
                            <?php endif; ?>
                            <?php if ($topic['is_locked']): ?>
                                <span class="topic-badge badge-locked">🔒 Kilitli</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="topic-meta">
                            <span>👤 <?php echo htmlspecialchars($topic['username']); ?></span>
                            <span>📅 <?php echo date('d.m.Y H:i', strtotime($topic['created_date'])); ?></span>
                            <span>💬 <?php echo $topic['reply_count']; ?> Mesaj</span>
                            <span>👁️ <?php echo number_format($topic['view_count']); ?> Görüntülenme</span>
                        </div>
                    </div>

                    <div class="topic-actions">
                        <a href="../forum_topic.php?id=<?php echo $topic['topic_id']; ?>" 
                           class="action-btn" 
                           style="background: #3b82f6;"
                           target="_blank">
                            👁️ Görüntüle
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
