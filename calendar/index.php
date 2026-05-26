<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Task.php';

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$errors = [];
$success = false;
$formData = [];

if (isset($_SESSION['flash_success'])) {
    $success = true;
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    $errors   = $_SESSION['form_errors'] ?? [];
    unset($_SESSION['form_data'], $_SESSION['form_errors']);
}

$taskTypes = $dbo->query('SELECT id, name FROM task_types ORDER BY id')->fetchAll();

$durations = [
    15 => '15 минут',
    30 => '30 минут',
    60 => '1 час',
    120 => '2 часа',
    240 => '4 часа',
    480 => 'Весь рабочий день (8 ч)'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = new Task([
        'title'      => trim($_POST['title'] ?? ''),
        'type_id'    => $_POST['type_id'] ?? '',
        'location'   => trim($_POST['location'] ?? ''),
        'task_date'  => $_POST['task_date'] ?? '',
        'task_time'  => $_POST['task_time'] ?? '',
        'duration'   => $_POST['duration'] ?? '',
        'comment'    => trim($_POST['comment'] ?? '')
    ]);

    $errors = $task->validate();

    if (empty($errors)) {
        if ($task->save($dbo)) {
            $_SESSION['flash_success'] = true;
            header('Location: index.php');
            exit;
        }
        $errors[] = 'Ошибка при сохранении задачи в БД.';
    }

    $_SESSION['form_data']   = $_POST;
    $_SESSION['form_errors'] = $errors;
    header('Location: index.php');
    exit;
}

if (empty($formData) && empty($errors)) {
    $formData['task_date'] = date('Y-m-d');
    $formData['task_time'] = date('H:00', strtotime('+1 hour'));
}

$filter = $_GET['filter'] ?? 'current';
$dateFilter = $_GET['date_filter'] ?? null;
if (isset($_GET['exact_date']) && !empty($_GET['exact_date'])) {
    $dateFilter = $_GET['exact_date'];
}

$tasks = Task::loadList($dbo, $filter, $dateFilter);

include __DIR__ . '/index_template.php';
