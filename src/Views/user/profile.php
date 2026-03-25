<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
    <title>My Profile - Sailing Club</title>
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>My Profile</h1>

        <div class="profile-card" style="max-width: 500px; margin: 2rem 0;">
            <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600; color: #333;">Name</label>
                    <p style="margin: 0.5rem 0; font-size: 1.1rem;"><?php echo htmlspecialchars($user['name']); ?></p>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 600; color: #333;">Email</label>
                    <p style="margin: 0.5rem 0; font-size: 1.1rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div style="margin-bottom: 2rem;">
                    <label style="font-weight: 600; color: #333;">Role</label>
                    <p style="margin: 0.5rem 0; font-size: 1.1rem;"><?php echo htmlspecialchars($user['role']); ?></p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="index.php?route=/profile/edit" class="btn btn-primary">Edit Profile</a>
                    <a href="index.php?route=/profile/change-password" class="btn btn-outline-primary">Change Password</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
