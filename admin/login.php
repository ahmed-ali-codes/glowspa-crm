<?php
session_start();

$config_file = __DIR__ . '/../includes/config.php';
$config_warning = !file_exists($config_file);

if (!$config_warning) {
    require_once $config_file;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$config_warning) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verify against defined constants in config.php
    if (defined('ADMIN_USERNAME') && defined('ADMIN_PASSWORD_HASH') && 
        $username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | GlowSpa CRM</title>
    <meta name="description" content="GlowSpa CRM Admin Portal — Secure login for spa management.">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="login-page">
    <div class="login-card">

        <!-- Lotus symbol in rose gold -->
        <span class="login-symbol">✿</span>

        <h1 class="login-title">GlowSpa Admin Portal</h1>
        <p class="login-subtitle">Where Wellness Meets Luxury</p>

        <?php if ($config_warning): ?>
            <div class="alert alert-warning" style="background: #FFF3CD; color: #856404; border-left: 4px solid #FFEBA8; font-size: 0.85rem; text-align: left; margin-bottom: 1.5rem; padding: 1rem;">
                <strong>Security Notice:</strong> <code>includes/config.php</code> is missing. You cannot log in until you create this file. Please copy <code>includes/config.example.php</code> to <code>includes/config.php</code> and set up your password hash.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="admin" value="admin" required autocomplete="username">
            </div>

            <div class="form-group" style="margin-bottom: 1.75rem;">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••" value="admin123" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary">
                ✿ &nbsp;Sign In to Portal
            </button>
        </form>

        <div class="login-back">
            <a href="../index.php">&larr; Back to Booking Page</a>
        </div>
    </div>
</div>

</body>
</html>
