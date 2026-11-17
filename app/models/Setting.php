<?php
/**
 * karyaNovel - Setting Model
 * Mengelola konfigurasi aplikasi dan AI Assistant secara dinamis dan persisten
 */

class Setting extends Model {
    protected string $table = 'settings';
    protected string $primaryKey = 'key_name';
    private static array $cache = [];

    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Memastikan tabel settings tersedia di database
     */
    public function ensureTableExists(): void {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `settings` (
                `key_name` VARCHAR(100) NOT NULL PRIMARY KEY,
                `key_value` LONGTEXT NULL,
                `description` VARCHAR(255) NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->query($sql);
            $this->db->execute();
        } catch (Throwable $e) {
            // Abaikan jika error (misal koneksi DB sedang offline)
        }
    }

    /**
     * Mendapatkan nilai pengaturan berdasarkan key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key] ?? $default;
        }

        try {
            $db = Database::getInstance();
            if ($db->isConnected()) {
                $db->query("SELECT `key_value` FROM `settings` WHERE `key_name` = :key LIMIT 1");
                $db->bind(':key', $key);
                $row = $db->single();
                if ($row && isset($row['key_value']) && $row['key_value'] !== '') {
                    self::$cache[$key] = $row['key_value'];
                    return $row['key_value'];
                }
            }
        } catch (Throwable $e) {
            // Fallback ke default jika tabel belum ada atau DB error
        }

        self::$cache[$key] = $default;
        return $default;
    }

    /**
     * Menyimpan atau memperbarui nilai pengaturan
     *
     * @param string $key
     * @param string|null $value
     * @param string|null $description
     * @return bool
     */
    public function set(string $key, ?string $value, ?string $description = null): bool {
        try {
            $sql = "INSERT INTO `settings` (`key_name`, `key_value`, `description`) 
                    VALUES (:key, :val, :desc)
                    ON DUPLICATE KEY UPDATE 
                    `key_value` = VALUES(`key_value`),
                    `description` = COALESCE(VALUES(`description`), `description`),
                    `updated_at` = NOW()";

            $this->db->query($sql);
            $this->db->bind(':key', $key);
            $this->db->bind(':val', $value);
            $this->db->bind(':desc', $description);
            $success = $this->db->execute();

            if ($success) {
                self::$cache[$key] = $value;
            }
            return $success;
        } catch (Throwable $e) {
            error_log("Error saving setting {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil seluruh data pengaturan sebagai associative array
     *
     * @return array
     */
    public function getAllSettings(): array {
        try {
            $this->db->query("SELECT * FROM `settings` ORDER BY `key_name` ASC");
            $rows = $this->db->resultSet();
            $result = [];
            foreach ($rows as $row) {
                $result[$row['key_name']] = $row['key_value'];
                self::$cache[$row['key_name']] = $row['key_value'];
            }
            return $result;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Helper untuk memperbarui file .env agar tersinkronisasi
     *
     * @param array $envUpdates ['AI_API_KEY' => '...', 'AI_MODEL' => '...']
     * @return bool
     */
    public static function syncToEnvFile(array $envUpdates): bool {
        $envPath = ROOT_PATH . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($envPath)) {
            return false;
        }

        try {
            $content = file_get_contents($envPath);
            if ($content === false) {
                return false;
            }

            foreach ($envUpdates as $key => $value) {
                $pattern = "/^{$key}=.*/m";
                $replacement = "{$key}={$value}";

                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, $replacement, $content);
                } else {
                    $content .= "\n{$replacement}";
                }

                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }

            return (bool)file_put_contents($envPath, $content);
        } catch (Throwable $e) {
            error_log("Gagal menyinkronkan ke .env: " . $e->getMessage());
            return false;
        }
    }
}
