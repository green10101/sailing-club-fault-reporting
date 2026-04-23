<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link rel="stylesheet" href="/bosun/assets/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Thank You!</h1>
        <?php if (($submissionType ?? 'fault') === 'checkin'): ?>
            <p>Your boat check-in has been submitted successfully. Thank you for helping keep the fleet ready and safe.</p>
        <?php else: ?>
            <p>Your fault report has been submitted successfully. We appreciate your help in keeping our fleet in good condition.</p>
        <?php endif; ?>
        <?php if (isset($isBosun) && $isBosun): ?>
            <a href="index.php?route=/bosun/dashboard" class="btn btn-primary">Back to Dashboard</a>
        <?php else: ?>
            <a href="index.php?route=/" class="btn btn-primary">Return to Home</a>
        <?php endif; ?>
        <p style="margin-top: 1.5rem; font-size: 0.875rem; color: #666;">App version: <?php echo htmlspecialchars(getAppVersionLabel()); ?></p>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>