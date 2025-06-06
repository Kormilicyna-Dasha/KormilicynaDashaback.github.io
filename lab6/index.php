<?php
// Отключаем вывод ошибок на экран
error_reporting(0);
ini_set('display_errors', 0);

// Подключение к базе данных
require 'db.php';

// Инициализация переменных
$today_applications = 0;
$total_applications = 0;
$applications = [];
$stats = [];
$all_languages = [];
$messages = [];
$errors = [];

// Создаем таблицу для администраторов, если ее нет
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            login VARCHAR(50) UNIQUE NOT NULL,
            pass_hash VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB
    ");

    // Добавляем администратора по умолчанию, если таблица пуста
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    if ($stmt->fetchColumn() == 0) {
        $pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (login, pass_hash) VALUES (?, ?)")
            ->execute(['admin', $pass_hash]);
    }
} catch (PDOException $e) {
    $errors[] = "Ошибка инициализации БД: " . $e->getMessage();
}

// HTTP-аутентификация
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    $errors[] = 'Требуется авторизация';
} else {
    // Проверка логина и пароля
    try {
        $stmt = $pdo->prepare("SELECT pass_hash FROM admins WHERE login = ?");
        $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['pass_hash'])) {
            header('WWW-Authenticate: Basic realm="Admin Panel"');
            header('HTTP/1.0 401 Unauthorized');
            $errors[] = 'Неверные логин или пароль';
        }
    } catch (PDOException $e) {
        $errors[] = "Ошибка аутентификации: " . $e->getMessage();
    }
}

// Получаем статистику
try {
    // Общее количество заявок
    $total_applications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
    
    // Получаем все заявки
    $applications = $pdo->query("
        SELECT a.*, GROUP_CONCAT(l.name) as languages
        FROM applications a
        LEFT JOIN application_languages al ON a.id = al.application_id
        LEFT JOIN languages l ON al.language_id = l.id
        GROUP BY a.id
        ORDER BY a.id DESC
    ")->fetchAll();

    // Получаем статистику по языкам
    $stats = $pdo->query("
        SELECT l.name, COUNT(al.application_id) as count
        FROM languages l
        LEFT JOIN application_languages al ON l.id = al.language_id
        GROUP BY l.id
        ORDER BY count DESC
    ")->fetchAll();

    // Получаем список всех языков
    $all_languages = $pdo->query("SELECT name FROM languages")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $errors[] = "Ошибка получения данных: " . $e->getMessage();
}

// Обработка действий администратора
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

try {
    // Удаление записи
    if ($action === 'delete' && $id > 0) {
        $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$id]);
        header("Location: index.php");
        exit();
    }

    // Обновление записи
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE applications SET
            name = ?, phone = ?, email = ?, birthdate = ?,
            gender = ?, bio = ?, agreement = ?
            WHERE id = ?");

        $stmt->execute([
            $_POST['name'] ?? '',
            $_POST['phone'] ?? '',
            $_POST['email'] ?? '',
            $_POST['birthdate'] ?? '',
            $_POST['gender'] ?? 'male',
            $_POST['bio'] ?? '',
            isset($_POST['agreement']) ? 1 : 0,
            $id
        ]);

        // Обновляем языки
        $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")
            ->execute([$id]);

        $lang_stmt = $pdo->prepare("INSERT INTO application_languages
            (application_id, language_id) SELECT ?, id FROM languages WHERE name = ?");

        foreach (($_POST['languages'] ?? []) as $lang) {
            $lang_stmt->execute([$id, $lang]);
        }

        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $errors[] = "Ошибка обработки действия: " . $e->getMessage();
}

// Форма редактирования
$edit_data = null;
if ($action === 'edit' && $id > 0) {
    foreach ($applications as $app) {
        if ($app['id'] == $id) {
            $edit_data = $app;
            $edit_data['languages'] = !empty($app['languages']) ? explode(',', $app['languages']) : [];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-header {
            background-color: #343a40;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 0.25rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            margin-bottom: 1rem;
            border-left: 4px solid #007bff;
        }
        .stat-card h3 {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .stat-card p {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .badge-language {
            background-color: #e9ecef;
            color: #495057;
            font-weight: normal;
        }
        .btn-action {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1>Админ-панель</h1>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <h3>Всего заявок</h3>
                    <p><?= $total_applications ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card" style="border-left-color: #17a2b8;">
                    <h3>Языков программирования</h3>
                    <p><?= count($all_languages) ?></p>
                </div>
            </div>
        </div>

        <!-- Популярные языки -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                Популярные языки программирования
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($stats as $stat): ?>
                        <div class="col-md-3 mb-2">
                            <span class="badge bg-primary p-2 w-100">
                                <?= htmlspecialchars($stat['name']) ?>: <?= $stat['count'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Форма редактирования -->
        <?php if ($edit_data): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Редактирование заявки #<?= $edit_data['id'] ?>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>ФИО</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label>Телефон</label>
                                    <input type="text" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($edit_data['phone'] ?? '') ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Дата рождения</label>
                                    <input type="date" name="birthdate" class="form-control" 
                                           value="<?= htmlspecialchars($edit_data['birthdate'] ?? '') ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label>Пол</label>
                                    <select name="gender" class="form-control" required>
                                        <option value="male" <?= ($edit_data['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Мужской</option>
                                        <option value="female" <?= ($edit_data['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Женский</option>
                                        <option value="other" <?= ($edit_data['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Другое</option>
                                    </select>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="agreement" id="agreement" 
                                           <?= ($edit_data['agreement'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="agreement">Контракт принят</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Языки программирования</label>
                            <select name="languages[]" class="form-control" multiple size="5" required>
                                <?php foreach ($all_languages as $lang): ?>
                                    <option value="<?= htmlspecialchars($lang) ?>"
                                        <?= in_array($lang, $edit_data['languages'] ?? []) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($lang) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Биография</label>
                            <textarea name="bio" class="form-control" rows="4" required><?= 
                                htmlspecialchars($edit_data['bio'] ?? '') 
                            ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="index.php" class="btn btn-secondary mr-2">Отмена</a>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Список заявок -->
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span>Список заявок</span>
                <span class="badge bg-light text-dark"><?= $total_applications ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>ФИО</th>
                                <th>Email</th>
                                <th>Телефон</th>
                                <th>Языки</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?= $app['id'] ?></td>
                                    <td><?= htmlspecialchars($app['name'] ?? 'Не указано') ?></td>
                                    <td><?= htmlspecialchars($app['email'] ?? 'Не указано') ?></td>
                                    <td><?= htmlspecialchars($app['phone'] ?? 'Не указано') ?></td>
                                    <td>
                                        <?php if (!empty($app['languages'])): ?>
                                            <?php foreach (explode(',', $app['languages']) as $lang): ?>
                                                <span class="badge badge-language mr-1"><?= htmlspecialchars($lang) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            Не указаны
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="index.php?action=edit&id=<?= $app['id'] ?>" 
                                               class="btn btn-sm btn-warning btn-action mr-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="index.php?action=delete&id=<?= $app['id'] ?>" 
                                               class="btn btn-sm btn-danger btn-action"
                                               onclick="return confirm('Вы уверены, что хотите удалить эту заявку?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>