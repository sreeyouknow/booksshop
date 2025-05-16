<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

// Handle Book Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_book'])) {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $uploaded_by = $_SESSION['user_id']; // Logged-in agent

    // Handle File Upload for Cover Image
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $cover_image = 'uploads/' . basename($_FILES['cover_image']['name']);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], '../uploads/' . basename($_FILES['cover_image']['name']));
    }

    // Insert into the database
    $upload_query = $conn->prepare("INSERT INTO books (title, author, description, price, cover_image, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
    $upload_query->bind_param('sssssi', $title, $author, $description, $price, $cover_image, $uploaded_by);
    $upload_query->execute();
}

// Handle Request Permission for Book Fulfillment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_permission'])) {
    $request_id = $_POST['request_id'];
    $agent_id = $_SESSION['id'];  // Logged-in agent

    // Update the request with agent ID and status 'waiting_for_approval'
    $update_query = $conn->prepare("UPDATE book_requests SET agent_id = ?, status = 'waiting_for_approval' WHERE id = ?");
    $update_query->bind_param('ii', $agent_id, $request_id);
    $update_query->execute();
}

// Fetch pending book requests
$request_query = $conn->prepare("SELECT * FROM book_requests WHERE status = 'pending' ORDER BY requested_at DESC");
$request_query->execute();
$requests = $request_query->get_result();
?>

<section>
    <!-- Book Upload Section -->
    <h2>Upload Book</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Book Title" required>
        <input type="text" name="author" placeholder="Book Author" required>
        <textarea name="description" placeholder="Book Description" required></textarea>
        <input type="number" name="price" placeholder="Price" required>
        <input type="file" name="cover_image" accept="image/*">
        <button type="submit" name="upload_book">Upload Book</button>
    </form>
</section>

<?php include '../includes/footer.php'; ?>
