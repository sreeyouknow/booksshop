<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';


// Handle DB Actions
$errors = [];
$success = "";

// === CREATE or UPDATE ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $role === '') {
        $errors[] = "All fields except password (for update) are required.";
    } else {
        if ($id == '') {
            // CREATE
            if ($password === '') {
                $errors[] = "Password required for new user.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hash, $role);
                $stmt->execute();
                $success = "User added successfully!";
            }
        } else {
            // UPDATE
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?");
                $stmt->bind_param("ssssi", $name, $email, $hash, $role, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
                $stmt->bind_param("sssi", $name, $email, $role, $id);
            }
            $stmt->execute();
            $success = "User updated successfully!";
        }
    }
}

// === DELETE ===
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = " . intval($delete_id));
    $success = "User deleted.";
}

// === EDIT ===
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
}
?>

<section>
    <h1>User Management</h1>

    <?php foreach ($errors as $err) echo "<p style='color:red;'>$err</p>"; ?>
    <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

    <h2><?php echo $edit_user ? "Edit User" : "Add New User"; ?></h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $edit_user['id'] ?? ''; ?>">
        <input type="text" name="name" placeholder="Full Name" value="<?php echo $edit_user['name'] ?? ''; ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?php echo $edit_user['email'] ?? ''; ?>" required>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="client" <?php if (($edit_user['role'] ?? '') == 'client') echo 'selected'; ?>>Client</option>
            <option value="agent" <?php if (($edit_user['role'] ?? '') == 'agent') echo 'selected'; ?>>Agent</option>
            <option value="admin" <?php if (($edit_user['role'] ?? '') == 'admin') echo 'selected'; ?>>Admin</option>
        </select>
        <input type="password" name="password" placeholder="<?php echo $edit_user ? 'Leave blank to keep current password' : 'Password'; ?>">
        <button type="submit"><?php echo $edit_user ? 'Update User' : 'Add User'; ?></button>
    </form>

    <hr>

   <h2 class="section-title">👥 All Users</h2>

<div class="card-grid">
    <?php
    $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
    while ($row = $res->fetch_assoc()):
    ?>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user"></i> <?php echo htmlspecialchars($row['name']); ?>
        </div>
        <div class="card-body" style="text-align:left;">
            <p><strong>ID:</strong> <?php echo $row['id']; ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
            <p><strong>Role:</strong> <?php echo $row['role']; ?></p>
        </div>
        <div class="card-actions">
            <a href="?edit=<?php echo $row['id']; ?>">✏️ Edit</a>
            <a href="?delete=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
        </div>
    </div>
    <?php endwhile; ?>
</div>

</section>

<?php include '../includes/footer.php'; ?>
