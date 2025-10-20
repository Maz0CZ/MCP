<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/mailer.php';
ensure_session_started();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$email || !$password || $password !== $confirm) {
        $error = 'Please provide a valid email and matching passwords.';
    } else {
        $db = get_db();
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare('INSERT INTO users (email, password_hash, is_admin) VALUES (:email, :hash, 0)')
                ->execute(['email' => $email, 'hash' => $hash]);
            $success = 'Account created successfully. You can log in now.';
            $autoloader = __DIR__ . '/../vendor/autoload.php';
            if (file_exists($autoloader)) {
                try {
                    require_once $autoloader;
                    $mailer = create_mailer();
                    $mailer->addAddress($email);
                    $mailer->Subject = 'Welcome to Zibuu MCP';
                    $mailer->Body = 'Thank you for registering. Your account is ready to provision servers!';
                    $mailer->send();
                } catch (Throwable $e) {
                    // Mail failures are silently ignored but logged in production
                }
            }
        }
    }
}

include __DIR__ . '/../templates/header.php';
?>
<div class="form-card">
    <h2>Create Account</h2>
    <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <button class="button-primary" type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="/login.php">Log in</a>.</p>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
