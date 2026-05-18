<?php
// admin/dashboard.php - Admin Panel Ana Sayfa
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$page_title = 'Admin Paneli';

// İstatistikler
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_movies' => $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn(),
    'total_reviews' => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
    'total_ratings' => $pdo->query("SELECT COUNT(*) FROM ratings")->fetchColumn(),
    'new_users_today' => $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(registration_date) = CURDATE()")->fetchColumn(),
    'new_reviews_today' => $pdo->query("SELECT COUNT(*) FROM reviews WHERE DATE(review_date) = CURDATE()")->fetchColumn()
];

// Son eklenen kullanıcılar
$stmt = $pdo->query("
    SELECT user_id, username, email, registration_date, is_active 
    FROM users 
    ORDER BY registration_date DESC 
    LIMIT 10
");
$recent_users = $stmt->fetchAll();

// Son incelemeler
$stmt = $pdo->query("
    SELECT 
        r.review_id, r.content, r.review_date,
        u.username,
        m.title as movie_title
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN movies m ON r.movie_id = m.movie_id
    ORDER BY r.review_date DESC
    LIMIT 10
");
$recent_reviews = $stmt->fetchAll();

// En çok izlenen filmler
$stmt = $pdo->query("
    SELECT movie_id, title, view_count, release_year
    FROM movies
    ORDER BY view_count DESC
    LIMIT 5
");
$top_movies = $stmt->fetchAll();

include '../header.php';
?>

<style>
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px;
    }

    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .admin-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .admin-nav {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .admin-nav a {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .admin-nav a:hover,
    .admin-nav a.active {
        background: white;
        color: #764ba2;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2em;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-info {
        flex: 1;
    }

    .stat-label {
        color: #999;
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 2em;
        font-weight: bold;
        color: #333;
    }

    .stat-change {
        font-size: 0.85em;
        color: #10b981;
        margin-top: 5px;
    }

    .admin-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 1.5em;
        color: #764ba2;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #666;
        border-bottom: 2px solid #e0e0e0;
    }

    .data-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee;
        color: #c33;
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85em;
        margin: 0 3px;
        transition: all 0.2s;
    }

    .btn-edit {
        background: #3b82f6;
        color: white;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-view {
        background: #10b981;
        color: white;
    }

    .action-btn:hover {
        opacity: 0.8;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .data-table {
            font-size: 0.9em;
        }

        .data-table th,
        .data-table td {
            padding: 8px;
        }
    }
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>🔧 Admin Paneli</h1>
        <p>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <div class="admin-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="movies.php">Filmler</a>
            <a href="users.php">Kullanıcılar</a>
            <a href="reviews.php">İncelemeler</a>
            <a href="forum.php">Forum</a>
            <a href="settings.php">Ayarlar</a>
            <a href="../index.php">Siteye Dön</a>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <div class="stat-label">Toplam Kullanıcı</div>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-change">+<?php echo $stats['new_users_today']; ?> bugün</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎬</div>
            <div class="stat-info">
                <div class="stat-label">Toplam Film</div>
                <div class="stat-value"><?php echo number_format($stats['total_movies']); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-info">
                <div class="stat-label">Toplam İnceleme</div>
                <div class="stat-value"><?php echo number_format($stats['total_reviews']); ?></div>
                <div class="stat-change">+<?php echo $stats['new_reviews_today']; ?> bugün</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-info">
                <div class="stat-label">Toplam Puan</div>
                <div class="stat-value"><?php echo number_format($stats['total_ratings']); ?></div>
            </div>
        </div>
    </div>

    <!-- Son Kullanıcılar -->
    <div class="admin-section">
        <h2 class="section-title">👥 Son Kayıt Olan Kullanıcılar</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Kayıt Tarihi</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_users as $user): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($user['registration_date'])); ?></td>
                    <td>
                        <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $user['is_active'] ? 'Aktif' : 'Pasif'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="action-btn btn-view" onclick="viewUser(<?php echo $user['user_id']; ?>)">
                            Görüntüle
                        </button>
                        <button class="action-btn btn-edit" onclick="editUser(<?php echo $user['user_id']; ?>)">
                            Düzenle
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Son İncelemeler -->
    <div class="admin-section">
        <h2 class="section-title">📝 Son İncelemeler</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>Film</th>
                    <th>İnceleme</th>
                    <th>Tarih</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_reviews as $review): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($review['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($review['movie_title']); ?></td>
                    <td><?php echo substr(htmlspecialchars($review['content']), 0, 80) . '...'; ?></td>
                    <td><?php echo date('d.m.Y', strtotime($review['review_date'])); ?></td>
                    <td>
                        <button class="action-btn btn-view" onclick="viewReview(<?php echo $review['review_id']; ?>)">
                            Görüntüle
                        </button>
                        <button class="action-btn btn-delete" onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                            Sil
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- En Popüler Filmler -->
    <div class="admin-section">
        <h2 class="section-title">🔥 En Çok İzlenen Filmler</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Film Adı</th>
                    <th>Yıl</th>
                    <th>Görüntülenme</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_movies as $movie): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($movie['title']); ?></strong></td>
                    <td><?php echo $movie['release_year']; ?></td>
                    <td><?php echo number_format($movie['view_count']); ?></td>
                    <td>
                        <button class="action-btn btn-view" onclick="window.location.href='../movie.php?id=<?php echo $movie['movie_id']; ?>'">
                            Görüntüle
                        </button>
                        <button class="action-btn btn-edit" onclick="editMovie(<?php echo $movie['movie_id']; ?>)">
                            Düzenle
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function viewUser(userId) {
        window.location.href = 'users.php?view=' + userId;
    }

    function editUser(userId) {
        window.location.href = 'users.php?edit=' + userId;
    }

    function viewReview(reviewId) {
        window.location.href = 'reviews.php?view=' + reviewId;
    }

    function deleteReview(reviewId) {
        if (confirm('Bu incelemeyi silmek istediğinize emin misiniz?')) {
            // AJAX ile silme işlemi
            const formData = new FormData();
            formData.append('action', 'delete_review');
            formData.append('review_id', reviewId);
            formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

            fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('İnceleme silindi!');
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    }

    function editMovie(movieId) {
        window.location.href = 'movies.php?edit=' + movieId;
    }
</script>

<?php include '../footer.php'; ?>