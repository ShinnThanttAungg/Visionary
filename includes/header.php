<?php
require_once __DIR__ . '/functions.php';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visionary</title>
    <link rel="stylesheet" href="/visionary/assets/style.css">
    <script src="/visionary/assets/script.js" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a href="/visionary/index.php" class="brand">VISIONARY</a>

        <nav class="nav-links">
            <a href="/visionary/index.php">Home</a>

            <?php if (isLoggedIn()): ?>
                <a href="/visionary/pages/dashboard.php">Dashboard</a>
                <a href="/visionary/pages/create_post.php">New Post</a>
                <a href="/visionary/pages/profile.php">Profile</a>
                <a href="/visionary/pages/logout.php">Logout</a>
            <?php else: ?>
                <a href="/visionary/pages/login.php">Login</a>
                <a href="/visionary/pages/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if ($flash): ?>
    <div class="container flash-wrap">
        <div class="<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>
