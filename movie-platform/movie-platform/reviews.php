<?php
// reviews.php - Tüm İncelemeler Sayfası
require_once 'config.php';

$page_title = 'Tüm İncelemeler';

// Sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Toplam inceleme sayısı
$total_reviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$total_pages = ceil($total_reviews / $per_page);

// İncelemeleri çek
$stmt = $pdo->prepare("
    SELECT 
        rev.*,
        u.username,
        u.profile_image,
        m.title as movie_title,
        m.movie_id,
        m.poster_url
    FROM reviews rev
    JOIN users u ON rev.user_id = u.user_id
    JOIN movies m ON rev.movie_id = m.movie_id
    ORDER BY rev.review_date DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$per_page, $offset]);
$reviews = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .reviews-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 50px;
        border-radius: 20px;
        margin-bottom: 30px;
        text-align: center;
    }

    .page-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .reviews-list {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .review-item {
        display: flex;
        gap: 20px;
        padding: 25px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .review-item:hover {
        background: #f8f9fa;
    }

    .review-item:last-child {
        border-bottom: none;
    }

    .review-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.5em;
        flex-shrink: 0;
    }

    .review-content {
        flex: 1;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .review-user {
        font-weight: 600;
        color: #333;
    }

    .review-movie {
        color: #9d7cce;
        text-decoration: none;
        font-size: 0.95em;
    }

    .review-movie:hover {
        text-decoration: underline;
    }

    .review-date {
        color: #999;
        font-size: 0.85em;
    }

    .review-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .review-text {
        color: #666;
        line-height: 1.6;
        margin-bottom: 10px;
    }

    .review-stats {
        display: flex;
        gap: 15px;
        color: #999;
        font-size: 0.9em;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }

    .page-link {
        padding: 10px 18px;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        text-decoration: none;
        color: #666;
        font-weight: 600;
        transition: all 0.3s;
    }

    .page-link:hover,
    .page-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #999;
    }
</style>

<div class="reviews-container">
    <div class="page-header">
        <h1>📝 Tüm İncelemeler</h1>
        <p>Toplam <?php echo number_format($total_reviews); ?> inceleme</p>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <p style="font-size: 3em;">📝</p>
            <h2>Henüz inceleme yok</h2>
            <p>İlk inceleyen siz olun!</p>
        </div>
    <?php else: ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-avatar">
                        <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                    </div>
                    <div class="review-content">
                        <div class="review-header">
                            <div>
                                <span class="review-user"><?php echo htmlspecialchars($review['username']); ?></span>
                                <span style="color: #ccc; margin: 0 10px;">•</span>
                                <a href="movie.php?id=<?php echo $review['movie_id']; ?>" class="review-movie">
                                    <?php echo htmlspecialchars($review['movie_title']); ?>
                                </a>
                            </div>
                            <span class="review-date">
                                <?php echo date('d.m.Y', strtotime($review['review_date'])); ?>
                            </span>
                        </div>

                        <?php if ($review['title']): ?>
                            <h3 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h3>
                        <?php endif; ?>

                        <div class="review-text">
                            <?php echo nl2br(htmlspecialchars($review['content'])); ?>
                        </div>

                        <div class="review-stats">
                            <span>👍 <?php echo $review['likes_count']; ?></span>
                            <span>👎 <?php echo $review['dislikes_count']; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="page-link">◀️ Önceki</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="page-link">Sonraki ▶️</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>