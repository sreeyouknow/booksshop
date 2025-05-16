<?php
session_start();
session_destroy();
header('Location: ../base/login.php');
exit();
?>
