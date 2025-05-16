<?php
session_start();
include_once '../public/config.php';
include_once '../public/functions.php';

$c_user_role = $_SESSION['role'] ?? null;
$c_user_name = $_SESSION['name'] ?? 'Guest';
$c_user_id   = $_SESSION['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Buy Website</title>
    <link rel="stylesheet" href="../base/styles.css">
</head>
<body>
<header>
    <div id="header">
        <a href="../base/home.php">Homepage |</a>
        <?php if (!is_logged_in()): ?>
            <a href="../base/login.php">Login |</a>
            <a href="../base/register.php">Register</a>
        <?php else: ?>
            <?php if ($c_user_role === 'client'): ?>
                <a href="../client/dashboard.php">Client Panel |</a>
            <?php elseif ($c_user_role === 'agent'): ?>
                <a href="../agent/dashboard.php">Agent Panel |</a>
            <?php elseif ($c_user_role === 'admin'): ?>
                <a href="../admin/dashboard.php">Admin Panel |</a>
            <?php endif; ?>
            <a href="../base/logout.php">Logout (<?php echo $c_user_name ?>)</a>
        <?php endif; ?>
    </div>
</header>
<div id="container">