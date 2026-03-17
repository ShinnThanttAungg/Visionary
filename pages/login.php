<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            redirect('/visionary/pages/dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-shell">
    <div class="auth-card auth-grid">
        <div class="auth-visual">
            <div class="eyebrow">Welcome Back</div>
            <h2>Return to your gallery.</h2>
            <p>Continue publishing image series, writing captions, and refining your visual language.</p>
        </div>

        <div class="auth-form-wrap">
            <h1>Login</h1>
            <p class="form-note">Your account gives access to the dashboard and publishing tools.</p>

            <?php if ($error): ?>
                <div class="error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-row">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>