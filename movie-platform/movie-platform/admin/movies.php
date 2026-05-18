<?php
// admin/movies.php - Film Yönetimi
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$page_title = 'Film Yönetimi';

// Sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Arama
$search = $_GET['search'] ?? '';
$search_sql = '';
$search_params = [];

if ($search) {
    $search_sql = " WHERE title LIKE ? OR director LIKE ?";
    $search_term = "%$search%";
    $search_params = [$search_term, $search_term];
}

// Toplam film sayısı
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM movies" . $search_sql);
$count_stmt->execute($search_params);
$total_movies = $count_stmt->fetchColumn();
$total_pages = ceil($total_movies / $per_page);

// Filmleri çek
$stmt = $pdo->prepare("
    SELECT 
        m.*,
        u.username as added_by_username,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(DISTINCT r.rating_id) as rating_count,
        GROUP_CONCAT(DISTINCT g.genre_name SEPARATOR ', ') as genres
    FROM movies m
    LEFT JOIN users u ON m.added_by = u.user_id
    LEFT JOIN ratings r ON m.movie_id = r.movie_id
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    $search_sql
    GROUP BY m.movie_id
    ORDER BY m.added_date DESC
    LIMIT ? OFFSET ?
");

$params = array_merge($search_params, [$per_page, $offset]);
$stmt->execute($params);
$movies = $stmt->fetchAll();

include '../header.php';
?>

<style>
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .btn-add {
        background: white;
        color: #764ba2;
        padding: 12px 25px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255,255,255,0.3);
    }

    .search-bar {
        background: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .search-form {
        display: flex;
        gap: 15px;
    }

    .search-input {
        flex: 1;
        padding: 12px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 1em;
    }

    .search-input:focus {
        outline: none;
        border-color: #9d7cce;
    }

    .btn-search {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
    }

    .movies-table {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #666;
        border-bottom: 2px solid #e0e0e0;
        white-space: nowrap;
    }

    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .movie-thumb {
        width: 60px;
        height: 90px;
        object-fit: cover;
        border-radius: 8px;
        background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2em;
    }

    .movie-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .movie-meta {
        font-size: 0.85em;
        color: #999;
    }

    .rating-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 5px 12px;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.9em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .action-btns {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 15px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9em;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-view {
        background: #10b981;
        color: white;
    }

    .btn-edit {
        background: #3b82f6;
        color: white;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-action:hover {
        opacity: 0.8;
        transform: translateY(-2px);
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

    .page-link.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
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

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }

        .search-form {
            flex-direction: column;
        }

        .data-table {
            font-size: 0.85em;
        }

        .action-btns {
            flex-direction: column;
        }
    }
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="header-left">
            <div style="margin-bottom: 15px;">
                <a href="dashboard.php" style="color: rgba(255,255,255,0.8); text-decoration: none;">← Admin Panel</a>
            </div>
            <h1>🎬 Film Yönetimi</h1>
            <p>Toplam <?php echo number_format($total_movies); ?> film</p>
        </div>
        <a href="movie_add.php" class="btn-add">
            ➕ Yeni Film Ekle
        </a>
    </div>

    <!-- Arama -->
    <div class="search-bar">
        <form method="GET" class="search-form">
            <input type="text" name="search" class="search-input" 
                   placeholder="Film adı veya yönetmen ara..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn-search">🔍 Ara</button>
            <?php if ($search): ?>
                <a href="movies.php" class="btn-search" style="background: #6c757d; text-decoration: none;">
                    Temizle
                </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($movies)): ?>
        <div class="movies-table">
            <div class="empty-state">
                <div class="empty-icon">🎬</div>
                <h2>Film bulunamadı</h2>
                <p><?php echo $search ? 'Aramanızla eşleşen film yok.' : 'Henüz film eklenmemiş.'; ?></p>
                <a href="movie_add.php" style="margin-top: 20px; display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 600;">
                    İlk Filmi Ekle
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="movies-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Film Bilgileri</th>
                        <th>Yönetmen</th>
                        <th>Yıl</th>
                        <th>Türler</th>
                        <th>Puan</th>
                        <th>Görüntülenme</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td>
                            <div class="movie-thumb">
                                <?php if ($movie['poster_url']): ?>
                                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    🎬
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                            <div class="movie-meta">
                                <?php if ($movie['original_title']): ?>
                                    <?php echo htmlspecialchars($movie['original_title']); ?>
                                <?php endif; ?>
                                <?php if ($movie['duration']): ?>
                                    • <?php echo $movie['duration']; ?> dk
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($movie['director']); ?></td>
                        <td><?php echo $movie['release_year']; ?></td>
                        <td style="max-width: 150px;">
                            <small><?php echo $movie['genres'] ?: '-'; ?></small>
                        </td>
                        <td>
                            <span class="rating-badge">
                                ⭐ <?php echo number_format($movie['avg_rating'], 1); ?>
                                <small style="opacity: 0.7;">(<?php echo $movie['rating_count']; ?>)</small>
                            </span>
                        </td>
                        <td><?php echo number_format($movie['view_count']); ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="../movie.php?id=<?php echo $movie['movie_id']; ?>" 
                                   class="btn-action btn-view" target="_blank">
                                    👁️
                                </a>
                                <a href="movie_edit.php?id=<?php echo $movie['movie_id']; ?>" 
                                   class="btn-action btn-edit">
                                    ✏️
                                </a>
                                <button class="btn-action btn-delete" 
                                        onclick="deleteMovie(<?php echo $movie['movie_id']; ?>, '<?php echo htmlspecialchars(addslashes($movie['title'])); ?>')">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <a href="?page=1<?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                        ⏮️ İlk
                    </a>
                    
                    <a href="?page=<?php echo max(1, $page-1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                        ◀️ Önceki
                    </a>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?page=<?php echo min($total_pages, $page+1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                        Sonraki ▶️
                    </a>

                    <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                        Son ⏭️
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    async function deleteMovie(movieId, movieTitle) {
        if (!confirm(`"${movieTitle}" filmini silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz!`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_movie');
        formData.append('movie_id', movieId);
        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

        try {
            const response = await fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Film silindi!');
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('Bir hata oluştu!');
        }
    }
</script>

<?php include '../footer.php'; ?>