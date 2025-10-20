<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/repositories.php';
require_once __DIR__ . '/../includes/server_manager.php';
require_login();

$user = current_user();
$serverId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$server = $serverId ? find_server($serverId) : null;

if (!$server || ($server['user_id'] !== $user['id'] && !$user['is_admin'])) {
    header('HTTP/1.1 404 Not Found');
    echo 'Server not found';
    exit;
}

$logFile = $server['directory'] . '/' . (require __DIR__ . '/../includes/config.php')['minecraft']['log_filename'];
$logContent = is_file($logFile) ? file_get_contents($logFile) : 'Console will appear once the server starts.';

include __DIR__ . '/../templates/header.php';
?>
<section>
    <h2>Server #<?php echo (int)$server['id']; ?> Console</h2>
    <p>Package: <?php echo htmlspecialchars($server['package_name']); ?> — Port <?php echo (int)$server['port']; ?></p>
    <div class="console-container" data-console-log="<?php echo (int)$server['id']; ?>"><?php echo htmlspecialchars($logContent); ?></div>
    <form class="console-input" method="post" data-console-form>
        <input type="hidden" name="server_id" value="<?php echo (int)$server['id']; ?>">
        <input type="text" name="command" placeholder="Enter console command" autocomplete="off" required>
        <button class="button-primary" type="submit">Send</button>
    </form>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
