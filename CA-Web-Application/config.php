<?php
/**
 * config.php — central configuration for the apply.acmeca.com application.
 * Every page includes this FIRST, before anything else.
 */

ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

define('DB_PATH', __DIR__ . '/data/acmeca.sqlite');

// Adjust to your actual home directory if different.
define('PKI_ROOT', '/home/prime/pki');
define('DV_CA_DIR', PKI_ROOT . '/dv-ca');
define('OV_CA_DIR', PKI_ROOT . '/ov-ca');
define('EV_CA_DIR', PKI_ROOT . '/ev-ca');

define('RESET_TOKEN_EXPIRY_MINUTES', 30);
define('MIN_PASSWORD_LENGTH', 10);

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_secure', '1');

session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
