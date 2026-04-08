<?php

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'ojs_developer');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Site URL
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost:8000');

// Upload Settings
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp');

// WhatsApp API Token (Sensitive Data - Load from environment variable)
define('WA_API_TOKEN', getenv('WA_API_TOKEN') ?: '');

// Admin Path
define('ADMIN_PATH', __DIR__ . '/admin');
