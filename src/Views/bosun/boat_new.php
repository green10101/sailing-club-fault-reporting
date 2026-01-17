<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/app.css">
    <title>New Boat</title>
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Add New Boat</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body">
                <form action="/bosun/boat/new" method="post">
                    <div class="mb-3">
                        <label for="boat_name" class="form-label">Boat Name</label>
                        <input type="text" class="form-control" id="boat_name" name="boat_name" value="<?php echo isset($prefill['boatName']) ? htmlspecialchars($prefill['boatName']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="boat_type" class="form-label">Boat Type</label>
                        <input type="text" class="form-control" id="boat_type" name="boat_type" value="<?php echo isset($prefill['boatType']) ? htmlspecialchars($prefill['boatType']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="serial_number" class="form-label">Serial Number</label>
                        <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo isset($prefill['serialNumber']) ? htmlspecialchars($prefill['serialNumber']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <?php $sel = isset($prefill['status']) ? $prefill['status'] : 'OK'; ?>
                            <option value="OK" <?php echo $sel === 'OK' ? 'selected' : ''; ?>>OK</option>
                            <option value="Minor Faults" <?php echo $sel === 'Minor Faults' ? 'selected' : ''; ?>>Minor Faults</option>
                            <option value="Out of Operation" <?php echo $sel === 'Out of Operation' ? 'selected' : ''; ?>>Out of Operation</option>
                            <option value="Retired" <?php echo $sel === 'Retired' ? 'selected' : ''; ?>>Retired</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Boat</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>