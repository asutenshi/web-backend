<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/conference_request.php';

function e($value) {
    return htmlspecialchars($value ?? '');
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

$subjects = $dbo->query('SELECT id, name FROM subjects ORDER BY id')->fetchAll();
$payments = $dbo->query('SELECT id, name FROM payments ORDER BY id')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request = new ConferenceRequest([
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'topic'      => $_POST['topic'] ?? '',
        'payment'    => $_POST['payment'] ?? '',
        'newsletter' => isset($_POST['newsletter']) ? 'Да' : 'Нет',
    ]);

    $errors = $request->validate();

    if (empty($errors)) {
        if ($request->save($dbo)) {
            $_SESSION['flash_success'] = true;
            header('Location: index.php');
            exit;
        }
        $errors[] = 'Ошибка при сохранении заявки.';
    }

    $_SESSION['form_data']   = $_POST;
    $_SESSION['form_errors'] = $errors;
    header('Location: index.php');
    exit;
}

if (empty($formData)) {
    $formData = $_POST;
}

include __DIR__ . '/form_template.php';
