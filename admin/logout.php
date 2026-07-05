<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout | GlowSpa CRM</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="logout-page">
    <div class="logout-card">
        <span style="font-size: 3rem; color: var(--rose-gold); display: block; margin-bottom: 0.5rem;">✿</span>
        <h2>Sign Out</h2>
        <p>Are you sure you want to log out of the GlowSpa Admin Portal?</p>
        <form method="POST" class="btn-group">
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" name="confirm_logout" value="1" class="btn btn-danger">
                Confirm Logout
            </button>
        </form>
    </div>
</div>

</body>
</html>
