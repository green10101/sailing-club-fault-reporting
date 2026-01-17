<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4" style="background-color: #f8f9fa; padding: 1rem; margin-bottom: 2rem; border-bottom: 1px solid #dee2e6;">
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
        <a href="/bosun/boats" class="btn btn-primary">Boat Status</a>
        <a href="/bosun/dashboard" class="btn btn-info">Fault Reports</a>
        <a href="/" class="btn btn-success">Report New Fault</a>
        <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
            <a href="/admin/users" class="btn btn-warning">User Management</a>
        <?php endif; ?>
        <a href="/logout" class="btn btn-secondary" style="margin-left: auto;">Logout</a>
    </div>
</nav>