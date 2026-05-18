<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap / Kayıt Ol - FilmKutusu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            min-height: 600px;
        }

        .left-panel {
            background: linear-gradient(135deg, #9d7cce 0%, #b794f4 100%);
            padding: 60px 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        .left-panel h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .icon {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .right-panel {
            flex: 1;
            padding: 60px 50px;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-title {
            font-size: 2em;
            color: #764ba2;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #9d7cce;
            background: white;
            box-shadow: 0 0 0 4px rgba(157, 124, 206, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(118, 75, 162, 0.4);
        }

        .switch-form {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .switch-form a {
            color: #9d7cce;
            text-decoration: none;
            font-weight: 600;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="icon">🎬</div>
            <h1>FilmKutusu</h1>
            <p>Filmleri keşfet, incele, paylaş!</p>
        </div>

        <div class="right-panel">
            <div id="message-container"></div>

            <div id="login-form" class="form-container active">
                <h2 class="form-title">Giriş Yap</h2>
                <form id="loginForm">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label>Kullanıcı Adı veya E-posta</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Şifre</label>
                        <input type="password" name="password" required>
                    </div>

                    <button type="submit" class="btn-submit">Giriş Yap</button>

                    <div class="switch-form">
                        Hesabın yok mu?<a href="#" onclick="showRegister(); return false;">Kayıt Ol</a>
                    </div>
                </form>
            </div>

            <div id="register-form" class="form-container">
                <h2 class="form-title">Kayıt Ol</h2>
                <form id="registerForm">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label>Ad Soyad</label>
                        <input type="text" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label>Kullanıcı Adı</label>
                        <input type="text" name="username" required minlength="3" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label>E-posta</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Şifre</label>
                        <input type="password" name="password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label>Şifre Tekrar</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn-submit">Kayıt Ol</button>

                    <div class="switch-form">
                        Zaten hesabın var mı?<a href="#" onclick="showLogin(); return false;">Giriş Yap</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showLogin() {
            document.getElementById('login-form').classList.add('active');
            document.getElementById('register-form').classList.remove('active');
            document.getElementById('message-container').innerHTML = '';
        }

        function showRegister() {
            document.getElementById('register-form').classList.add('active');
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('message-container').innerHTML = '';
        }

        function showMessage(message, type) {
            document.getElementById('message-container').innerHTML = 
                `<div class="alert alert-${type}">${message}</div>`;
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => window.location.href = data.data.redirect, 1000);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Bir hata oluştu!', 'error');
            }
        });

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => window.location.href = data.data.redirect, 1000);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                showMessage('Bir hata oluştu!', 'error');
            }
        });
    </script>
</body>
</html>