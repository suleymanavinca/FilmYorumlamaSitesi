<?php
// movie.php - Film Detay Sayfası (EKSİK DOSYA - ŞİMDİ EKLENİYOR)
require_once 'config.php';

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$movie_id) {
    redirect('movies.php');
}

// Film bilgilerini çek
$stmt = $pdo->prepare("
    SELECT 
        m.*,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(DISTINCT r.rating_id) as rating_count,
        COUNT(DISTINCT rev.review_id) as review_count,
        u.username as added_by_username
    FROM movies m
    LEFT JOIN ratings r ON m.movie_id = r.movie_id
    LEFT JOIN reviews rev ON m.movie_id = rev.movie_id
    LEFT JOIN users u ON m.added_by = u.user_id
    WHERE m.movie_id = ?
    GROUP BY m.movie_id
");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch();

if (!$movie) {
    redirect('movies.php');
}

$page_title = $movie['title'];

// Görüntülenme sayısını artır
$stmt = $pdo->prepare("UPDATE movies SET view_count = view_count + 1 WHERE movie_id = ?");
$stmt->execute([$movie_id]);

// Türleri çek
$stmt = $pdo->prepare("
    SELECT g.* 
    FROM genres g
    JOIN movie_genres mg ON g.genre_id = mg.genre_id
    WHERE mg.movie_id = ?
");
$stmt->execute([$movie_id]);
$genres = $stmt->fetchAll();

// Kullanıcının puanını çek (giriş yapmışsa)
$user_rating = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT rating FROM ratings WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$movie_id, $_SESSION['user_id']]);
    $user_rating = $stmt->fetchColumn();
    
    // İzleme listesinde mi?
    $stmt = $pdo->prepare("SELECT watchlist_id FROM watchlist WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$movie_id, $_SESSION['user_id']]);
    $in_watchlist = $stmt->fetch() ? true : false;
}

