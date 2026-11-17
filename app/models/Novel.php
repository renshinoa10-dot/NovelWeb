<?php
/**
 * karyaNovel - Novel Model
 * Mengelola data novel, relasi ke penulis (User) dan Bab (Chapter)
 */

class Novel extends Model {
    protected string $table = 'novels';

    /**
     * Mengambil daftar novel berstatus 'published' untuk eksplorasi publik
     *
     * @param int $limit
     * @param string|null $genre
     * @param string|null $search
     * @return array
     */
    public function getPublished(int $limit = 12, ?string $genre = null, ?string $search = null): array {
        $sql = "
            SELECT n.*, u.name as author_name,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id AND c.status = 'published') as total_chapters
            FROM novels n
            JOIN users u ON n.user_id = u.id
            WHERE n.status = 'published'
        ";

        if ($genre) {
            $sql .= " AND LOWER(n.genre) = LOWER(:genre)";
        }
        if ($search) {
            $sql .= " AND (n.title LIKE :search1 OR n.synopsis LIKE :search2)";
        }

        $sql .= " ORDER BY n.created_at DESC LIMIT :limit";

        $this->db->query($sql);
        if ($genre) {
            $this->db->bind(':genre', $genre);
        }
        if ($search) {
            $this->db->bind(':search1', '%' . $search . '%');
            $this->db->bind(':search2', '%' . $search . '%');
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Mengambil daftar novel published dengan pagination untuk halaman katalog publik
     *
     * @param int $page
     * @param int $perPage
     * @param string|null $genre
     * @param string|null $search
     * @return array ['data', 'total', 'page', 'per_page', 'total_pages']
     */
    public function getPublishedPaginated(int $page = 1, int $perPage = 9, ?string $genre = null, ?string $search = null): array {
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 9;
        $offset = ($page - 1) * $perPage;

        // 1. Hitung total novel published yang sesuai filter
        $countSql = "
            SELECT COUNT(*) as total
            FROM novels n
            JOIN users u ON n.user_id = u.id
            WHERE n.status = 'published'
        ";
        if ($genre) {
            $countSql .= " AND LOWER(n.genre) = LOWER(:genre)";
        }
        if ($search) {
            $countSql .= " AND (n.title LIKE :search1 OR n.synopsis LIKE :search2)";
        }

        $this->db->query($countSql);
        if ($genre) {
            $this->db->bind(':genre', $genre);
        }
        if ($search) {
            $this->db->bind(':search1', '%' . $search . '%');
            $this->db->bind(':search2', '%' . $search . '%');
        }
        $countRow = $this->db->single();
        $total = (int)($countRow['total'] ?? 0);
        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        // 2. Ambil data baris untuk halaman ini
        $dataSql = "
            SELECT n.*, u.name as author_name,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id AND c.status = 'published') as total_chapters
            FROM novels n
            JOIN users u ON n.user_id = u.id
            WHERE n.status = 'published'
        ";
        if ($genre) {
            $dataSql .= " AND LOWER(n.genre) = LOWER(:genre)";
        }
        if ($search) {
            $dataSql .= " AND (n.title LIKE :search1 OR n.synopsis LIKE :search2)";
        }
        $dataSql .= " ORDER BY n.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($dataSql);
        if ($genre) {
            $this->db->bind(':genre', $genre);
        }
        if ($search) {
            $this->db->bind(':search1', '%' . $search . '%');
            $this->db->bind(':search2', '%' . $search . '%');
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
     * Mengambil satu novel lengkap beserta informasi penulisnya
     *
     * @param int $id
     * @return array|null
     */
    public function getWithAuthor(int $id): ?array {
        $this->db->query("
            SELECT n.*, u.name as author_name, u.email as author_email,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id AND c.status = 'published') as total_chapters,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id) as all_chapters_count
            FROM novels n
            JOIN users u ON n.user_id = u.id
            WHERE n.id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $result = $this->db->single();
        return $result ?: null;
    }

    /**
     * Mengambil seluruh novel milik penulis tertentu
     *
     * @param int $userId
     * @param string|null $status 'draft', 'published', atau null untuk semua
     * @return array
     */
    public function getByAuthor(int $userId, ?string $status = null): array {
        $sql = "
            SELECT n.*, u.name as author_name,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id) as total_chapters
            FROM novels n
            JOIN users u ON n.user_id = u.id
            WHERE n.user_id = :user_id
        ";
        if ($status) {
            $sql .= " AND n.status = :status";
        }
        $sql .= " ORDER BY n.updated_at DESC";

        $this->db->query($sql);
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->resultSet();
    }

    /**
     * Mengambil statistik dashboard ringkas untuk satu penulis
     *
     * @param int $userId
     * @return array
     */
    public function getAuthorDashboardStats(int $userId): array {
        $this->db->query("
            SELECT 
                COUNT(*) as total_novels,
                COALESCE(SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END), 0) as published_novels,
                COALESCE(SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END), 0) as draft_novels,
                COALESCE((
                    SELECT COUNT(*) 
                    FROM chapters c 
                    JOIN novels n2 ON c.novel_id = n2.id 
                    WHERE n2.user_id = :user_id_chap
                ), 0) as total_chapters
            FROM novels 
            WHERE user_id = :user_id
        ");
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        $this->db->bind(':user_id_chap', $userId, PDO::PARAM_INT);
        $row = $this->db->single();

