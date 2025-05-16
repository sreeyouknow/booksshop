
<div class="sidebar">
    <ul class="nav-list">
        <li>
            <a href="dashboard.php">
                <span class="icon"> </span>
                <span class="title"><strong> BOOKS HERE </strong></span>
            </a>
        </li>
        <li>
            <a href="dashboard.php">
                <span class="icon">🏠</span>
                <span class="title">Dashboard</span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <span class="icon">👤</span>
                <span class="title">Profile ▾</span>
            </a>
            <ul class="submenu">
                <a href="update_profile.php">📝| Update Profile</a>
                <a href="change_password.php">🔒| Change Password</a>
            </ul>
        </li>
        <li>
            <a href="view-books.php">
                <span class="icon">📚</span>
                <span class="title">Book list</span>
            </a>
        </li>
        <li>
            <a href="wishlist.php"> 
                <span class="icon">❤️</span>
                <span class="title">Wishlist</span>
            </a>
        </li>
        <li>
            <a href="cart.php">
                <span class="icon">🛒</span>
                <span class="title">Cart</span>
            </a>
        </li>
        <li>
            <a href="book-request.php">
                <span class="icon">📨</span>
                <span class="title">My Requests</span>
            </a>
        </li>
        <li>
            <a href="purchase.php">
                <span class="icon">📦</span>
                <span class="title">My Orders</span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <span class="icon">📞</span>
                <span class="title">Contact ▾</span>
            </a>
            <ul class="submenu">
                <a href="messages.php">💬| Messages</a>
                <a href="reviews.php">⭐| Reviews</a>
            </ul>
        </li>
        <li>
            <a href="../base/logout.php">
                <span class="icon">🚪</span>
                <span class="title">Logout</span>
            </a>
        </li>
    </ul>
</div>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const toggles = document.querySelectorAll(".submenu-toggle");
    toggles.forEach(toggle => {
      toggle.addEventListener("click", function (e) {
        e.preventDefault();
        const submenu = this.nextElementSibling;
        submenu.style.display = submenu.style.display === "flex" ? "none" : "flex";
      });
    });
  });
</script>
