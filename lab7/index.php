<?php
header("Content-Type: text/html; charset=UTF-8");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self';");
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Получаем данные из cookies
$errors = [];
$oldValues = [];
$savedValues = [];

if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    $oldValues = json_decode($_COOKIE['old_values'], true);
}

// Получаем сохраненные значения
foreach ($_COOKIE as $name => $value) {
    if (strpos($name, 'saved_') === 0) {
        $field = substr($name, 6);
        $savedValues[$field] = $value;
    }
}

// Функция для получения значения поля
function getFieldValue($field, $default = '') {
    global $oldValues, $savedValues;

    if (isset($oldValues[$field])) {
        return $oldValues[$field];
    }

    if (isset($savedValues[$field])) {
        return $savedValues[$field];
    }

    return $default;
}

// Функция для проверки выбранного значения
function isSelected($field, $value) {
    global $oldValues, $savedValues;

    $currentValues = [];
    if (isset($oldValues[$field])) {
        if ($field === 'languages') {
            $currentValues = explode(',', $oldValues[$field]);
        } else {
            return $oldValues[$field] === $value ? 'checked' : '';
        }
    } elseif (isset($savedValues[$field])) {
        if ($field === 'languages') {
            $currentValues = explode(',', $savedValues[$field]);
        } else {
            return $savedValues[$field] === $value ? 'checked' : '';
        }
    }

    return in_array($value, $currentValues) ? 'selected' : '';
}

// Функция для проверки чекбокса
function isChecked($field) {
    global $oldValues, $savedValues;

    if (isset($oldValues[$field])) {
        return $oldValues[$field] ? 'checked' : '';
    }

    if (isset($savedValues[$field])) {
        return $savedValues[$field] ? 'checked' : '';
    }

    return '';
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
    <?php if (isset($_GET['success'])): ?>
                <div class="success-message">Данные успешно сохранены!</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <h3>Обнаружены ошибки:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
    <form action="sub.php" method="POST" id="form" class="w-50 mx-auto">

          <label class="form-label">
            1) ФИО:<br>
            <input type="text" class="form-control" placeholder="Введите ваше ФИО" name="name" id = "name" required
                           value="<?php echo htmlspecialchars(getFieldValue('name')); ?>"
                           class="<?php echo isset($errors['name']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['name']); ?></div>
                    <?php endif; ?>
          </label><br>

          <label class="form-label">
            2) Телефон:<br>
            <input class="form-control" type="tel" placeholder="+123456-78-90" name="phone" id="phone" required
                           value="<?php echo htmlspecialchars(getFieldValue('phone')); ?>"
                           class="<?php echo isset($errors['phone']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['phone']); ?></div>
                    <?php endif; ?>
          </label><br>

          <label class="form-label">
            3) e-mail:<br>
            <input class="form-control" type="email" placeholder="Введите вашу почту" name="email" id="email" required
                           value="<?php echo htmlspecialchars(getFieldValue('email')); ?>"
                           class="<?php echo isset($errors['email']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
          </label><br>

          <label class="form-label">
            4) Дата рождения:<br>
            <input class="form-control" value="2000-07-15" type="date" name="birthdate" id="birthdate" required
                           value="<?php echo htmlspecialchars(getFieldValue('birthdate')); ?>"
                           class="<?php echo isset($errors['birthdate']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['birthdate'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                    <?php endif; ?>
          </label><br>
          <div><br>
            5) Пол:<br>
          <label class="form-check-label"><input type="radio" checked="checked" class="form-check-input" value="male" id="male" name="gender" required
                               <?php echo isSelected('gender', 'male'); ?>
                               class="<?php echo isset($errors['gender']) ? 'error-field' : ''; ?>">>
            Мужской</label>
          <label class="form-check-label"><input type="radio" class="form-check-input" value="female" id="female" name="gender"
                               <?php echo isSelected('gender', 'female'); ?>
                               class="<?php echo isset($errors['gender']) ? 'error-field' : ''; ?>">>
            Женский</label><br>
            <?php if (isset($errors['gender'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['gender']); ?></div>
                    <?php endif; ?>

          </div><br>

          <label class="form-label">
            6) Любимый язык программирования:<br>
            <select class="form-select" id="languages" name="languages[]" multiple="multiple" required class="<?php echo isset($errors['languages']) ? 'error-field' : ''; ?>" size="5">
                        <?php
                        $allLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala'];
                        foreach ($allLanguages as $lang): ?>
                            <option value="<?php echo htmlspecialchars($lang); ?>"
                                <?php echo isSelected('languages', $lang); ?>>
                                <?php echo htmlspecialchars($lang); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['languages'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['languages']); ?></div>
                    <?php endif; ?>
          </label><br>

          <label class="form-label">
            7) Биография:<br>
            <input type="text" class="form-control" id="bio" name="bio" required
                              class="<?php echo isset($errors['bio']) ? 'error-field' : ''; ?>"><?php
                              echo htmlspecialchars(getFieldValue('bio')); ?></textarea>
                    <?php if (isset($errors['bio'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['bio']); ?></div>
                    <?php endif; ?>
          </label><br>

            8):<br>
          <label class="form-check-label"><input type="checkbox" class="form-check-input" name="agreement" id="agreement" value="1" required
                           <?php echo isChecked('agreement'); ?>
                           class="<?php echo isset($errors['agreement']) ? 'error-field' : ''; ?>">
                    С контрактом ознакомлен(а)
                    <?php if (isset($errors['agreement'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['agreement']); ?></div>
                    <?php endif; ?>
          </label><br>
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            9)Кнопка:<br>
          <button type="submit" name="save" class="btn btn-primary">Опубликовать</button>

    </form>
  </body>

</html>