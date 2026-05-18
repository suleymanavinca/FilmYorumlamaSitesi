<?php
// admin/reviews.php - İncelemeler Yönetimi
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$page_title = 'İnceleme Yönetimi';

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
        r.*,
        u.username,
        m.title as movie_title,
        m.movie_id
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN movies m ON r.movie_id = m.movie_id
    ORDER BY r.review_date DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$per_page, $offset]);
$reviews = $stmt->fetchAll();

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

    .reviews-table {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
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
    }

    .data-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .review-content-preview {
        max-width: 400px;
        color: #666;
        line-height: 1.5;
    }

    .action-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9em;
        margin: 0 3px;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-view {
        background: #10b981;
        color: white;
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .action-btn:hover {
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
    }

    .page-link:hover,
    .page-link.active {
        background: #764ba2;
        color: white;
        border-color: #764ba2;
    }
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>📝 İnceleme Yönetimi</h1>
        <p>Toplam <?php echo number_format($total_reviews); ?> inceleme</p>
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="movies.php">Filmler</a>
            <a href="users.php">Kullanıcılar</a>
            <a href="reviews.php" class="active">İncelemeler</a>
            <a href="forum.php">Forum</a>
            <a href="../index.php">Siteye Dön</a>
        </div>
    </div>

    <div class="reviews-table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı</th>
                    <th>Film</th>
                    <th>İnceleme</th>
                    <th>Beğeni</th>
                    <th>Tarih</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <td><strong>#<?php echo $review['review_id']; ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($review['username']); ?></strong></td>
                    <td>
                        <a href="../movie.php?id=<?php echo $review['movie_id']; ?>" 
                           style="color: #764ba2; text-decoration: none; font-weight: 600;">
                            <?php echo htmlspecialchars($review['movie_title']); ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($review['title']): ?>
                            <strong><?php echo htmlspecialchars($review['title']); ?></strong><br>
                        <?php endif; ?>
                        <div class="review-content-preview">
                            <?php 
                            $content = htmlspecialchars($review['content']);
                            echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                            ?>
                        </div>
                    </td>
                    <td>
                        👍 <?php echo $review['likes_count']; ?><br>
                        👎 <?php echo $review['dislikes_count']; ?>
                    </td>
                    <td><?php echo date('d.m.Y H:i', strtotime($review['review_date'])); ?></td>
                    <td>
                        <a href="../movie.php?id=<?php echo $review['movie_id']; ?>#review-<?php echo $review['review_id']; ?>" 
                           class="action-btn btn-view" target="_blank">
                            Görüntüle
                        </a>
                        <button class="action-btn btn-delete" 
                                onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                            Sil
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>" class="page-link">◀ Önceki</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>" class="page-link">Sonraki ▶</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
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
            const response = await fetch('admin_actions.php', {
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

<?php include '../footer.php'; ?>