<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

$agent_id = $_SESSION['user_id'] ?? null;

// === Change Password ===
if (isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($old, $hashed_password)) {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new, $agent_id);
        $stmt->execute();
        echo "<p style='color:green;'>Password changed successfully.</p>";
    } else {
        echo "<p style='color:red;'>Incorrect old password.</p>";
    }
}
?>
<section>
<h2>Change Password</h2>
<!-- Change Password -->
<form method="POST">
    <h3>Change Password</h3>
    <input type="password" name="old_password" placeholder="Old Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <button type="submit" name="change_password">Change Password</button>
</form>
</section>
<?php include '../includes/footer.php'; ?>
