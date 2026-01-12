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
        <a href="/" class="btn btn-primary">Back to Home</a>
        <h2>Reported Faults</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Boat Name</th>
                    <th>Fault Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['boat_name']); ?></td>
                        <td><?php echo htmlspecialchars($report['fault_description']); ?></td>
                        <td><?php echo htmlspecialchars($report['status']); ?></td>
                        <td>
                            <form action="/bosun/update-status" method="post" style="display:inline;">
                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                <select name="status">
                                    <option value="pending" <?php if ($report['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="in_progress" <?php if ($report['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                                    <option value="waiting_parts" <?php if ($report['status'] == 'waiting_parts') echo 'selected'; ?>>Waiting for Parts</option>
                                    <option value="completed" <?php if ($report['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                </select>
                                <button type="submit" class="btn btn-warning">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>