<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Карточка задачи</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; color: #333; }
        .box { border: 1px solid #000; padding: 20px; }
        .form-group { margin-bottom: 15px; display: flex; align-items: flex-start; }
        .form-group label { width: 150px; text-align: right; margin-right: 15px; padding-top: 5px; font-weight: bold; }
        .form-group .controls { flex: 1; }
        input[type="text"], input[type="date"], input[type="time"], select, textarea { 
            width: 100%; padding: 6px; box-sizing: border-box; border: 1px solid #ccc; 
        }
        .short-inputs { display: flex; gap: 10px; width: 300px; }
        button { padding: 8px 20px; border: 2px solid #000; background: #fff; cursor: pointer; font-weight: bold; }
        .error { color: red; background: #fee; padding: 10px; margin-bottom: 15px; border: 1px solid red; }
        .success { color: green; background: #efe; padding: 15px; border: 1px solid green; margin-bottom: 15px; }
    </style>
</head>
<body>

    <h1>Редактирование задачи</h1>
    <p><a href="index.php">← Назад к списку</a></p>

    <div class="box">
        <?php if ($success): ?>
            <div class="success">Задача успешно обновлена!</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?= $task->getId() ?>" method="POST">
            <div class="form-group">
                <label>Тема:</label>
                <div class="controls">
                    <input type="text" name="title" value="<?= e($task->getTitle()) ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Тип:</label>
                <div class="controls" style="width: 200px;">
                    <select name="type_id">
                        <?php foreach ($taskTypes as $type): ?>
                            <option value="<?= e($type['id']) ?>" <?= ((int)$task->getTypeId() === (int)$type['id']) ? 'selected' : '' ?>>
                                <?= e($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Место:</label>
                <div class="controls">
                    <input type="text" name="location" value="<?= e($task->getLocation()) ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Дата и время:</label>
                <div class="controls short-inputs">
                    <input type="date" name="task_date" value="<?= e($taskDate) ?>">
                    <input type="time" name="task_time" value="<?= e($taskTime) ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Длительность:</label>
                <div class="controls" style="width: 200px;">
                    <select name="duration">
                        <?php foreach ($durations as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ((int)$task->getDuration() === $val) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Комментарий:</label>
                <div class="controls">
                    <textarea name="comment" rows="3"><?= e($task->getComment()) ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label>Статус:</label>
                <div class="controls" style="padding-top: 5px;">
                    <label style="text-align: left; font-weight: normal; width: auto;">
                        <input type="checkbox" name="is_completed" <?= $task->isCompleted() ? 'checked' : '' ?>>
                        Отметить как выполненную
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label></label>
                <div class="controls">
                    <button type="submit">Сохранить изменения</button>
                </div>
            </div>
        </form>
    </div>

</body>
</html>
