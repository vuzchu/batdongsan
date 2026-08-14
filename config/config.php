<?php
/**
 * Cau hinh chung cua he thong: session, base URL, hang so.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') === '1' ? '1' : '0');

define('BASE_URL', rtrim(getenv('APP_URL') ?: '', '/'));
define('SITE_NAME', 'Homeland Real Estate');
define('UPLOAD_DIR', __DIR__ . '/../uploads/properties/');
define('UPLOAD_URL', 'uploads/properties/');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/imgbb.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
