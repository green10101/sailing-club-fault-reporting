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
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Edit Fault Report</h1>

        <div class="card">
            <div class="card-body">
                <form action="index.php?route=/bosun/edit/<?php echo $report['id']; ?>" method="post">
                    <div class="mb-3">
                        <label for="id" class="form-label">Report ID</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($report['id']); ?></p>
                    </div>

                    <div class="mb-3">
                        <label for="boat_id" class="form-label">Boat Name</label>
                        <select class="form-select" id="boat_id" name="boat_id" required>
                            <option value="">Select a boat</option>
                            <?php foreach ($boats as $boat): ?>
                                <option value="<?php echo $boat['id']; ?>" <?php if ($boat['id'] == $report['boat_id']) echo 'selected'; ?>><?php echo htmlspecialchars($boat['boat_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
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

                    <div class="mb-3">
                        <label for="bosun_assessment" class="form-label">Bosun's Assessment</label>
                        <select class="form-select" id="bosun_assessment" name="bosun_assessment">
                            <option value="">Select assessment...</option>
                            <option value="No Fault Found" <?php if (($report['bosun_assessment'] ?? '') == 'No Fault Found') echo 'selected'; ?>>No Fault Found</option>
                            <option value="Damage/Impact" <?php if (($report['bosun_assessment'] ?? '') == 'Damage/Impact') echo 'selected'; ?>>Damage/Impact</option>
                            <option value="Wear and Tear" <?php if (($report['bosun_assessment'] ?? '') == 'Wear and Tear') echo 'selected'; ?>>Wear and Tear</option>
                            <option value="Consumable" <?php if (($report['bosun_assessment'] ?? '') == 'Consumable') echo 'selected'; ?>>Consumable</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="report_date" class="form-label">Date Report Generated</label>
                        <input type="text" class="form-control" id="report_date" name="report_date" value="<?php echo !empty($report['reported_at']) ? date('d/m/Y', strtotime($report['reported_at'])) : ''; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="completion_date" class="form-label">Date Report Completed</label>
                        <input type="date" class="form-control" id="completion_date" name="completion_date" value="<?php echo !empty($report['completion_date']) ? date('Y-m-d', strtotime($report['completion_date'])) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="part_required" class="form-label">Part Required</label>
                        <input type="text" class="form-control" id="part_required" name="part_required" placeholder="Specify part name or number..." value="<?php echo htmlspecialchars($report['part_required'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="part_status" class="form-label">Part Status</label>
                        <select class="form-select" id="part_status" name="part_status">
                            <option value="">Select status...</option>
                            <option value="Not in the Store" <?php if (($report['part_status'] ?? '') == 'Not in the Store') echo 'selected'; ?>>Not in the Store</option>
                            <option value="In the Store" <?php if (($report['part_status'] ?? '') == 'In the Store') echo 'selected'; ?>>In the Store</option>
                            <option value="On Order" <?php if (($report['part_status'] ?? '') == 'On Order') echo 'selected'; ?>>On Order</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="index.php?route=/bosun/dashboard" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>