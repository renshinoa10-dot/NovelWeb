<?php
/**
 * karyaNovel - Konfigurasi Aplikasi Berbasis Environment Variables
 */

// Path Direktori Utama
if (!defined('APPROOT')) define('APPROOT', dirname(__DIR__));
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(APPROOT));
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');

// Muat Core Env Loader & Muat File .env
require_once APPROOT . '/core/Env.php';
Env::load(ROOT_PATH);

// Environment: 'development' atau 'production'
define('ENVIRONMENT', Env::get('APP_ENV', 'development'));

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Konfigurasi Aplikasi
define('APP_NAME', Env::get('APP_NAME', 'karyaNovel'));
define('APP_TAGLINE', 'Platform Penulisan Novel dengan AI Writing Assistant');
define('APP_VERSION', '1.0.0');
define('APP_KEY', Env::get('APP_KEY', 'default_secret_key'));

// Base URL detection (prioritaskan APP_URL dari .env jika valid, atau deteksi otomatis)
$configuredUrl = Env::get('APP_URL');
if (!empty($configuredUrl) && filter_var($configuredUrl, FILTER_VALIDATE_URL)) {
    define('BASE_URL', rtrim($configuredUrl, '/'));
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $baseUrl = rtrim($protocol . $host . $scriptName, '/');

    if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
        $baseUrl = 'http://localhost/Novelweb';
    }
    define('BASE_URL', $baseUrl);
}

// Konfigurasi Database MySQL dari .env
define('DB_HOST', Env::get('DB_HOST', '127.0.0.1'));
define('DB_PORT', Env::get('DB_PORT', '3306'));
define('DB_NAME', Env::get('DB_NAME', 'karyanovel_db'));
define('DB_USER', Env::get('DB_USER', 'root'));
define('DB_PASS', Env::get('DB_PASS', ''));
define('DB_CHARSET', Env::get('DB_CHARSET', 'utf8mb4'));

// Konfigurasi AI Writing Assistant
define('AI_PROVIDER', Env::get('AI_PROVIDER', 'custom'));
define('AI_API_KEY', Env::get('AI_API_KEY', ''));
define('AI_MODEL', Env::get('AI_MODEL', 'karyanovel-ai-v1'));
define('AI_TIMEOUT', (int)Env::get('AI_TIMEOUT', 30));
define('AI_MAX_PROMPT_LENGTH', (int)Env::get('AI_MAX_PROMPT_LENGTH', 2000));

// Konfigurasi Notifikasi WhatsApp Gateway (Fonnte)
define('FONNTE_API_KEY', Env::get('FONNTE_API_KEY', 'NM8K5TKmpDu1gDaVaNyj'));
define('FONNTE_TARGET_PHONE', Env::get('FONNTE_TARGET_PHONE', '089634700208'));
define('FONNTE_ENABLED', filter_var(Env::get('FONNTE_ENABLED', true), FILTER_VALIDATE_BOOLEAN));
define('FONNTE_NOTIFY_NEW_NOVEL', filter_var(Env::get('FONNTE_NOTIFY_NEW_NOVEL', true), FILTER_VALIDATE_BOOLEAN));
define('FONNTE_NOTIFY_NEW_CHAPTER', filter_var(Env::get('FONNTE_NOTIFY_NEW_CHAPTER', true), FILTER_VALIDATE_BOOLEAN));
define('FONNTE_NOTIFY_SYSTEM', filter_var(Env::get('FONNTE_NOTIFY_SYSTEM', true), FILTER_VALIDATE_BOOLEAN));

// Konfigurasi Default Controller & Method
define('DEFAULT_CONTROLLER', 'HomeController');
define('DEFAULT_METHOD', 'index');
