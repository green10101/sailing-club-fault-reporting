<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
    <title>Reset Password - Admin</title>
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Reset Password for <?php echo htmlspecialchars($user['name']); ?></h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="index.php?route=/admin/user/reset-password/<?php echo $user['id']; ?>" method="post">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="text" class="form-control" id="new_password" name="new_password" required autofocus>
                        <small class="form-text text-muted">Enter a new plain text password</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>