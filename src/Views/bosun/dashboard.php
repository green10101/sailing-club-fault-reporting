<?php
session_start();
require_once '../../config/database.php';
require_once '../../Models/Report.php';
require_once '../../Models/User.php';

$reports = Report::getAllReports();
$bosunId = $_SESSION['user_id'] ?? null;

if (!$bosunId) {
    header('Location: /login.php');
    exit;
}

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
        <a href="/report.php" class="btn btn-primary">Report a Fault</a>
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
                            <a href="/bosun/update_report.php?id=<?php echo $report['id']; ?>" class="btn btn-warning">Update</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>