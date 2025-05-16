<?php include '../admin/include/header.php'; ?>
<?php include '../admin/include/sidebar.php'; ?>

<?php

// Total Users
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_users = $result['total'];

// Total Agents
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'agent'");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_agents = $result['total'];

// Total Clients
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = 'client'");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_clients = $result['total'];

// Total Books
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM books");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_books = $result['total'];

// Total Book Requests
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM book_requests");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_requests = $result['total'];

// Total Purchases
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM purchases");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_purchases = $result['total'];

// Recent Activity (Last 5 book uploads)
$recent_books = $conn->prepare("SELECT id, title, uploaded_by, uploaded_at FROM books ORDER BY uploaded_at DESC LIMIT 5");
$recent_books->execute();
$recent_result = $recent_books->get_result();
?>
<style>
    table {
        border-collapse: collapse;
        width: 80%;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
        color:black;
    }
    h2 {
        margin-top: 30px;
    }
    .admin-dashboard {
    padding: 40px 20px;
    background: linear-gradient(to right, #f9f9f9, #ffffff);
    color: #333;
}

.admin-dashboard h1 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #0b4b88;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px 50px;
    margin-bottom: 40px;
    width: 80%;
}

.stat-card {
    background: white;
    border-left: 6px solid gold;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    text-align: center;
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: scale(1.02);
}

.stat-card .icon {
    font-size: 36px;
    display: block;
    margin-bottom: 10px;
    color: navy;
}

.stat-card h3 {
    font-size: 18px;
    margin-bottom: 8px;
}

.stat-card p {
    font-size: 20px;
    font-weight: bold;
    color: #0b4b88;
}

.books-grid{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px 50px;
    margin-bottom: 40px;
    width: 80%;
}

.book-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    text-align: left;
}

.book-card .icon {
    font-size: 30px;
    color: #f3c507;
    margin-bottom: 10px;
}

</style>

<section class="admin-dashboard">
  <h1>
    <i class="fas fa-tachometer-alt"></i>
    Admin Dashboard | <span><?= htmlspecialchars($c_user_name) ?></span>
  </h1>

  <!-- Quick Stats Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="icon"><i class="fas fa-users"></i></div>
      <h3>Total Users</h3>
      <p><?= $total_users ?></p>
    </div>
    <div class="stat-card">
      <div class="icon"><i class="fas fa-user-friends"></i></div>
      <h3>Total Clients</h3>
      <p><?= $total_clients ?></p>
    </div>
    <div class="stat-card">
      <div class="icon"><i class="fas fa-user-tie"></i></div>
      <h3>Total Agents</h3>
      <p><?= $total_agents ?></p>
    </div>
    <div class="stat-card">
      <div class="icon"><i class="fas fa-book"></i></div>
      <h3>Total Books</h3>
      <p><?= $total_books ?></p>
    </div>
    <div class="stat-card">
      <div class="icon"><i class="fas fa-inbox"></i></div>
      <h3>Book Requests</h3>
      <p><?= $total_requests ?></p>
    </div>
    <div class="stat-card">
      <div class="icon"><i class="fas fa-shopping-cart"></i></div>
      <h3>Total Purchases</h3>
      <p><?= $total_purchases ?></p>
    </div>
  </div>
<hr>
  <!-- Recent Book Uploads Cards -->
  <div class="section-title">
    <i class="fas fa-book-reader"></i>
    Recent Book Uploads
  </div> <br>
  <div class="books-grid">
    <?php while($book = $recent_result->fetch_assoc()): ?>
      <div class="book-card">
        <span class="icon"><i class="fas fa-book-open"></i></span>
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <p><strong>By:</strong> <?= htmlspecialchars($book['uploaded_by']) ?></p>
        <p><strong>On:</strong> <?= $book['uploaded_at'] ?></p>
      </div>
    <?php endwhile; ?>
  </div>
</section>


<?php include '../includes/footer.php'; ?>
