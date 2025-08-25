<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require __DIR__.'/lib/app.php';

$sent = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $msg   = trim($_POST['message'] ?? '');

    if (!$email || !$msg) {
        $errorMsg = 'Email and message are required.';
    } else {
        try {
            // Ensure contacts table exists
            db()->exec("CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )");

            $stmt = db()->prepare("INSERT INTO contacts(email, message) VALUES (?, ?)");
            $stmt->execute([$email, $msg]);
            $sent = true;
        } catch (Throwable $e) {
            $errorMsg = 'Could not save your message.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Contact · <?=APP_NAME?></title>
    <link rel="stylesheet" href="/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php require __DIR__.'/partials/header.php'; ?>

<main class="page">
    <section class="card" style="max-width:600px">
        <h1>Contact us</h1>
        <p class="muted">We’d love to hear from you — feedback, questions, or issues.</p>

        <form method="post">
            <label>Email</label>
            <input type="email" name="email" placeholder="you@example.com" required>

            <label>Message</label>
            <textarea name="message" rows="5" placeholder="How can we help?" required></textarea>

            <button>Send message</button>
        </form>
    </section>
</main>

<?php require __DIR__.'/partials/footer.php'; ?>

<?php if($sent): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Message sent!',
            text: 'Thanks for reaching out. We will get back to you soon.'
        });
    </script>
<?php elseif($errorMsg): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= addslashes($errorMsg) ?>'
        });
    </script>
<?php endif; ?>

</body>
</html>
