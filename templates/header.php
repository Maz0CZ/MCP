<?php
require_once __DIR__ . '/../includes/auth.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zibuu MCP</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <header class="top-bar">
        <div class="brand">Zibuu MCP</div>
        <nav>
            <a href="/index.php">Home</a>
            <?php if ($user): ?>
                <a href="/dashboard.php">Dashboard</a>
                <?php if ($user['is_admin']): ?>
                    <a href="/admin.php">Admin</a>
                <?php endif; ?>
                <a href="/logout.php">Logout</a>
            <?php else: ?>
                <a href="/login.php">Login</a>
                <a href="/register.php" class="accent">Get Started</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="content">
