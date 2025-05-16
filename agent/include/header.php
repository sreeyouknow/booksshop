<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../base/login.php");
    exit;
}

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
    <link rel="stylesheet" href="include/ag-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header>
    <div id="header">
        <div>
            <a href="dashboard.php"><h2>Books here</h2></a>
        </div>
        <div>
            <a href="request-books.php"><strong> 📚 </strong></a>
            <a href="client-interaction.php"><strong> 🖂 </strong> </a>
            <a href="profile.php"><strong> 👤 </strong></a>
            
            <span><a href="../base/logout.php">| Logout</a></span>
        </div>
    </div>
</header>
<div id="container">