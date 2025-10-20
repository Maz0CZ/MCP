<?php
require_once __DIR__ . '/../includes/auth.php';
ensure_session_started();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if (!$email || !$password || !authenticate($email, $password)) {
        $error = 'Invalid credentials. Please try again.';
    } else {
        header('Location: /dashboard.php');
        exit;
    }
}

include __DIR__ . '/../templates/header.php';
?>
<div class="form-card">
    <h2>Welcome back</h2>
    <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <button class="button-primary" type="submit">Login</button>
    </form>
    <p>Need an account? <a href="/register.php">Register</a>.</p>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
