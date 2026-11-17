<?php
/**
 * karyaNovel - Global Helper Functions
 */

/**
 * Mendapatkan nilai environment variable dengan fallback
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null) {
    return Env::get($key, $default);
}

/**
 * Mendapatkan URL lengkap berdasarkan path
 *
 * @param string $path
 * @return string
 */
function base_url(string $path = ''): string {
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($path ? '/' . $path : '');
}

/**
 * Mendapatkan URL aset statis dalam folder public
 *
 * @param string $path
 * @return string
 */
function asset(string $path = ''): string {
    $path = ltrim($path, '/');
    return base_url('public/' . $path);
}

/**
 * Redirect ke URL tertentu
 *
 * @param string $path
 * @return void
 */
function redirect(string $path = ''): void {
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        header("Location: " . $path);
    } else {
        header("Location: " . base_url($path));
    }
    exit;
}

/**
 * Sanitasi string untuk keamanan output HTML
 *
 * @param mixed $data
 * @return string
 */
function sanitize($data): string {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format tanggal dalam bahasa Indonesia
 *
 * @param string $date
 * @param bool $withTime
 * @return string
 */
function format_date(string $date, bool $withTime = false): string {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($date);
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);

    $formatted = "$tgl $bln $thn";
    if ($withTime) {
        $formatted .= ' ' . date('H:i', $timestamp) . ' WIB';
    }
    return $formatted;
}

/**
 * Menampilkan pesan Flash Notification
 *
 * @return string
 */
function render_flash(): string {
    if (!isset($_SESSION['flash'])) {
        return '';
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type = $flash['type'] ?? 'info';
    $message = $flash['message'] ?? '';
    $icon = match($type) {
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default => 'bi-info-circle-fill'
    };

    $safeMessage = sanitize($message);

    return "
    <div class=\"alert alert-{$type} alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3\" role=\"alert\">
        <i class=\"bi {$icon} fs-5\"></i>
        <div>{$safeMessage}</div>
        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
    </div>";
}

/**
 * Menetapkan pesan flash baru
 *
 * @param string $message
 * @param string $type success|danger|warning|info
 * @return void
 */
function set_flash(string $message, string $type = 'info'): void {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Menghasilkan atau mengambil token CSRF dari session
 *
 * @return string
 */
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        Session::start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Menghasilkan input hidden form CSRF token
 *
 * @return string
 */
function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Memverifikasi validitas token CSRF
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token = null): bool {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        Session::start();
    }
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Potong teks untuk preview/sinopsis
 *
 * @param string $text
 * @param int $length
 * @return string
 */
function truncate_text(string $text, int $length = 120): string {
    $text = strip_tags($text);
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * Mendapatkan URL cover novel yang valid, atau null jika file tidak ditemukan
 *
 * @param string|null $cover
 * @return string|null
 */
function novel_cover_url(?string $cover): ?string {
    if (empty($cover)) {
        return null;
    }
    $path = ltrim($cover, '/');
    if (str_starts_with($path, 'public/')) {
        $path = substr($path, 7);
    }
    $fullPath = PUBLIC_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (file_exists($fullPath) && !is_dir($fullPath)) {
        return asset($path);
    }
    return null;
}

/**
 * Mendapatkan nilai pengaturan aplikasi dari Database (dengan fallback ke default / .env)
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function app_setting(string $key, $default = null) {
    if (class_exists('Setting')) {
        return Setting::get($key, $default);
    }
    $settingModelFile = APPROOT . '/models/Setting.php';
    if (file_exists($settingModelFile)) {
        require_once $settingModelFile;
        return Setting::get($key, $default);
    }
    return $default;
}

