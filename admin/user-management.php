<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

$errors = [];
$success = "";

// === Handle Create/Update ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $role === '') {
        $errors[] = "All fields except password (for update) are required.";
    } else {
        if ($id === '') {
            if ($password === '') {
                $errors[] = "Password is required for new user.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hash, $role);
                $stmt->execute();
                $success = "User added successfully!";
            }
        } else {
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

// === Handle Delete ===
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    $success = "User deleted.";
}

// === Handle Edit ===
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
}

// === Pagination & Search ===
$limit = 3;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$searchParam = "%{$search}%";

// Count total users
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE name LIKE ? OR email LIKE ?");
$count_stmt->bind_param("ss", $searchParam, $searchParam);
$count_stmt->execute();
$total_users = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Fetch paginated users
$user_stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ?, ?");
$user_stmt->bind_param("ssii", $searchParam, $searchParam, $start, $limit);
$user_stmt->execute();
$users = $user_stmt->get_result();
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

    <form method="GET" style="margin-bottom:15px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email...">
        <button type="submit">Search</button>
    </form>

    <div class="card-grid">
        <?php if ($users->num_rows > 0): ?>
            <?php while ($row = $users->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($row['name']) ?>
                    </div>
                    <div class="card-body" style="text-align:left;">
                        <p><strong>ID:</strong> <?= $row['id'] ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                        <p><strong>Role:</strong> <?= $row['role'] ?></p>
                    </div>
                    <div class="card-actions">
                        <a href="?edit=<?= $row['id'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">✏️ Edit</a>
                        <a href="?delete=<?= $row['id'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>" class="delete" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

</section>

<?php include '../includes/footer.php'; ?>
