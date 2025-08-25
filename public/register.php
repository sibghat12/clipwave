<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require __DIR__.'/lib/app.php';

$success = false; $err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $role  = ($_POST['role'] ?? 'viewer') === 'creator' ? 'creator' : 'viewer';

    if (!$email || !$pass) {
        $err = "Email and password required";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = db()->prepare("INSERT INTO users(email,password_hash,role) VALUES (?,?,?)");
        try {
            $stmt->execute([$email,$hash,$role]);
            $success = true;
        } catch (Throwable $e) {
            $err = "Email already in use";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register · <?=APP_NAME?></title>
    <link rel="stylesheet" href="/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php require __DIR__.'/partials/header.php'; ?>

<main class="page">
    <h1>Create an account</h1>

    <form method="post" class="card" style="max-width:400px">
        <label>Email</label>
        <input name="email" type="email" placeholder="you@example.com" required>

        <label>Password</label>
        <input name="password" type="password" placeholder="Choose a password" required>

        <label>Role</label>
        <div>
            <label><input type="radio" name="role" value="viewer" checked> Viewer</label>
            <label style="margin-left:12px"><input type="radio" name="role" value="creator"> Creator</label>
        </div>

        <button style="margin-top:10px">Sign up</button>
    </form>

    <p class="muted" style="margin-top:14px">
        Already have an account? <a href="/login.php">Sign in</a>
    </p>
</main>

<?php require __DIR__.'/partials/footer.php'; ?>

<?php if($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: 'You can now log in to <?=APP_NAME?>',
            confirmButtonText: 'Go to Login'
        }).then(() => { window.location = '/login.php'; });
    </script>
<?php elseif($err): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= addslashes($err) ?>'
        });
    </script>
<?php endif; ?>

</body>
</html>
