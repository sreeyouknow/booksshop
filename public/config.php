<?php
$conn = mysqli_connect('localhost', 'root', '', 'book_can');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} 

include '../includes/error_handler.php'; // load custom error handler
?>
