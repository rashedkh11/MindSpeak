<?php
// csrf.php


function getCSRFToken() {
    if (empty($_SESSION['csrf_token']) || 
       (time() - ($_SESSION['csrf_token_time'] ?? 0) > 3600)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function checkCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $token) ||
        (time() - $_SESSION['csrf_token_time'] > 3600)) {
        http_response_code(403);
        die("⛔ Session expired or invalid request");
    }
}
