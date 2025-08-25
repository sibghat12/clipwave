<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require __DIR__.'/lib/app.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>About · <?=APP_NAME?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<?php require __DIR__.'/partials/header.php'; ?>

<main class="page">
    <section class="card" style="max-width:900px">
        <h1>About <?=APP_NAME?></h1>
        <p>
            <strong><?=APP_NAME?></strong> is a lightweight video-sharing app built to demonstrate
            scalable cloud architecture and CI/CD deployment. It has two roles:
        </p>
        <ul>
            <li><strong>Creators</strong> – sign in to upload and manage your videos in the Dashboard.</li>
            <li><strong>Viewers</strong> – watch clips, like, and comment (sign in required for likes/comments).</li>
        </ul>

        <h2>What’s under the hood</h2>
        <ul>
            <li>Core PHP + MySQL (simple, fast to deploy).</li>
            <li>File uploads saved locally for dev; swapped to Azure Blob in production.</li>
            <li>Clean navigation: Home, About, Contact, Sign in/Sign up, and Creator Dashboard.</li>
        </ul>

        <h2>Why we built it</h2>
        <p>
            The project focuses on <em>deployment and reliability</em>:
            Docker image builds, automation with CI/CD, cloud database, and a path to
            high availability on Azure services.
        </p>

        <h2>Next steps (roadmap)</h2>
        <ul>
            <li