        return [
            'total_novels' => (int)($row['total_novels'] ?? 0),
            'published_novels' => (int)($row['published_novels'] ?? 0),
            'draft_novels' => (int)($row['draft_novels'] ?? 0),
            'total_chapters' => (int)($row['total_chapters'] ?? 0)
        ];
    }

    /**
     * Mengambil statistik global seluruh novel di database (untuk Admin & Chatbot)
     *
     * @return array
     */
    public function getGlobalStats(): array {
        $this->db->query("
            SELECT 
                COUNT(*) as total_novels,
                COALESCE(SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END), 0) as published_novels,
                COALESCE(SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END), 0) as draft_novels
            FROM novels
        ");
        $row = $this->db->single();
        return [
            'total_novels' => (int)($row['total_novels'] ?? 0),
            'published_novels' => (int)($row['published_novels'] ?? 0),
            'draft_novels' => (int)($row['draft_novels'] ?? 0),
        ];
    }

    /**
     * Mengambil daftar novel terbaru milik penulis
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentByAuthor(int $userId, int $limit = 6): array {
        $this->db->query("
            SELECT n.*,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id) as total_chapters,
                   (SELECT MAX(created_at) FROM chapters c WHERE c.novel_id = n.id) as latest_chapter_date
            FROM novels n
            WHERE n.user_id = :user_id
            ORDER BY n.updated_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Mengambil daftar bab dari novel ini (Relationship 1 to Many)
     *
     * @param int $novelId
     * @param string|null $status
     * @return array
     */
    public function getChapters(int $novelId, ?string $status = null): array {
        $sql = "SELECT * FROM chapters WHERE novel_id = :novel_id";
        if ($status) {
            $sql .= " AND status = :status";
        }
        $sql .= " ORDER BY chapter_order ASC, created_at ASC";

        $this->db->query($sql);
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->resultSet();
    }

    /**
     * Mengambil seluruh novel beserta data penulisnya (untuk Admin Panel)
     *
     * @param int $limit
     * @return array
     */
    public function getAllWithAuthor(int $limit = 50): array {
        $this->db->query("
            SELECT n.*, u.name as author_name, u.email as author_email,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id) as total_chapters
            FROM novels n
            JOIN users u ON n.user_id = u.id
            ORDER BY n.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /**
     * Mengubah status publikasi novel (draft <-> published)
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function changeStatus(int $id, string $status): bool {
        if (!in_array($status, ['draft', 'published'])) {
            return false;
        }
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Mengubah status novel antara draft dan published milik penulis (Toggle)
     *
     * @param int $id
     * @param int $userId
     * @return string|null Status baru ('published' atau 'draft')
     */
    public function toggleStatus(int $id, int $userId): ?string {
        $novel = $this->findAuthorNovel($id, $userId);
        if (!$novel) {
            return null;
        }

        $newStatus = ($novel['status'] === 'published') ? 'draft' : 'published';
        $updated = $this->update($id, ['status' => $newStatus]);
        return $updated ? $newStatus : null;
    }

    /**
     * Mengambil satu novel milik penulis tertentu (verifikasi kepemilikan)
     *
     * @param int $id
     * @param int $userId
     * @return array|null
     */
    public function findAuthorNovel(int $id, int $userId): ?array {
        $this->db->query("
            SELECT n.*, u.name as author_name,
                   (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id) as total_chapters
            FROM novels n
            JOIN users u ON n.user_id = u.id
            WHERE n.id = :id AND n.user_id = :user_id
            LIMIT 1
        ");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        $result = $this->db->single();
        return $result ?: null;
    }

    /**
     * Membuat novel baru
     *
     * @param array $data ['user_id', 'title', 'synopsis', 'genre', 'cover', 'status']
     * @return string ID novel baru
     */
    public function createNovel(array $data): string {
        return $this->create($data);
    }

    /**
     * Memperbarui data novel milik penulis (dengan proteksi user_id)
     *
     * @param int $id
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateNovel(int $id, int $userId, array $data): bool {
        // Cek kepemilikan
        $novel = $this->findAuthorNovel($id, $userId);
        if (!$novel) {
            return false;
        }

        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "`{$column}` = :{$column}";
        }
        $setString = implode(', ', $setClauses);

        $sql = "UPDATE novels SET {$setString} WHERE id = :id AND user_id = :user_id";
        $this->db->query($sql);

        foreach ($data as $column => $value) {
            $this->db->bind(':' . $column, $value);
        }
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Menghapus novel milik penulis (dengan proteksi user_id)
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function deleteNovel(int $id, int $userId): bool {
        $novel = $this->findAuthorNovel($id, $userId);
        if (!$novel) {
            return false;
        }

        $this->db->query("DELETE FROM novels WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);

        return $this->db->execute();
    }

    /**
     * Mengambil daftar genre unik beserta jumlah novelnya
     *
     * @return array
     */
    public function getGenresSummary(): array {
        $this->db->query("
            SELECT genre, COUNT(*) as count
            FROM novels
            WHERE status = 'published'
            GROUP BY genre
            ORDER BY count DESC
        ");
        return $this->db->resultSet();
    }

    /**
     * Menghapus novel oleh Administrator (Moderasi Konten Melanggar Aturan)
     *
     * @param int $id
     * @return bool
     */
    public function adminDeleteNovel(int $id): bool {
        $novel = $this->find($id);
        if (!$novel) {
            return false;
        }

        // Hapus file cover fisik dari server jika ada
        if (!empty($novel['cover']) && str_starts_with($novel['cover'], 'uploads/covers/')) {
            $fullPath = PUBLIC_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $novel['cover']);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        return $this->delete($id);
    }
}
