<?php
// admin/admin_actions.php - Admin İşlemleri Backend

require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'Yetkisiz erişim!');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Geçersiz istek!');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, 'Geçersiz token!');
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_movie':
        addMovie();
        break;
    case 'update_movie':
        updateMovie();
        break;
    case 'delete_movie':
        deleteMovie();
        break;
    case 'delete_review':
        deleteReview();
        break;
    case 'update_user':
        updateUser();
        break;
    case 'delete_user':
        deleteUser();
        break;
    case 'toggle_user_status':
        toggleUserStatus();
        break;
    default:
        jsonResponse(false, 'Geçersiz işlem!');
}

// Film Ekle
function addMovie() {
    global $pdo;
    
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
        jsonResponse(false, 'Film adı ve yıl gereklidir!');
    }
    
    if (empty($genres)) {
        jsonResponse(false, 'En az bir tür seçmelisiniz!');
    }
    
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
        
        jsonResponse(true, 'Film başarıyla eklendi!', ['movie_id' => $movie_id]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        jsonResponse(false, 'Film eklenirken bir hata oluştu!');
    }
}

// Film Güncelle
function updateMovie() {
    global $pdo;
    
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $original_title = sanitize($_POST['original_title'] ?? '');
    $director = sanitize($_POST['director'] ?? '');
    $release_year = (int)($_POST['release_year'] ?? 0);
    $duration = (int)($_POST['duration'] ?? 0);
    $synopsis = sanitize($_POST['synopsis'] ?? '');
    $poster_url = sanitize($_POST['poster_url'] ?? '');
    $trailer_url = sanitize($_POST['trailer_url'] ?? '');
    $genres = $_POST['genres'] ?? [];
    
    if (!$movie_id || empty($title) || !$release_year) {
        jsonResponse(false, 'Gerekli alanları doldurun!');
    }
    
    if (empty($genres)) {
        jsonResponse(false, 'En az bir tür seçmelisiniz!');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Film güncelle
        $stmt = $pdo->prepare("
            UPDATE movies 
            SET title = ?, original_title = ?, director = ?, release_year = ?, 
                duration = ?, synopsis = ?, poster_url = ?, trailer_url = ?
            WHERE movie_id = ?
        ");
        $stmt->execute([
            $title, $original_title, $director, $release_year, 
            $duration ?: null, $synopsis, $poster_url, $trailer_url, $movie_id
        ]);
        
        // Mevcut türleri sil
        $stmt = $pdo->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
        $stmt->execute([$movie_id]);
        
        // Yeni türleri ekle
        $stmt = $pdo->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
        foreach ($genres as $genre_id) {
            $stmt->execute([$movie_id, (int)$genre_id]);
        }
        
        $pdo->commit();
        
        jsonResponse(true, 'Film başarıyla güncellendi!');
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        jsonResponse(false, 'Film güncellenirken bir hata oluştu!');
    }
}

// Film Sil
function deleteMovie() {
    global $pdo;
    
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    
    if (!$movie_id) {
        jsonResponse(false, 'Geçersiz film!');
    }
    
    try {
        // CASCADE ile ilgili tüm veriler otomatik silinecek
        $stmt = $pdo->prepare("DELETE FROM movies WHERE movie_id = ?");
        $stmt->execute([$movie_id]);
        
        jsonResponse(true, 'Film silindi!');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Film silinirken bir hata oluştu!');
    }
}

// İnceleme Sil
function deleteReview() {
    global $pdo;
    
    $review_id = (int)($_POST['review_id'] ?? 0);
    
    if (!$review_id) {
        jsonResponse(false, 'Geçersiz inceleme!');
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
        $stmt->execute([$review_id]);
        
        jsonResponse(true, 'İnceleme silindi!');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'İnceleme silinirken bir hata oluştu!');
    }
}

// Kullanıcı Güncelle
function updateUser() {
    global $pdo;
    
    $user_id = (int)($_POST['user_id'] ?? 0);
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    if (!$user_id || empty($username) || empty($email)) {
        jsonResponse(false, 'Gerekli alanları doldurun!');
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = ?, email = ?, is_admin = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$username, $email, $is_admin, $user_id]);
        
        jsonResponse(true, 'Kullanıcı güncellendi!');
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(false, 'Bu kullanıcı adı veya e-posta zaten kullanılıyor!');
        }
        jsonResponse(false, 'Kullanıcı güncellenirken bir hata oluştu!');
    }
}

// Kullanıcı Sil
function deleteUser() {
    global $pdo;
    
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if (!$user_id) {
        jsonResponse(false, 'Geçersiz kullanıcı!');
    }
    
    // Kendi hesabını silmeye çalışıyor mu?
    if ($user_id == $_SESSION['user_id']) {
        jsonResponse(false, 'Kendi hesabınızı silemezsiniz!');
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        jsonResponse(true, 'Kullanıcı silindi!');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Kullanıcı silinirken bir hata oluştu!');
    }
}

// Kullanıcı Durumunu Değiştir (Aktif/Pasif)
function toggleUserStatus() {
    global $pdo;
    
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if (!$user_id) {
        jsonResponse(false, 'Geçersiz kullanıcı!');
    }
    
    if ($user_id == $_SESSION['user_id']) {
        jsonResponse(false, 'Kendi hesabınızın durumunu değiştiremezsiniz!');
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $status = $stmt->fetchColumn();
        
        jsonResponse(true, 'Kullanıcı durumu değiştirildi!', ['is_active' => $status]);
        
    } catch (PDOException $e) {
        jsonResponse(false, 'İşlem sırasında bir hata oluştu!');
    }
}
?>