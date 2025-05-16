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

$count_cart = $conn->prepare("SELECT COUNT(book_id) AS total_book FROM cart");
$count_cart->execute();
$result = $count_cart->get_result();
$row =$result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Buy Website</title>
    <link rel="stylesheet" href="include/c-style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header>
    <div id="header">
        <div>
            <a href="dashboard.php"><h2>Books here</h2></a>
        </div>
        <div>
            <a href="wishlist.php"><span id = "wishlist"> ♡ </span></a>
            <a href="cart.php">
                <strong>🛒</strong>
                <span id="count"><?php echo $row['total_book']; ?></span>
            </a>
            <a href="message.php"><strong> 🖂 </strong> </a>
            
            <span><a href="../base/logout.php">| Logout</a></span>
        </div>
    </div>
</header>
<div id="container">