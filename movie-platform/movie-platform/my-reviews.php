<?php
// my-reviews.php - Kullanıcının İncelemeleri
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'İncelemelerim';
$user_id = $_SESSION['user_id'];

// Kullanıcının incelemelerini çek
$stmt = $pdo->prepare("
    SELECT 
        rev.*,
        m.title as movie_title,
        m.movie_id,
        m.poster_url,
        m.release_year
    FROM reviews rev
    JOIN movies m ON rev.movie_id = m.movie_id
    WHERE rev.user_id = ?
    ORDER BY rev.review_date DESC
");
$stmt->execute([$user_id]);
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

    .reviews-grid {
        display: grid;
        gap: 25px;
    }

    .review-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        display: flex;
        gap: 25px;
    }

    .review-poster {
        width: 150px;
        height: 220px;
        border-radius: 10px;
        background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3em;
        flex-shrink: 0;
    }

    .review-content {
        flex: 1;
    }

    .review-movie-title {
        font-size: 1.5em;
        font-weight: 600;
        color: #764ba2;
        margin-bottom: 10px;
        text-decoration: none;
        display: inline-block;
    }

    .review-movie-title:hover {
        text-decoration: underline;
    }

    .review-date {
        color: #999;
        font-size: 0.9em;
        margin-bottom: 15px;
    }

    .review-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }

    .review-text {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .review-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }

    .stat-item {
        color: #999;
        font-size: 0.95em;
    }

    .review-actions {
        display: flex;
        gap: 10px;
    }

    .btn-edit {
        padding: 8px 20px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-delete {
        padding: 8px 20px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #999;
    }

    .empty-icon {
        font-size: 5em;
        margin-bottom: 20px;
    }
</style>

<div class="reviews-container">
    <div class="page-header">
        <h1>📝 İncelemelerim</h1>
        <p>Toplam <?php echo count($reviews); ?> inceleme</p>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <h2 style="font-size: 2em; color: #666; margin-bottom: 10px;">Henüz inceleme yazmadınız</h2>
            <p style="margin-bottom: 20px;">Bir film izleyin ve ilk incelemenizi yazın!</p>
            <a href="movies.php" style="display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 600;">
                Filmlere Göz At
            </a>
        </div>
    <?php else: ?>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-poster">
                        <?php if ($review['poster_url']): ?>
                            <img src="<?php echo htmlspecialchars($review['poster_url']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                        <?php else: ?>
                            🎬
                        <?php endif; ?>
                    </div>
                    
                    <div class="review-content">
                        <a href="movie.php?id=<?php echo $review['movie_id']; ?>" class="review-movie-title">
                            <?php echo htmlspecialchars($review['movie_title']); ?> 
                            (<?php echo $review['release_year']; ?>)
                        </a>
                        
                        <div class="review-date">
                            📅 <?php echo date('d F Y, H:i', strtotime($review['review_date'])); ?>
                        </div>

                        <?php if ($review['title']): ?>
                            <div class="review-title"><?php echo htmlspecialchars($review['title']); ?></div>
                        <?php endif; ?>

                        <div class="review-text">
                            <?php echo nl2br(htmlspecialchars($review['content'])); ?>
                        </div>

                        <div class="review-stats">
                            <span class="stat-item">👍 <?php echo $review['likes_count']; ?> beğeni</span>
                            <span class="stat-item">👎 <?php echo $review['dislikes_count']; ?> beğenmeme</span>
                        </div>

                        <div class="review-actions">
                            <a href="movie.php?id=<?php echo $review['movie_id']; ?>" class="btn-edit">
                                👁️ Film Sayfasına Git
                            </a>
                            <button class="btn-delete" onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                                🗑️ Sil
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    async function deleteReview(reviewId) {
        if (!confirm('Bu incelemeyi silmek istediğinize emin misiniz?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_review');
        formData.append('review_id', reviewId);
        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

        try {
            const response = await fetch('movie_actions.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('İnceleme silindi!');
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('Bir hata oluştu!');
        }
    }
</script>

<?php include 'footer.php'; ?>
