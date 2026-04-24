<?php
$currentBoatId = $_GET['boat_id'] ?? null;
$currentFaultFilter = $_GET['fault_filter'] ?? 'all';
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

function buildCheckinFilterUrl($boatId, $faultFilter, $page = 1)
{
    $url = "index.php?route=/bosun/checkins&fault_filter=" . urlencode($faultFilter) . "&page=" . (int) $page;
    if (!empty($boatId)) {
        $url .= '&boat_id=' . urlencode($boatId);
    }
    return $url;
}

function buildCheckinExportUrl($boatId, $faultFilter)
{
    $url = "index.php?route=/bosun/checkins/export&fault_filter=" . urlencode($faultFilter);
    if (!empty($boatId)) {
        $url .= '&boat_id=' . urlencode($boatId);
    }
    return $url;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boat Check-In History</title>
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
</head>
<body>
    <div class="container">
        <?php include '../src/Views/layouts/nav.php'; ?>
        <h1>Boat Check-In History</h1>

        <form method="GET" action="index.php" class="mb-3" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
            <input type="hidden" name="route" value="/bosun/checkins">
            <div>
                <label for="boat_id" class="form-label">Asset</label>
                <select name="boat_id" id="boat_id" class="form-select">
                    <option value="">All Assets</option>
                    <?php foreach ($boats as $boat): ?>
                        <option value="<?php echo $boat['id']; ?>" <?php echo ((string) $currentBoatId === (string) $boat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($boat['boat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fault_filter" class="form-label">Fault Filter</label>
                <select name="fault_filter" id="fault_filter" class="form-select">
                    <option value="all" <?php echo $currentFaultFilter === 'all' ? 'selected' : ''; ?>>All Check-Ins</option>
                    <option value="with_fault" <?php echo $currentFaultFilter === 'with_fault' ? 'selected' : ''; ?>>With Fault Report</option>
                    <option value="without_fault" <?php echo $currentFaultFilter === 'without_fault' ? 'selected' : ''; ?>>No Fault Report</option>
                </select>
            </div>
            <div style="align-self: end;">
                <div style="display: flex; gap: 0.5rem; flex-wrap: nowrap; align-items: center;">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a class="btn btn-outline-secondary" href="<?php echo buildCheckinExportUrl($currentBoatId, $currentFaultFilter); ?>" style="white-space: nowrap;">Export CSV</a>
                </div>
            </div>
        </form>

        <table class="table table-responsive">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Asset</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Put Away</th>
                    <th>Safe for Next</th>
                    <th>Faults to Rectify</th>
                    <th>Damage During Checkout</th>
                    <th>Additional Notes</th>
                    <th>Fault Report</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($checkins)): ?>
                    <tr>
                        <td colspan="10">No check-ins found for the selected filters.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($checkins as $checkin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($checkin['checked_in_at']))); ?></td>
                        <td><?php echo htmlspecialchars($checkin['boat_name']); ?></td>
                        <td><?php echo htmlspecialchars($checkin['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($checkin['user_email']); ?></td>
                        <td><?php echo ((int) $checkin['put_away_ok'] === 1) ? 'Yes' : 'No'; ?></td>
                        <td><?php echo ((int) $checkin['safe_for_next_user'] === 1) ? 'Yes' : 'No'; ?></td>
                        <td><?php echo ((int) $checkin['has_faults_to_rectify'] === 1) ? 'Yes' : 'No'; ?></td>
                        <td>
                            <?php if ($checkin['damage_during_checkout'] === null): ?>
                                Not asked
                            <?php else: ?>
                                <?php echo ((int) $checkin['damage_during_checkout'] === 1) ? 'Yes' : 'No'; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($checkin['checkin_notes']) ? nl2br(htmlspecialchars($checkin['checkin_notes'])) : '-'; ?></td>
                        <td>
                            <?php if (!empty($checkin['fault_report_id'])): ?>
                                <a href="index.php?route=/bosun/edit/<?php echo (int) $checkin['fault_report_id']; ?>">
                                    #<?php echo (int) $checkin['fault_report_id']; ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 1.5rem; flex-wrap: wrap;">
                <?php if ($currentPage > 1): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo buildCheckinFilterUrl($currentBoatId, $currentFaultFilter, $currentPage - 1); ?>">Previous</a>
                <?php endif; ?>

                <span style="padding: 0.5rem 1rem; background: #f8f9fa; border-radius: 6px; font-weight: 600;">
                    Page <?php echo (int) $currentPage; ?> of <?php echo (int) $totalPages; ?>
                    <span style="color: #6c757d; font-weight: normal;">(<?php echo (int) $totalCheckins; ?> total check-ins)</span>
                </span>

                <?php if ($currentPage < $totalPages): ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo buildCheckinFilterUrl($currentBoatId, $currentFaultFilter, $currentPage + 1); ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>
