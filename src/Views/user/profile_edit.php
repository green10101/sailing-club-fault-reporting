<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
    <title>Edit Profile - Sailing Club</title>
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Edit Profile</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=/profile/update" style="max-width: 500px; margin: 2rem 0;">
            <?php echo csrfField(); ?>
            <div class="form-group mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <?php if (isset($supportsNotifyPreference) && $supportsNotifyPreference): ?>
                <div class="form-group mb-3 form-check">
                    <input
                        type="checkbox"
                        id="notify_new_reports"
                        name="notify_new_reports"
                        class="form-check-input"
                        value="1"
                        <?php echo !empty($user['notify_new_reports']) ? 'checked' : ''; ?>
                    >
                    <label for="notify_new_reports" class="form-check-label">Email me new faults</label>
                </div>
            <?php endif; ?>
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="index.php?route=/profile" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
