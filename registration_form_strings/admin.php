<?php
function e($value) {
    return htmlspecialchars($value ?? '');
}

$message = '';
$requestsFile = 'requests.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_deleted'])) {
    $idsToDelete = $_POST['to_delete'] ?? [];
    
    if (!empty($idsToDelete) && file_exists($requestsFile)) {
        $lines = file($requestsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $newLines = [];

        foreach ($lines as $line) {
            $parts = explode('||', $line);
            $lineId = $parts[0];
            
            if (in_array($lineId, $idsToDelete)) {
                $parts[10] = 'deleted';
                $newLines[] = implode('||', $parts);
            } else {
                $newLines[] = $line;
            }
        }

        file_put_contents($requestsFile, implode(PHP_EOL, $newLines) . PHP_EOL);
        $message = 'Выбранные заявки помечены как удалённые.';
    } elseif (!empty($idsToDelete)) {
        $message = 'Файл с заявками еще не создан.';
    }
}

$requests = [];
if (file_exists($requestsFile)) {
    $lines = file($requestsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $parts = explode('||', $line);

        if (count($parts) >= 11) {
            $request = [
                'id'         => $parts[0],
                'datetime'   => $parts[1],
                'first_name'  => $parts[2],
                'last_name'   => $parts[3],
                'email'      => $parts[4],
                'phone'      => $parts[5],
                'topic'      => $parts[6],
                'payment'    => $parts[7],
                'newsletter' => $parts[8],
                'ip'         => $parts[9],
                'status'     => $parts[10],
            ];

            if ($request['status'] === 'active') {
                $requests[] = $request;
            }
        }
    }
}



include 'admin_template.php';