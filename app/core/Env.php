<?php
/**
 * karyaNovel - Environment Variables Loader
 */

class Env {
    private static bool $loaded = false;

    /**
     * Memuat file .env ke dalam environment PHP
     *
     * @param string $path Path direktori tempat file .env berada
     * @return void
     */
    public static function load(string $path): void {
        if (self::$loaded) {
            return;
        }

        $envFile = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($envFile)) {
            // Coba cek .env.example sebagai fallback
            $exampleFile = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '.env.example';
            if (file_exists($exampleFile)) {
                $envFile = $exampleFile;
            } else {
                return;
            }
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Lewati komentar
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Parse format KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Hapus tanda kutip jika ada
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Mendapatkan nilai konfigurasi environment
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        if (isset($_ENV[$key])) {
            return self::normalizeValue($_ENV[$key]);
        }

        $val = getenv($key);
        if ($val !== false) {
            return self::normalizeValue($val);
        }

        return $default;
    }

    /**
     * Menormalkan tipe data boolean/null/numeric
     */
    private static function normalizeValue($value) {
        $lower = strtolower((string)$value);
        if ($lower === 'true' || $lower === '(true)') return true;
        if ($lower === 'false' || $lower === '(false)') return false;
        if ($lower === 'null' || $lower === '(null)' || $lower === '') return null;
        if (is_numeric($value)) return $value + 0;
        return $value;
    }
}
