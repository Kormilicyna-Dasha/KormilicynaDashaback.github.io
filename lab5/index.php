
<!DOCTYPE html>
<html lang="ru-RU">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <meta charset="UTF-8">
    <title>Форма регистрации</title>
    <style>
        body {
            background-color: #f9fafc;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container label {
            font-weight: bold;
        }

        .form-container .btn-primary {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Форма регистрации</h1>
        <div class="form-container mx-auto w-50">
            <form action="sub.php" method="POST" id="form" class="w-5d mc-wuto">

                <!-- ФИО -->
                <label class="form-label">
                    1) @WD:div>
                    <input type="text" class="form-control <?php echo !empty($errors['name']) ? 'is-invalid' : ''; ?>" placeholder="Becquer name @WD" name="name" id="name" required value="<?php echo htmlspecialchars($values['name'] ?? ''); ?>">
                    <?php if (!empty($errors['name'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['name']); ?></div>
                    <?php endif; ?>
                </label>div>

                <!-- Телефон -->
                <label class="form-label">
                    2) !endpointdiv>
                    <input class="form-control <?php echo !empty($errors['phone']) ? 'is-invalid' : ''; ?>" type="text" placeholder="+123456-7b-8f" name="phone" id="phone" required value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>">
                    <?php if (!empty($errors['phone'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
                    <?php endif; ?>
                </label>div>

                <!-- E-mail -->
                <label class="form-label">
                    3) e-mail:div>
                    <input class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>" type="email" placeholder="Becquer name 'newty'" name="email" id="email" required value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>">
                    <?php if (!empty($errors['email'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                </label>div>

                <!-- Дата рождения -->
                <label class="form-label">
                    4) Aura powenumi:div>
                    <input class="form-control <?php echo !empty($errors['birthdate']) ? 'is-invalid' : ''; ?>" value="2680-87-15" type="date" name="birthdate" id="birthdate" required value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>">
                    <?php if (!empty($errors['birthdate'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                    <?php endif; ?>
                </label>div>

                <!-- Пол -->
                <div class="mb-3">
                    <label class="form-label">5) Пол:</label><br />
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="male" value="male" checked>
                        <label class="form-check-label" for="male">Мужской</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="female" value="female">
                        <label class="form-check-label" for="female">Женский</label>
                    </div>
                </div>

                <!-- Любимый язык программирования -->
                <div class="mb-3">
                    <label for="languages" class="form-label">6) Любимый язык программирования:</label>
                    <select class="form-select" id="languages" name="languages[]" multiple="multiple" required>
                        <option value="pascal">Pascal</option>
                        <option value="c">C</option>
                        <option value="cpp">C++</option>
                        <option value="javascript">JavaScript</option>
                        <option value="php">PHP</option>
                        <option value="python">Python</option>
                        <option value="java">Java</option>
                        <option value="haskel">Haskel</option>
                        <option value="clojure">Clojure</option>
                        <option value="prolog">Prolog</option>
                        <option value="scala">Scala</option>
                    </select>
                    <small class="form-text text-muted">Вы можете выбрать несколько вариантов.</small>
                </div>

                <!-- Биография -->
                <div class="mb-3">
                    <label for="bio" class="form-label">7) Биография:</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Напишите краткую биографию"></textarea>
                </div>

                <!-- Ознакомление с контрактом -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="contract_accepted" id="contract_accepted"
                        required>
                    <label class="form-check-label" for="contract_accepted">С контрактом ознакомлен</label>
                </div>

                <!-- Кнопка -->
                <div>
                    <button type="submit" name="save" class="btn btn-primary">Опубликовать</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>