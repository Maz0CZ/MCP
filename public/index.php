<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/repositories.php';
$packages = get_packages();
include __DIR__ . '/../templates/header.php';
?>
<section class="hero">
    <h1>Host Minecraft Servers Instantly</h1>
    <p>Provision autonomous Spigot servers on your Debian host with real-time console access and zero hassle.</p>
    <a href="<?php echo current_user() ? '/dashboard.php' : '/register.php'; ?>" class="button-primary">Get Started</a>
    <div class="packages">
        <?php foreach ($packages as $package): ?>
            <div class="package-card">
                <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                <div class="package-meta"><?php echo (int)$package['ram_mb']; ?> MB RAM</div>
                <p><?php echo nl2br(htmlspecialchars($package['description'] ?? 'Reliable Spigot hosting with instant deployment.')); ?></p>
                <a class="button-primary" href="<?php echo current_user() ? '/dashboard.php?select=' . (int)$package['id'] : '/register.php'; ?>">Select Plan</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/../templates/footer.php'; ?>
