<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="container">
        <h1>Thank You!</h1>
        <p>Your fault report has been submitted successfully. We appreciate your help in keeping our fleet in good condition.</p>
        <?php if (isset($isBosun) && $isBosun): ?>
            <a href="index.php?route=/bosun/dashboard" class="btn btn-primary">Back to Dashboard</a>
        <?php else: ?>
            <a href="index.php" class="btn btn-primary">Return to Home</a>
        <?php endif; ?>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>