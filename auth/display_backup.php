<?php
require __DIR__ . '/../db.php';
define('SECURE_ACCESS', true);
require __DIR__ . '/../config.php';

//session_start();

// Enforce HTTPS
$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if (!$isLocal) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if (!$isSecure) {
        die("This page must be accessed over a secure HTTPS connection.");
    }
}


// Check for backup codes in session
if (!isset($_SESSION['backup_codes_display'])) {
    die("No codes to display.");
}

$backup_codes = $_SESSION['backup_codes_display'];

// One-time view: unset after use
unset($_SESSION['backup_codes_display']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Backup Codes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2em;
            background: #f9f9f9;
        }
        code {
            display: block;
            background: #eee;
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
        }
        button {
            margin-top: 15px;
            padding: 10px 15px;
            font-size: 14px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .download-btn {
            background-color: #4CAF50;
            color: white;
        }
        .copy-btn {
            background-color: #2196F3;
            color: white;
        }
        p.warning {
            color: red;
            font-weight: bold;
        }
        action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .return-btn {
            background-color: #3a7bd5;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            flex: 1;
        }
    </style>
</head>
<body>
    <h2>Save These Backup Codes Securely</h2>

    <div id="codes-container">
        <?php foreach ($backup_codes as $code): ?>
            <code><?= htmlspecialchars($code) ?></code>
        <?php endforeach; ?>
    </div>

    <p class="warning">These codes will not be shown again. Store them in a safe place.</p>

    <div class="action-buttons">
        <!-- Download Codes -->
        <form method="post" action="download_backup_codes.php" style="flex: 1">
            <input type="hidden" name="codes" value="<?= htmlspecialchars(json_encode($backup_codes)) ?>">
            <button type="submit" class="download-btn">Download as .txt</button>
        </form>

        <!-- Copy to Clipboard -->
        <button class="copy-btn" onclick="copyCodes()" style="flex: 1">Copy All to Clipboard</button>
    </div>
    <div style="margin-top: 20px;">
        <a href="verify_otp.php" class="return-btn">Return to Verification</a>
    </div>

    <script>
        function copyCodes() {
            const codes = Array.from(document.querySelectorAll('#codes-container code'))
                               .map(el => el.textContent).join('\n');
            navigator.clipboard.writeText(codes).then(() => {
                alert('Backup codes copied to clipboard!');
            });
        }
    </script>
</body>
</html>

