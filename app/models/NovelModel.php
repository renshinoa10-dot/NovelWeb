<?php
/**
 * karyaNovel - Novel Model
 * Mengelola data novel dan bab
 */

class NovelModel extends Model {
    /**
     * Mengambil novel yang telah dipublikasikan untuk halaman beranda / eksplorasi
     *
     * @param int $limit
     * @return array
     */
    public function getPublishedNovels(int $limit = 6): array {
        if (!$this->isDbConnected()) {
            return $this->getMockNovels();
        }

        try {
            $this->db->query("
                SELECT n.*, u.name as author_name, u.avatar as author_avatar,
                       (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id AND c.status = 'published') as total_chapters
                FROM novels n
                JOIN users u ON n.user_id = u.id
                WHERE n.status = 'published'
                ORDER BY n.created_at DESC
                LIMIT :limit
            ");
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            return $this->db->resultSet();
        } catch (Exception $e) {
            return $this->getMockNovels();
        }
    }

    /**
     * Mengambil novel unggulan / terpopuler
     */
    public function getFeaturedNovels(int $limit = 3): array {
        if (!$this->isDbConnected()) {
            return array_slice($this->getMockNovels(), 0, $limit);
        }

        try {
            $this->db->query("
                SELECT n.*, u.name as author_name, u.avatar as author_avatar,
                       (SELECT COUNT(*) FROM chapters c WHERE c.novel_id = n.id AND c.status = 'published') as total_chapters
                FROM novels n
                JOIN users u ON n.user_id = u.id
                WHERE n.status = 'published' AND n.is_featured = 1
                ORDER BY n.views_count DESC
                LIMIT :limit
            ");
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            $results = $this->db->resultSet();
            return !empty($results) ? $results : $this->getPublishedNovels($limit);
        } catch (Exception $e) {
            return array_slice($this->getMockNovels(), 0, $limit);
        }
    }

    /**
     * Fallback mock data jika database belum dimigrasikan
     */
    public function getMockNovels(): array {
        return [
            [
                'id' => 1,
                'title' => 'Lembah Bintang: Menembus Horizon',
                'slug' => 'lembah-bintang-menembus-horizon',
                'synopsis' => 'Di dunia masa depan di mana ingatan manusia dapat diarsipkan ke dalam kristal kuantum, seorang penjelajah menemukan kristal terlarang yang menyimpan rahasia penciptaan galaksi.',
                'cover_image' => 'public/images/novel-cover-1.svg',
                'genre' => 'Sci-Fi Fantasy',
                'author_name' => 'Ahmad Rian',
                'views_count' => 14200,
                'likes_count' => 3890,
                'total_chapters' => 28,
                'status' => 'published',
                'rating' => 4.9,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'title' => 'Detak Jantung di Ujung Senja',
                'slug' => 'detak-jantung-di-ujung-senja',
                'synopsis' => 'Pertemuan tak sengaja antara seorang musisi jalanan di Kyoto dan seorang arsitek muda yang kehilangan gairah hidupnya melahirkan simfoni cinta yang tak terduga.',
                'cover_image' => 'public/images/novel-cover-2.svg',
                'genre' => 'Romance Drama',
                'author_name' => 'Clara Sinta',
                'views_count' => 9850,
                'likes_count' => 2410,
                'total_chapters' => 16,
                'status' => 'published',
                'rating' => 4.8,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'id' => 3,
                'title' => 'Legenda Pedang Petir & Malam Abadi',
                'slug' => 'legenda-pedang-petir-malam-abadi',
                'synopsis' => 'Kisah epik persilatan nusantara abad pertengahan. Seorang pendekar muda bertualang menguak misteri hilangnya perguruan pedang petir dan ramalan malam abadi.',
                'cover_image' => 'public/images/novel-cover-3.svg',
                'genre' => 'Action Wuxia',
                'author_name' => 'Bayu Wicaksono',
                'views_count' => 22300,
                'likes_count' => 5120,
                'total_chapters' => 45,
                'status' => 'published',
                'rating' => 4.9,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'id' => 4,
                'title' => 'Misteri Paviliun Nomor 13',
                'slug' => 'misteri-paviliun-nomor-13',
                'synopsis' => 'Seorang detektif swasta disewa menyelidiki kematian misterius pewaris tunggal konglomerat di dalam kamar paviliun yang terkunci rapat dari dalam.',
                'cover_image' => 'public/images/novel-cover-4.svg',
                'genre' => 'Mystery Thriller',
                'author_name' => 'Maya Danastri',
                'views_count' => 11400,
                'likes_count' => 1980,
                'total_chapters' => 20,
                'status' => 'published',
                'rating' => 4.7,
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days'))
            ]
        ];
    }
}
