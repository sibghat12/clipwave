<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require __DIR__.'/lib/app.php';
require_creator();

$uid = current_user()['id'];
$mine = db()->prepare("SELECT id, title, file_url, created_at FROM videos WHERE user_id=? ORDER BY id DESC");
$mine->execute([$uid]);

$uploaded = isset($_GET['uploaded']);
$errorMsg = $_GET['error'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard · <?=APP_NAME?></title>
    <link rel="stylesheet" href="/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* small extras for the toggle panel and header row */
        .bar { display:flex; align-items:center; gap:12px; justify-content:space-between; margin: 8px 0 16px; }
        .btn-primary { background: var(--brand); color:#fff; border:0; padding:10px 14px; border-radius:10px; cursor:pointer; }
        .upload-panel { display:none; }
        .upload-panel.open { display:block; }
    </style>
</head>
<body>

<?php require __DIR__.'/partials/header.php'; ?>

<main class="page">
    <div class="bar">
        <h1 style="margin:0">Creator Dashboard</h1>
        <button id="btnToggleUpload" class="btn-primary">＋ Add video</button>
    </div>

    <section id="uploadPanel" class="upload-panel">
        <form method="post" action="/upload.php" enctype="multipart/form-data" class="card" style="max-width:680px">
            <h2 style="margin-top:0">Upload a new video</h2>
            <label>Title</label>
            <input name="title" type="text" placeholder="Amazing clip title" required>

            <label>Video file</label>
            <input name="file" type="file" accept="video/*" required>

            <button style="margin-top:10px" class="btn-primary">Upload</button>
            <p class="muted" style="margin-top:8px">Supported: mp4, webm, ogg, mov</p>
        </form>
    </section>

    <h2>Your videos</h2>

    <?php if ($mine->rowCount() === 0): ?>
        <div class="empty">
            <h3>No uploads yet</h3>
            <p>Click “＋ Add video” to upload your first clip to <?=APP_NAME?>.</p>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach($mine as $v): ?>
                <article class="card">
                    <div class="thumb">
                        <video preload="metadata" playsinline src="<?=h($v['file_url'])?>"></video>
                        <span class="badge"><?=date('M j', strtotime($v['created_at']))?></span>
                    </div>
                    <h3 style="margin-top:10px"><a href="/video.php?id=<?=$v['id']?>"><?=h($v['title'])?></a></h3>
                    <p class="muted">Uploaded on <?=h($v['created_at'])?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__.'/partials/footer.php'; ?>

<script>
    // Toggle the upload panel
    const btn = document.getElementById('btnToggleUpload');
    const panel = document.getElementById('uploadPanel');
    btn.addEventListener('click', () => {
        panel.classList.toggle('open');
        btn.textContent = panel.classList.contains('open') ? '− Close' : '＋ Add video';
    });

    // SweetAlert notifications
    <?php if ($uploaded): ?>
    Swal.fire({ icon:'success', title:'Upload complete', text:'Your video is now available.', confirmButtonText:'Great' });
    <?php endif; ?>
    <?php if ($errorMsg): ?>
    Swal.fire({ icon:'error', title:'Upload failed', text:'<?= addslashes($errorMsg) ?>' });
    <?php endif; ?>
</script>

</body>
</html>
