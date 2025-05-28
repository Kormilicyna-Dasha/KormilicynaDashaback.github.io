<?php
session_start();
require 'db.php';

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

$messages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['pass'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['pass_hash'])) {
            $_SESSION['login'] = $user['login'];
            $_SESSION['uid'] = $user['id'];

            header('Location: index.php');
            exit();
        } else {
            $messages[] = '<div class="alert alert-danger">Неверный логин или пароль</div>';
        }
    } catch (PDOException $e) {
        $messages[] = '<div class="alert alert-danger">Ошибка входа: '.htmlspecialchars($e->getMessage()).'</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
        }
        
        body {
            background-color: var(--secondary-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .login-header img {
            width: 80px;
            margin-bottom: 1rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.35rem;
            margin-bottom: 1.25rem;
        }
        
        .btn-login {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background-color: #3a5bc7;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 1rem;
            color: #858796;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <img src="https://cdn-icons-png.flaticon.com/512/5087/5087579.png" alt="Логотип">
                <h2>Вход в систему</h2>
            </div>
            
            <?php if (!empty($messages)): ?>
                <div class="mb-3">
                    <?php foreach ($messages as $message): ?>
                        <?= $message ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="login" class="form-label">Логин</label>
                    <input type="text" class="form-control" id="login" name="login" placeholder="Введите ваш логин" required>
                </div>
                
                <div class="form-group">
                    <label for="pass" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="pass" name="pass" placeholder="Введите ваш пароль" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">Войти</button>
            </form>
            
            <div class="footer-text">
                Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>