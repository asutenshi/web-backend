<?php
session_start();

require_once __DIR__ . "/conference_request.php";

function e($value)
{
    return htmlspecialchars($value ?? "");
}

$message = "";

if (isset($_SESSION["flash_message"])) {
    $message = $_SESSION["flash_message"];
    unset($_SESSION["flash_message"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mark_deleted"])) {
    $idsToDelete = $_POST["to_delete"] ?? [];

    if (!empty($idsToDelete)) {
        if (ConferenceRequest::markDeleted($idsToDelete)) {
            $_SESSION["flash_message"] =
                "Выбранные заявки помечены как удалённые";
        } else {
            $_SESSION["flash_message"] = "Не удалось обновить заявки";
        }

        header("Location: admin.php");
    } else {
        $_SESSION["flash_message"] = "Не выбрано ни одной заявки";
        header("Location: admin.php");
    }

    exit();
}

$requests = ConferenceRequest::loadActive();

include __DIR__ . "/admin_template.php";
