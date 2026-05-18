<?php
// admin/users.php - Kullanıcılar Yönetimi
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$page_title = 'Kullanıcı Yönetimi';

// Sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Arama ve filtreleme
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';

$where = [];
$params = [];

if ($search) {
    $where[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($status === 'active') {
    $where[] = "is_active = 1";
} elseif ($status === 'inactive') {
    $where[] = "is_active = 0";
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Toplam kullanıcı sayısı
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $per_page);

// Kullanıcıları çek
$sql = "
    SELECT 
        u.*,
        (SELECT COUNT(*) FROM reviews WHERE user_id = u.user_id) as review_count,
        (SELECT COUNT(*) FROM ratings WHERE user_id = u.user_id) as rating_count
    FROM users u
    $where_clause
    ORDER BY u.registration_date DESC
    LIMIT ? OFFSET ?
";

$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

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
        align-items: center;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 300px;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
    }

    .filter-select {
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .users-table {
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

    .badge-admin {
        background: #dbeafe;
        color: #1e40af;
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

    .btn-edit {
        background: #3b82f6;
        color: white;
    }

    .btn-toggle {
        background: #f59e0b;
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
        <h1>👥 Kullanıcı Yönetimi</h1>
        <p>Toplam <?php echo number_format($total_users); ?> kullanıcı</p>
        <div class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="movies.php">Filmler</a>
            <a href="users.php" class="active">Kullanıcılar</a>
            <a href="reviews.php">İncelemeler</a>
            <a href="forum.php">Forum</a>
            <a href="../index.php">Siteye Dön</a>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="filters-bar">
        <form method="GET" action="users.php">
            <div class="filters-row">
                <input type="text" name="search" class="search-input" 
                       placeholder="Kullanıcı adı, e-posta veya ad soyad ara..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>Tüm Durumlar</option>
                    <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                </select>

                <button type="submit" class="btn-primary">🔍 Ara</button>
                
                <?php if ($search || $status != 'all'): ?>
                    <a href="users.php" class="action-btn btn-delete">Filtreleri Temizle</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Kullanıcılar Tablosu -->
    <div class="users-table">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Ad Soyad</th>
                    <th>İnceleme</th>
                    <th>Puan</th>
                    <th>Kayıt Tarihi</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong>#<?php echo $user['user_id']; ?></strong></td>
                    <td>
                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                        <?php if ($user['is_admin']): ?>
                            <span class="badge badge-admin">Admin</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                    <td><?php echo $user['review_count']; ?></td>
                    <td><?php echo $user['rating_count']; ?></td>
                    <td><?php echo date('d.m.Y', strtotime($user['registration_date'])); ?></td>
                    <td>
                        <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $user['is_active'] ? 'Aktif' : 'Pasif'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="../profile.php?id=<?php echo $user['user_id']; ?>" 
                           class="action-btn btn-view" target="_blank">
                            Görüntüle
                        </a>
                        <button class="action-btn btn-toggle" 
                                onclick="toggleUserStatus(<?php echo $user['user_id']; ?>)">
                            <?php echo $user['is_active'] ? 'Devre Dışı' : 'Aktif Et'; ?>
                        </button>
                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                            <button class="action-btn btn-delete" 
                                    onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                Sil
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($users)): ?>
            <p style="text-align: center; padding: 40px; color: #999;">
                Kullanıcı bulunamadı.
            </p>
        <?php endif; ?>

        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" 
                       class="page-link">◀ Önceki</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" 
                       class="page-link">Sonraki ▶</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    async function toggleUserStatus(userId) {
        if (!confirm('Bu kullanıcının durumunu değiştirmek istediğinize emin misiniz?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'toggle_user_status');
        formData.append('user_id', userId);
        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

        try {
            const response = await fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Kullanıcı durumu değiştirildi!');
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert('Bir hata oluştu!');
        }
    }

    async function deleteUser(userId, username) {
        if (!confirm(`"${username}" kullanıcısını silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz!`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_user');
        formData.append('user_id', userId);
        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

        try {
            const response = await fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Kullanıcı silindi!');
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