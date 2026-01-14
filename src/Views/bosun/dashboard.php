<?php
// $reports is passed from controller
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/app.css">
    <title>Bosun Dashboard</title>
</head>
<body>
    <div class="container">
        <h1>Bosun Dashboard</h1>
        <div class="mb-3">
            <a href="/" class="btn btn-primary">Report New Fault</a>
            <a href="/logout" class="btn btn-secondary">Logout</a>
        </div>
        <h2>Reported Faults</h2>
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th>Boat Name</th>
                    <th>Fault Description</th>
                    <th>Status</th>
                    <th>Bosun Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['boat_name']); ?></td>
                        <td><?php echo htmlspecialchars($report['fault_description']); ?></td>
                        <td><?php echo htmlspecialchars($report['status']); ?></td>
                        <td><?php echo htmlspecialchars($report['bosun_notes'] ?? ''); ?></td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <!-- Status Update Section -->
                                <div class="mb-2">
                                    <form action="/bosun/update-status" method="post" class="d-inline">
                                        <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="pending" <?php if ($report['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                                <option value="in_progress" <?php if ($report['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                                <option value="waiting_parts" <?php if ($report['status'] == 'waiting_parts') echo 'selected'; ?>>Waiting for Parts</option>
                                                <option value="completed" <?php if ($report['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                            </select>
                                            <button type="submit" class="btn btn-warning btn-sm">Update</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Notes Update Section -->
                                <div>
                                    <form action="/bosun/update-notes" method="post">
                                        <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                        <div class="mb-1">
                                            <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Add bosun notes..."><?php echo htmlspecialchars($report['bosun_notes'] ?? ''); ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-info btn-sm">Update Notes</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>