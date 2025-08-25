<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require __DIR__.'/lib/app.php';

$videos = db()->query("SELECT v.id,v.title,v.file_url,v.created_at,u.email creator
                       FROM videos v JOIN users u ON v.user_id=u.id
                       ORDER BY v.id DESC")->fetchAll();
$logged = is_logged_in();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=APP_NAME?></title>
    <link rel="stylesheet" href="/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php require __DIR__.'/partials/header.php'; ?>

<main class="page">
    <?php if(empty($videos)): ?>
        <div class="empty"><h2>No videos yet.</h2><p>Creators can upload their first clip from the Dashboard.</p></div>
    <?php else: ?>
        <div class="grid">
            <?php foreach($videos as $v): ?>
                <?php $dateLabel = $v['created_at'] ? date('M j', strtotime($v['created_at'])) : ''; ?>
                <article class="card" data-video-id="<?=$v['id']?>">

                    <div class="thumb">
                        <video class="card-video" preload="metadata" playsinline src="<?=h($v['file_url'])?>"></video>
                        <button type="button" class="play-overlay" aria-label="Play"></button>
                        <?php if($dateLabel): ?><span class="badge"><?=$dateLabel?></span><?php endif; ?>
                    </div>

                    <!-- moved below video -->
                    <h3><?=h($v['title'])?></h3>
                    <p class="muted">by <?=h($v['creator'])?></p>

                    <div class="card-actions">
                        <button class="btn-chip like-btn">❤️ Like <span class="like-count">0</span></button>
                        <button class="btn-chip comments-btn">💬 Comments <span class="comment-count">0</span></button>
                    </div>

                    <div class="panel" aria-hidden="true">
                        <div class="panel-body"></div>
                        <form class="comment-form" style="display:none">
                            <input type="text" name="text" placeholder="Write a comment…">
                            <button>Post</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__.'/partials/footer.php'; ?>

<script>
    /* --- existing JS stays unchanged (likes/comments + play overlay) --- */
    const LOGGED_IN = <?= $logged ? 'true' : 'false' ?>;
    function promptLogin(){const ret=encodeURIComponent(location.pathname+location.search);Swal.fire({title:'Sign in required',text:'Log in to like or comment.',icon:'info',showCancelButton:true,confirmButtonText:'Sign in',cancelButtonText:'Sign up'}).then(res=>{if(res.isConfirmed)location.href='/login.php?return='+ret;else location.href='/register.php';});}
    async function fetchJSON(url,opts={}){const r=await fetch(url,opts);if(!r.ok)throw r;return r.json();}
    async function loadStats(card){const vid=card.dataset.videoId;const stats=await fetchJSON(`/api.php?action=stats&video_id=${vid}`);card.querySelector('.like-count').textContent=stats.likes;card.querySelector('.comment-count').textContent=stats.comments;}
    async function openComments(card){const panel=card.querySelector('.panel');const body=card.querySelector('.panel-body');const form=card.querySelector('.comment-form');panel.classList.add('open');const vid=card.dataset.videoId;const rows=await fetchJSON(`/api.php?action=comments_list&video_id=${vid}`);body.innerHTML=`<h4>Comments</h4>`+(rows.length?rows.map(r=>`<div class="item"><b>${r.email}</b> <span class="muted">${new Date(r.created_at).toLocaleString()}</span><br>${escapeHtml(r.text)}</div>`).join(''):`<div class="muted">No comments yet.</div>`);if(LOGGED_IN){form.style.display='flex';form.onsubmit=async e=>{e.preventDefault();const text=form.querySelector('[name=text]').value.trim();if(!text)return;const fd=new FormData();fd.append('action','comment_ajax');fd.append('video_id',vid);fd.append('text',text);const r=await fetch('/api.php',{method:'POST',body:fd});if(r.status===401){promptLogin();return;}if(!r.ok){Swal.fire('Error','Could not post comment','error');return;}form.reset();await loadStats(card);await openComments(card);};}else{form.style.display='none';}}
    async function openLikes(card){const panel=card.querySelector('.panel');const body=card.querySelector('.panel-body');const form=card.querySelector('.comment-form');panel.classList.add('open');form.style.display='none';const vid=card.dataset.videoId;const rows=await fetchJSON(`/api.php?action=likes_list&video_id=${vid}`);body.innerHTML=`<h4>Likes</h4>`+(rows.length?rows.map(r=>`<div class="item">${r.email}</div>`).join(''):`<div class="muted">No likes yet.</div>`);}
    function escapeHtml(s){return s.replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}
    document.querySelectorAll('.card').forEach(card=>{loadStats(card).catch(()=>{});const vid=card.dataset.videoId;card.querySelector('.like-btn').addEventListener('click',async()=>{if(!LOGGED_IN)return promptLogin();const fd=new FormData();fd.append('action','like_ajax');fd.append('video_id',vid);const r=await fetch('/api.php',{method:'POST',body:fd});if(r.status===401){promptLogin();return;}if(!r.ok){Swal.fire('Error','Could not like','error');return;}await loadStats(card);await openLikes(card);});card.querySelector('.comments-btn').addEventListener('click',()=>openComments(card));const video=card.querySelector('.card-video');const playBtn=card.querySelector('.play-overlay');function showOverlay(show){playBtn.style.opacity=show?1:0;playBtn.style.pointerEvents=show?'auto':'none';}showOverlay(true);playBtn.addEventListener('click',()=>{video.controls=true;video.muted=true;video.play().catch(()=>{});showOverlay(false);});video.addEventListener('pause',()=>showOverlay(true));video.addEventListener('ended',()=>showOverlay(true));});
</script>

</body>
</html>
