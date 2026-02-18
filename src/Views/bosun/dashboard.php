<?php
// $reports is passed from controller
$currentStatus = $_GET['status'] ?? null;
$currentBoatId = $_GET['boat_id'] ?? null;
$currentSort = $_GET['sort'] ?? 'r.reported_at';
$currentOrder = $_GET['order'] ?? 'DESC';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Determine if we're showing only active faults
$showActiveOnly = ($currentStatus !== 'Complete' && $currentStatus !== 'All');

function getSortUrl($column, $currentStatus, $currentBoatId, $currentSort, $currentOrder, $currentPage) {
    $sortableColumns = ['r.id', 'b.boat_name', 'r.status', 'r.reported_at'];
    if (!in_array($column, $sortableColumns)) {
        return '#';
    }
    $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    $url = "index.php?route=/bosun/dashboard&sort={$column}&order={$newOrder}&page={$currentPage}";
    if ($currentStatus) {
        $url .= "&status={$currentStatus}";
    }
    if ($currentBoatId) {
        $url .= "&boat_id={$currentBoatId}";
    }
    return $url;
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

function buildFilterUrl($status, $currentBoatId, $currentSort, $currentOrder, $page = 1) {
    $url = "index.php?route=/bosun/dashboard&sort={$currentSort}&order={$currentOrder}&page={$page}";
    if ($status) {
        $url .= "&status={$status}";
    }
    if ($currentBoatId) {
        $url .= "&boat_id={$currentBoatId}";
    }
    return $url;
}

function buildPageUrl($page, $currentStatus, $currentBoatId, $currentSort, $currentOrder) {
    $url = "index.php?route=/bosun/dashboard&page={$page}&sort={$currentSort}&order={$currentOrder}";
    if ($currentStatus) {
        $url .= "&status={$currentStatus}";
    }
    if ($currentBoatId) {
        $url .= "&boat_id={$currentBoatId}";
    }
    return $url;
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
        </div>
        
        <div class="mb-3 status-filter-section" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; font-size: 0.9rem;">
            <label style="font-weight: 600; white-space: nowrap; margin: 0;">Status:</label>
            <a href="<?php echo buildFilterUrl(null, $currentBoatId, $currentSort, $currentOrder); ?>" 
               class="btn btn-sm <?php echo (!$currentStatus ? 'btn-primary' : 'btn-outline-secondary'); ?>" 
               style="display: flex; align-items: center; gap: 0.25rem; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <span>⭐ All Active</span>
                <span class="badge" style="background-color: rgba(0,0,0,0.15); padding: 0.125rem 0.375rem; border-radius: 10px; font-size: 0.7rem;"><?php echo $countAllActive ?? 0; ?></span>
            </a>
            <a href="<?php echo buildFilterUrl('All', $currentBoatId, $currentSort, $currentOrder); ?>" 
               class="btn btn-sm <?php echo ($currentStatus === 'All' ? 'btn-primary' : 'btn-outline-secondary'); ?>" 
               style="display: flex; align-items: center; gap: 0.25rem; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <span>All Reports</span>
                <span class="badge" style="background-color: rgba(0,0,0,0.15); padding: 0.125rem 0.375rem; border-radius: 10px; font-size: 0.7rem;"><?php echo $countAllReports ?? 0; ?></span>
            </a>
            <a href="<?php echo buildFilterUrl('New', $currentBoatId, $currentSort, $currentOrder); ?>" 
               class="btn btn-sm <?php echo ($currentStatus === 'New' ? 'btn-primary' : 'btn-outline-secondary'); ?>" 
               style="display: flex; align-items: center; gap: 0.25rem; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <span>🆕 New</span>
                <span class="badge" style="background-color: rgba(0,0,0,0.15); padding: 0.125rem 0.375rem; border-radius: 10px; font-size: 0.7rem;"><?php echo $countNew ?? 0; ?></span>
            </a>
            <a href="<?php echo buildFilterUrl('In progress', $currentBoatId, $currentSort, $currentOrder); ?>" 
               class="btn btn-sm <?php echo ($currentStatus === 'In progress' ? 'btn-primary' : 'btn-outline-secondary'); ?>" 
               style="display: flex; align-items: center; gap: 0.25rem; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <span>🛠️ In Progress</span>
                <span class="badge" style="background-color: rgba(0,0,0,0.15); padding: 0.125rem 0.375rem; border-radius: 10px; font-size: 0.7rem;"><?php echo $countInProgress ?? 0; ?></span>
            </a>
            <a href="<?php echo buildFilterUrl('Waiting parts', $currentBoatId, $currentSort, $currentOrder); ?>" 
               class="btn btn-sm <?php echo ($currentStatus === 'Waiting parts' ? 'btn-primary' : 'btn-outline-secondary'); ?>" 
               style="display: flex; align-items: center; gap: 0.25rem; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <span>⏰ Waiting Parts</span>
                <span class="badge" style="background-color: rgba(0,0,0,0.15); padding: 0.125rem 0.375rem; border-radius: 10px; font-size: 0.7rem;"><?php echo $countWaitingParts ?? 0; ?></span>
            </a>
            <a href="<?php echo buildFilterUrl('Complete', $currentBoatId, $currentSort, $currentOrder); ?>" 
               class="btn btn-sm <?php echo ($currentStatus === 'Complete' ? 'btn-primary' : 'btn-outline-secondary'); ?>" 
               style="display: flex; align-items: center; gap: 0.25rem; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
                <span>✅ Complete</span>
                <span class="badge" style="background-color: rgba(0,0,0,0.15); padding: 0.125rem 0.375rem; border-radius: 10px; font-size: 0.7rem; min-width: 1.5rem; text-align: center;"><?php echo $countComplete ?? 0; ?></span>
            </a>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
            <button id="printSelectedBtn" class="btn btn-success" style="gap: 0.5rem; align-items: center; display: flex;">
                🖨️ Print Selected
            </button>
            <a href="index.php?route=/bosun/print-report" class="btn btn-success" target="_blank">🖨️ Print All Reports</a>
        </div>
        <table class="table table-responsive">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAllCheckbox" title="Select all reports on this page">
                    </th>
                    <th><a href="<?php echo getSortUrl('r.id', $currentStatus, $currentBoatId, $currentSort, $currentOrder, $currentPage); ?>" class="text-decoration-none">ID <?php echo getSortIcon('r.id', $currentSort, $currentOrder); ?></a></th>
                    <th><a href="<?php echo getSortUrl('b.boat_name', $currentStatus, $currentBoatId, $currentSort, $currentOrder, $currentPage); ?>" class="text-decoration-none">Asset Name <?php echo getSortIcon('b.boat_name', $currentSort, $currentOrder); ?></a></th>
                    <th>Fault Description</th>
                    <th>Reported By</th>
                    <th><a href="<?php echo getSortUrl('r.status', $currentStatus, $currentBoatId, $currentSort, $currentOrder, $currentPage); ?>" class="text-decoration-none">Status <?php echo getSortIcon('r.status', $currentSort, $currentOrder); ?></a></th>
                    <th>Bosun Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox" class="report-checkbox" value="<?php echo htmlspecialchars($report['id']); ?>" title="Select this report">
                        </td>
                        <td><?php echo htmlspecialchars($report['id']); ?></td>
                        <td><?php echo htmlspecialchars($report['boat_name']); ?></td>
                        <td><?php echo htmlspecialchars($report['fault_description']); ?></td>
                        <td><?php echo htmlspecialchars($report['reporter_name'] ?? ''); ?></td>
                        <td style="text-align: center;">
                            <?php 
                                $statusClass = 'status-new';
                                $statusIcon = '🆕';
                                if ($report['status'] === 'In progress') {
                                    $statusClass = 'status-in-progress';
                                    $statusIcon = '🛠️';
                                } elseif ($report['status'] === 'Waiting parts') {
                                    $statusClass = 'status-waiting-parts';
                                    $statusIcon = '⏰';
                                } elseif ($report['status'] === 'Complete') {
                                    $statusClass = 'status-complete';
                                    $statusIcon = '✅';
                                }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>" style="font-size: 1.5rem;">
                                <?php echo $statusIcon; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($report['bosun_notes'] ?? ''); ?></td>
                        <td style="white-space: nowrap;">
                            <a href="index.php?route=/bosun/edit/<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit Report" style="padding: 0.25rem 0.5rem; line-height: 1;">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle;">
                                    <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                                </svg>
                            </a>
                            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                                <button onclick="if(confirm('Are you sure you want to delete this fault report? This action cannot be undone.')) { window.location.href='index.php?route=/admin/delete-report/<?php echo $report['id']; ?>'; }" class="btn btn-sm btn-outline-danger" title="Delete Report" style="padding: 0.25rem 0.5rem; line-height: 1; margin-left: 0.25rem;">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle;">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 2rem; flex-wrap: wrap;">
                <?php if ($currentPage > 1): ?>
                    <a href="<?php echo buildPageUrl($currentPage - 1, $currentStatus, $currentBoatId, $currentSort, $currentOrder); ?>" class="btn btn-sm btn-outline-primary">« Previous</a>
                <?php endif; ?>

                <span style="padding: 0.5rem 1rem; background: #f8f9fa; border-radius: 6px; font-weight: 600;">
                    Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
                    <span style="color: #6c757d; font-weight: normal;">(<?php echo $totalReports; ?> total reports)</span>
                </span>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?php echo buildPageUrl($currentPage + 1, $currentStatus, $currentBoatId, $currentSort, $currentOrder); ?>" class="btn btn-sm btn-outline-primary">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="/assets/js/app.js"></script>
    <script>
        // Multi-select functionality for all users
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const reportCheckboxes = document.querySelectorAll('.report-checkbox');
        const printSelectedBtn = document.getElementById('printSelectedBtn');

        // Select all checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                reportCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateButtonVisibility();
            });
        }

        // Individual checkboxes
        reportCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Update select all checkbox state
                const allChecked = Array.from(reportCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(reportCheckboxes).some(cb => cb.checked);
                
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                }
                
                updateButtonVisibility();
            });
        });

        function updateButtonVisibility() {
            const selectedCount = Array.from(reportCheckboxes).filter(cb => cb.checked).length;
            if (printSelectedBtn) {
                printSelectedBtn.disabled = selectedCount === 0;
            }
        }

        // Print selected reports
        if (printSelectedBtn) {
            printSelectedBtn.addEventListener('click', function() {
                const selectedIds = Array.from(reportCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value)
                    .join(',');
                
                if (selectedIds) {
                    window.open(`index.php?route=/bosun/print-report&report_ids=${selectedIds}`, '_blank');
                }
            });
        }
    </script>
</body>
</html>