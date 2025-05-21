<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

$edit_mode = false;
$edit_data = [
    'id' => '',
    'title' => '',
    'author' => '',
    'description' => '',
    'price' => '',
    'uploaded_by' => '',
];

// Fetch users for dropdown
$users = $conn->query("SELECT id, name FROM users");

// Delete Book
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $del_stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $del_stmt->bind_param("i", $id);
    $del_stmt->execute();
    echo "<script>alert('Book deleted successfully.'); window.location.href='book-manage.php';</script>";
    exit;
}

$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// Get total number of books for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM books WHERE title LIKE ?");
$count_stmt->bind_param("s", $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'];
$total_pages = ceil($total_books / $limit);

// Edit Book - Fetch data
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $get_stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $get_stmt->bind_param("i", $edit_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_mode = true;
        $edit_data = $result->fetch_assoc();
    }
}

// Handle Upload or Update
if (isset($_POST['save'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $price = intval($_POST['price']);
    $uploaded_by = intval($_POST['uploaded_by']);
    $uploaded_at = date("Y-m-d H:i:s");

    if (!empty($_POST['id'])) {
        // Update
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, description=?, price=?, uploaded_by=? WHERE id=?");
        $stmt->bind_param("sssiii", $title, $author, $description, $price, $uploaded_by, $id);
        $stmt->execute();
        echo "<script>alert('Book updated successfully.'); window.location.href='book-manage.php';</script>";
        exit;
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO books (title, author, description, price, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $title, $author, $description, $price, $uploaded_by, $uploaded_at);
        $stmt->execute();
        echo "<script>alert('Book uploaded successfully.'); window.location.href='book-manage.php';</script>";
        exit;
    }
}
?>

<section>
    <h2><?= $edit_mode ? "Edit Book" : "Upload Book" ?></h2>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <input type="text" name="title" placeholder="Book Title" value="<?= htmlspecialchars($edit_data['title']) ?>" required><br>
        <input type="text" name="author" placeholder="Author" value="<?= htmlspecialchars($edit_data['author']) ?>" required><br>
        <textarea name="description" placeholder="Description" required><?= htmlspecialchars($edit_data['description']) ?></textarea><br>
        <input type="number" name="price" placeholder="Price" value="<?= htmlspecialchars($edit_data['price']) ?>" required min="0"><br>

        <label>Uploaded By:</label>
        <select name="uploaded_by" required>
            <option value="">-- Select Uploader --</option>
            <?php
            $users->data_seek(0);
            while ($u = $users->fetch_assoc()):
                $selected = $u['id'] == $edit_data['uploaded_by'] ? 'selected' : '';
            ?>
                <option value="<?= $u['id'] ?>" <?= $selected ?>><?= htmlspecialchars($u['name']) ?></option>
            <?php endwhile; ?>
        </select><br>

        <button type="submit" name="save"><?= $edit_mode ? "Update Book" : "Upload Book" ?></button>
    </form>

    <h3 class="section-title">📚 All Books</h3>
    <form method="GET" style="margin-bottom:15px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search books...">
        <button type="submit">Search</button>
    </form>

    <div class="card-grid">
        <?php
        $stmt = $conn->prepare("SELECT b.*, u.name AS uploader 
                                FROM books b 
                                JOIN users u ON b.uploaded_by = u.id 
                                WHERE b.title LIKE ? 
                                ORDER BY b.uploaded_at DESC 
                                LIMIT ?, ?");
        $stmt->bind_param("sii", $searchParam, $start, $limit);
        $stmt->execute();
        $books = $stmt->get_result();

        while ($row = $books->fetch_assoc()):
        ?>
        <div class="card">
            <div class="card-header">
                <strong><?= htmlspecialchars($row['title']) ?></strong>
            </div>
            <div class="card-body" style="text-align:left;">
                <p><strong>📖 Author:</strong> <?= htmlspecialchars($row['author']) ?></p>
                <p><strong>👤 Uploaded By:</strong> <?= htmlspecialchars($row['uploader']) ?></p>
                <p><strong>💰 Price:</strong> ₹<?= htmlspecialchars($row['price']) ?></p>
                <p><strong>🕒 Uploaded At:</strong> <?= htmlspecialchars($row['uploaded_at']) ?></p>
            </div>
            <div class="card-actions">
                <a href="?edit=<?= $row['id'] ?>">✏️ Edit</a>
                <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this book?')">🗑️ Delete</a>
            </div>
        </div>
        <?php endwhile; ?>
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
