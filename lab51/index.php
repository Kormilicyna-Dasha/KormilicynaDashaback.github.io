<?php
session_start();
require 'db.php';

$messages = [];
$errors = [];
$values = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = 'Спасибо, результаты сохранены.';

        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                htmlspecialchars($_COOKIE['login']),
                htmlspecialchars($_COOKIE['pass'])
            );
        }
    }

    $field_names = ['name', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'agreement'];
    foreach ($field_names as $field) {
        $errors[$field] = !empty($_COOKIE[$field.'_error']) ? $_COOKIE[$field.'_error'] : '';
        if (!empty($errors[$field])) {
            setcookie($field.'_error', '', time() - 3600);
        }
        $values[$field] = empty($_COOKIE[$field.'_value']) ? '' : $_COOKIE[$field.'_value'];
    }

    if (!empty($_SESSION['login'])) {
        try {
            $stmt = $pdo->prepare("SELECT a.*, GROUP_CONCAT(l.name) as languages
                FROM applications a
                LEFT JOIN application_languages al ON a.id = al.application_id
                LEFT JOIN languages l ON al.language_id = l.id
                WHERE a.login = ?
                GROUP BY a.id");
            $stmt->execute([$_SESSION['login']]);
            $user_data = $stmt->fetch();

            if ($user_data) {
                $values = array_merge($values, $user_data);
                $values['languages'] = $user_data['languages'] ? explode(',', $user_data['languages']) : [];
            }
        } catch (PDOException $e) {
            $messages[] = '<div class="alert alert-danger">Ошибка загрузки данных: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru-RU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма регистрации</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --light-bg: #f8f9fa;
            --border-radius: 0.375rem;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .form-container {
            max-width: 800px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem auto;
        }
        
        .form-title {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-submit:hover {
            background-color: #3a5bc7;
        }
        
        .btn-logout {
            margin-left: 1rem;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .alert-container {
            max-width: 800px;
            margin: 2rem auto 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($messages)): ?>
            <div class="alert-container">
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        $has_errors = false;
        foreach ($errors as $error) {
            if (!empty($error)) {
                $has_errors = true;
                break;
            }
        }
        ?>

        <?php if ($has_errors): ?>
            <div class="alert-container">
                <div class="alert alert-danger">
                    <h4 class="alert-heading">Обнаружены ошибки:</h4>
                    <ul class="mb-0">
                        <?php foreach ($errors as $field => $error): ?>
                            <?php if (!empty($error)): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h1 class="form-title">Форма регистрации</h1>
            
            <form action="sub.php" method="POST" id="form">
                <!-- Секция 1: Личные данные -->
                <div class="form-section">
                    <h5>1. Личные данные</h5>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">ФИО:</label>
                        <input type="text" class="form-control <?php echo !empty($errors['name']) ? 'is-invalid' : ''; ?>" 
                               placeholder="Введите ваше ФИО" name="name" id="name" required
                               value="<?php echo htmlspecialchars($values['name'] ?? ''); ?>">
                        <?php if (!empty($errors['name'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон:</label>
                        <input class="form-control <?php echo !empty($errors['phone']) ? 'is-invalid' : ''; ?>" 
                               type="tel" placeholder="+123456-78-90" name="phone" id="phone" required
                               value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>">
                        <?php if (!empty($errors['phone'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail:</label>
                        <input class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" 
                               type="email" placeholder="Введите вашу почту" name="email" id="email" required
                               value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="birthdate" class="form-label">Дата рождения:</label>
                        <input class="form-control <?php echo !empty($errors['birthdate']) ? 'is-invalid' : ''; ?>" 
                               value="2000-07-15" type="date" name="birthdate" id="birthdate" required
                               value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>">
                        <?php if (!empty($errors['birthdate'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Секция 2: Пол -->
                <div class="form-section">
                    <h5>2. Пол</h5>
                    <div class="form-check">
                        <input class="form-check-input <?php echo !empty($errors['gender']) ? 'is-invalid' : ''; ?>" 
                               type="radio" name="gender" id="male" value="male" required
                               <?php echo ($values['gender'] ?? '') === 'male' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="male">Мужской</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input <?php echo !empty($errors['gender']) ? 'is-invalid' : ''; ?>" 
                               type="radio" name="gender" id="female" value="female"
                               <?php echo ($values['gender'] ?? '') === 'female' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="female">Женский</label>
                    </div>
                    <?php if (!empty($errors['gender'])): ?>
                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['gender']); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Секция 3: Языки программирования -->
                <div class="form-section">
                    <h5>3. Любимый язык программирования</h5>
                    <label class="form-label">Выберите один или несколько вариантов:</label>
                    <select class="form-select <?php echo !empty($errors['languages']) ? 'is-invalid' : ''; ?>" 
                            id="languages" name="languages[]" multiple="multiple" required size="5">
                        <?php
                        $allLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala'];
                        $selectedLanguages = isset($values['languages']) ? (is_array($values['languages']) ? $values['languages'] : explode(',', $values['languages'])) : [];
                        
                        foreach ($allLanguages as $lang): ?>
                            <option value="<?php echo htmlspecialchars($lang); ?>"
                                <?php echo in_array($lang, $selectedLanguages) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lang); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['languages'])): ?>
                        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['languages']); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Секция 4: Биография -->
                <div class="form-section">
                    <h5>4. Биография</h5>
                    <label for="bio" class="form-label">Расскажите о себе:</label>
                    <textarea class="form-control <?php echo !empty($errors['bio']) ? 'is-invalid' : ''; ?>" 
                              id="bio" name="bio" rows="4" required><?php
                              echo htmlspecialchars($values['bio'] ?? ''); ?></textarea>
                    <?php if (!empty($errors['bio'])): ?>
                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['bio']); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Секция 5: Соглашение -->
                <div class="form-section">
                    <h5>5. Соглашение</h5>
                    <div class="form-check">
                        <input class="form-check-input <?php echo !empty($errors['agreement']) ? 'is-invalid' : ''; ?>" 
                               type="checkbox" name="agreement" id="agreement" value="1" required
                               <?php echo ($values['agreement'] ?? '') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="agreement">С контрактом ознакомлен(а)</label>
                        <?php if (!empty($errors['agreement'])): ?>
                            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['agreement']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Кнопки отправки -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" name="save" class="btn btn-primary btn-submit">Опубликовать</button>
                    <?php if (!empty($_SESSION['login'])): ?>
                        <a href="logout.php" class="btn btn-danger btn-logout">Выйти</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>