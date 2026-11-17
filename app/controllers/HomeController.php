<?php
/**
 * karyaNovel - HomeController
 * Mengatur halaman landing/beranda utama karyaNovel
 */

class HomeController extends Controller {
    private Novel $novelModel;
    private User $userModel;
    private Chapter $chapterModel;

    public function __construct() {
        $this->novelModel = $this->model('Novel');
        $this->userModel = $this->model('User');
        $this->chapterModel = $this->model('Chapter');
    }

    /**
     * Halaman Landing / Beranda
     */
    public function index(): void {
        // Gatekeeper: Pengguna wajib login / register sebelum masuk ke web karyaNovel
        if (!Session::isLoggedIn()) {
            set_flash('Silakan masuk atau daftar terlebih dahulu untuk mengakses karyaNovel.', 'info');
            redirect('auth/login');
            return;
        }

        // Cek apakah akun memerlukan verifikasi OTP WhatsApp
        $currentUser = Session::user();
        if ($currentUser && isset($currentUser['is_verified']) && (int)$currentUser['is_verified'] === 0) {
            redirect('auth/verify_otp');
            return;
        }

        // Ambil data novel yang telah dipublikasikan dari database
        $publishedNovels = $this->novelModel->getPublished(6);

        // Ambil ringkasan genre dari database
        $genreSummary = $this->novelModel->getGenresSummary();

        // Hitung total metrik statistik
        $totalNovels = $this->novelModel->count("WHERE status = 'published'");
        $totalUsers = $this->userModel->count();
        $totalChapters = $this->chapterModel->count("WHERE status = 'published'");

        $data = [
            'pageTitle' => 'karyaNovel - Sistem Penulisan Novel & AI Writing Assistant',
            'metaDescription' => 'Tulis, kembangkan, dan publikasikan novelmu lebih cepat dengan bantuan AI Writing Assistant tercanggih di karyaNovel.',
            'featuredNovels' => $publishedNovels,
            'genreSummary' => $genreSummary,
            'genres' => [
                ['name' => 'Fantasi', 'icon' => 'bi-stars', 'count' => '3.4k Novel', 'color' => '#8b5cf6'],
                ['name' => 'Romansa', 'icon' => 'bi-heart-fill', 'count' => '5.1k Novel', 'color' => '#ec4899'],
                ['name' => 'Sci-Fi', 'icon' => 'bi-rocket-takeoff-fill', 'count' => '1.8k Novel', 'color' => '#3b82f6'],
                ['name' => 'Misteri', 'icon' => 'bi-search', 'count' => '2.2k Novel', 'color' => '#10b981'],
                ['name' => 'Horor', 'icon' => 'bi-moon-stars-fill', 'count' => '1.5k Novel', 'color' => '#ef4444'],
                ['name' => 'Aksi & Silat', 'icon' => 'bi-lightning-charge-fill', 'count' => '2.9k Novel', 'color' => '#f59e0b'],
            ],
            'aiFeatures' => [
                [
                    'icon' => 'bi-magic',
                    'title' => 'Story Continuation & Plot Generator',
                    'badge' => 'AI Cerdas',
                    'description' => 'Buntu ide di tengah bab? AI menganalisis konteks cerita Anda dan memberikan opsi kelanjutan adegan yang koheren dan menegangkan.'
                ],
                [
                    'icon' => 'bi-person-badge',
                    'title' => 'Character & Dialogue Enhancer',
                    'badge' => 'Karakter Hidup',
                    'description' => 'Bangun dialog yang realistis dan kepribadian karakter yang konsisten dengan saran intonasi serta dinamika emosi otomatis.'
                ],
                [
                    'icon' => 'bi-pencil-square',
                    'title' => 'Grammar & Literary Polish',
                    'badge' => 'Ejaan & Diksi',
                    'description' => 'Perkaya kosakata, variasi majas, dan tata bahasa PUEBI tanpa merusak ciri khas gaya kepenulisan orisinal Anda.'
                ],
                [
                    'icon' => 'bi-diagram-3',
                    'title' => 'World Building & Outline Builder',
                    'badge' => 'Struktur Rapi',
                    'description' => 'Susun babak cerita, catatan dunia fiksi (lore), dan alur bab secara terstruktur dari awal prolog hingga epilog memikat.'
                ]
            ],
            'stats' => [
                ['value' => ($totalUsers > 0 ? number_format($totalUsers) . '+' : '15k+'), 'label' => 'Penulis Terdaftar', 'icon' => 'bi-people-fill'],
                ['value' => ($totalNovels > 0 ? number_format($totalNovels) . '+' : '42k+'), 'label' => 'Novel Dipublikasikan', 'icon' => 'bi-journal-bookmark-fill'],
                ['value' => ($totalChapters > 0 ? number_format($totalChapters) . '+' : '2.8M+'), 'label' => 'Bab Naskah Aktif', 'icon' => 'bi-eye-fill'],
                ['value' => '99.4%', 'label' => 'Kepuasan AI Assistant', 'icon' => 'bi-sparkles']
            ]
        ];

        $this->view('home/index', $data, 'main');
    }
}
