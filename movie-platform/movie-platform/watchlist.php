<?php
// watchlist.php - İzleme Listesi
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'İzleme Listem';
$user_id = $_SESSION['user_id'];

// İzleme listesindeki filmleri çek
$stmt = $pdo->prepare("
    SELECT 
        w.*,
        m.title,
        m.movie_id,
        m.poster_url,
        m.release_year,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(DISTINCT r.rating_id) as rating_count
    FROM watchlist w
    JOIN movies m ON w.movie_id = m.movie_id
    LEFT JOIN ratings r ON m.movie_id = r.movie_id
    WHERE w.user_id = ?
    GROUP BY w.watchlist_id, m.movie_id
    ORDER BY w.added_date DESC
");
$stmt->execute([$user_id]);
$watchlist = $stmt->fetchAll();

include 'header.php';
?>

<style>
    .watchlist-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 50px;
        border-radius: 20px;
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .movies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 25px;
    }

    .movie-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        position: relative;
    }

    .movie-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(118, 75, 162, 0.3);
    }

    .movie-poster {
        width: 100%;
        height: 320px;
        background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5em;
    }

    .remove-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        font-size: 1.2em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .remove-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    .movie-info {
        padding: 18px;
    }

    .movie-title {
        font-size: 1.1em;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .movie-meta {
        display: flex;
        justify-content: space-between;
        color: #666;
        font-size: 0.9em;
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

    .btn-browse {
        display: inline-block;
        margin-top: 20px;
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
    }
</style>

<div class="watchlist-container">
    <div class="page-header">
        <h1>📋 İzleme Listem</h1>
        <p>Toplam <?php echo count($watchlist); ?> film</p>
    </div>

    <?php if (empty($watchlist)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h2 style="font-size: 2em; color: #666; margin-bottom: 10px;">İzleme listeniz boş</h2>
            <p>İzlemek istediğiniz filmleri ekleyin!</p>
            <a href="movies.php" class="btn-browse">Filmlere Göz At</a>
        </div>
    <?php else: ?>
        <div class="movies-grid">
            <?php foreach ($watchlist as $item): ?>
                <div class="movie-card">
                    <button class="remove-btn" onclick="removeFromWatchlist(<?php echo $item['movie_id']; ?>)" title="Listeden Çıkar">
                        ✕
                    </button>
                    <a href="movie.php?id=<?php echo $item['movie_id']; ?>">
                        <div class="movie-poster">
                            <?php if ($item['poster_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['poster_url']); ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                🎬
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="movie-info">
                        <div class="movie-title">
                            <a href="movie.php?id=<?php echo $item['movie_id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </div>
                        <div class="movie-meta">
                            <span><?php echo $item['release_year']; ?></span>
                            <span>⭐ <?php echo number_format($item['avg_rating'], 1); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    async function removeFromWatchlist(movieId) {
        if (!confirm('Bu filmi izleme listenizden çıkarmak istediğinize emin misiniz?')) {
            return;
        }

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

            if (data.success) {
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