<?php
// admin/users.php - Kullanıcı Yönetimi
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$page_title = 'Kullanıcılar';

// Kullanıcıları çek
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(DISTINCT r.review_id) as review_count,
               COUNT(DISTINCT rat.rating_id) as rating_count
        FROM users u
        LEFT JOIN reviews r ON u.user_id = r.user_id
        LEFT JOIN ratings rat ON u.user_id = rat.user_id
        GROUP BY u.user_id
        ORDER BY u.registration_date DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $error = "Kullanıcılar yüklenemedi: " . $e->getMessage();
}

include '../header.php';
?>

<style>
.users-admin { padding: 40px 20px; max-width: 1400px; margin: 0 auto; }
.users-table { background: white; border-radius: 16px; box-shadow: var(--shadow); overflow: hidden; margin-top: 30px; }
</style>

<div class="users-admin">
    <h1 style="font-size: 2.5rem; margin-bottom: 8px;">👥 Kullanıcılar</h1>
    <p style="color: var(--gray); margin-bottom: 30px;">Toplam <?php echo count($users); ?> kullanıcı</p>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="users-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Kayıt Tarihi</th>
                    <th>İnceleme</th>
                    <th>Puan</th>
                    <th>Rol</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            <?php if ($user['full_name']): ?>
                                <div style="font-size:0.85rem;color:var(--gray);margin-top:2px;">
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($user['registration_date'])); ?></td>
                        <td><?php echo $user['review_count']; ?></td>
                        <td><?php echo $user['rating_count']; ?></td>
                        <td>
                            <?php if ($user['is_admin']): ?>
                                <span class="badge" style="background:#fef3c7;color:#92400e;">👑 Admin</span>
                            <?php else: ?>
                                <span class="badge" style="background:var(--light);color:var(--gray);">Üye</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success">✅ Aktif</span>
                            <?php else: ?>
                                <span class="badge" style="background:#fee2e2;color:#991b1b;">❌ Pasif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>