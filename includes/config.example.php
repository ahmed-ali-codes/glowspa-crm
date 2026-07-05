<?php
// ============================================================
// GlowSpa CRM — Admin Credentials & Settings
// Default: username = admin
// ============================================================
define('ADMIN_USERNAME', 'admin');

// 🔐 How to generate a secure password hash:
// Run this command in your terminal/command prompt:
// php -r "echo password_hash('YourSecretPassword', PASSWORD_BCRYPT);"
// Copy the generated hash (starts with $2y$) and paste it below.
define('ADMIN_PASSWORD_HASH', 'YOUR_HASH_HERE'); // PASTE YOUR BCRYPT HASH HERE

define('ADMIN_EMAIL', 'admin@glowspa.com');
define('SPA_NAME', 'GlowSpa CRM');
define('SPA_TIMEZONE', 'Asia/Dubai');
define('SPA_OPEN_TIME', '09:00');
define('SPA_CLOSE_TIME', '21:00');
define('CRON_SECRET_KEY', 'CHANGE_THIS_TO_A_RANDOM_SECRET');

// Set default timezone
date_default_timezone_set(SPA_TIMEZONE);
?>