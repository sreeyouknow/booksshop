<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

// Fetch all clients
$clients = $conn->query("SELECT * FROM users WHERE role = 'client'");

// Handle delete client
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: client_management.php");
    exit;
}

// Handle edit client
if (isset($_POST['edit_client'])) {
    $client_id = $_POST['client_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Update client information
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $client_id);
    $stmt->execute();

    header("Location: client_management.php");
    exit;
}
?>

<section>
    <h2>👥 Client Management</h2>

    <!-- Client Cards -->
    <div class="card-grid">
        <?php while ($client = $clients->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <p><strong>👤 Name:</strong> <?= htmlspecialchars($client['name']) ?></p>
                    <p><strong>📧 Email:</strong> <?= htmlspecialchars($client['email']) ?></p>
                    <div class="card-actions">
                        <a href="client_management.php?edit_id=<?= $client['id'] ?>" class="btn-edit">Edit</a>
                        <a href="client_management.php?delete_id=<?= $client['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete?')" style="color:red;">Delete</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if (isset($_GET['edit_id'])):
        $edit_id = $_GET['edit_id'];
        $client_to_edit = $conn->query("SELECT * FROM users WHERE id = $edit_id AND role = 'client'")->fetch_assoc();
    ?>
        <h3>✏️ Edit Client</h3>
        <form method="POST" action="client_management.php" class="edit-form">
            <input type="hidden" name="client_id" value="<?= $client_to_edit['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($client_to_edit['name']) ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($client_to_edit['email']) ?>" required>
            <button type="submit" name="edit_client">Update Client</button>
        </form>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
