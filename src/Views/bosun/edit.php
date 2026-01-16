<?php
// $report is passed from controller
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/app.css">
    <title>Edit Fault Report</title>
</head>
<body>
    <div class="container">
        <h1>Edit Fault Report</h1>
        <div class="mb-3">
            <a href="/bosun/dashboard" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="/bosun/edit/<?php echo $report['id']; ?>" method="post">
                    <div class="mb-3">
                        <label for="id" class="form-label">Report ID</label>
                        <input type="text" class="form-control" id="id" value="<?php echo htmlspecialchars($report['id']); ?>" readonly>
                        <div class="form-text">ID cannot be modified</div>
                    </div>

                    <div class="mb-3">
                        <label for="boat_name" class="form-label">Boat Name</label>
                        <input type="text" class="form-control" id="boat_name" name="boat_name" value="<?php echo htmlspecialchars($report['boat_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="fault_description" class="form-label">Fault Description</label>
                        <textarea class="form-control" id="fault_description" name="fault_description" rows="4" required><?php echo htmlspecialchars($report['fault_description']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="New" <?php if ($report['status'] == 'New') echo 'selected'; ?>>New</option>
                            <option value="In progress" <?php if ($report['status'] == 'In progress') echo 'selected'; ?>>In Progress</option>
                            <option value="Waiting parts" <?php if ($report['status'] == 'Waiting parts') echo 'selected'; ?>>Waiting Parts</option>
                            <option value="Complete" <?php if ($report['status'] == 'Complete') echo 'selected'; ?>>Complete</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bosun_notes" class="form-label">Bosun Notes</label>
                        <textarea class="form-control" id="bosun_notes" name="bosun_notes" rows="4" placeholder="Add bosun notes..."><?php echo htmlspecialchars($report['bosun_notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/bosun/dashboard" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>