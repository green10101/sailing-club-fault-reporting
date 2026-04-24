<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($currentPath === '/index.php' || $currentPath === '/public/index.php' || $currentPath === '/' || $currentPath === '/bosun/' || $currentPath === '/bosun') {
    $currentPath = $_GET['route'] ?? $currentPath;
}
$isBoats = strpos($currentPath, '/bosun/boats') === 0 || strpos($currentPath, '/bosun/boat/') === 0;
$isDashboard = strpos($currentPath, '/bosun/dashboard') === 0 || strpos($currentPath, '/bosun/edit/') === 0;
$isReportForm = strpos($currentPath, '/report-form') !== false;
$isCheckinForm = strpos($currentPath, '/checkin') !== false;
$isCheckins = strpos($currentPath, '/bosun/checkins') === 0;
$isAdmin = strpos($currentPath, '/admin/') === 0;
$isProfile = strpos($currentPath, '/profile') === 0;
$isAdminUser = function_exists('userHasAdminRole') && userHasAdminRole();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4" style="background-color: #f8f9fa; padding: 0.5rem; margin-bottom: 2rem; border-bottom: 1px solid #dee2e6;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <a href="index.php?route=/bosun/boats" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isBoats ? 'opacity: 0.7;' : ''; ?>padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">Asset Status</a>
        <a href="index.php?route=/bosun/dashboard" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isDashboard ? 'opacity: 0.7;' : ''; ?>padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">Fault Reports</a>
        <a href="index.php?route=/bosun/checkins" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isCheckins ? 'opacity: 0.7;' : ''; ?>padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">Check-In History</a>
        <a href="index.php?route=/checkin" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isCheckinForm ? 'opacity: 0.7;' : ''; ?>padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">Boat Check-In</a>
        <a href="index.php?route=/report-form" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isReportForm ? 'opacity: 0.7;' : ''; ?>padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">Report a Fault</a>
        <?php if ($isAdminUser): ?>
            <a href="index.php?route=/admin/users" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isAdmin ? 'opacity: 0.7;' : ''; ?>padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">User Management</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="index.php?route=/profile" class="btn" style="background-color: #004d99; color: white; border: none; <?php echo !$isProfile ? 'opacity: 0.7;' : ''; ?>padding: 8px 12px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">My Profile</a>
        <?php endif; ?>
        <a href="index.php?route=/logout" class="btn btn-secondary" style="margin-left: auto; padding: 8px 12px; font-size: 0.9rem;">Logout</a>
    </div>
</nav>