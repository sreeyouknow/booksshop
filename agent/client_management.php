<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// --- Total Count for Pagination ---
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'client' AND name LIKE ?");
$count_stmt->bind_param("s", $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_clients = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_clients / $limit);

// --- Fetch Clients ---
$clients_stmt = $conn->prepare("SELECT id, name, email FROM users WHERE role = 'client' AND name LIKE ? ORDER BY id DESC LIMIT ?, ?");
$clients_stmt->bind_param("sii", $searchParam, $start, $limit);
$clients_stmt->execute();
$client_result = $clients_stmt->get_result();

// --- Handle delete client ---
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = $delete_id AND role = 'client'");
    header("Location: client_management.php");
    exit;
}

// --- Handle edit client ---
if (isset($_POST['edit_client'])) {
    $client_id = $_POST['client_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'client'");
    $stmt->bind_param("ssi", $name, $email, $client_id);
    $stmt->execute();

    header("Location: client_management.php");
    exit;
}
?>
<style>
    .pagination a {
    padding: 6px 12px;
    margin: 2px;
    border: 1px solid #ccc;
    text-decoration: none;
    color: #1a2942;
}
.pagination a.active {
    font-weight: bold;
    background-color: #c7a100;
    color: #fff;
}
</style>
<section>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search clients..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <h2>👥 Client Management</h2>

    <!-- Client Cards -->
    <div class="card-grid">
        <?php while ($client = $client_result->fetch_assoc()): ?>
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
        $edit_id = (int)$_GET['edit_id'];
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
