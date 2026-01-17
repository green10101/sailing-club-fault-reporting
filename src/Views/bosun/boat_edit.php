<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/app.css">
    <title>Edit Boat</title>
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Edit Boat</h1>

        <div class="card">
            <div class="card-body">
                <form action="/bosun/boat/edit/<?php echo $boat['id']; ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Boat ID</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($boat['id']); ?></p>
                    </div>

                    <div class="mb-3">
                        <label for="boat_name" class="form-label">Boat Name</label>
                        <input type="text" class="form-control" id="boat_name" name="boat_name" value="<?php echo htmlspecialchars($boat['boat_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="boat_type" class="form-label">Boat Type</label>
                        <input type="text" class="form-control" id="boat_type" name="boat_type" value="<?php echo htmlspecialchars($boat['boat_type']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="serial_number" class="form-label">Serial Number</label>
                        <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($boat['serial_number'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="OK" <?php echo $boat['status'] === 'OK' ? 'selected' : ''; ?>>OK</option>
                            <option value="Minor Faults" <?php echo $boat['status'] === 'Minor Faults' ? 'selected' : ''; ?>>Minor Faults</option>
                            <option value="Out of Operation" <?php echo $boat['status'] === 'Out of Operation' ? 'selected' : ''; ?>>Out of Operation</option>
                            <option value="Retired" <?php echo $boat['status'] === 'Retired' ? 'selected' : ''; ?>>Retired</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>