<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);

        if ($check->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);

            redirect('/visionary/pages/login.php');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-shell">
    <div class="auth-card auth-grid">
        <div class="auth-visual">
            <div class="eyebrow">Create Account</div>
            <h2>Begin your visual archive.</h2>
            <p>Build a profile, publish photo series, and pair images with quiet short-form writing.</p>
        </div>

        <div class="auth-form-wrap">
            <h1>Register</h1>
            <p class="form-note">Minimal, elegant, and made for image storytelling.</p>

            <?php if ($error): ?>
                <div class="error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>

                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-row">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit">Create account</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>