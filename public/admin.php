<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/repositories.php';
require_admin();

$db = get_db();
$users = $db->query('SELECT id, email, is_admin FROM users ORDER BY id')->fetchAll();
$servers = $db->query('SELECT servers.*, users.email AS owner_email, packages.name AS package_name FROM servers JOIN users ON users.id = servers.user_id JOIN packages ON packages.id = servers.package_id ORDER BY servers.id DESC')->fetchAll();
$packages = get_packages();

include __DIR__ . '/../templates/header.php';
?>
<section>
    <h2>Users</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo (int)$user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['is_admin'] ? 'Admin' : 'Customer'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section>
    <h2>Servers</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Owner</th>
                <th>Package</th>
                <th>Port</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servers as $server): ?>
                <tr>
                    <td>#<?php echo (int)$server['id']; ?></td>
                    <td><?php echo htmlspecialchars($server['owner_email']); ?></td>
                    <td><?php echo htmlspecialchars($server['package_name']); ?></td>
                    <td><?php echo (int)$server['port']; ?></td>
                    <td><?php echo htmlspecialchars($server['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section>
    <h2>Packages</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>RAM (MB)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packages as $package): ?>
                <tr>
                    <td>#<?php echo (int)$package['id']; ?></td>
                    <td><?php echo htmlspecialchars($package['name']); ?></td>
                    <td><?php echo (int)$package['ram_mb']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
