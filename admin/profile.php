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

// --- Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

        if ($email && !empty($name)) {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $admin_id);
            $stmt->execute();

            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;

            $action = 'Updated profile';
            $log_stmt = $conn->prepare("INSERT INTO logs (admin_id, action) VALUES (?, ?)");
            $log_stmt->bind_param("is", $admin_id, $action);
            $log_stmt->execute();

            header("Location: profile.php?status=profile_updated");
            exit;
        } else {
            $error_msg = 'Please provide a valid name and email.';
        }
    }
}
?>
<section>
    <h2>Admin Profile & Settings</h2>

    <?php if ($success_msg): ?>
        <p style="color:green;"><?php echo $success_msg; ?></p>
    <?php elseif ($error_msg): ?>
        <p style="color:red;"><?php echo $error_msg; ?></p>
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'profile_updated'): ?>
        <p style="color:green;">Profile updated successfully!</p>
    <?php endif; ?>

    <!-- Update Profile -->
    <form method="POST">
        <h3>Update Profile</h3>
        <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" name="update_profile">Update</button>
    </form>
</section>

<?php include '../includes/footer.php'; ?>
