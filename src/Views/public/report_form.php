<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Fault</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>Report a Fault</h1>
            <a href="index.php?route=/login" class="btn btn-secondary">Staff Login</a>
        </div>
        <form action="index.php?route=/report" method="POST">
            <div class="form-group">
                <label for="boat_id">Boat Name</label>
                <select id="boat_id" name="boat_id" class="form-control" required>
                    <option value="">Select a boat</option>
                    <?php foreach ($boats as $boat): ?>
                        <option value="<?php echo $boat['id']; ?>"><?php echo htmlspecialchars($boat['boat_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="reporter_name">Your Name</label>
                <input type="text" id="reporter_name" name="reporter_name" class="form-control" value="<?php echo isset($_SESSION['user']['name']) ? htmlspecialchars($_SESSION['user']['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="reporter_email">Your Email</label>
                <input type="email" id="reporter_email" name="reporter_email" class="form-control" value="<?php echo isset($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="fault_description">Fault Description</label>
                <textarea id="fault_description" name="fault_description" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Submit Report</button>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="index.php?route=/bosun/boats" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>