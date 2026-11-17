<?php
/**
 * karyaNovel - Session Management Helper
 */

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return self::has('user_id') && !empty(self::get('user_id'));
    }

    public static function userId(): ?int {
        return self::get('user_id', null);
    }

    public static function user(): ?array {
        return self::get('user', null);
    }

    public static function isAdmin(): bool {
        $user = self::user();
        return ($user && isset($user['role']) && $user['role'] === 'admin');
    }

    public static function isPenulis(): bool {
        $user = self::user();
        return ($user && isset($user['role']) && in_array($user['role'], ['penulis', 'user'], true) && $user['role'] !== 'admin');
    }

    public static function isPembaca(): bool {
        $user = self::user();
        return ($user && isset($user['role']) && $user['role'] === 'pembaca');
    }

    public static function userRole(): string {
        $user = self::user();
        return $user['role'] ?? 'guest';
    }

    public static function setAuth(array $user): void {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true); // Cegah Session Fixation
        }
        unset($user['password']); // Keamanan: jangan simpan hash password di session
        self::set('user_id', (int)$user['id']);
        self::set('user', $user);
        self::set('role', $user['role'] ?? 'user');
    }

    public static function clearAuth(): void {
        self::remove('user_id');
        self::remove('user');
        self::remove('role');
    }
}
