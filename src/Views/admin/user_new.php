<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/app.css">
    <title>Add User - Admin</title>
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Add New User</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="/admin/user/new" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($prefill['username']) ? htmlspecialchars($prefill['username']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($prefill['name']) ? htmlspecialchars($prefill['name']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="text" class="form-control" id="password" name="password" value="<?php echo isset($prefill['password']) ? htmlspecialchars($prefill['password']) : ''; ?>" required>
                        <small class="form-text text-muted">Enter a human-readable password</small>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($prefill['email']) ? htmlspecialchars($prefill['email']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="bosun" <?php echo isset($prefill['role']) && $prefill['role'] === 'bosun' ? 'selected' : ''; ?>>Bosun</option>
                            <option value="admin" <?php echo isset($prefill['role']) && $prefill['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Create User</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>