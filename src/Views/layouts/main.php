<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sailing Club Fault Reporting</title>
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