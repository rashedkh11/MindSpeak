<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codes'])) {
    $codes = json_decode($_POST['codes'], true);
    if (!is_array($codes)) {
        die("Invalid codes format.");
    }

    $filename = "backup_codes_" . date("Ymd_His") . ".txt";
    header('Content-Type: text/plain');
    header("Content-Disposition: attachment; filename=$filename");

    echo "Your Backup Codes:\n\n";
    foreach ($codes as $code) {
        echo $code . "\n";
    }
    exit;
} else {
    die("Unauthorized access.");
}
