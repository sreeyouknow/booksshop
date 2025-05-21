<div class="sidebar">
    <ul class="nav-list">
        <li>
            <a href="dashboard.php">
                <span class="icon">📚</span>
                <span class="title"><strong>BOOKS HERE</strong></span>
            </a>
        </li>
        <li>
            <a href="dashboard.php">
                <span class="icon">🏠</span>
                <span class="title">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="upload_book.php">
                <span class="icon">📖</span>
                <span class="title">Upload Books</span>
            </a>
        </li>
        <li>
            <a href="client_management.php">
                <span class="icon">🧑</span>
                <span class="title">Client Management</span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <span class="icon">📝</span>
                <span class="title">Client Activies ▾</span>
            </a>
            <ul class="submenu">
                <a href="client-messages.php">💬| Client Messages</a>
                <a href="client-reviews.php">⭐| Client Reviews</a>
            </ul>
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