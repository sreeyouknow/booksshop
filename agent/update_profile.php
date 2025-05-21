<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

$agent_id = $_SESSION['user_id'] ?? null;


// === Fetch Agent Info ===
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$stmt->bind_result($agent_name, $agent_email);
$stmt->fetch();
$stmt->close();

// === Update Profile ===
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $agent_id);
    $stmt->execute();
    echo "<p style='color:green;'>Profile updated successfully.</p>";
}
?>
<section>
<h2>Agent Profile</h2>

<!-- Update Profile -->
<form method="POST">
    <h3>Update Profile</h3>
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($agent_name) ?>" required>
    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($agent_email) ?>" required>
    <button type="submit" name="update_profile">Update</button>
</form>
</section>
<?php include '../includes/footer.php'; ?>
 