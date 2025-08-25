<?php
require_once __DIR__ . '/../lib/app.php';
require_once __DIR__ . '/../lib/auth.php';

$path = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
function active($file, $current){ return $file === $current ? 'class="active"' : ''; }
?>
<header class="site">
    <div class="shell">
        <a class="brand" href="/"><?= APP_NAME ?></a>
        <nav>
            <a <?=active('index.php',$path)?> href="/">Home</a>
            <a <?=active('about.php',$path)?> href="/about.php">About</a>
            <a <?=active('contact.php',$path)?> href="/contact.php">Contact</a>
            <?php if (is_logged_in()): ?>
                <?php if (is_creator()): ?><a <?=active('dashboard.php',$path)?> href="/dashboard.php">Dashboard</a><?php endif; ?>
                <a href="/logout.php">Logout</a>
            <?php else: ?>
                <a <?=active('login.php',$path)?> href="/login.php">Sign in</a>
                <a <?=active('register.php',$path)?> href="/register.php" class="cta">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
