<?php
// admin/movie_add.php - Film Ekleme Sayfası
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$page_title = 'Yeni Film Ekle';

// Tüm türleri çek
$stmt = $pdo->query("SELECT * FROM genres ORDER BY genre_name ASC");
$all_genres = $stmt->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Geçersiz istek!';
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $original_title = sanitize($_POST['original_title'] ?? '');
        $director = sanitize($_POST['director'] ?? '');
        $release_year = (int)($_POST['release_year'] ?? 0);
        $duration = (int)($_POST['duration'] ?? 0);
        $synopsis = sanitize($_POST['synopsis'] ?? '');
        $poster_url = sanitize($_POST['poster_url'] ?? '');
        $trailer_url = sanitize($_POST['trailer_url'] ?? '');
        $genres = $_POST['genres'] ?? [];
        
        if (empty($title) || !$release_year) {
            $error = 'Film adı ve yıl gereklidir!';
        } elseif (empty($genres)) {
            $error = 'En az bir tür seçmelisiniz!';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Film ekle
                $stmt = $pdo->prepare("
                    INSERT INTO movies (title, original_title, director, release_year, duration, synopsis, poster_url, trailer_url, added_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $original_title, $director, $release_year, 
                    $duration ?: null, $synopsis, $poster_url, $trailer_url, $_SESSION['user_id']
                ]);
                
                $movie_id = $pdo->lastInsertId();
                
                // Türleri ekle
                $stmt = $pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                foreach ($genres as $genre_id) {
                    $stmt->execute([$movie_id, (int)$genre_id]);
                }
                
                $pdo->commit();
                
                $success = 'Film başarıyla eklendi!';
                
                // Formu temizle
                $_POST = [];
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Film eklenirken bir hata oluştu: ' . $e->getMessage();
            }
        }
    }
}

// Yıllar listesi (şimdiki yıldan 100 yıl geriye)
$current_year = date('Y');
$years = range($current_year + 1, $current_year - 100);

include '../header.php';
?>

<style>
    .admin-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 30px;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .breadcrumb {
        margin-bottom: 20px;
    }

    .breadcrumb a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        margin-right: 10px;
    }

    .breadcrumb a:hover {
        color: white;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .alert-error {
        background: #fee;
        color: #c33;
        border: 1px solid #fcc;
    }

    .form-container {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 1.3em;
        color: #764ba2;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }

    .form-label.required::after {
        content: ' *';
        color: #ef4444;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1em;
        font-family: inherit;
        transition: all 0.3s;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #9d7cce;
        box-shadow: 0 0 0 4px rgba(157, 124, 206, 0.1);
    }

    .form-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .genre-checkboxes {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .checkbox-item:hover {
        background: #e9ecef;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-item label {
        cursor: pointer;
        user-select: none;
    }

    .form-help {
        font-size: 0.85em;
        color: #999;
        margin-top: 5px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 1em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(118, 75, 162, 0.3);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .genre-checkboxes {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-container">
    <div class="page-header">
        <div class="breadcrumb">
            <a href="dashboard.php">← Admin Panel</a>
            <a href="movies.php">Filmler</a>
            <span>→ Yeni Film Ekle</span>
        </div>
        <h1>🎬 Yeni Film Ekle</h1>
        <p>Film veritabanına yeni bir film ekleyin</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            ✓ <?php echo $success; ?>
            <a href="movies.php" style="margin-left: 15px; color: #065f46; font-weight: 600;">Filmlere Dön</a>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <!-- Temel Bilgiler -->
            <div class="form-section">
                <h2 class="section-title">📋 Temel Bilgiler</h2>

                <div class="form-group">
                    <label class="form-label required">Film Adı</label>
                    <input type="text" name="title" class="form-input" required 
                           placeholder=""
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Orijinal Adı</label>
                    <input type="text" name="original_title" class="form-input" 
                           placeholder=""
                           value="<?php echo htmlspecialchars($_POST['original_title'] ?? ''); ?>">
                    <div class="form-help"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Yönetmen</label>
                        <input type="text" name="director" class="form-input" required
                               placeholder=""
                               value="<?php echo htmlspecialchars($_POST['director'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Yapım Yılı</label>
                        <select name="release_year" class="form-select" required>
                            <option value="">Yıl seçin</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>" 
                                    <?php echo (isset($_POST['release_year']) && $_POST['release_year'] == $year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Süre (dakika)</label>
                    <input type="number" name="duration" class="form-input" 
                           placeholder=""
                           value="<?php echo htmlspecialchars($_POST['duration'] ?? ''); ?>"
                           min="1" max="500">
                </div>
            </div>

            <!-- Türler -->
            <div class="form-section">
                <h2 class="section-title">🎭 Türler</h2>
                <div class="genre-checkboxes">
                    <?php foreach ($all_genres as $genre): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" 
                                   name="genres[]" 
                                   value="<?php echo $genre['genre_id']; ?>"
                                   id="genre_<?php echo $genre['genre_id']; ?>"
                                   <?php echo (isset($_POST['genres']) && in_array($genre['genre_id'], $_POST['genres'])) ? 'checked' : ''; ?>>
                            <label for="genre_<?php echo $genre['genre_id']; ?>">
                                <?php echo htmlspecialchars($genre['genre_name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-help">En az bir tür seçmelisiniz</div>
            </div>

            <!-- Açıklama -->
            <div class="form-section">
                <h2 class="section-title">📝 Açıklama</h2>
                <div class="form-group">
                    <label class="form-label">Film Özeti</label>
                    <textarea name="synopsis" class="form-textarea" 
                              placeholder="Filmin konusunu kısaca açıklayın..."><?php echo htmlspecialchars($_POST['synopsis'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Medya -->
            <div class="form-section">
                <h2 class="section-title">🖼️ Medya</h2>

                <div class="form-group">
                    <label class="form-label">Poster URL</label>
                    <input type="url" name="poster_url" class="form-input" 
                           placeholder="https://example.com/poster.jpg"
                           value="<?php echo htmlspecialchars($_POST['poster_url'] ?? ''); ?>">
                    <div class="form-help">Filmin afişinin URL adresi (IMDb veya başka bir kaynaktan)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Fragman URL</label>
                    <input type="url" name="trailer_url" class="form-input" 
                           placeholder="https://www.youtube.com/watch?v=..."
                           value="<?php echo htmlspecialchars($_POST['trailer_url'] ?? ''); ?>">
                    <div class="form-help">YouTube fragman linki</div>
                </div>
            </div>

            <!-- Form Butonları -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Film Ekle</button>
                <a href="movies.php" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>