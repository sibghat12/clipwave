<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require_creator();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard.php'); exit;
}

$title = trim($_POST['title'] ?? '');
if ($title === '' || empty($_FILES['file']['tmp_name'])) {
    header('Location: /dashboard.php?error=' . urlencode('Title and file are required')); exit;
}

$allowed = ['mp4','webm','ogg','mov'];
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    header('Location: /dashboard.php?error=' . urlencode('Unsupported file type')); exit;
}

// ensure uploads dir exists
$dir = __DIR__ . '/uploads';
if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
    header('Location: /dashboard.php?error=' . urlencode('Cannot create uploads directory')); exit;
}

// safe filename + unique prefix
$basename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file']['name']));
$fname = time() . '_' . $basename;
$path = $dir . '/' . $fname;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
    header('Location: /dashboard.php?error=' . urlencode('Server failed to save the upload')); exit;
}

// In production you’ll store to Azure Blob and set $url to the blob URL
$url = '/uploads/' . $fname;

$stmt = db()->prepare("INSERT INTO videos(user_id, title, file_url) VALUES (?, ?, ?)");
$stmt->execute([ current_user()['id'], $title, $url ]);

// success → show SweetAlert on dashboard
header('Location: /dashboard.php?uploaded=1');
