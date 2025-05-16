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


// --- Save SMTP Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_smtp'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = 'Invalid CSRF token.';
    } else {
        $host = trim($_POST['smtp_host']);
        $user = trim($_POST['smtp_user']);
        $pass = trim($_POST['smtp_pass']); // NOTE: Should be encrypted in production
        $port = (int) $_POST['smtp_port'];

        if ($host && $user && $pass && $port > 0) {
            $existing = $conn->query("SELECT id FROM settings LIMIT 1");

            if ($existing->num_rows > 0) {
                $existing_id = $existing->fetch_assoc()['id'];
                $stmt = $conn->prepare("UPDATE settings SET smtp_host=?, smtp_user=?, smtp_pass=?, smtp_port=? WHERE id=?");
                $stmt->bind_param("sssii", $host, $user, $pass, $port, $existing_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO settings (smtp_host, smtp_user, smtp_pass, smtp_port) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $host, $user, $pass, $port);
            }

            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO logs (admin_id, action) VALUES (?, 'Updated SMTP settings')");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();

            $success_msg = "SMTP settings saved.";
        } else {
            $error_msg = "Please fill all SMTP fields properly.";
        }
    }
}

// --- Fetch SMTP Settings
$smtp_result = $conn->query("SELECT * FROM settings LIMIT 1");
$smtp = ($smtp_result->num_rows > 0) ? $smtp_result->fetch_assoc() : null;
?>
<section>
    <!-- SMTP Configuration -->
    <form method="POST">
        <h3>Email / SMTP Settings</h3>
        <input type="text" name="smtp_host" value="<?= $smtp ? htmlspecialchars($smtp['smtp_host']) : '' ?>" placeholder="SMTP Host" required>
        <input type="text" name="smtp_user" value="<?= $smtp ? htmlspecialchars($smtp['smtp_user']) : '' ?>" placeholder="SMTP User" required>
        <input type="password" name="smtp_pass" value="<?= $smtp ? htmlspecialchars($smtp['smtp_pass']) : '' ?>" placeholder="SMTP Password" required>
        <input type="number" name="smtp_port" value="<?= $smtp ? htmlspecialchars($smtp['smtp_port']) : '' ?>" placeholder="SMTP Port" required>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" name="save_smtp">Save SMTP</button>
    </form>
    <p><a href="../base/logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a></p>
</section>

<?php include '../includes/footer.php'; ?>
