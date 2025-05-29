<?php
// Устанавливаем соединение с базой данных
$host = 'localhost';
$dbname = 'u68677';
$username = 'u68677';
$password = '2026544';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $oldValues = [];

    $validationRules = [
        'name' => [
            'pattern' => '/^[a-zA-Zа-яА-ЯёЁ\s]{1,150}$/u',
            'message' => 'ФИО должно содержать только буквы и пробелы (макс. 150 символов)'
        ],
        'phone' => [
            'pattern' => '/^\+?\d{1,3}[-\s]?\(?\d{3}\)?[-\s]?\d{3}[-\s]?\d{2}[-\s]?\d{2}$/',
            'message' => 'Телефон должен быть в формате +7(XXX)XXX-XX-XX'
        ],
        'email' => [
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'message' => 'Введите корректный email'
        ],
        'birthdate' => [
            'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
            'message' => 'Дата должна быть в формате ГГГГ-ММ-ДД'
        ],
        'gender' => [
            'pattern' => '/^(male|female)$/',
            'message' => 'Выберите пол'
        ],
        'languages' => [
            'pattern' => '/^(Pascal|C|C\+\+|JavaScript|PHP|Python|Java|Haskell|Clojure|Prolog|Scala)(,(Pascal|C|C\+\+|JavaScript|PHP|Python|Java|Haskell|Clojure|Prolog|Scala))*$/',
            'message' => 'Выберите хотя бы один язык программирования'
        ],
        'bio' => [
            'pattern' => '/^.{10,2000}$/s',
            'message' => 'Биография должна содержать от 10 до 2000 символов'
        ],
        'agreement' => [
            'pattern' => '/^1$/',
            'message' => 'Необходимо принять условия контракта'
        ]
    ];

    foreach ($validationRules as $field => $rule) {
        $value = $_POST[$field] ?? '';

        if ($field === 'languages') {
            $value = implode(',', $_POST['languages'] ?? []);
        } elseif ($field === 'agreement') {
            $value = isset($_POST['agreement']) ? '1' : '';
        }

        $oldValues[$field] = $value;

        if (!preg_match($rule['pattern'], $value)) {
            $errors[$field] = $rule['message'];
        }
    }

    if (!empty($errors)) {
        setcookie('form_errors', json_encode($errors), 0, '/');
        setcookie('old_values', json_encode($oldValues), 0, '/');
        header('Location: index.php');
        exit;
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO applications (name, phone, email, birthdate, gender, bio, agreement)
                              VALUES (:name, :phone, :email, :birthdate, :gender, :bio, :agreement)");
        $stmt->execute([
            ':name' => $_POST['name'],
            ':phone' => $_POST['phone'],
            ':email' => $_POST['email'],
            ':birthdate' => $_POST['birthdate'],
            ':gender' => $_POST['gender'],
            ':bio' => $_POST['bio'],
            ':agreement' => isset($_POST['agreement']) ? 1 : 0
        ]);

        $applicationId = $pdo->lastInsertId();

        if (!empty($_POST['languages'])) {
            $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id)
                                  SELECT ?, id FROM languages WHERE name = ?");
            foreach ($_POST['languages'] as $lang) {
                $stmt->execute([$applicationId, $lang]);
            }
        }

        $pdo->commit();

        foreach ($oldValues as $field => $value) {
            setcookie("saved_$field", $value, time() + 60*60*24*365, '/');
        }

        setcookie('form_errors', '', time() - 3600, '/');
        setcookie('old_values', '', time() - 3600, '/');

        header('Location: index.php?success=1');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Ошибка при сохранении данных: " . $e->getMessage());
    }
}
?>