<?php
/**
 * app/config/app.php - general settings.
 * base_url: 'auto' (default) detects the URL of the public/ folder, so it works with
 * php -S, XAMPP and virtual hosts. Set it manually (e.g. '/online-laundry-system/public')
 * only if auto-detection gets it wrong.
 */
return [
    'app_name' => 'FreshFold Laundry',
    'base_url' => 'auto',
    'currency' => 'RWF',
    'timezone' => 'Africa/Kigali',
    // Configure this to an address accepted by your server's mail transport.
    'reset_email_from' => 'no-reply@example.com',
];
