<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .message { color: blue; font-weight: bold; margin-bottom: 15px; }
        .no-data { color: #666; font-style: italic; }
        tr:hover { background-color: #f9f9f9; }
    </style>
</head>
<body>

    <h1>Список поступивших заявок</h1>
    <p><a href="index.php">← Назад к форме регистрации</a></p>

    <?php if ($message): ?>
        <div class="message"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <p class="no-data">Заявок пока нет.</p>
    <?php else: ?>
        <form action="admin.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Выбрать</th>
                        <th>Дата/Время</th>
                        <th>Имя Фамилия</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Тематика</th>
                        <th>Оплата</th>
                        <th>Рассылка</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="to_delete[]" value="<?= e($req->getId()) ?>">
                            </td>
                            <td><?= e($req->getDatetime()) ?></td>
                            <td><?= e($req->getFirstName()) ?> <?= e($req->getLastName()) ?></td>
                            <td><?= e($req->getEmail()) ?></td>
                            <td><?= e($req->getPhone()) ?></td>
                            <td><?= e($req->getTopic()) ?></td>
                            <td><?= e($req->getPayment()) ?></td>
                            <td><?= e($req->getNewsletter()) ?></td>
                            <td><?= e($req->getIp()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <br>
            <button type="submit" name="mark_deleted" onclick="return confirm('Пометить выбранные заявки как удалённые?')">
                Пометить как удалённые
            </button>
        </form>
    <?php endif; ?>

</body>
</html>
