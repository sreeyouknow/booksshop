<?php
include '../client/include/header.php';
include '../client/include/sidebar.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Password Change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $check = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $check->bind_param('i', $user_id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();

        if (password_verify($current_password, $res['password'])) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param('si', $hashed, $user_id);
            if ($update->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Failed to change password.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>
<section>
    <div>
        <h2>Change Password</h2>
        <form method="post" action="profile.php">
            <label>Current Password</label>
            <input type="password" name="current_password" required>

            <label>New Password</label>
            <input type="password" name="new_password" required>
            <small>Must be at least 6 characters.</small><br>

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="change_password">Change Password</button>
            <a href="../base/forgot-password.php">Forgot Password?</a>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>