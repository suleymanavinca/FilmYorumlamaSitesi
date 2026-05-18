<?php
// movie_actions.php - Film İşlemleri (İnceleme, Puanlama, vb.)

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Geçersiz istek!');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, 'Geçersiz token!');
}

if (!isLoggedIn()) {
    jsonResponse(false, 'Giriş yapmalısınız!');
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_review':
        addReview();
        break;
    case 'rate_movie':
        rateMovie();
        break;
    case 'like_review':
        likeReview();
        break;
    case 'toggle_watchlist':
        toggleWatchlist();
        break;
    case 'add_comment':
        addComment();
        break;
    default:
        jsonResponse(false, 'Geçersiz işlem!');
}

// İnceleme Ekle
function addReview() {
    global $pdo;
    
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $is_spoiler = isset($_POST['is_spoiler']) ? 1 : 0;
    
    if (!$movie_id || empty($content)) {
        jsonResponse(false, 'Gerekli alanları doldurun!');
    }
    
    if (strlen($content) < 10) {
        jsonResponse(false, 'İnceleme en az 10 karakter olmalıdır!');
    }
    
    // Daha önce inceleme yapılmış mı kontrol et
    $stmt = $pdo->prepare("SELECT review_id FROM reviews WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$movie_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, 'Bu film için zaten bir inceleme yazmışsınız!');
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (movie_id, user_id, title, content, is_spoiler) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$movie_id, $_SESSION['user_id'], $title, $content, $is_spoiler]);
        
        // Aktivite kaydı
        logActivity($_SESSION['user_id'], 'review', 'Yeni inceleme ekledi');
        
        jsonResponse(true, 'İncelemeniz başarıyla eklendi!');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'İnceleme eklenirken bir hata oluştu!');
    }
}

// Film Puanla
function rateMovie() {
    global $pdo;
    
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    $rating = (float)($_POST['rating'] ?? 0);
    
    if (!$movie_id || $rating < 1 || $rating > 5) {
        jsonResponse(false, 'Geçersiz puan!');
    }
    
    try {
        // Önce kontrol et, varsa güncelle, yoksa ekle
        $stmt = $pdo->prepare("
            INSERT INTO ratings (movie_id, user_id, rating) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = ?
        ");
        $stmt->execute([$movie_id, $_SESSION['user_id'], $rating, $rating]);
        
        // Yeni ortalama puanı hesapla
        $stmt = $pdo->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as count 
            FROM ratings 
            WHERE movie_id = ?
        ");
        $stmt->execute([$movie_id]);
        $result = $stmt->fetch();
        
        // Aktivite kaydı
        logActivity($_SESSION['user_id'], 'rating', 'Film puanladı');
        
        jsonResponse(true, 'Puanınız kaydedildi!', [
            'avg_rating' => round($result['avg_rating'], 1),
            'rating_count' => $result['count']
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Puanlama sırasında bir hata oluştu!');
    }
}

// İnceleme Beğen/Beğenme
function likeReview() {
    global $pdo;
    
    $review_id = (int)($_POST['review_id'] ?? 0);
    $like_type = $_POST['like_type'] ?? '';
    
    if (!$review_id || !in_array($like_type, ['like', 'dislike'])) {
        jsonResponse(false, 'Geçersiz işlem!');
    }
    
    try {
        // Önce mevcut beğeniyi kontrol et
        $stmt = $pdo->prepare("
            SELECT like_type FROM review_likes 
            WHERE review_id = ? AND user_id = ?
        ");
        $stmt->execute([$review_id, $_SESSION['user_id']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($existing['like_type'] == $like_type) {
                // Aynı işlem, beğeniyi kaldır
                $stmt = $pdo->prepare("
                    DELETE FROM review_likes 
                    WHERE review_id = ? AND user_id = ?
                ");
                $stmt->execute([$review_id, $_SESSION['user_id']]);
            } else {
                // Farklı işlem, güncelle
                $stmt = $pdo->prepare("
                    UPDATE review_likes 
                    SET like_type = ? 
                    WHERE review_id = ? AND user_id = ?
                ");
                $stmt->execute([$like_type, $review_id, $_SESSION['user_id']]);
            }
        } else {
            // Yeni beğeni ekle
            $stmt = $pdo->prepare("
                INSERT INTO review_likes (review_id, user_id, like_type) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$review_id, $_SESSION['user_id'], $like_type]);
        }
        
        // Güncel sayıları al
        $stmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN like_type = 'like' THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN like_type = 'dislike' THEN 1 ELSE 0 END) as dislikes
            FROM review_likes
            WHERE review_id = ?
        ");
        $stmt->execute([$review_id]);
        $counts = $stmt->fetch();
        
        // Review tablosundaki sayıları güncelle
        $stmt = $pdo->prepare("
            UPDATE reviews 
            SET likes_count = ?, dislikes_count = ? 
            WHERE review_id = ?
        ");
        $stmt->execute([$counts['likes'], $counts['dislikes'], $review_id]);
        
        jsonResponse(true, 'İşlem başarılı!', [
            'likes' => $counts['likes'],
            'dislikes' => $counts['dislikes']
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(false, 'İşlem sırasında bir hata oluştu!');
    }
}

// İzleme Listesine Ekle/Çıkar
function toggleWatchlist() {
    global $pdo;
    
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    
    if (!$movie_id) {
        jsonResponse(false, 'Geçersiz film!');
    }
    
    try {
        // Kontrol et
        $stmt = $pdo->prepare("
            SELECT watchlist_id FROM watchlist 
            WHERE movie_id = ? AND user_id = ?
        ");
        $stmt->execute([$movie_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            // Varsa çıkar
            $stmt = $pdo->prepare("
                DELETE FROM watchlist 
                WHERE movie_id = ? AND user_id = ?
            ");
            $stmt->execute([$movie_id, $_SESSION['user_id']]);
            $message = 'Film izleme listenizden çıkarıldı.';
            $added = false;
        } else {
            // Yoksa ekle
            $stmt = $pdo->prepare("
                INSERT INTO watchlist (movie_id, user_id, watch_status) 
                VALUES (?, ?, 'want_to_watch')
            ");
            $stmt->execute([$movie_id, $_SESSION['user_id']]);
            $message = 'Film izleme listenize eklendi!';
            $added = true;
        }
        
        jsonResponse(true, $message, ['added' => $added]);
        
    } catch (PDOException $e) {
        jsonResponse(false, 'İşlem sırasında bir hata oluştu!');
    }
}

// İncelemeye Yorum Ekle
function addComment() {
    global $pdo;
    
    $review_id = (int)($_POST['review_id'] ?? 0);
    $comment_text = sanitize($_POST['comment'] ?? '');
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    
    if (!$review_id || empty($comment_text)) {
        jsonResponse(false, 'Yorum boş olamaz!');
    }
    
    if (strlen($comment_text) < 2) {
        jsonResponse(false, 'Yorum çok kısa!');
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO review_comments (review_id, user_id, parent_comment_id, comment_text) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $review_id, 
            $_SESSION['user_id'], 
            $parent_id ?: null, 
            $comment_text
        ]);
        
        jsonResponse(true, 'Yorumunuz eklendi!', [
            'comment_id' => $pdo->lastInsertId()
        ]);
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Yorum eklenirken bir hata oluştu!');
    }
}

// Aktivite Logla
function logActivity($user_id, $type, $description) {
    global $pdo;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, activity_type, activity_description, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $type, $description, $ip]);
    } catch (PDOException $e) {
        // Log hatası kritik değil, sessizce geç
    }
}
?>