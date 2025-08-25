<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require __DIR__.'/lib/app.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT v.*, u.email creator FROM videos v JOIN users u ON v.user_id=u.id WHERE v.id=?");
$stmt->execute([$id]); $v = $stmt->fetch(); if(!$v){ http_response_code(404); exit('Not found'); }

$likes = db()->prepare("SELECT COUNT(*) c FROM likes WHERE video_id=?");
$likes->execute([$id]); $likeCount = (int)$likes->fetch()['c'];

$comments = db()->prepare("SELECT c.text, u.email, c.created_at
                           FROM comments c JOIN users u ON c.user_id=u.id
                           WHERE c.video_id=?
                           ORDER BY c.id DESC");
$comments->execute([$id]);

$logged = is_logged_in();
$return = urlencode($_SERVER['REQUEST_URI'] ?? "/video.php?id=$id");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=h($v['title'])?> · <?=APP_NAME?></title>
    <link rel="stylesheet" href="/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php require __DIR__.'/partials/header.php'; ?>

<main class="page">
    <article class="card">
        <h1 style="margin:6px 0"><?=h($v['title'])?></h1>
        <p class="muted">by <?=h($v['creator'])?></p>

        <div class="thumb" id="actions">
            <video controls src="<?=h($v['file_url'])?>"></video>
        </div>

        <div class="actions">
            <?php if($logged): ?>
                <form method="post" action="/api.php">
                    <input type="hidden" name="action" value="like">
                    <input type="hidden" name="video_id" value="<?=$id?>">
                    <button>❤️ Like</button>
                </form>
            <?php else: ?>
                <button class="need-login" data-target="/login.php?return=<?=$return?>">❤️ Like</button>
            <?php endif; ?>
            <span class="badge-pill"><?=$likeCount?> likes</span>
        </div>

        <section style="margin-top:16px">
            <h3>Comments</h3>

            <?php if($logged): ?>
                <form method="post" action="/api.php" class="card" style="padding:12px;box-shadow:none">
                    <input type="hidden" name="action" value="comment">
                    <input type="hidden" name="video_id" value="<?=$id?>">
                    <input name="text" type="text" placeholder="Write a comment..." required>
                    <button style="margin-top:8px">Post</button>
                </form>
            <?php else: ?>
                <button class="need-login" data-target="/login.php?return=<?=$return?>">💬 Login to comment</button>
            <?php endif; ?>

            <?php foreach($comments as $c): ?>
                <div class="comment">
                    <b><?=h($c['email'])?></b> · <span class="muted"><?=date('M j H:i', strtotime($c['created_at']))?></span><br>
                    <?=h($c['text'])?>
                </div>
            <?php endforeach; ?>
            <?php if(!$comments->rowCount()) echo "<p class='muted'>No comments yet.</p>"; ?>
        </section>
    </article>
</main>

<?php require __DIR__.'/partials/footer.php'; ?>

<script>
    // Prompt non-logged-in users to sign in / sign up, then redirect back
    document.querySelectorAll('.need-login').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const target = btn.dataset.target || '/login.php';
            Swal.fire({
                title: 'Sign in required',
                text: 'Log in to like or comment.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Sign in',
                cancelButtonText: 'Sign up'
            }).then(result=>{
                if (result.isConfirmed) window.location = target;
                else window.location = '/register.php';
            });
        });
    });

    // Optional: show success toasts after like/comment via query flags
    const params = new URLSearchParams(location.search);
    if (params.get('liked')==='1') {
        Swal.fire({ icon:'success', title:'Liked!', timer:1200, showConfirmButton:false });
        params.delete('liked'); history.replaceState({},'',location.pathname+'?'+params.toString());
    }
    if (params.get('commented')==='1') {
        Swal.fire({ icon:'success', title:'Comment posted', timer:1200, showConfirmButton:false });
        params.delete('commented'); history.replaceState({},'',location.pathname+'?'+params.toString());
    }
</script>

</body>
</html>
