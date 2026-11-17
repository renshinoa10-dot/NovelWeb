<?php
/**
 * karyaNovel - Chapter Model
 * Mengelola data bab novel, urutan penomoran (chapter_order), dan navigasi pembaca
 */

class Chapter extends Model {
    protected string $table = 'chapters';

    /**
     * Mengambil daftar bab berdasarkan ID novel
     *
     * @param int $novelId
     * @param string|null $status
     * @return array
     */
    public function getByNovel(int $novelId, ?string $status = null): array {
        $sql = "SELECT * FROM chapters WHERE novel_id = :novel_id";
        if ($status) {
            $sql .= " AND status = :status";
}
        $sql .= " ORDER BY chapter_order ASC, id ASC";

        $this->db->query($sql);
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        if ($status) {
            $this->db->bind(':status', $status);
}

        return $this->db->resultSet();
}

    /**
     * Mendapatkan nomor urutan bab selanjutnya secara otomatis
     *
     * @param int $novelId
     * @return int
     */
    public function getNextOrder(int $novelId): int {
        $this->db->query("SELECT MAX(chapter_order) as max_order FROM chapters WHERE novel_id = :novel_id");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $result = $this->db->single();
        return ((int)($result['max_order'] ?? 0)) + 1;
}

    /**
     * Mengambil satu bab beserta data novel induknya
     *
     * @param int $chapterId
     * @return array|null
     */
    public function getWithNovel(int $chapterId): ?array {
        $this->db->query("
            SELECT c.*, n.title as novel_title, n.user_id as author_id, n.genre as novel_genre,
                   n.status as novel_status, u.name as author_name
            FROM chapters c
            JOIN novels n ON c.novel_id = n.id
            JOIN users u ON n.user_id = u.id
            WHERE c.id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
        $result = $this->db->single();
        return $result ?: null;
}

    /**
     * Mengambil satu bab di dalam novel tertentu berdasarkan nomor urutan (chapter_order) atau ID bab
     * Sangat penting untuk routing /novels/{id
}/read/{chapter
}
     *
     * @param int $novelId
     * @param int|string $chapterIdentifier
     * @return array|null
     */
    public function findInNovel(int $novelId, $chapterIdentifier): ?array {
        // Cek berdasarkan nomor urut bab (chapter_order) terlebih dahulu (e.g. /novels/1/read/1 -> Bab 1)
        $this->db->query("
            SELECT c.*, n.title as novel_title, n.user_id as author_id, n.genre as novel_genre,
                   n.status as novel_status, n.cover as novel_cover, n.synopsis as novel_synopsis,
                   u.name as author_name
            FROM chapters c
            JOIN novels n ON c.novel_id = n.id
            JOIN users u ON n.user_id = u.id
            WHERE c.novel_id = :novel_id AND c.chapter_order = :order
            LIMIT 1
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $this->db->bind(':order', (int)$chapterIdentifier, PDO::PARAM_INT);
        $result = $this->db->single();
        if ($result) {
            return $result;
}

        // Jika tidak ditemukan berdasarkan chapter_order, cari berdasarkan ID bab unik (e.g. /novels/1/read/15)
        $this->db->query("
            SELECT c.*, n.title as novel_title, n.user_id as author_id, n.genre as novel_genre,
                   n.status as novel_status, n.cover as novel_cover, n.synopsis as novel_synopsis,
                   u.name as author_name
            FROM chapters c
            JOIN novels n ON c.novel_id = n.id
            JOIN users u ON n.user_id = u.id
            WHERE c.novel_id = :novel_id AND c.id = :id
            LIMIT 1
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $this->db->bind(':id', (int)$chapterIdentifier, PDO::PARAM_INT);
        $result = $this->db->single();
        return $result ?: null;
}

    /**
     * Mencari bab berdasarkan nomor urutan (chapter_order) dalam novel tertentu
     *
     * @param int $novelId
     * @param int $order
     * @return array|null
     */
    public function findByOrder(int $novelId, int $order): ?array {
        $this->db->query("
            SELECT c.*, n.title as novel_title, n.user_id as author_id, n.genre as novel_genre,
                   n.status as novel_status, n.cover as novel_cover, n.synopsis as novel_synopsis,
                   u.name as author_name
            FROM chapters c
            JOIN novels n ON c.novel_id = n.id
            JOIN users u ON n.user_id = u.id
            WHERE c.novel_id = :novel_id AND c.chapter_order = :order
            LIMIT 1
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $this->db->bind(':order', $order, PDO::PARAM_INT);
        $result = $this->db->single();
        return $result ?: null;
}

    /**
     * Mendapatkan bab sebelumnya (Previous) dan bab selanjutnya (Next) untuk navigasi pembaca
     *
     * @param int $novelId
     * @param int $currentOrder
     * @param bool $onlyPublished
     * @return array ['prev' => array|null, 'next' => array|null]
     */
    public function getAdjacentChapters(int $novelId, int $currentOrder, bool $onlyPublished = true): array {
        $statusClause = $onlyPublished ? " AND status = 'published'" : "";

        // Prev chapter
        $this->db->query("
            SELECT id, title, chapter_order
            FROM chapters
            WHERE novel_id = :novel_id AND chapter_order < :current_order {$statusClause
}
            ORDER BY chapter_order DESC
            LIMIT 1
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $this->db->bind(':current_order', $currentOrder, PDO::PARAM_INT);
        $prev = $this->db->single();

        // Next chapter
        $this->db->query("
            SELECT id, title, chapter_order
            FROM chapters
            WHERE novel_id = :novel_id AND chapter_order > :current_order {$statusClause
}
            ORDER BY chapter_order ASC
            LIMIT 1
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $this->db->bind(':current_order', $currentOrder, PDO::PARAM_INT);
        $next = $this->db->single();

        return [
            'prev' => $prev ?: null,
            'next' => $next ?: null
        ];
}

    /**
     * Memperbarui susunan urutan bab (Reordering)
     *
     * @param int $novelId
     * @param array $orderedChapterIds Array ID bab berurutan [id1, id2, id3, ...]
     * @return bool
     */
    public function reorder(int $novelId, array $orderedChapterIds): bool {
        $order = 1;
        foreach ($orderedChapterIds as $chapterId) {
            $this->db->query("
                UPDATE chapters
                SET chapter_order = :order
                WHERE id = :id AND novel_id = :novel_id
            ");
            $this->db->bind(':order', $order, PDO::PARAM_INT);
            $this->db->bind(':id', (int)$chapterId, PDO::PARAM_INT);
            $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
            $this->db->execute();
            $order++;
}
        return true;
}

    /**
     * Mengambil bab terbaru yang ditulis oleh penulis tertentu
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentByAuthor(int $userId, int $limit = 5): array {
        $this->db->query("
            SELECT c.*, n.title as novel_title, n.genre as novel_genre
            FROM chapters c
            JOIN novels n ON c.novel_id = n.id
            WHERE n.user_id = :user_id
            ORDER BY c.created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
}

    /**
     * Mengambil data satu bab dengan verifikasi kepemilikan penulis (Otorisasi)
     *
     * @param int $chapterId
     * @param int $userId
     * @return array|null
     */
    public function findAuthorChapter(int $chapterId, int $userId): ?array {
        $this->db->query("
            SELECT c.*, n.title as novel_title, n.user_id as author_id, n.status as novel_status
            FROM chapters c
            JOIN novels n ON c.novel_id = n.id
            WHERE c.id = :id AND n.user_id = :user_id
            LIMIT 1
        ");
        $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
        $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        $result = $this->db->single();
        return $result ?: null;
}

    /**
     * Membuat bab baru
     *
     * @param array $data ['novel_id', 'title', 'content', 'chapter_order', 'status']
     * @return string ID bab baru
     */
    public function createChapter(array $data): string {
        if (empty($data['chapter_order'])) {
            $data['chapter_order'] = $this->getNextOrder((int)$data['novel_id']);
}

        $newId = $this->create([
            'novel_id' => $data['novel_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'chapter_order' => $data['chapter_order'],
            'status' => $data['status'] ?? 'draft'
        ]);

        $this->resequenceOrders((int)$data['novel_id']);
        return $newId;
}

    /**
     * Memperbarui data bab milik penulis (dengan proteksi user_id)
     *
     * @param int $chapterId
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateChapter(int $chapterId, int $userId, array $data): bool {
        $chapter = $this->findAuthorChapter($chapterId, $userId);
        if (!$chapter) {
            return false;
}

        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "`{$column
}` = :{$column
}";
}
        $setString = implode(', ', $setClauses);

        $sql = "UPDATE chapters SET {$setString
} WHERE id = :id";
        $this->db->query($sql);

        foreach ($data as $column => $value) {
            $this->db->bind(':' . $column, $value);
}
        $this->db->bind(':id', $chapterId, PDO::PARAM_INT);

        $executed = $this->db->execute();
        if ($executed && isset($data['chapter_order'])) {
            $this->resequenceOrders((int)$chapter['novel_id']);
}
        return $executed;
}

    /**
     * Menghapus bab milik penulis dan merapikan urutan bab yang tersisa (Resequence)
     *
     * @param int $chapterId
     * @param int $userId
     * @return bool
     */
    public function deleteChapter(int $chapterId, int $userId): bool {
        $chapter = $this->findAuthorChapter($chapterId, $userId);
        if (!$chapter) {
            return false;
}

        $novelId = (int)$chapter['novel_id'];

        $this->db->query("DELETE FROM chapters WHERE id = :id");
        $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
        $deleted = $this->db->execute();

        if ($deleted) {
            // Urutan bab lain harus tetap konsisten dan valid setelah penghapusan
            $this->resequenceOrders($novelId);
}

        return $deleted;
}

    /**
     * Memastikan konsistensi penomoran bab tanpa ada angka lompat (1, 2, 3, ...)
     *
     * @param int $novelId
     * @return void
     */
    public function resequenceOrders(int $novelId): void {
        $this->db->query("
            SELECT id FROM chapters
            WHERE novel_id = :novel_id
            ORDER BY chapter_order ASC, id ASC
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $chapters = $this->db->resultSet();

        $order = 1;
        foreach ($chapters as $ch) {
            $this->db->query("UPDATE chapters SET chapter_order = :order WHERE id = :id");
            $this->db->bind(':order', $order, PDO::PARAM_INT);
            $this->db->bind(':id', (int)$ch['id'], PDO::PARAM_INT);
            $this->db->execute();
            $order++;
}
}

    /**
     * Menggeser urutan bab ke atas (Move Up)
     *
     * @param int $chapterId
     * @param int $userId
     * @return bool
     */
    public function moveUp(int $chapterId, int $userId): bool {
        $current = $this->findAuthorChapter($chapterId, $userId);
        if (!$current) {
            return false;
}

        $novelId = (int)$current['novel_id'];
        $currentOrder = (int)$current['chapter_order'];

        if ($currentOrder <= 1) {
            return true; // Sudah di posisi paling atas
}

        // Cari bab persis di atasnya
        $this->db->query("
            SELECT id, chapter_order FROM chapters
            WHERE novel_id = :novel_id AND chapter_order < :current_order
            ORDER BY chapter_order DESC, id DESC
            LIMIT 1
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $this->db->bind(':current_order', $currentOrder, PDO::PARAM_INT);
        $prev = $this->db->single();

        if ($prev) {
            // Tukar posisi (swap order)
            $this->db->query("UPDATE chapters SET chapter_order = :target_order WHERE id = :id");
            $this->db->bind(':target_order', $prev['chapter_order'], PDO::PARAM_INT);
            $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
            $this->db->execute();

            $this->db->query("UPDATE chapters SET chapter_order = :target_order WHERE id = :id");
            $this->db->bind(':target_order', $currentOrder, PDO::PARAM_INT);
            $this->db->bind(':id', (int)$prev['id'], PDO::PARAM_INT);
            $this->db->execute();

            $this->resequenceOrders($novelId);
}

        return true;
}

    /**
     * Menggeser urutan bab ke bawah (Move Down)
     *
     * @param int $chapterId
     * @param int $userId
     * @return bool
     */
    public function moveDown(int $chapterId, int $userId): bool {
        $current = $this->findAuthorChapter($chapterId, $userId);
        if (!$current) {
            return false;
}

        $novelId = (int)$current['novel_id'];
        $currentOrder = (int)$current['chapter_order'];

        // Cari bab persis di bawahnya
        $this->db->query("
            SELECT id, chapter_order FROM chapters
            WHERE novel_id = :novel_id AND chapter_order > :current_order
            ORDER BY chapter_order ASC, id ASC
            LIMIT 1
        ");
        $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
        $this->db->bind(':current_order', $currentOrder, PDO::PARAM_INT);
        $next = $this->db->single();

        if ($next) {
            // Tukar posisi (swap order)
            $this->db->query("UPDATE chapters SET chapter_order = :target_order WHERE id = :id");
            $this->db->bind(':target_order', $next['chapter_order'], PDO::PARAM_INT);
            $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
            $this->db->execute();

            $this->db->query("UPDATE chapters SET chapter_order = :target_order WHERE id = :id");
            $this->db->bind(':target_order', $currentOrder, PDO::PARAM_INT);
            $this->db->bind(':id', (int)$next['id'], PDO::PARAM_INT);
            $this->db->execute();

            $this->resequenceOrders($novelId);
}

        return true;
}

    /**
     * Mengubah status bab antara draft dan published
     *
     * @param int $chapterId
     * @param int $userId
     * @return string|null Status baru ('published' atau 'draft')
     */
    public function toggleStatus(int $chapterId, int $userId): ?string {
        $chapter = $this->findAuthorChapter($chapterId, $userId);
        if (!$chapter) {
            return null;
}

        $newStatus = ($chapter['status'] === 'published') ? 'draft' : 'published';
        $this->db->query("UPDATE chapters SET status = :status WHERE id = :id");
        $this->db->bind(':status', $newStatus);
        $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
        $this->db->execute();

        return $newStatus;
}

    /**
     * Mengambil daftar seluruh bab beserta judul novel dan nama penulisnya untuk panel admin
     *
     * @param int|null $novelId
     * @param int $limit
     * @return array
     */
    public function getAllWithNovelAndAuthor(?int $novelId = null, int $limit = 100): array {
        $sql = "
            SELECT c.*, n.title as novel_title, n.status as novel_status, u.id as author_id, u.name as author_name, u.email as author_email
            FROM chapters c
            JOIN novels n ON c.novel_id = n.id
            JOIN users u ON n.user_id = u.id
        ";
        if ($novelId !== null) {
            $sql .= " WHERE c.novel_id = :novel_id";
}
        $sql .= " ORDER BY c.created_at DESC LIMIT :limit";

        $this->db->query($sql);
        if ($novelId !== null) {
            $this->db->bind(':novel_id', $novelId, PDO::PARAM_INT);
}
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
}

    /**
     * Mengubah status publikasi bab oleh Administrator
     *
     * @param int $chapterId
     * @return string|null Status baru ('published' atau 'draft')
     */
    public function adminToggleStatus(int $chapterId): ?string {
        $chapter = $this->find($chapterId);
        if (!$chapter) {
            return null;
}

        $newStatus = ($chapter['status'] === 'published') ? 'draft' : 'published';
        $this->db->query("UPDATE chapters SET status = :status WHERE id = :id");
        $this->db->bind(':status', $newStatus);
        $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
        $this->db->execute();

        return $newStatus;
}

    /**
     * Menghapus bab oleh Administrator (Moderasi Konten)
     *
     * @param int $chapterId
     * @return bool
     */
    public function adminDeleteChapter(int $chapterId): bool {
        $chapter = $this->find($chapterId);
        if (!$chapter) {
            return false;
}

        $novelId = (int)$chapter['novel_id'];

        $this->db->query("DELETE FROM chapters WHERE id = :id");
        $this->db->bind(':id', $chapterId, PDO::PARAM_INT);
        $deleted = $this->db->execute();

        if ($deleted) {
            $this->resequenceOrders($novelId);
}

        return $deleted;
}


    /**
     * Mengambil statistik global seluruh bab di database
     *
     * @return array
     */
    public function getGlobalStats(): array {
        $this->db->query("
            SELECT 
                COUNT(*) as total_chapters,
                COALESCE(SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END), 0) as published_chapters,
                COALESCE(SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END), 0) as draft_chapters
            FROM chapters
        ");
        $row = $this->db->single();
        return [
            'total_chapters' => (int)($row['total_chapters'] ?? 0),
            'published_chapters' => (int)($row['published_chapters'] ?? 0),
            'draft_chapters' => (int)($row['draft_chapters'] ?? 0),
        ];
    }

}