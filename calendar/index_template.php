<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой календарь</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; color: #333; }
        .block-title { font-weight: bold; margin: 20px 0 10px; border-bottom: 2px solid #000; display: inline-block; padding-right: 20px; }
        .box { border: 1px solid #000; padding: 20px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; display: flex; align-items: flex-start; }
        .form-group label { width: 150px; text-align: right; margin-right: 15px; padding-top: 5px; }
        .form-group .controls { flex: 1; }
        input[type="text"], input[type="date"], input[type="time"], select, textarea { 
            width: 100%; padding: 6px; box-sizing: border-box; border: 1px solid #ccc; 
        }
        .short-inputs { display: flex; gap: 10px; width: 300px; }
        button { padding: 8px 20px; border: 2px solid #000; background: #fff; cursor: pointer; font-weight: bold; }
        
        .error { color: red; background: #fee; padding: 10px; margin-bottom: 15px; border: 1px solid red; }
        .success { color: green; background: #efe; padding: 15px; border: 1px solid green; margin-bottom: 15px; }
        
        .filters { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .filters select, .filters input[type="date"] { width: auto; }
        .quick-links { white-space: nowrap; }
        .quick-links a { margin: 0 5px; color: #0066cc; text-decoration: none; }
        .quick-links a:hover { text-decoration: underline; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #e0e0e0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>

    <h1>Мой календарь</h1>

    <div class="block-title">Новая задача</div>
    <div class="box">
        <?php if ($success): ?>
            <div class="success">Задача успешно добавлена!</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label>Тема:</label>
                <div class="controls">
                    <input type="text" name="title" value="<?= e($formData['title'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Тип:</label>
                <div class="controls" style="width: 200px;">
                    <select name="type_id">
                        <?php foreach ($taskTypes as $type): ?>
                            <option value="<?= e($type['id']) ?>" <?= ((int)($formData['type_id'] ?? 0) === (int)$type['id']) ? 'selected' : '' ?>>
                                <?= e($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Место:</label>
                <div class="controls">
                    <input type="text" name="location" value="<?= e($formData['location'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Дата и время:</label>
                <div class="controls short-inputs">
                    <input type="date" name="task_date" value="<?= e($formData['task_date'] ?? '') ?>">
                    <input type="time" name="task_time" value="<?= e($formData['task_time'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Длительность:</label>
                <div class="controls" style="width: 200px;">
                    <select name="duration">
                        <?php foreach ($durations as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ((int)($formData['duration'] ?? 60) === $val) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Комментарий:</label>
                <div class="controls">
                    <textarea name="comment" rows="3"><?= e($formData['comment'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="form-group">
                <label></label>
                <div class="controls">
                    <button type="submit">Добавить</button>
                </div>
            </div>
        </form>
    </div>

    <div class="block-title">Список задач</div>
    <div class="box">
        <form method="GET" action="index.php" class="filters">
            <select name="filter" onchange="this.form.submit()">
                <option value="current" <?= $filter === 'current' ? 'selected' : '' ?>>Текущие задачи</option>
                <option value="overdue" <?= $filter === 'overdue' ? 'selected' : '' ?>>Просроченные задачи</option>
                <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Выполненные задачи</option>
            </select>
            
            <input type="date" name="exact_date" value="<?= e(preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter) ? $dateFilter : '') ?>" onchange="this.form.submit()">
            
            <span class="quick-links">
                <a href="?filter=current&date_filter=today">сегодня</a> | 
                <a href="?filter=current&date_filter=tomorrow">завтра</a> | 
                <a href="?filter=current&date_filter=this_week">на эту неделю</a> | 
                <a href="?filter=current&date_filter=next_week">на след. неделю</a>
            </span>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Тип</th>
                    <th>Задача</th>
                    <th>Место</th>
                    <th>Дата и время ▼</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tasks)): ?>
                    <tr><td colspan="4" style="text-align:center;">Задач не найдено</td></tr>
                <?php else: ?>
                    <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td><?= e($t->getTypeName()) ?></td>
                            <td>
                                <!-- Переход в карточку задачи (редактирование) -->
                                <a href="edit.php?id=<?= $t->getId() ?>">
                                    <?= e($t->getTitle()) ?>
                                </a>
                            </td>
                            <td><?= e($t->getLocation() ?: '-') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($t->getTaskDatetime())) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
