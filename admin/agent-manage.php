<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

// Add or update agent
if (isset($_POST['save'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Update agent
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $password, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $id);
        }
    } else {
        // Insert new agent
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = "agent";
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
    }

    $stmt->execute();
    header("Location: agent-manage.php");
    exit;
}

$edit_agent = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'agent'");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    $edit_agent = $edit_result->fetch_assoc();
}

// Delete agent or user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete from clients_assigned where this user is either agent or client
    $del_assigned = $conn->prepare("DELETE FROM clients_assigned WHERE agent_id = ? OR client_id = ?");
    $del_assigned->bind_param("ii", $id, $id);
    $del_assigned->execute();

    // Delete user from users table
    $del_user = $conn->prepare("DELETE FROM users WHERE id = ?");
    $del_user->bind_param("i", $id);
    $del_user->execute();

    header("Location: agent-manage.php");
    exit;
}

// Fetch agents
$agents = $conn->query("SELECT * FROM users WHERE role='agent'");
?>

<section>
    <h2>Agent Management</h2>

    <!-- Agent Form -->
    <!-- Agent Form -->
<form method="POST" action="">
    <input type="hidden" name="id" value="<?= $edit_agent ? $edit_agent['id'] : '' ?>">
    
    <input type="text" name="name" placeholder="Agent Name" required
        value="<?= $edit_agent ? htmlspecialchars($edit_agent['name']) : '' ?>">

    <input type="email" name="email" placeholder="Agent Email" required
        value="<?= $edit_agent ? htmlspecialchars($edit_agent['email']) : '' ?>">

    <?php if (!$edit_agent): ?>
        <input type="password" name="password" placeholder="Password" required>
    <?php else: ?>
        <small>Leave password empty if not changing</small><br>
        <input type="password" name="password" placeholder="New Password (optional)">
    <?php endif; ?>

    <button type="submit" name="save">Save Agent</button>
</form>


    <!-- Agent List -->
    <h3 class="section-title">🧑‍💼 All Agents</h3>

    <div class="card-grid" style="text-align:left;">
        <?php $agents->data_seek(0); while ($row = $agents->fetch_assoc()): ?>
        <div class="card">
            <div class="card-header">
                <strong>👤 <?= htmlspecialchars($row['name']) ?></strong>
            </div>
            <div class="card-body">
                <p><strong>📧 Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                <p><strong>🆔 ID:</strong> <?= $row['id'] ?></p>
            </div>
            <div class="card-actions">
                <a href="?edit=<?= $row['id'] ?>">✏️ Edit</a>
                <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this agent?')">🗑️ Delete</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php include '../includes/footer.php'; ?>
