<?php
function e($value) {
    return htmlspecialchars($value ?? '');
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

    if (empty($errors)) {
        $data = [
            'datetime'   => date('Y-m-d H:i:s'),
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone,
            'topic'      => $topic,
            'payment'    => $payment,
            'newsletter' => $newsletter
        ];

        if (!is_dir('requests')) {
            mkdir('requests', 0777);
        }

        $filename = 'requests/req_' . time() . '_' . uniqid() . '.json';

        file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $success = true;
    }
}

include 'form_template.php';