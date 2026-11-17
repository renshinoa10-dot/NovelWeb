<?php
/**
 * karyaNovel - User Model
 * Mengelola entitas pengguna, otentikasi, dan relasi data
 */

class User extends Model {
    protected string $table = 'users';

    /**
     * Mencari user berdasarkan alamat email
     */
    public function findByEmail(string $email): ?array {
        return $this->whereFirst('email', $email);
    }

    /**
     * Mencari user berdasarkan nomor telepon WhatsApp
     */
    public function findByPhone(string $phone): ?array {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        $this->db->query("SELECT * FROM users WHERE phone = :phone OR phone = :cleaned LIMIT 1");
        $this->db->bind(':phone', $phone);
        $this->db->bind(':cleaned', $cleaned);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Mencari user berdasarkan email atau nomor telepon (untuk fleksibilitas Login)
     */
    public function findByEmailOrPhone(string $identifier): ?array {
        $identifier = trim($identifier);
        $cleaned = preg_replace('/[^0-9]/', '', $identifier);
        
        $sql = "SELECT * FROM users WHERE email = :email";
        if (!empty($cleaned) && strlen($cleaned) >= 8) {
            $sql .= " OR phone = :phone OR phone = :cleaned";
        }
        $sql .= " LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':email', $identifier);
        if (!empty($cleaned) && strlen($cleaned) >= 8) {
            $this->db->bind(':phone', $identifier);
            $this->db->bind(':cleaned', $cleaned);
        }

        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Memeriksa apakah nomor telepon WhatsApp sudah digunakan
     */
    public function isPhoneTaken(string $phone, ?int $excludeUserId = null): bool {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        $sql = "SELECT id FROM users WHERE (phone = :phone OR phone = :cleaned)";
        if ($excludeUserId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':cleaned', $cleaned);
        if ($excludeUserId !== null) {
            $this->db->bind(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }
        return (bool)$this->db->single();
    }

    /**
     * Memeriksa apakah email sudah terdaftar
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function isEmailTaken(string $email, ?int $excludeUserId = null): bool {
        $sql = "SELECT id FROM users WHERE email = :email";
        if ($excludeUserId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " LIMIT 1";

        $this->db->query($sql);
        $this->db->bind(':email', $email);
        if ($excludeUserId !== null) {
            $this->db->bind(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }
        return (bool)$this->db->single();
    }

    /**
     * Mendaftarkan pengguna baru dengan enkripsi password Bcrypt dan dukungan Aktor
     *
     * @param array $data ['name', 'email', 'phone', 'password', 'role', 'otp_code', 'otp_expiry', 'is_verified']
     * @return string ID user baru
     */
    public function register(array $data): string {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Standarisasi role aktor: 'pembaca', 'penulis', 'admin'
        $allowedRoles = ['pembaca', 'penulis', 'admin', 'user'];
        if (!isset($data['role']) || !in_array($data['role'], $allowedRoles, true)) {
            $data['role'] = 'pembaca';
        }
        
        // Default is_verified jika belum diset
        if (!isset($data['is_verified'])) {
            $data['is_verified'] = 0;
        }

        return $this->create($data);
    }

    /**
     * Menyimpan kode OTP baru dan masa berlakunya
     */
    public function setOtp(int $userId, string $otpCode, int $expiryMinutes = 10): bool {
        $expiry = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));
        $this->db->query("UPDATE users SET otp_code = :otp, otp_expiry = :expiry, updated_at = NOW() WHERE id = :id");
        $this->db->bind(':otp', $otpCode);
        $this->db->bind(':expiry', $expiry);
        $this->db->bind(':id', $userId, PDO::PARAM_INT);
        return $this->db->execute();
    }

    /**
     * Memverifikasi kode OTP yang diinputkan pengguna
     */
    public function verifyOtp(int $userId, string $inputOtp): array {
        $user = $this->find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Pengguna tidak ditemukan.'];
        }

        if (empty($user['otp_code'])) {
            return ['success' => false, 'message' => 'Tidak ada kode OTP aktif. Silakan kirim ulang kode OTP.'];
        }

        if ($user['otp_code'] !== trim($inputOtp)) {
            return ['success' => false, 'message' => 'Kode OTP yang Anda masukkan salah. Periksa pesan WhatsApp Anda.'];
        }

        // Cek kedaluwarsa
        if (!empty($user['otp_expiry']) && strtotime($user['otp_expiry']) < time()) {
            return ['success' => false, 'message' => 'Kode OTP telah kedaluwarsa. Silakan minta kirim ulang kode OTP baru.'];
        }

        // OTP Valid: Tandai verified dan hapus OTP dari database
        $this->db->query("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL, updated_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $userId, PDO::PARAM_INT);
        $updated = $this->db->execute();

        return [
            'success' => $updated,
            'message' => $updated ? 'Verifikasi OTP berhasil!' : 'Gagal memperbarui status verifikasi akun.',
            'user' => $user
        ];
    }

    /**
     * Memverifikasi kredensial login (bisa menggunakan Email atau Nomor Telepon)
     *
     * @param string $identifier Email atau Nomor Telepon
     * @param string $password Kata sandi
     * @return array|null Mengembalikan data user jika valid, null jika salah
     */
    public function authenticate(string $identifier, string $password): ?array {
        $user = $this->findByEmailOrPhone($identifier);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Hapus hash password dari memori
            return $user;
        }
        return null;
    }

