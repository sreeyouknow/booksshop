<?php
include '../client/include/header.php';
include '../client/include/sidebar.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current user info
$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p style='color:red;'>User not found in database.</p>";
    exit;
}

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $role = sanitize($_POST['role']);
    $c_password = $_POST['c_password'];

    // Verify current password
    $check = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $check->bind_param('i', $user_id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if (password_verify($c_password, $res['password'])) {
        $update = $conn->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?");
        $update->bind_param('ssi', $name, $role, $user_id);
        if ($update->execute()) {
            $success = "Profile updated successfully.";
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            $user['name'] = $name;
            $user['role'] = $role;
        } else {
            $error = "Failed to update profile.";
        }
    } else {
        $error = "Incorrect current password.";
    }
}
?>

<section>
    <h1>My Profile</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php elseif ($success): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php endif; ?>

    <div>
        <h2>Account Information</h2>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <form method="post" action="profile.php">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>Current Role: <?= htmlspecialchars($user['role']) ?></label>
            <select name="role" required>
                <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Client</option>
            </select>

            <label>Current Password (to confirm changes)</label>
            <input type="password" name="c_password" required>

            <button type="submit" name="update_profile">Save Profile Changes</button>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
