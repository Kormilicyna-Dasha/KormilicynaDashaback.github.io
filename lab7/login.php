<?php
header("Content-Type: text/html; charset=UTF-8");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self';");

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <title>index</title>
</head>

<body>
<?php if (!empty($messages)): ?>
    <div class="mb-3">
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
    <div class="alert alert-danger mb-3">
        <h4>Обнаружены ошибки:</h4>
        <ul class="mb-0">
            <?php foreach ($errors as $field => $error): ?>
                <?php if (!empty($error)): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="sub.php" method="POST" id="form" class="w-75 mx-auto bg-light p-4 rounded shadow">
    <h2 class="text-center mb-4">Регистрация</h2>

    <div class="mb-3">
        <label for="name" class="form-label">1) ФИО:</label>
        <input type="text" class="form-control <?php echo !empty($errors['name']) ? 'is-invalid' : ''; ?>" placeholder="Введите ваше ФИО" name="name" id="name" required
               value="<?php echo htmlspecialchars($values['name'] ?? ''); ?>">
        <?php if (!empty($errors['name'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">2) Телефон:</label>
        <input type="tel" class="form-control <?php echo !empty($errors['phone']) ? 'is-invalid' : ''; ?>" placeholder="+123456-78-90" name="phone" id="phone" required
               value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>">
        <?php if (!empty($errors['phone'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">3) e-mail:</label>
        <input type="email" class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" placeholder="Введите вашу почту" name="email" id="email" required
               value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>">
        <?php if (!empty($errors['email'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="birthdate" class="form-label">4) Дата рождения:</label>
        <input type="date" class="form-control <?php echo !empty($errors['birthdate']) ? 'is-invalid' : ''; ?>" name="birthdate" id="birthdate" required
               value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>">
        <?php if (!empty($errors['birthdate'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">5) Пол:</label><br>
        <div class="form-check">
            <input type="radio" class="form-check-input <?php echo !empty($errors['gender']) ? 'is-invalid' : ''; ?>" value="male" id="male" name="gender" required
                   <?php echo ($values['gender'] ?? '') === 'male' ? 'checked' : ''; ?>>
            <label class="form-check-label" for="male">Мужской</label>
        </div>
        <div class="form-check">
            <input type="radio" class="form-check-input <?php echo !empty($errors['gender']) ? 'is-invalid' : ''; ?>" value="female" id="female" name="gender"
                   <?php echo ($values['gender'] ?? '') === 'female' ? 'checked' : ''; ?>>
            <label class="form-check-label" for="female">Женский</label>
        </div>
        <?php if (!empty($errors['gender'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['gender']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label for="languages" class="form-label">6) Любимый язык программирования:</label>
        <select class="form-select <?php echo !empty($errors['languages']) ? 'is-invalid' : ''; ?>" id="languages" name="languages[]" multiple="multiple" required size="5">
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

    <div class="mb-3">
        <label for="bio" class="form-label">7) Биография:</label>
        <textarea class="form-control <?php echo !empty($errors['bio']) ? 'is-invalid' : ''; ?>" id="bio" name="bio" required><?php echo htmlspecialchars($values['bio'] ?? ''); ?></textarea>
        <?php if (!empty($errors['bio'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['bio']); ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input <?php echo !empty($errors['agreement']) ? 'is-invalid' : ''; ?>" name="agreement" id="agreement" value="1" required
               <?php echo ($values['agreement'] ?? '') ? 'checked' : ''; ?>>
        <label class="form-check-label" for="agreement">С контрактом ознакомлен(а)</label>
        <?php if (!empty($errors['agreement'])): ?>
            <div class="invalid-feedback d-block"><?php echo htmlspecialchars($errors['agreement']); ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" name="save" class="btn btn-primary">Опубликовать</button>

    <?php if (!empty($_SESSION['login'])): ?>
        <a href="logout.php" class="btn btn-danger ms-2">Выйти</a>
    <?php endif; ?>
</form>
</body>

</html>
