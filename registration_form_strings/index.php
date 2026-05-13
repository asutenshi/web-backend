<?php
function e($value) {
    return htmlspecialchars($value ?? '');
}

function containsDelimeter($value) {
    return strpos($value, '||') !== false;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $topic     = $_POST['topic'] ?? '';
    $payment   = $_POST['payment'] ?? '';
    $newsletter = isset($_POST['newsletter']) ? 'Да' : 'Нет';

    if (empty($firstName)) $errors[] = "Введите имя";
    if (empty($lastName))  $errors[] = "Введите фамилию";
    if (empty($email))     $errors[] = "Введите email";
    if (empty($phone))     $errors[] = "Введите телефон";
    if (empty($topic))     $errors[] = "Выберите тематику";
    if (empty($payment))   $errors[] = "Выберите метод оплаты";

    foreach ([$firstName, $lastName, $email, $phone] as $field) {
        if (containsDelimeter($field)) {
            $errors[] = "Строка `{$field}` содержит недопустимые символы";
        }
    }

    if (empty($errors)) {
        $id = uniqid();
        $datetime = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $line = implode('||', [
            $id,
            $datetime,
            $firstName,
            $lastName,
            $email,
            $phone,
            $topic,
            $payment,
            $newsletter,
            $ip,
            'active'
        ]) . PHP_EOL;
        
        file_put_contents('requests.txt', $line, FILE_APPEND | LOCK_EX);

        $success = true;
    }
}

include 'form_template.php';