// İncelemeleri çek
$stmt = $pdo->prepare("
    SELECT 
        rev.*,
        u.username,
        u.profile_image
    FROM reviews rev
    JOIN users u ON rev.user_id = u.user_id
    WHERE rev.movie_id = ?
    ORDER BY rev.review_date DESC
");
$stmt->execute([$movie_id]);
$reviews = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .movie-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px;
    }

    .movie-header {
        display: flex;
        gap: 40px;
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 40px;
    }

    .movie-poster-large {
        width: 300px;
        height: 450px;
        border-radius: 15px;
        object-fit: cover;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5em;
        flex-shrink: 0;
    }

    .movie-details {
        flex: 1;
    }

    .movie-title-main {
        font-size: 2.5em;
        color: #333;
        margin-bottom: 10px;
    }

    .movie-original-title {
        font-size: 1.2em;
        color: #999;
        margin-bottom: 20px;
        font-style: italic;
    }

    .movie-meta-bar {
        display: flex;
        gap: 30px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
        font-size: 1.05em;
    }

    .movie-rating-display {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }

    .rating-number {
        font-size: 3em;
        font-weight: bold;
        color: #f59e0b;
    }

    .rating-stars {
        font-size: 1.5em;
        color: #f59e0b;
    }

    .rating-count {
        color: #999;
        font-size: 0.95em;
    }

    .movie-genres {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .genre-tag {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 600;
    }

    .movie-synopsis {
        color: #555;
        line-height: 1.8;
        margin-bottom: 25px;
        font-size: 1.05em;
    }

    .movie-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 12px 25px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        font-size: 1em;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-secondary {
        background: white;
        color: #764ba2;
        border: 2px solid #764ba2;
    }

    .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(118, 75, 162, 0.3);
    }

    .rating-section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .rating-section h2 {
        color: #764ba2;
        margin-bottom: 20px;
        font-size: 1.8em;
    }

    .star-rating {
        display: flex;
        gap: 10px;
        font-size: 2.5em;
        margin-bottom: 15px;
    }

    .star {
        cursor: pointer;
        color: #ddd;
        transition: all 0.2s;
    }

    .star:hover,
    .star.active {
        color: #f59e0b;
        transform: scale(1.2);
    }

    .reviews-section {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    }

    .review-form {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        font-family: inherit;
    }

    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }

    .review-item {
        padding: 25px;
        border-bottom: 1px solid #f0f0f0;
    }

    .review-item:last-child {
        border-bottom: none;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .review-author {
        display: flex;
        align-items: center;
        gap: 12px;
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

    .review-content-text {
        color: #555;
        line-height: 1.8;
        margin-bottom: 15px;
    }

    .review-actions {
        display: flex;
        gap: 15px;
    }

    .btn-like {
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 1em;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: color 0.2s;
    }

    .btn-like:hover {
        color: #667eea;
    }

    @media (max-width: 768px) {
        .movie-header {
            flex-direction: column;
        }

        .movie-poster-large {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        .movie-meta-bar {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<div class="movie-container">
    <!-- Film Başlığı ve Detaylar -->
    <div class="movie-header">
        <div class="movie-poster-large">
            <?php if ($movie['poster_url']): ?>
                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;">
            <?php else: ?>
                🎬
            <?php endif; ?>
        </div>

        <div class="movie-details">
            <h1 class="movie-title-main"><?php echo htmlspecialchars($movie['title']); ?></h1>
            <?php if ($movie['original_title']): ?>
                <div class="movie-original-title"><?php echo htmlspecialchars($movie['original_title']); ?></div>
            <?php endif; ?>

            <div class="movie-meta-bar">
                <div class="meta-item">📅 <?php echo $movie['release_year']; ?></div>
                <?php if ($movie['duration']): ?>
                    <div class="meta-item">⏱️ <?php echo $movie['duration']; ?> dk</div>
                <?php endif; ?>
                <div class="meta-item">👁️ <?php echo number_format($movie['view_count']); ?></div>
                <?php if ($movie['director']): ?>
                    <div class="meta-item">🎬 <?php echo htmlspecialchars($movie['director']); ?></div>
                <?php endif; ?>
            </div>

            <div class="movie-rating-display">
                <div class="rating-number"><?php echo number_format($movie['avg_rating'], 1); ?></div>
                <div>
                    <div class="rating-stars">
                        <?php
                        $fullStars = floor($movie['avg_rating']);
                        for ($i = 0; $i < 5; $i++) {
                            echo $i < $fullStars ? '⭐' : '☆';
                        }
                        ?>
                    </div>
                    <div class="rating-count"><?php echo $movie['rating_count']; ?> puan</div>
                </div>
            </div>

            <?php if (!empty($genres)): ?>
                <div class="movie-genres">
                    <?php foreach ($genres as $genre): ?>
                        <span class="genre-tag"><?php echo htmlspecialchars($genre['genre_name']); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($movie['synopsis']): ?>
                <div class="movie-synopsis">
                    <?php echo nl2br(htmlspecialchars($movie['synopsis'])); ?>
                </div>
            <?php endif; ?>

            <div class="movie-actions">
                <?php if (isLoggedIn()): ?>
                    <button class="btn-action btn-secondary" onclick="toggleWatchlist(<?php echo $movie_id; ?>)">
                        <?php echo isset($in_watchlist) && $in_watchlist ? '✓ Listede' : '+ İzleme Listesi'; ?>
                    </button>
                <?php endif; ?>
                <?php if ($movie['trailer_url']): ?>
                    <a href="<?php echo htmlspecialchars($movie['trailer_url']); ?>" target="_blank" class="btn-action btn-primary">
                        ▶️ Fragman İzle
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Puanlama Bölümü -->
    <?php if (isLoggedIn()): ?>
        <div class="rating-section">
            <h2>⭐ Bu Filmi Puanla</h2>
            <div class="star-rating" id="starRating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star <?php echo ($user_rating && $i <= $user_rating) ? 'active' : ''; ?>" 
                          data-rating="<?php echo $i; ?>" 
                          onclick="rateMovie(<?php echo $movie_id; ?>, <?php echo $i; ?>)">
                        ⭐
                    </span>
                <?php endfor; ?>
            </div>
            <p id="ratingMessage" style="color: #666;">
                <?php if ($user_rating): ?>
                    Puanınız: <?php echo $user_rating; ?> yıldız
                <?php else: ?>
                    Bu filme puan vermek için yıldızlara tıklayın
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- İncelemeler -->
    <div class="reviews-section">
        <h2 style="color: #764ba2; margin-bottom: 25px; font-size: 1.8em;">
            📝 İncelemeler (<?php echo count($reviews); ?>)
        </h2>

        <?php if (isLoggedIn()): ?>
            <div class="review-form">
                <h3 style="margin-bottom: 15px;">İncelemenizi Yazın</h3>
                <form id="reviewForm">
                    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label>Başlık (İsteğe Bağlı)</label>
                        <input type="text" name="title" placeholder="İnceleme başlığı...">
                    </div>

                    <div class="form-group">
                        <label>İnceleme *</label>
                        <textarea name="content" required placeholder="Düşüncelerinizi paylaşın..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_spoiler"> 
                            Bu inceleme spoiler içeriyor
                        </label>
                    </div>

                    <button type="submit" class="btn-action btn-primary">📤 İncelemeyi Gönder</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <p style="text-align: center; padding: 40px; color: #999;">
                Henüz inceleme yok. İlk yorumu yapan siz olun!
            </p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <div class="review-author">
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #333;">
                                    <?php echo htmlspecialchars($review['username']); ?>
                                </div>
                                <div style="font-size: 0.85em; color: #999;">
                                    <?php echo date('d.m.Y H:i', strtotime($review['review_date'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($review['title']): ?>
                        <h4 style="margin-bottom: 10px; color: #333;">
                            <?php echo htmlspecialchars($review['title']); ?>
                        </h4>
                    <?php endif; ?>

                    <div class="review-content-text">
                        <?php echo nl2br(htmlspecialchars($review['content'])); ?>
                    </div>

                    <div class="review-actions">
                        <button class="btn-like" onclick="likeReview(<?php echo $review['review_id']; ?>, 'like')">
                            👍 <?php echo $review['likes_count']; ?>
                        </button>
                        <button class="btn-like" onclick="likeReview(<?php echo $review['review_id']; ?>, 'dislike')">
                            👎 <?php echo $review['dislikes_count']; ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// İzleme Listesi
async function toggleWatchlist(movieId) {
    const formData = new FormData();
    formData.append('action', 'toggle_watchlist');
    formData.append('movie_id', movieId);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

    try {
        const response = await fetch('movie_actions.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        alert(data.message);
        if (data.success) location.reload();
    } catch (error) {
        alert('Bir hata oluştu!');
    }
}

// Film Puanlama
async function rateMovie(movieId, rating) {
    const formData = new FormData();
    formData.append('action', 'rate_movie');
    formData.append('movie_id', movieId);
    formData.append('rating', rating);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

    try {
        const response = await fetch('movie_actions.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            document.getElementById('ratingMessage').textContent = 'Puanınız: ' + rating + ' yıldız';
            
            // Yıldızları güncelle
            document.querySelectorAll('.star').forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Bir hata oluştu!');
    }
}

// İnceleme Gönderme
document.getElementById('reviewForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'add_review');

    try {
        const response = await fetch('movie_actions.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        alert(data.message);
        
        if (data.success) {
            location.reload();
        }
    } catch (error) {
        alert('Bir hata oluştu!');
    }
});

// İnceleme Beğenme
async function likeReview(reviewId, likeType) {
    const formData = new FormData();
    formData.append('action', 'like_review');
    formData.append('review_id', reviewId);
    formData.append('like_type', likeType);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

    try {
        const response = await fetch('movie_actions.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            location.reload();
        }
    } catch (error) {
        alert('Bir hata oluştu!');
    }
}
</script>

<?php include 'footer.php'; ?>
