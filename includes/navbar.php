<nav class="navbar">
    <div class="nav-container">
        <a href="/University_Complaint_Management_System/index.php" class="logo">University CMS</a>
        <div class="nav-links">
            <?php if (($_SESSION["role"] ?? null) === "admin" && isset($_SESSION["user_id"])): ?>
                <a href="/University_Complaint_Management_System/admin/dashboard.php">Dashboard</a>
                <a href="/University_Complaint_Management_System/auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="/University_Complaint_Management_System/auth/admin_login.php">Admin login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
