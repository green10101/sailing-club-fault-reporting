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
        <h1>Report a Fault</h1>
        <form action="/report" method="POST">
            <div class="form-group">
                <label for="boat_name">Boat Name</label>
                <input type="text" id="boat_name" name="boat_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="fault_description">Fault Description</label>
                <textarea id="fault_description" name="fault_description" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Report</button>
        </form>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>