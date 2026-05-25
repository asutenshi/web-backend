<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация на конференцию</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 20px auto; }
        .error { color: red; background: #faa; padding: 10px; margin-bottom: 15px; border: 1px solid red; }
        .success { color: green; font-weight: bold; background: #efe; padding: 20px; border: 1px solid green; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="email"], select { width: 100%; padding: 8px; box-sizing: border-box; }
    </style>
</head>
<body>

    <?php if ($success): ?>
        <div class="success">
            <h2>Успешно!</h2>
            <p>Ваша заявка принята. Мы свяжемся с вами в ближайшее время.</p>
            <a href="index.php">Отправить новую заявку</a>
        </div>
    <?php else: ?>

        <h1>Заявка на конференцию</h1>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="first_name" value="<?= e($formData['first_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Фамилия:</label>
                <input type="text" name="last_name" value="<?= e($formData['last_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?= e($formData['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Телефон:</label>
                <input type="text" name="phone" value="<?= e($formData['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Тематика конференции:</label>
                <select name="topic">
                    <option value="">-- Выберите --</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= e($s['id']) ?>" <?= ((int)($formData['topic'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
                            <?= e($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Метод оплаты:</label>
                <?php foreach ($payments as $p): ?>
                    <label style="font-weight: normal;">
                        <input type="radio" name="payment" value="<?= e($p['id']) ?>"
                               <?= ((int)($formData['payment'] ?? 0) === (int)$p['id']) ? 'checked' : '' ?>>
                        <?= e($p['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label style="font-weight: normal;">
                    <input type="checkbox" name="newsletter" <?= isset($formData['newsletter']) ? 'checked' : '' ?>>
                    Получать рассылку о конференции
                </label>
            </div>

            <button type="submit" style="padding: 10px 20px;">Отправить заявку</button>
        </form>

    <?php endif; ?>

</body>
</html>
