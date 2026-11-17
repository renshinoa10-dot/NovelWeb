<?php
/**
 * karyaNovel - NotificationLog Model
 * Mencatat riwayat forwarding notifikasi WhatsApp via Fonnte
 */

class NotificationLog extends Model {
    protected string $table = 'notification_logs';
    protected string $primaryKey = 'id';

    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Memastikan tabel notification_logs tersedia
     */
    public function ensureTableExists(): void {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `notification_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `type` VARCHAR(50) NOT NULL,
                `target` VARCHAR(255) NOT NULL,
                `title` VARCHAR(255) NULL,
                `message` TEXT NOT NULL,
                `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
                `response` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_notif_type` (`type`),
                INDEX `idx_notif_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->query($sql);
            $this->db->execute();
        } catch (Throwable $e) {
            // Error ditangani secara diam agar tidak memblokir aplikasi
        }
    }

    /**
     * Menyimpan riwayat log notifikasi baru
     *
     * @param string $type Jenis notifikasi: 'new_novel', 'new_chapter', 'system', 'test'
     * @param string $target Nomor penerima / Group ID WhatsApp
     * @param string|null $title Judul notifikasi
     * @param string $message Isi pesan lengkap
     * @param string $status 'success' atau 'failed'
     * @param string|null $response Respon JSON mentah dari Fonnte
     * @return int ID log yang tersimpan
     */
    public function record(string $type, string $target, ?string $title, string $message, string $status = 'success', ?string $response = null): int {
        try {
            $sql = "INSERT INTO `notification_logs` (`type`, `target`, `title`, `message`, `status`, `response`, `created_at`)
                    VALUES (:type, :target, :title, :message, :status, :response, NOW())";
            $this->db->query($sql);
            $this->db->bind(':type', $type);
            $this->db->bind(':target', $target);
            $this->db->bind(':title', $title);
            $this->db->bind(':message', $message);
            $this->db->bind(':status', $status);
            $this->db->bind(':response', $response);
            $this->db->execute();
            return (int)$this->db->lastInsertId();
        } catch (Throwable $e) {
            error_log("Gagal menyimpan NotificationLog: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mengambil daftar log notifikasi terbaru
     *
     * @param int $limit
     * @param string|null $typeFilter
     * @return array
     */
    public function getRecent(int $limit = 50, ?string $typeFilter = null): array {
        try {
            $sql = "SELECT * FROM `notification_logs`";
            if ($typeFilter) {
                $sql .= " WHERE `type` = :type";
            }
            $sql .= " ORDER BY `id` DESC LIMIT :limit";

            $this->db->query($sql);
            if ($typeFilter) {
                $this->db->bind(':type', $typeFilter);
            }
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            return $this->db->resultSet();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Mengambil ringkasan statistik notifikasi
     *
     * @return array
     */
    public function getSummaryStats(): array {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN `status` = 'success' THEN 1 ELSE 0 END) as success_count,
                        SUM(CASE WHEN `status` = 'failed' THEN 1 ELSE 0 END) as failed_count,
                        SUM(CASE WHEN `type` = 'new_novel' THEN 1 ELSE 0 END) as novel_count,
                        SUM(CASE WHEN `type` = 'new_chapter' THEN 1 ELSE 0 END) as chapter_count,
                        SUM(CASE WHEN `type` = 'system' THEN 1 ELSE 0 END) as system_count
                    FROM `notification_logs`";
            $this->db->query($sql);
            $row = $this->db->single();
            return [
                'total' => (int)($row['total'] ?? 0),
                'success_count' => (int)($row['success_count'] ?? 0),
                'failed_count' => (int)($row['failed_count'] ?? 0),
                'novel_count' => (int)($row['novel_count'] ?? 0),
                'chapter_count' => (int)($row['chapter_count'] ?? 0),
                'system_count' => (int)($row['system_count'] ?? 0),
            ];
        } catch (Throwable $e) {
            return [
                'total' => 0, 'success_count' => 0, 'failed_count' => 0,
                'novel_count' => 0, 'chapter_count' => 0, 'system_count' => 0
            ];
        }
    }
}

