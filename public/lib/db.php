<?php
// Simple PDO singleton. Uses DDEV defaults locally.
function db() {
    static $pdo;
    if ($pdo) return $pdo;

    $host = getenv('DB_HOST') ?: 'db';
    $name = getenv('DB_NAME') ?: 'db';
    $user = getenv('DB_USER') ?: 'db';
    $pass = getenv('DB_PASS') ?: 'db';

    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}
