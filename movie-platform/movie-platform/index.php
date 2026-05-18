<?php
// index.php - Modern UX/UI Ana Sayfa
require_once 'config.php';
$page_title = 'Ana Sayfa';

// Popüler filmler
$stmt = $pdo->query("
    SELECT m.*, COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.rating_id) as rating_count,
           GROUP_CONCAT(DISTINCT g.genre_name SEPARATOR ', ') as genres
    FROM movies m
    LEFT JOIN ratings r ON m.movie_id = r.movie_id
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    GROUP BY m.movie_id
    ORDER BY m.view_count DESC
    LIMIT 12
");
$popular_movies = $stmt->fetchAll();

// İstatistikler
$stats = [
    'total_movies' => $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_reviews' => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn()
];

include 'header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary: #FF6B35;
        --secondary: #004E89;
        --accent: #FFD23F;
        --dark: #1A1A2E;
        --light: #F8F9FA;
        --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
        --shadow-lg: 0 8px 32px rgba(0,0,0,0.16);
        --radius: 16px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
        background: var(--light);
        color: var(--dark);
        line-height: 1.6;
        overflow-x: hidden;
    }

    /* Hero Section - Modern & Dynamic */
    .hero {
        position: relative;
        background: var(--gradient-1);
        padding: 120px 20px 80px;
        overflow: hidden;
        margin-top: -20px;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
        animation: pulse 8s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    .hero-content {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .hero h1 {
        font-family: 'Fraunces', serif;
        font-size: clamp(2.5rem, 6vw, 5rem);
        font-weight: 800;
        color: white;
        margin-bottom: 20px;
        line-height: 1.1;
        letter-spacing: -0.02em;
        animation: slideUp 0.8s ease-out;
    }

    .hero p {
        font-size: clamp(1.1rem, 2vw, 1.4rem);
        color: rgba(255,255,255,0.95);
        margin-bottom: 40px;
        max-width: 600px;
        animation: slideUp 0.8s ease-out 0.2s backwards;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stats - Glassmorphism */
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        max-width: 1200px;
        margin: -60px auto 0;
        padding: 0 20px;
        position: relative;
        z-index: 10;
        animation: slideUp 0.8s ease-out 0.4s backwards;
    }

    .stat-card {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding: 30px;
        border-radius: var(--radius);
        text-align: center;
        box-shadow: var(--shadow-lg);
        transition: var(--transition);
        border: 1px solid rgba(255,255,255,0.3);
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 48px rgba(0,0,0,0.2);
    }

    .stat-number {
        display: block;
        font-size: 3rem;
        font-weight: 800;
        background: var(--gradient-1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 8px;
    }

    .stat-label {
        color: #666;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Container */
    .container {
        max-width: 1400px;
        margin: 80px auto;
        padding: 0 20px;
    }

    /* Section Header */
    .section-header {
        margin-bottom: 40px;
        animation: slideUp 0.6s ease-out;
    }

    .section-title {
        font-family: 'Fraunces', serif;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .section-title::before {
        content: '';
        width: 6px;
        height: 50px;
        background: var(--gradient-1);
        border-radius: 3px;
    }

    .section-subtitle {
        color: #666;
        font-size: 1.1rem;
        margin-left: 22px;
    }

    /* Movies Grid - Modern Cards */
    .movies-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 32px;
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .movie-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        text-decoration: none;
        color: inherit;
        display: block;
        position: relative;
    }

    .movie-card:hover {
        transform: translateY(-12px);
        box-shadow: var(--shadow-lg);
    }

    .movie-card:hover .movie-poster img {
        transform: scale(1.08);
    }

    .movie-poster {
        position: relative;
        width: 100%;
        height: 420px;
        overflow: hidden;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .movie-poster img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .movie-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--dark);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .movie-rating-badge {
        position: absolute;
        bottom: 16px;
        left: 16px;
        background: rgba(255,215,0,0.95);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--dark);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .movie-info {
        padding: 24px;
    }

    .movie-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 12px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .movie-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 12px;
    }

    .movie-year {
        font-weight: 600;
    }

    .movie-genres {
        color: #999;
        font-size: 0.9rem;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* CTA Section */
    .cta-section {
        background: var(--gradient-2);
        padding: 80px 20px;
        text-align: center;
        margin: 80px 0;
        border-radius: 24px;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .cta-content {
        position: relative;
        z-index: 1;
    }

    .cta-title {
        font-family: 'Fraunces', serif;
        font-size: clamp(2rem, 4vw, 3.5rem);
        font-weight: 800;
        color: white;
        margin-bottom: 20px;
    }

    .cta-text {
        font-size: 1.2rem;
        color: rgba(255,255,255,0.95);
        margin-bottom: 40px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .btn-cta {
        display: inline-block;
        background: white;
        color: var(--dark);
        padding: 18px 48px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        transition: var(--transition);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }

    .btn-cta:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 32px rgba(0,0,0,0.25);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero {
            padding: 80px 20px 60px;
        }

        .stats {
            grid-template-columns: 1fr;
            margin-top: -40px;
        }

        .movies-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .movie-poster {
            height: 300px;
        }
    }

    /* Loading Animation */
    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    .skeleton {
        animation: shimmer 2s infinite;
        background: linear-gradient(to right, #f0f0f0 8%, #e0e0e0 18%, #f0f0f0 33%);
        background-size: 1000px 100%;
    }
</style>

<!-- Hero Section -->
<div class="hero">
    <div class="hero-content">
        <h1>Filmlerin Sihirli Dünyası</h1>
        <p>Binlerce film, sonsuz hikaye. Keşfet, paylaş, deneyimle.</p>
    </div>
</div>

<!-- Stats -->
<div class="stats">
    <div class="stat-card">
        <span class="stat-number"><?php echo number_format($stats['total_movies']); ?></span>
        <span class="stat-label">Film</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo number_format($stats['total_users']); ?></span>
        <span class="stat-label">Üye</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?php echo number_format($stats['total_reviews']); ?></span>
        <span class="stat-label">İnceleme</span>
    </div>
</div>

<!-- Popüler Filmler -->
<div class="container">
    <div class="section-header">
        <h2 class="section-title">🔥 Popüler Filmler</h2>
        <p class="section-subtitle">En çok izlenen ve sevilen filmler</p>
    </div>

    <?php if (empty($popular_movies)): ?>
        <div style="text-align: center; padding: 100px 20px; color: #999;">
            <div style="font-size: 5rem; margin-bottom: 20px;">🎬</div>
            <h2 style="font-size: 2rem; margin-bottom: 10px;">Henüz film eklenmemiş</h2>
            <p style="font-size: 1.1rem;">İlk filmi ekleyin ve keşfetmeye başlayın!</p>
            <?php if (isAdmin()): ?>
                <a href="admin/movie_add.php" class="btn-cta" style="margin-top: 30px;">
                    ➕ Film Ekle
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="movies-grid">
            <?php foreach ($popular_movies as $movie): ?>
                <a href="movie.php?id=<?php echo $movie['movie_id']; ?>" class="movie-card">
                    <div class="movie-poster">
                        <?php if ($movie['poster_url']): ?>
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 5rem; color: white;">
                                🎬
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($movie['view_count'] > 1000): ?>
                            <div class="movie-badge">🔥 Trend</div>
                        <?php endif; ?>
                        
                        <?php if ($movie['avg_rating'] > 0): ?>
                            <div class="movie-rating-badge">
                                ⭐ <?php echo number_format($movie['avg_rating'], 1); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                        <div class="movie-meta">
                            <span class="movie-year"><?php echo $movie['release_year']; ?></span>
                            <span><?php echo $movie['rating_count']; ?> puan</span>
                        </div>
                        <div class="movie-genres">
                            <?php echo $movie['genres'] ?: 'Tür belirtilmemiş'; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- CTA Section -->
<?php if (!isLoggedIn()): ?>
<div class="container">
    <div class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Topluluğumuza Katıl!</h2>
            <p class="cta-text">Filmleri keşfet, incelemeni paylaş, yeni arkadaşlar edin</p>
            <a href="login.php" class="btn-cta">Hemen Başla →</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>