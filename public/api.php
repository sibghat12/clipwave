<?php
// public/api.php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (!$action) { http_response_code(400); echo "Bad request"; exit; }

function json($data, $code=200){
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/* ---------- JSON helpers for cards ---------- */

// GET ?action=stats&video_id=#
if ($action === 'stats') {
    $vid = (int)($_GET['video_id'] ?? 0);
    $likes = db()->prepare("SELECT COUNT(*) c FROM likes WHERE video_id=?"); $likes->execute([$vid]);
    $comments = db()->prepare("SELECT COUNT(*) c FROM comments WHERE video_id=?"); $comments->execute([$vid]);
    json(['likes'=>(int)$likes->fetch()['c'], 'comments'=>(int)$comments->fetch()['c']]);
}

// GET ?action=comments_list&video_id=#
if ($action === 'comments_list') {
    $vid = (int)($_GET['video_id'] ?? 0);
    $q = db()->prepare("SELECT u.email, c.text, c.created_at
                      FROM comments c JOIN users u ON c.user_id=u.id
                      WHERE c.video_id=? ORDER BY c.id DESC");
    $q->execute([$vid]);
    json($q->fetchAll());
}

// GET ?action=likes_list&video_id=#
if ($action === 'likes_list') {
    $vid = (int)($_GET['video_id'] ?? 0);
    $q = db()->prepare("SELECT u.email FROM likes l JOIN users u ON l.user_id=u.id
                      WHERE l.video_id=? ORDER BY l.created_at DESC");
    $q->execute([$vid]);
    json($q->fetchAll());
}

// POST action=like_ajax (JSON)
if ($action === 'like_ajax') {
    if (!is_logged_in()) json(['error'=>'login-required'], 401);
    $vid = (int)($_POST['video_id'] ?? 0);
    if (!$vid) json(['error'=>'missing-video'], 400);
    $stmt = db()->prepare("INSERT IGNORE INTO likes(video_id,user_id) VALUES(?,?)");
    $stmt->execute([$vid, current_user()['id']]);
    json(['ok'=>true]);
}

// POST action=comment_ajax (JSON)
if ($action === 'comment_ajax') {
    if (!is_logged_in()) json(['error'=>'login-required'], 401);
    $vid  = (int)($_POST['video_id'] ?? 0);
    $text = trim((string)($_POST['text'] ?? ''));
    if (!$vid || $text==='') json(['error'=>'missing-fields'], 400);
    $stmt = db()->prepare("INSERT INTO comments(video_id,user_id,text) VALUES(?,?,?)");
    $stmt->execute([$vid, current_user()['id'], $text]);
    json(['ok'=>true]);
}

/* ---------- Legacy redirects (still fine for /video.php) ---------- */

if ($action === 'like') {
    require_login();
    $vid = (int)($_POST['video_id'] ?? 0);
    if ($vid) {
        $stmt = db()->prepare("INSERT IGNORE INTO likes(video_id,user_id) VALUES(?,?)");
        $stmt->execute([$vid, current_user()['id']]);
    }
    header("Location: /video.php?id=$vid&liked=1"); exit;
}

if ($action === 'comment') {
    require_login();
    $vid  = (int)($_POST['video_id'] ?? 0);
    $text = trim((string)($_POST['text'] ?? ''));
    if ($vid && $text !== '') {
        $stmt = db()->prepare("INSERT INTO comments(video_id,user_id,text) VALUES(?,?,?)");
        $stmt->execute([$vid, current_user()['id'], $text]);
    }
    header("Location: /video.php?id=$vid&commented=1"); exit;
}

http_response_code(404); echo "Unknown action";
