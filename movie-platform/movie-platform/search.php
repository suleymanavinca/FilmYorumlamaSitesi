<?php
// search.php - Film Arama Sayfası
require_once 'config.php';

$page_title = 'Film Ara';

$query = $_GET['q'] ?? '';
$genre = $_GET['genre'] ?? '';
$year = $_GET['year'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';

$results = [];
$total_results = 0;

if ($query || $genre || $year) {
    $sql = "
        SELECT DISTINCT
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
    
    if ($query) {
        $sql .= " AND (m.title LIKE ? OR m.original_title LIKE ? OR m.director LIKE ? OR m.synopsis LIKE ?)";
        $search_term = "%$query%";
        $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    }
    
    if ($genre) {
        $sql .= " AND mg.genre_id = ?";
        $params[] = $genre;
    }
    
    if ($year) {
        $sql .= " AND m.release_year = ?";
        $params[] = $year;
    }
    
    $sql .= " GROUP BY m.movie_id";
    
    // Sıralama
    switch ($sort) {
        case 'rating':
            $sql .= " ORDER BY avg_rating DESC, rating_count DESC";
            break;
        case 'year_desc':
            $sql .= " ORDER BY m.release_year DESC";
            break;
        case 'year_asc':
            $sql .= " ORDER BY m.release_year ASC";
            break;
        case 'title':
            $sql .= " ORDER BY m.title ASC";
            break;
        default:
            $sql .= " ORDER BY m.view_count DESC, avg_rating DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    $total_results = count($results);
}

// Tüm türler
$stmt = $pdo->query("SELECT * FROM genres ORDER BY genre_name");
$all_genres = $stmt->fetchAll();

// Yıllar (son 50 yıl)
$current_year = date('Y');
$years = range($current_year, $current_year - 50);

include 'header.php';
?>

<style>
    .search-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px;
    }

    .search-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .search-header h1 {
        font-size: 2.5em;
        margin-bottom: 20px;
    }

    .search-form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .search-input-wrapper {
        flex: 1;
        min-width: 300px;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: none;
        border-radius: 12px;
        font-size: 1.1em;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .search-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        cursor: pointer;
    }

    .filter-section {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .filter-title {
        font-size: 1.2em;
        font-weight: 600;
        color: #764ba2;
        margin-bottom: 15px;
    }

    .filter-options {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        cursor: pointer;
        background: white;
    }

    .filter-select:focus {
        outline: none;
        border-color: #9d7cce;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .results-count {
        font-size: 1.3em;
        color: #333;
    }

    .results-count strong {
        color: #764ba2;
    }

    .sort-options {
        display: flex;
        gap: 10px;
        align-items: center;
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
        cursor: pointer;
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
    }

    .no-results {
        text-align: center;
        padding: 80px 20px;
        color: #999;
    }

    .no-results-icon {
        font-size: 5em;
        margin-bottom: 20px;
    }

    .no-results h2 {
        font-size: 2em;
        color: #666;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .search-form {
            flex-direction: column;
        }

        .search-input-wrapper {
            min-width: 100%;
        }

        .filter-options {
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

<div class="search-container">
    <div class="search-header">
        <h1>🔍 Film Ara</h1>
        <form class="search-form" method="GET" action="search.php">
            <div class="search-input-wrapper">
                <input type="text" name="q" class="search-input" 
                       placeholder="Film adı, yönetmen veya açıklama ara..." 
                       value="<?php echo htmlspecialchars($query); ?>">
                <button type="submit" class="search-btn">Ara</button>
            </div>
        </form>
    </div>

    <div class="filter-section">
        <div class="filter-title">🎯 Filtreler</div>
        <form method="GET" action="search.php">
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($query); ?>">
            <div class="filter-options">
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
                    <option value="relevance" <?php echo $sort == 'relevance' ? 'selected' : ''; ?>>
                        İlgili Olanlar
                    </option>
                    <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>
                        En Yüksek Puan
                    </option>
                    <option value="year_desc" <?php echo $sort == 'year_desc' ? 'selected' : ''; ?>>
                        Yıl (Yeni → Eski)
                    </option>
                    <option value="year_asc" <?php echo $sort == 'year_asc' ? 'selected' : ''; ?>>
                        Yıl (Eski → Yeni)
                    </option>
                    <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>
                        İsme Göre (A-Z)
                    </option>
                </select>

                <?php if ($genre || $year || ($query && ($genre || $year))): ?>
                    <a href="search.php?q=<?php echo urlencode($query); ?>" 
                       style="padding: 10px 20px; background: #ef4444; color: white; border-radius: 8px; text-decoration: none;">
                        Filtreleri Temizle
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($query || $genre || $year): ?>
        <div class="results-header">
            <div class="results-count">
                <strong><?php echo $total_results; ?></strong> sonuç bulundu
            </div>
        </div>

        <?php if (empty($results)): ?>
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h2>Sonuç Bulunamadı</h2>
                <p>Aramanızla eşleşen film bulunamadı. Farklı anahtar kelimeler deneyin.</p>
            </div>
        <?php else: ?>
            <div class="movies-grid">
                <?php foreach ($results as $movie): ?>
                    <a href="movie.php?id=<?php echo $movie['movie_id']; ?>" class="movie-card">
                        <div class="movie-poster">
                            <?php if ($movie['poster_url']): ?>
                                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                🎬
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
                                <?php echo $movie['genres'] ?: 'Tür yok'; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-results">
            <div class="no-results-icon">🎬</div>
            <h2>Arama Yapmaya Başlayın</h2>
            <p>Yukarıdaki arama kutusunu kullanarak istediğiniz filmleri bulabilirsiniz.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>