    /**
     * Mengambil seluruh data user untuk keperluan Admin Panel
     *
     * @param int $limit
     * @return array
     */
    public function getAllUsers(int $limit = 50): array {
        $this->db->query("
            SELECT u.id, u.name, u.email, u.phone, u.role, u.created_at, u.updated_at,
                   (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) as total_novels,
                   (SELECT COUNT(*) FROM ai_histories a WHERE a.user_id = u.id) as total_ai_prompts
            FROM users u
            ORDER BY u.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Mengubah hak akses role pengguna (pembaca, penulis, admin)
     *
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function changeRole(int $userId, string $role): bool {
        if (!in_array($role, ['pembaca', 'penulis', 'admin', 'user'], true)) {
            return false;
        }
        return $this->update($userId, ['role' => $role]);
    }

    /**
     * Mengambil seluruh novel yang ditulis oleh user ini (Relationship 1 to Many)
     *
     * @param int $userId
     * @return array
     */
    public function getNovels(int $userId): array {
        $this->db->query("
            SELECT n.*,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id) as total_chapters
            FROM novels n
            WHERE n.user_id = :user_id
            ORDER BY n.created_at DESC
        ");
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Menghitung total seluruh pengguna terdaftar aktual dari database
     */
    public function getTotalUsersCount(): int {
        $this->db->query("SELECT COUNT(*) as total FROM users");
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Menghitung jumlah pengguna berdasarkan kategori role aktual
     * 'admin', 'penulis', 'pembaca'
     */
    public function getUsersCountByRole(string $role): int {
        $role = strtolower(trim($role));
        if ($role === 'admin') {
            $this->db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
            $row = $this->db->single();
            return (int)($row['total'] ?? 0);
        } elseif ($role === 'penulis') {
            $this->db->query("
                SELECT COUNT(*) as total 
                FROM users u 
                WHERE u.role != 'admin' 
                  AND (u.role = 'penulis' OR (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) > 0)
            ");
            $row = $this->db->single();
            return (int)($row['total'] ?? 0);
        } elseif ($role === 'pembaca') {
            $this->db->query("
                SELECT COUNT(*) as total 
                FROM users u 
                WHERE u.role != 'admin' 
                  AND u.role != 'penulis'
                  AND (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) = 0
            ");
            $row = $this->db->single();
            return (int)($row['total'] ?? 0);
        }
        return 0;
    }

    /**
     * Mengambil ringkasan breakdown seluruh pengguna berdasarkan role
     */
    public function getUsersBreakdown(): array {
        $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN u.role = 'admin' THEN 1 ELSE 0 END) as admin,
                SUM(CASE WHEN u.role != 'admin' AND (u.role = 'penulis' OR (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) > 0) THEN 1 ELSE 0 END) as penulis,
                SUM(CASE WHEN u.role != 'admin' AND u.role != 'penulis' AND (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) = 0 THEN 1 ELSE 0 END) as pembaca
            FROM users u
        ");
        $row = $this->db->single();
        return [
            'total' => (int)($row['total'] ?? 0),
            'admin' => (int)($row['admin'] ?? 0),
            'penulis' => (int)($row['penulis'] ?? 0),
            'pembaca' => (int)($row['pembaca'] ?? 0),
        ];
    }

    /**
     * Mengambil daftar pengguna untuk Admin (dengan opsi filter role, pencarian, dan pagination)
     * Tidak menyertakan kolom sensitif seperti password
     */
    public function getUsersListForAdmin(?string $roleFilter = null, int $page = 1, int $perPage = 50, ?string $search = null): array {
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $whereConditions = [];
        $params = [];

        if ($roleFilter !== null) {
            $roleFilter = strtolower(trim($roleFilter));
            if ($roleFilter === 'admin') {
                $whereConditions[] = "u.role = 'admin'";
            } elseif ($roleFilter === 'penulis') {
                $whereConditions[] = "(u.role != 'admin' AND (u.role = 'penulis' OR (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) > 0))";
            } elseif ($roleFilter === 'pembaca') {
                $whereConditions[] = "(u.role != 'admin' AND u.role != 'penulis' AND (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) = 0)";
            }
        }

        if ($search !== null && trim($search) !== '') {
            $whereConditions[] = "(u.name LIKE :search1 OR u.email LIKE :search2)";
            $params[':search1'] = '%' . trim($search) . '%';
            $params[':search2'] = '%' . trim($search) . '%';
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Hitung total data
        $countSql = "SELECT COUNT(*) as total FROM users u {$whereClause}";
        $this->db->query($countSql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $countRow = $this->db->single();
        $total = (int)($countRow['total'] ?? 0);
        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        // Ambil data halaman
        $dataSql = "
            SELECT u.id, u.name, u.email, u.phone, u.created_at,
                   (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) as total_novels,
                   CASE 
                       WHEN u.role = 'admin' THEN 'Admin'
                       WHEN u.role = 'penulis' OR (SELECT COUNT(*) FROM novels n WHERE n.user_id = u.id) > 0 THEN 'Penulis'
                       ELSE 'Pembaca'
                   END as role_display
            FROM users u
            {$whereClause}
            ORDER BY u.id ASC
            LIMIT :limit OFFSET :offset
        ";

        $this->db->query($dataSql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind(':limit', $perPage, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        $data = $this->db->resultSet();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Mengambil riwayat interaksi AI milik user ini (Relationship 1 to Many)
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getAiHistories(int $userId, int $limit = 20): array {
        $this->db->query("
            SELECT a.*, c.title as chapter_title, n.title as novel_title
            FROM ai_histories a
            LEFT JOIN chapters c ON a.chapter_id = c.id
            LEFT JOIN novels n ON c.novel_id = n.id
            WHERE a.user_id = :user_id
            ORDER BY a.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}
