<?php
// header.php - Site Üst Menü
if (!isset($pdo)) {
    require_once 'config.php';
}

$current_page = basename($_SERVER['PHP_SELF']);

// Admin panelinde mi değil mi kontrol et
$is_admin_panel = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$base_path = $is_admin_panel ? '../' : '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>FilmKutusu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f7;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-size: 1.5em;
            font-weight: bold;
        }

        .logo:hover {
            opacity: 0.9;
        }

        .nav-search {
            flex: 1;
            max-width: 500px;
            margin: 0 30px;
            position: relative;
        }

        .nav-search input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border: none;
            border-radius: 25px;
            font-size: 0.95em;
            background: rgba(255,255,255,0.2);
            color: white;
            backdrop-filter: blur(10px);
        }

        .nav-search input::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .nav-search input:focus {
            outline: none;
            background: rgba(255,255,255,0.3);
        }

        .nav-search button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            color: white;
            cursor: pointer;
        }

        .nav-search button:hover {
            background: rgba(255,255,255,0.3);
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 25px;
            list-style: none;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(255,255,255,0.2);
        }

        .user-menu {
            position: relative;
        }

        .user-button {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 25px;
            cursor: pointer;
            color: white;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #764ba2;
            font-weight: bold;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            min-width: 200px;
            margin-top: 10px;
            display: none;
        }

        .dropdown-menu.active {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s;
        }

        .dropdown-menu a:hover {
            background: #f5f5f7;
        }

        .dropdown-menu a:first-child {
            border-radius: 10px 10px 0 0;
        }

        .dropdown-menu a:last-child {
            border-radius: 0 0 10px 10px;
            color: #d9534f;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-wrap: wrap;
                padding: 15px 20px;
            }

            .nav-search {
                order: 3;
                width: 100%;
                margin: 15px 0 0 0;
                max-width: 100%;
            }

            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 250px;
                height: calc(100vh - 70px);
                background: #764ba2;
                flex-direction: column;
                align-items: flex-start;
                padding: 20px;
                transition: left 0.3s ease;
            }

            .nav-menu.active {
                left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo $base_path; ?>index.php" class="logo">
                <span>🎬</span>
                <span>FilmKutusu</span>
            </a>

            <div class="nav-search">
                <form action="<?php echo $base_path; ?>search.php" method="GET">
                    <input type="text" name="q" placeholder="Film, tür veya yönetmen ara...">
                    <button type="submit">🔍</button>
                </form>
            </div>

            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>

            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo $base_path; ?>index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Ana Sayfa</a></li>
                <li><a href="<?php echo $base_path; ?>movies.php" class="<?php echo $current_page == 'movies.php' ? 'active' : ''; ?>">Filmler</a></li>
                <li><a href="<?php echo $base_path; ?>forum.php" class="<?php echo $current_page == 'forum.php' ? 'active' : ''; ?>">Forum</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo $base_path; ?>watchlist.php">İzleme Listem</a></li>
                    <li class="user-menu">
                        <div class="user-button" onclick="toggleUserMenu()">
                            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="<?php echo $base_path; ?>profile.php">👤 Profilim</a>
                            <a href="<?php echo $base_path; ?>my-reviews.php">📝 İncelemelerim</a>
                            <a href="<?php echo $base_path; ?>settings.php">⚙️ Ayarlar</a>
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo $base_path; ?>admin/dashboard.php">🔧 Admin Panel</a>
                            <?php endif; ?>
                            <a href="#" onclick="logout(); return false;">🚪 Çıkış Yap</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo $base_path; ?>login.php" style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 20px;">Giriş Yap</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <script>
        function toggleUserMenu() {
            document.getElementById('userDropdown').classList.toggle('active');
        }

        function toggleMobileMenu() {
            document.getElementById('navMenu').classList.toggle('active');
        }

        // Dışarı tıklandığında menüyü kapat
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (userMenu && !userMenu.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        async function logout() {
            if (confirm('Çıkış yapmak istediğinize emin misiniz?')) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'logout');
                    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');

                    const basePath = '<?php echo $base_path; ?>';
                    const response = await fetch(basePath + 'auth.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        window.location.href = basePath + 'login.php';
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                }
            }
        }
    </script>
</body>
</html>