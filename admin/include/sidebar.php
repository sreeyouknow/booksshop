<div class="sidebar">
    <ul class="nav-list">
        <li>
            <a href="dashboard.php">
                <span class="icon">📖</span>
                <span class="title"><strong> BOOKS HERE </strong></span>
            </a>
        </li>
        <li>
            <a href="dashboard.php">
                <span class="icon">🏡</span>
                <span class="title">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="user-management.php">
                <span class="icon">👥</span>
                <span class="title">User Management</span>
            </a>
        </li>
        <li>
            <a href="book-manage.php"> 
                <span class="icon">📚</span>
                <span class="title">Book Management</span>
            </a>
        </li>
        <li>
            <a href="request-books.php">
                <span class="icon">📝</span>
                <span class="title">Book Requests</span>
            </a>
        </li>
        <li>
            <a href="agent-manage.php">
                <span class="icon">🕵️</span>
                <span class="title">Agent Management</span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="#" class="submenu-toggle">
                <span class="icon">⚙️</span>
                <span class="title">Admin Settings ▾</span>
            </a>
            <ul class="submenu">
                <a href="profile.php">👤| Update Profile</a>
                <a href="change-password.php">🔒| Change Password</a>
                <a href="update-smpt.php">📝| Change SMPT settings</a>
            </ul>
        </li>
        <li>
            <a href="../base/logout.php">
                <span class="icon">🔓</span>
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