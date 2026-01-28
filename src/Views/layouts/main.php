<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#007bff">
    <meta name="description" content="Sailing club fault and maintenance reporting system">
    <title>Sailing Club Fault Reporting</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16.png">
    <link rel="apple-touch-icon" href="/assets/icons/apple-touch-icon.png">
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/report.php">Report a Fault</a></li>
                <li><a href="/login.php">Bosun Login</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php echo $content; ?>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Sailing Club. All rights reserved.</p>
    </footer>
</body>
</html>