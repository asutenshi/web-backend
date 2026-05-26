<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Task.php';

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$task = Task::getById($dbo, $id);

if (!$task) {
    die('Задача не найдена. <a href="index.php">Вернуться</a>');
}

$errors = [];
$success = false;

$taskTypes = $dbo->query('SELECT id, name FROM task_types ORDER BY id')->fetchAll();
$durations = [
    15 => '15 минут', 30 => '30 минут', 60 => '1 час',
    120 => '2 часа', 240 => '4 часа', 480 => 'Весь рабочий день (8 ч)'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedData = [
        'id'            => $task->getId(),
        'title'         => trim($_POST['title'] ?? ''),
        'type_id'       => $_POST['type_id'] ?? '',
        'location'      => trim($_POST['location'] ?? ''),
        'task_date'     => $_POST['task_date'] ?? '',
        'task_time'     => $_POST['task_time'] ?? '',
        'duration'      => $_POST['duration'] ?? '',
        'comment'       => trim($_POST['comment'] ?? ''),
        'is_completed'  => isset($_POST['is_completed']) ? 1 : 0
    ];

    $updatedTask = new Task($updatedData);
    $errors = $updatedTask->validate();

    if (empty($errors)) {
        if ($updatedTask->save($dbo)) {
            $_SESSION['flash_success'] = true;
            header('Location: edit.php?id=' . $task->getId());
            exit;
        } else {
            $errors[] = "Не удалось обновить задачу.";
        }
    }
    $task = $updatedTask;
}

if (isset($_SESSION['flash_success'])) {
    $success = true;
    unset($_SESSION['flash_success']);
}

$dt = strtotime($task->getTaskDatetime());
$taskDate = date('Y-m-d', $dt);
$taskTime = date('H:i', $dt);

include __DIR__ . '/edit_template.php';
