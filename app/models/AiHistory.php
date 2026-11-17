<?php
/**
 * karyaNovel - AiHistory Model
 * Mengelola riwayat prompt & respon dari AI Writing Assistant
 */

class AiHistory extends Model {
    protected string $table = 'ai_histories';

    /**
     * Menyimpan log interaksi prompt AI baru
     *
     * @param int $userId
     * @param int|null $chapterId
     * @param string $prompt
     * @param string $response
     * @return string ID log baru
     */
    public function log(int $userId, ?int $chapterId, string $prompt, string $response): string {
        return $this->create([
            'user_id' => $userId,
            'chapter_id' => $chapterId,
            'prompt' => $prompt,
            'response' => $response
        ]);
    }

    /**
     * Mengambil seluruh riwayat AI milik pengguna tertentu
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getByUser(int $userId, int $limit = 20): array {
        $this->db->query("
            SELECT a.*, c.title as chapter_title, n.id as novel_id, n.title as novel_title
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

    /**
     * Mengambil riwayat AI yang terkait dengan bab naskah tertentu
     *
     * @param int $chapterId
     * @param int|null $userId
     * @return array
     */
    public function getByChapter(int $chapterId, ?int $userId = null): array {
        $sql = "SELECT * FROM ai_histories WHERE chapter_id = :chapter_id";
        if ($userId !== null) {
            $sql .= " AND user_id = :user_id";
        }
        $sql .= " ORDER BY created_at DESC";

        $this->db->query($sql);
        $this->db->bind(':chapter_id', $chapterId, PDO::PARAM_INT);
        if ($userId !== null) {
            $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
        }
        return $this->db->resultSet();
    }
}

