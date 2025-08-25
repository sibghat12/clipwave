<?php
// public/lib/auth.php

// Set cookie params BEFORE starting the session
if (session_status() === PHP_SESSION_NONE) {
    $secure = !empty($_SERVER['HTTPS']);
    // safe defaults
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_user() { return $_SESSION['user'] ?? null; }
function is_logged_in() { return !!current_user(); }
function is_creator() { return is_logged_in() && $_SESSION['user']['role']==='creator'; }

function require_login() {
    if (!is_logged_in()) { header('Location: /login.php'); exit; }
}
function require_creator() {
    if (!is_creator()) { http_response_code(403); echo "Creators only"; exit; }
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
