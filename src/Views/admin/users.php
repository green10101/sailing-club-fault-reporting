<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
    <title>User Management - Admin</title>
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>User Management</h1>
        <div class="mb-3">
            <a href="index.php?route=/admin/user/new" class="btn btn-primary">Add User</a>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Error deleting user. Please try again.</div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Logins</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo isset($user['login_count']) ? (int)$user['login_count'] : 0; ?></td>
                        <td>
                            <a href="index.php?route=/admin/user/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="index.php?route=/admin/user/reset-password/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-warning">Reset Password</a>
                            <a href="index.php?route=/admin/user/delete/<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>