<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

// --- Define admin_id
$admin_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// --- CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Fetch Admin Info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_result = $stmt->get_result();
$admin = $admin_result->fetch_assoc();

// --- Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = 'Invalid CSRF token.';
    } else {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];

        if (strlen($new) < 6) {
            $error_msg = "Password must be at least 6 characters.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current = $result->fetch_assoc()['password'];

            if (password_verify($old, $current)) {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt->bind_param("si", $hashed, $admin_id);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO logs (admin_id, action) VALUES (?, 'Changed password')");
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();

                $success_msg = "Password changed.";
            } else {
                $error_msg = "Incorrect old password.";
            }
        }
    }
}
?>
<section>
    <!-- Change Password -->
    <form method="POST">
        <h3>Change Password</h3>
        <input type="password" name="old_password" placeholder="Old Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" name="change_password">Change</button>
    </form>
    <p><a href="../base/logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a></p>
</section>

<?php include '../includes/footer.php'; ?>
