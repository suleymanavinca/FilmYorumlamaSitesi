<?php
// movies.php - Tüm Filmler Sayfası
require_once 'config.php';

$page_title = 'Filmler';

// Sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 24;
$offset = ($page - 1) * $per_page;

// Filtreler
$genre = $_GET['genre'] ?? '';
$year = $_GET['year'] ?? '';
$sort = $_GET['sort'] ?? 'popular';

// SQL sorgusu
$sql = "
    SELECT 
        m.*,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(DISTINCT r.rating_id) as rating_count,
        COUNT(DISTINCT rev.review_id) as review_count,
        GROUP_CONCAT(DISTINCT g.genre_name SEPARATOR ', ') as genres
    FROM movies m
    LEFT JOIN ratings r ON m.movie_id = r.movie_id
    LEFT JOIN reviews rev ON m.movie_id = rev.movie_id
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    WHERE 1=1
";

$params = [];
$count_sql = "SELECT COUNT(DISTINCT m.movie_id) FROM movies m";
$count_where = " WHERE 1=1";

if ($genre) {
    $sql .= " AND mg.genre_id = ?";
    $count_sql .= " JOIN movie_genres mg ON m.movie_id = mg.movie_id";
    $count_where .= " AND mg.genre_id = ?";
    $params[] = $genre;
}

if ($year) {
    $sql .= " AND m.release_year = ?";
    $count_where .= " AND m.release_year = ?";
    $params[] = $year;
}

$sql .= " GROUP BY m.movie_id";

// Sıralama
switch ($sort) {
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC, rating_count DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY m.created_at DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY m.release_year ASC";
        break;
    case 'title':
        $sql .= " ORDER BY m.title ASC";
        break;
    default: // popular
        $sql .= " ORDER BY m.view_count DESC, avg_rating DESC";
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

// Toplam film sayısı
$count_stmt = $pdo->prepare($count_sql . $count_where);
$count_params = array_slice($params, 0, -2); // Son iki parametreyi (limit ve offset) çıkar
$count_stmt->execute($count_params);
$total_movies = $count_stmt->fetchColumn();
$total_pages = ceil($total_movies / $per_page);

// Filmleri çek
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();

// Tüm türler
$stmt = $pdo->query("SELECT * FROM genres ORDER BY genre_name");
$all_genres = $stmt->fetchAll();

// Yıllar
$current_year = date('Y');
$years = range($current_year, $current_year - 50);

include 'header.php';
?>

<style>
    .movies-container {
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

    .filters-bar {
        background: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .filters-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-label {
        font-weight: 600;
        color: #666;
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        cursor: pointer;
        background: white;
        min-width: 150px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #9d7cce;
    }

    .btn-clear {
        background: #ef4444;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .results-count {
        font-size: 1.2em;
        color: #666;
    }

    .movies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .movie-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
        display: block;
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
        position: relative;
    }

    .movie-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(118, 75, 162, 0.9);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.35em;
        font-weight: 600;
    }

    .movie-info {
        padding: 18px;
    }

    .movie-title {
        font-size: 1.1em;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
        line-height: 1.3;
    }

    .movie-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        color: #666;
        font-size: 0.9em;
    }

    .movie-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #f59e0b;
        font-weight: 600;
    }

    .movie-genres {
        color: #9d7cce;
        font-size: 0.85em;
        line-height: 1.4;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 40px;
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

    .page-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .filters-row {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-select {
            width: 100%;
        }

        .movies-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .movie-poster {
            height: 220px;
        }
    }
</style>

<div class="movies-container">
    <div class="page-header">
        <h1>🎬 Tüm Filmler</h1>
        <p>Toplamda <?php echo number_format($total_movies); ?> film keşfetmeyi bekliyor!</p>
    </div>

    <div class="filters-bar">
        <form method="GET" action="movies.php">
            <div class="filters-row">
                <span class="filter-label">Filtrele:</span>
                
                <select name="genre" class="filter-select" onchange="this.form.submit()">
                    <option value="">Tüm Türler</option>
                    <?php foreach ($all_genres as $g): ?>
                        <option value="<?php echo $g['genre_id']; ?>" 
                                <?php echo $genre == $g['genre_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g['genre_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="year" class="filter-select" onchange="this.form.submit()">
                    <option value="">Tüm Yıllar</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>
                        En Popüler
                    </option>
                    <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>
                        En Yüksek Puan
                    </option>
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>
                        En Yeni
                    </option>
                    <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>
                        En Eski
                    </option>
                    <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>
                        İsme Göre (A-Z)
                    </option>
                </select>

                <?php if ($genre || $year): ?>
                    <a href="movies.php" class="btn-clear">Temizle</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="results-header">
        <div class="results-count">
            Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?>
        </div>
    </div>

    <?php if (empty($movies)): ?>
        <div style="text-align: center; padding: 80px 20px; color: #999;">
            <p style="font-size: 5em;">🎬</p>
            <h2 style="font-size: 2em; color: #666; margin-bottom: 10px;">Film bulunamadı</h2>
            <p>Filtrelerinizi değiştirerek tekrar deneyin.</p>
        </div>
    <?php else: ?>
        <div class="movies-grid">
            <?php foreach ($movies as $movie): ?>
                <a href="movie.php?id=<?php echo $movie['movie_id']; ?>" class="movie-card">
                    <div class="movie-poster">
                        <?php if ($movie['poster_url']): ?>
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            🎬
                        <?php endif; ?>
                        <?php if ($movie['view_count'] > 1000): ?>
                            <span class="movie-badge">🔥 Popüler</span>
                        <?php endif; ?>
                    </div>
                    <div class="movie-info">
                        <div class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                        <div class="movie-meta">
                            <span><?php echo $movie['release_year']; ?></span>
                            <div class="movie-rating">
                                ⭐ <?php echo number_format($movie['avg_rating'], 1); ?>
                                <span style="color: #999; font-weight: normal;">
                                    (<?php echo $movie['rating_count']; ?>)
                                </span>
                            </div>
                        </div>
                        <div class="movie-genres">
                            <?php echo $movie['genres'] ?: 'Tür belirtilmemiş'; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <a href="?page=1<?php echo $genre ? '&genre='.$genre : ''; ?><?php echo $year ? '&year='.$year : ''; ?><?php echo '&sort='.$sort; ?>" 
                   class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                    ⏮️ İlk
                </a>
                
                <a href="?page=<?php echo max(1, $page-1); ?><?php echo $genre ? '&genre='.$genre : ''; ?><?php echo $year ? '&year='.$year : ''; ?><?php echo '&sort='.$sort; ?>" 
                   class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                    ◀️ Önceki
                </a>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $genre ? '&genre='.$genre : ''; ?><?php echo $year ? '&year='.$year : ''; ?><?php echo '&sort='.$sort; ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <a href="?page=<?php echo min($total_pages, $page+1); ?><?php echo $genre ? '&genre='.$genre : ''; ?><?php echo $year ? '&year='.$year : ''; ?><?php echo '&sort='.$sort; ?>" 
                   class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                    Sonraki ▶️
                </a>

                <a href="?page=<?php echo $total_pages; ?><?php echo $genre ? '&genre='.$genre : ''; ?><?php echo $year ? '&year='.$year : ''; ?><?php echo '&sort='.$sort; ?>" 
                   class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                    Son ⏭️
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>