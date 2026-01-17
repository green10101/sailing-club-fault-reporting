<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isBoats = strpos($currentPath, '/bosun/boats') === 0 || strpos($currentPath, '/bosun/boat/') === 0;
$isDashboard = strpos($currentPath, '/bosun/dashboard') === 0 || strpos($currentPath, '/bosun/edit/') === 0;
$isReport = $currentPath === '/' || $currentPath === '/report';
$isAdmin = strpos($currentPath, '/admin/') === 0;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4" style="background-color: #f8f9fa; padding: 1rem; margin-bottom: 2rem; border-bottom: 1px solid #dee2e6;">
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
        <a href="/bosun/boats" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isBoats ? 'opacity: 0.7;' : ''; ?>padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">Boat Status</a>
        <a href="/bosun/dashboard" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isDashboard ? 'opacity: 0.7;' : ''; ?>padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">Fault Reports</a>
        <a href="/" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isReport ? 'opacity: 0.7;' : ''; ?>padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">Report New Fault</a>
        <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
            <a href="/admin/users" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isAdmin ? 'opacity: 0.7;' : ''; ?>padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">User Management</a>
        <?php endif; ?>
        <a href="/logout" class="btn btn-secondary" style="margin-left: auto;">Logout</a>
    </div>
</nav>