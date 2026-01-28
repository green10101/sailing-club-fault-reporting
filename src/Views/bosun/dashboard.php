<?php
// $reports is passed from controller
$currentFilter = $_GET['filter'] ?? 'active';
$currentSort = $_GET['sort'] ?? 'r.reported_at';
$currentOrder = $_GET['order'] ?? 'DESC';

function getSortUrl($column, $currentFilter, $currentSort, $currentOrder) {
    $sortableColumns = ['r.id', 'b.boat_name', 'r.status', 'r.reported_at'];
    if (!in_array($column, $sortableColumns)) {
        return '#';
    }
    $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    return "index.php?route=/bosun/dashboard&filter={$currentFilter}&sort={$column}&order={$newOrder}";
}

function getSortIcon($column, $currentSort, $currentOrder) {
    $sortableColumns = ['r.id', 'b.boat_name', 'r.status', 'r.reported_at'];
    if (!in_array($column, $sortableColumns)) {
        return '';
    }
    if ($currentSort !== $column) {
        return '↕️'; // neutral sort icon
    }
    return $currentOrder === 'ASC' ? '↑' : '↓';
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
        <?php include '../src/Views/layouts/nav.php'; ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h1 style="margin: 0;">Fault Reports<?php if ($filteredBoat): ?> for <?php echo htmlspecialchars($filteredBoat['boat_name']); ?><?php endif; ?></h1>
            <a href="index.php?route=/bosun/print-report" class="btn btn-success" target="_blank">🖨️ Print Report</a>
        </div>
        <div class="mb-3 filter-section">
            <form method="GET" action="index.php" class="filter-form">
                <input type="hidden" name="route" value="/bosun/dashboard">
                <label for="filter" class="form-label">Filter Reports:</label>
                <select name="filter" id="filter" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?php echo ($currentFilter === 'all') ? 'selected' : ''; ?>>All Reports</option>
                    <option value="active" <?php echo ($currentFilter === 'active') ? 'selected' : ''; ?>>Active Faults Only</option>
                </select>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($currentSort); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($currentOrder); ?>">
            </form>
        </div>
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th><a href="<?php echo getSortUrl('r.id', $currentFilter, $currentSort, $currentOrder); ?>" class="text-decoration-none">ID <?php echo getSortIcon('r.id', $currentSort, $currentOrder); ?></a></th>
                    <th><a href="<?php echo getSortUrl('b.boat_name', $currentFilter, $currentSort, $currentOrder); ?>" class="text-decoration-none">Boat Name <?php echo getSortIcon('b.boat_name', $currentSort, $currentOrder); ?></a></th>
                    <th>Fault Description</th>
                    <th>Reported By</th>
                    <th><a href="<?php echo getSortUrl('r.status', $currentFilter, $currentSort, $currentOrder); ?>" class="text-decoration-none">Status <?php echo getSortIcon('r.status', $currentSort, $currentOrder); ?></a></th>
                    <th>Bosun Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['id']); ?></td>
                        <td><?php echo htmlspecialchars($report['boat_name']); ?></td>
                        <td><?php echo htmlspecialchars($report['fault_description']); ?></td>
                        <td><?php echo htmlspecialchars($report['reporter_name'] ?? ''); ?></td>
                        <td>
                            <?php 
                                $statusClass = 'status-new';
                                if ($report['status'] === 'In progress') {
                                    $statusClass = 'status-in-progress';
                                } elseif ($report['status'] === 'Waiting parts') {
                                    $statusClass = 'status-waiting-parts';
                                } elseif ($report['status'] === 'Complete') {
                                    $statusClass = 'status-complete';
                                }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($report['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($report['bosun_notes'] ?? ''); ?></td>
                        <td>
                            <a href="index.php?route=/bosun/edit/<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Report">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                                </svg>
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>