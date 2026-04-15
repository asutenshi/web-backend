<?php
function e($value) {
    return htmlspecialchars($value ?? '');
}

$message = '';
$requestsDir = 'requests/';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_files'])) {
    $filesToDelete = $_POST['to_delete'] ?? [];
    
    $count = 0;
    foreach ($filesToDelete as $file) {
        $safeFile = $requestsDir . basename($file);
        
        if (file_exists($safeFile)) {
            unlink($safeFile);
            $count++;
        }
    }
    $message = "Успешно удалено заявок: $count";
}

$files = glob($requestsDir . '*.json');
$requestsData = [];

foreach ($files as $file) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    
    if ($data) {
        $data['filename'] = basename($file);
        $requestsData[] = $data;
    }
}

usort($requestsData, function($a, $b) {
    return $b['datetime'] <=> $a['datetime'];
});

include 'admin_template.php';