<?php include '../includes/header.php'; ?>

<?php 


if (!isset($_GET['token'])) {
    echo "Invalid request. No token provided.";
    exit;
}

$token = sanitize($_GET['token']);

// Check if token exists in the database and is not expired
$stmt = $conn->prepare("SELECT id, reset_token_expiry FROM users WHERE reset_token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Invalid token or token expired.";
    exit;
}

$user = $result->fetch_assoc();
$expiry_time = $user['reset_token_expiry'];

// Check if the token has expired
if (strtotime($expiry_time) < time()) {
    echo "Reset token has expired.";
    exit;
}

// Handle form submission for password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['password_c']);

    if (empty($new_password) || empty($confirm_password)) {
        echo "Both fields are required.";
    } elseif ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        echo "Password must be at least 6 characters long.";
    } else {
        // Update the password in the database
        $hashpassword = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        $update_stmt->bind_param('ss', $hashpassword, $token);
        $update_stmt->execute();

        echo "Your password has been reset successfully. <a href='login.php'>Login here</a>";
    }
}
?>
<form action="reset-password.php?token=<?php echo $token; ?>" method="POST">
    <input type="password" name="password" placeholder="Enter new password" required>
    <input type="password" name="password_c" placeholder="Confirm new password" required>
    <button type="submit">Reset Password</button>
</form>
<?php include '../includes/footer.php'; ?>
