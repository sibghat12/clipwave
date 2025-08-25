<?php
require __DIR__.'/lib/db.php';
require __DIR__.'/lib/auth.php';
require __DIR__.'/lib/app.php';

$err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare("SELECT id,email,password_hash,role FROM users WHERE email=?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password_hash'])) {
        $_SESSION['user'] = ['id'=>$u['id'],'email'=>$u['email'],'role'=>$u['role']];

        // If return param exists, go back there
        $return = $_GET['return'] ?? '';
        if ($return && str_starts_with($return, '/')) {
            header("Location: $return");
        } else {
            header("Location: /");
        }
        exit;
    } else {
        $err = "Invalid email or password";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign in · <?=APP_NAME?></title>
    <link rel="stylesheet" href="/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php require __DIR__.'/partials/header.php'; ?>

<main class="page">
    <h1>Sign in</h1>

    <form method="post" class="card" style="max-width:400px">
        <label>Email</label>
        <input name="email" type="email" placeholder="you@example.com" required>

        <label>Password</label>
        <input name="password" type="password" placeholder="Your password" required>

        <button style="margin-top:10px">Login</button>
    </form>

    <p class="muted" style="margin-top:14px">
        Don’t have an account? <a href="/register.php">Sign up</a>
    </p>
</main>

<?php require __DIR__.'/partials/footer.php'; ?>

<?php if($err): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: '<?= addslashes($err) ?>'
        });
    </script>
<?php endif; ?>

</body>
</html>
