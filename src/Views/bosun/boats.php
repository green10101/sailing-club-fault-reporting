<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Status - Bosun Dashboard</title>
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Asset Status Report</h1>
        <?php
        $currentFilter = $_GET['filter'] ?? 'current';
        $currentSort = $_GET['sort'] ?? 'boat_name';
        $currentOrder = $_GET['order'] ?? 'ASC';
        
        function getStatusDisplay($status) {
            switch ($status) {
                case 'OK':
                    return '<span style="color: #28a745; font-weight: 600;">✓ OK</span>';
                case 'Minor Faults':
                    return '<span style="color: #ffc107; font-weight: 600;">⚠️ Minor Faults</span>';
                case 'Out of Operation':
                    return '<span style="color: #dc3545; font-weight: 600;">⛔ Out of Operation</span>';
                case 'Retired':
                    return '<span style="color: #6c757d; font-weight: 600;">🔒 Retired</span>';
                default:
                    return htmlspecialchars($status);
            }
        }
        
        function getSortUrl($column, $currentFilter, $currentSort, $currentOrder) {
            $sortableColumns = ['boat_name','boat_type','status'];
            if (!in_array($column, $sortableColumns)) {
                return '#';
            }
            $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
            return "?filter={$currentFilter}&sort={$column}&order={$newOrder}";
        }
        function getSortIcon($column, $currentSort, $currentOrder) {
            $sortableColumns = ['boat_name','boat_type','status'];
            if (!in_array($column, $sortableColumns)) {
                return '';
            }
            if ($currentSort !== $column) {
                return '↕️';
            }
            return $currentOrder === 'ASC' ? '↑' : '↓';
        }
        ?>
        <form method="GET" action="index.php" class="mb-3">
            <input type="hidden" name="route" value="/bosun/boats">
            <label for="filter" class="form-label">Filter:</label>
            <select name="filter" id="filter" class="form-select d-inline w-auto" onchange="this.form.submit()">
                <option value="current" <?php echo ($currentFilter === 'current') ? 'selected' : ''; ?>>All Current Assets</option>
                <option value="ok_or_minor" <?php echo ($currentFilter === 'ok_or_minor') ? 'selected' : ''; ?>>OK and Minor Faults</option>
                <option value="not_operational" <?php echo ($currentFilter === 'not_operational') ? 'selected' : ''; ?>>Out of Operation</option>
                <option value="all" <?php echo ($currentFilter === 'all') ? 'selected' : ''; ?>>All Assets inc Retired</option>
            </select>
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th><a href="<?php echo getSortUrl('boat_name', $currentFilter, $currentSort, $currentOrder); ?>" class="text-decoration-none">Asset Name <?php echo getSortIcon('boat_name', $currentSort, $currentOrder); ?></a></th>
                    <th><a href="<?php echo getSortUrl('boat_type', $currentFilter, $currentSort, $currentOrder); ?>" class="text-decoration-none">Asset Type <?php echo getSortIcon('boat_type', $currentSort, $currentOrder); ?></a></th>
                    <th><a href="<?php echo getSortUrl('status', $currentFilter, $currentSort, $currentOrder); ?>" class="text-decoration-none">Status <?php echo getSortIcon('status', $currentSort, $currentOrder); ?></a></th>
                    <th>Active Faults</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($boats as $boat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($boat['boat_name']); ?></td>
                        <td><?php echo htmlspecialchars($boat['boat_type']); ?></td>
                        <td><?php echo getStatusDisplay($boat['status']); ?></td>
                        <td>
                            <a href="index.php?route=/bosun/dashboard&boat_id=<?php echo $boat['id']; ?>&filter=active">
                                <?php echo $boat['active_faults']; ?> active
                            </a>
                        </td>
                        <td>
                            <a href="index.php?route=/bosun/boat/edit/<?php echo $boat['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mb-3">
            <a href="index.php?route=/bosun/boat/new" class="btn btn-primary">Add Asset</a>
            <a href="index.php?route=/bosun/print-report" class="btn btn-success" target="_blank">🖨️ Print Active Faults Report</a>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>