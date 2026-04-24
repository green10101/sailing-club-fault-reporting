<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boat Check-In</title>
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 0.5rem; flex-wrap: wrap;">
            <h1>Boat Check-In</h1>
            <div>
                <a href="index.php?route=/report-form" class="btn btn-secondary">Report a Fault</a>
                <?php if (!isset($_SESSION['user'])): ?>
                    <a href="index.php?route=/login" class="btn btn-secondary">Bosun Login</a>
                <?php else: ?>
                    <a href="index.php?route=/bosun/boats" class="btn btn-secondary">Bosun Dashboard</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="index.php?route=/checkin" method="POST" data-single-submit="true">
            <input type="hidden" name="checkin_submission_token" value="<?php echo htmlspecialchars($checkinSubmissionToken ?? ''); ?>">

            <div class="form-group">
                <label for="boat_id">Boat Name</label>
                <select id="boat_id" name="boat_id" class="form-control" required>
                    <option value="">Select a boat</option>
                    <?php foreach ($boats as $boat): ?>
                        <option value="<?php echo $boat['id']; ?>" <?php echo ((string)($old['boat_id'] ?? '') === (string)$boat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($boat['boat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars(date('d/m/Y H:i')); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="user_name">Your Name</label>
                <input type="text" id="user_name" name="user_name" class="form-control" value="<?php echo htmlspecialchars($old['user_name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="user_email">Contact Email</label>
                <input type="email" id="user_email" name="user_email" class="form-control" value="<?php echo htmlspecialchars($old['user_email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Has the boat been put away properly and any radios, safety box, sails and foils back in the correct place?</label>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                    <label style="font-weight: 500;"><input type="radio" name="put_away_ok" value="yes" <?php echo (($old['put_away_ok'] ?? '') === 'yes') ? 'checked' : ''; ?> required> Yes</label>
                    <label style="font-weight: 500;"><input type="radio" name="put_away_ok" value="no" <?php echo (($old['put_away_ok'] ?? '') === 'no') ? 'checked' : ''; ?> required> No</label>
                </div>
                <small id="putAwayNoPrompt" style="display: none; color: #8a6d3b; margin-top: 0.5rem;">
                    You selected "No". Please add the reason in the Additional Notes section below.
                </small>
            </div>

            <div class="form-group">
                <label>Is the boat in a safe / working condition for the next user?</label>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                    <label style="font-weight: 500;"><input type="radio" name="safe_for_next_user" value="yes" <?php echo (($old['safe_for_next_user'] ?? '') === 'yes') ? 'checked' : ''; ?> required> Yes</label>
                    <label style="font-weight: 500;"><input type="radio" name="safe_for_next_user" value="no" <?php echo (($old['safe_for_next_user'] ?? '') === 'no') ? 'checked' : ''; ?> required> No</label>
                </div>
            </div>

            <div class="form-group">
                <label>Is the boat "Fault free"?</label>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                    <label style="font-weight: 500;"><input type="radio" name="has_faults_to_rectify" value="yes" <?php echo (($old['has_faults_to_rectify'] ?? '') === 'yes') ? 'checked' : ''; ?> required> Yes</label>
                    <label style="font-weight: 500;"><input type="radio" name="has_faults_to_rectify" value="no" <?php echo (($old['has_faults_to_rectify'] ?? '') === 'no') ? 'checked' : ''; ?> required> No</label>
                </div>
            </div>

            <div id="faultDetailsPanel" style="display: none; border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; background: #f8f9fa; margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Did this fault occur during your checkout?</label>
                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                        <label style="font-weight: 500;"><input type="radio" name="damage_during_checkout" value="yes" <?php echo (($old['damage_during_checkout'] ?? '') === 'yes') ? 'checked' : ''; ?>> Yes</label>
                        <label style="font-weight: 500;"><input type="radio" name="damage_during_checkout" value="no" <?php echo (($old['damage_during_checkout'] ?? '') === 'no') ? 'checked' : ''; ?>> No</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fault_description">Please describe the fault(s) that need rectification.</label>
                    <textarea id="fault_description" name="fault_description" class="form-control" rows="4" placeholder="Describe the fault(s) that need rectification..."><?php echo htmlspecialchars($old['fault_description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="checkin_notes">Additional Notes (Optional)</label>
                <textarea id="checkin_notes" name="checkin_notes" class="form-control" rows="3"><?php echo htmlspecialchars($old['checkin_notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" data-submitting-text="Submitting...">Submit Check-In</button>
            </div>
            <p data-submit-status style="margin-top: 0.75rem; color: #666; min-height: 1.5rem;"></p>
        </form>
        <p style="margin-top: 1.5rem; font-size: 0.875rem; color: #666;">App version: <?php echo htmlspecialchars(getAppVersionLabel()); ?></p>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        (function () {
            const putAwayInputs = document.querySelectorAll('input[name="put_away_ok"]');
            const safeInputs = document.querySelectorAll('input[name="safe_for_next_user"]');
            const faultInputs = document.querySelectorAll('input[name="has_faults_to_rectify"]');
            const damageInputs = document.querySelectorAll('input[name="damage_during_checkout"]');
            const faultDescription = document.getElementById('fault_description');
            const checkinNotes = document.getElementById('checkin_notes');
            const putAwayNoPrompt = document.getElementById('putAwayNoPrompt');
            const panel = document.getElementById('faultDetailsPanel');

            function selectedValue(inputs) {
                const checked = Array.from(inputs).find(function (input) { return input.checked; });
                return checked ? checked.value : null;
            }

            function updateFaultPanel() {
                const safeValue = selectedValue(safeInputs);
                const hasFaultsValue = selectedValue(faultInputs);
                const requiresFaultDetails = safeValue === 'no' || hasFaultsValue === 'no';

                panel.style.display = requiresFaultDetails ? 'block' : 'none';

                damageInputs.forEach(function (input) {
                    input.required = requiresFaultDetails;
                });

                faultDescription.required = requiresFaultDetails;
            }

            function updateNotesPrompt() {
                const putAwayValue = selectedValue(putAwayInputs);
                const shouldPromptForNotes = putAwayValue === 'no';

                putAwayNoPrompt.style.display = shouldPromptForNotes ? 'block' : 'none';

                if (shouldPromptForNotes) {
                    checkinNotes.setAttribute('aria-describedby', 'putAwayNoPrompt');
                } else {
                    checkinNotes.removeAttribute('aria-describedby');
                }
            }

            putAwayInputs.forEach(function (input) {
                input.addEventListener('change', updateNotesPrompt);
            });

            safeInputs.forEach(function (input) {
                input.addEventListener('change', updateFaultPanel);
            });
            faultInputs.forEach(function (input) {
                input.addEventListener('change', updateFaultPanel);
            });

            updateFaultPanel();
            updateNotesPrompt();
        })();
    </script>
</body>
</html>
