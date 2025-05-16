<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

// Define the role for client
$role_client = 'client';

// Fetch total clients using prepared statement
$total_clients_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = ?");
$total_clients_stmt->bind_param("s", $role_client); // Use the variable $role_client
$total_clients_stmt->execute();
$total_clients_result = $total_clients_stmt->get_result();
$total_clients = $total_clients_result->fetch_assoc()['total'];

// Fetch total books using prepared statement
$total_books_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM books");
$total_books_stmt->execute();
$total_books_result = $total_books_stmt->get_result();
$total_books = $total_books_result->fetch_assoc()['total'];

// Fetch recent activity (last 5 book uploads) using prepared statement
$recent_books_stmt = $conn->prepare("SELECT id, title, uploaded_by, uploaded_at FROM books ORDER BY uploaded_at DESC LIMIT 5");
$recent_books_stmt->execute();
$recent_books_result = $recent_books_stmt->get_result();
?>

<!-- Quick Stats -->
<section>
    <h2 class="section-title">Agent Dashboard</h2>

    <!-- Quick Stats Cards -->
    <h3 class="section-subtitle">📊 Quick Stats</h3>
    <div class="card-grid">
        <div class="card stat-card">
            <h4>Total Clients</h4>
            <p><?= $total_clients ?></p>
        </div>
        <div class="card stat-card">
            <h4>Total Books Uploaded</h4>
            <p><?= $total_books ?></p>
        </div>
    </div>

    <!-- Recent Book Uploads Cards -->
    <h3 class="section-subtitle">📚 Recent Book Uploads</h3>
    <div class="card-grid">
        <?php while ($book = $recent_books_result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-body">
                    <p><strong>📖 Title:</strong> <?= htmlspecialchars($book['title']) ?></p>
                    <p><strong>👤 Uploaded By:</strong> <?= htmlspecialchars($book['uploaded_by']) ?></p>
                    <p><strong>🕒 Uploaded At:</strong> <?= $book['uploaded_at'] ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>



<?php include '../includes/footer.php'; ?>
