<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/repositories.php';
require_once __DIR__ . '/../includes/server_manager.php';
require_once __DIR__ . '/../includes/database.php';
require_login();
$user = current_user();
$db = get_db();

$packages = get_packages();
$servers = get_user_servers($user['id']);

$selectedPackageId = isset($_GET['select']) ? (int)$_GET['select'] : null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    $packageId = (int)$_POST['package_id'];
    $package = null;
    foreach ($packages as $candidate) {
        if ((int)$candidate['id'] === $packageId) {
            $package = $candidate;
            break;
        }
    }

    if (!$package) {
        $error = 'Selected package not found.';
    } else {
        $db->beginTransaction();
        try {
            $port = next_available_port();
            $db->prepare('INSERT INTO servers (user_id, package_id, port, directory, status, screen_name) VALUES (:user_id, :package_id, :port, :directory, :status, :screen)')
                ->execute([
                    'user_id' => $user['id'],
                    'package_id' => $packageId,
                    'port' => $port,
                    'directory' => '',
                    'status' => 'provisioning',
                    'screen' => 'mcserver_temp',
                ]);
            $serverId = (int)$db->lastInsertId();
            $directory = create_server_directory($serverId);
            $db->prepare('UPDATE servers SET directory = :directory, screen_name = :screen WHERE id = :id')
                ->execute([
                    'directory' => $directory,
                    'screen' => 'mcserver_' . $serverId,
                    'id' => $serverId,
                ]);

            $server = find_server($serverId);
            write_server_files($server, $package);
            start_minecraft_server($server, $package);
            update_server_status($serverId, 'running');
            $db->commit();
            $success = 'Server provisioning started. Your console will update shortly.';
            $servers = get_user_servers($user['id']);
        } catch (Throwable $e) {
            $db->rollBack();
            $error = 'Failed to create server: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../templates/header.php';
?>
<section>
    <h2>Your Servers</h2>
    <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <?php if ($servers): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Package</th>
                    <th>Port</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servers as $server): ?>
                    <tr>
                        <td>#<?php echo (int)$server['id']; ?></td>
                        <td><?php echo htmlspecialchars($server['package_name']); ?> (<?php echo (int)$server['ram_mb']; ?> MB)</td>
                        <td><?php echo (int)$server['port']; ?></td>
                        <td><span class="status-chip"><?php echo htmlspecialchars($server['status']); ?></span></td>
                        <td>
                            <a class="button-primary" href="/server.php?id=<?php echo (int)$server['id']; ?>">Console</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not created any servers yet.</p>
    <?php endif; ?>
</section>

<section>
    <h2>Create a new server</h2>
    <form method="post">
        <label for="package_id">Hosting package</label>
        <select name="package_id" id="package_id" required>
            <option value="">Select a package</option>
            <?php foreach ($packages as $package): ?>
                <option value="<?php echo (int)$package['id']; ?>" <?php echo $selectedPackageId === (int)$package['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($package['name']); ?> (<?php echo (int)$package['ram_mb']; ?> MB)
                </option>
            <?php endforeach; ?>
        </select>
        <button class="button-primary" type="submit">Create server</button>
    </form>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
