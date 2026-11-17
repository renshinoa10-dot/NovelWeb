<?php
/**
 * karyaNovel - DashboardController
 * Mengatur tampilan dashboard penulis untuk mengelola novel dan bab miliknya sendiri
 */

class DashboardController extends Controller {
    private Novel $novelModel;
    private Chapter $chapterModel;
    private AiHistory $aiHistoryModel;

    public function __construct() {
        // Proteksi: Hanya user yang telah login yang dapat mengakses Dashboard Penulis
        $this->requireAuth();

        $this->novelModel = $this->model('Novel');
        $this->chapterModel = $this->model('Chapter');
        $this->aiHistoryModel = $this->model('AiHistory');
    }

    /**
     * Tampilan Utama Dashboard Penulis
     */
    public function index(): void {
        // Anti-cache headers untuk memastikan browser selalu menyajikan statistik terbaru
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        // 1. Ambil statistik naskah terisolasi hanya untuk user yang sedang login
        $stats = $this->novelModel->getAuthorDashboardStats($userId);

        // 2. Ambil daftar novel terbaru milik penulis ini
        $myNovels = $this->novelModel->getRecentByAuthor($userId, 10);

        // 3. Ambil aktivitas bab naskah terbaru milik penulis ini
        $recentChapters = $this->chapterModel->getRecentByAuthor($userId, 5);

        // 4. Ambil riwayat interaksi AI Writing Assistant milik penulis ini
        $recentAiLogs = $this->aiHistoryModel->getByUser($userId, 5);

        // 5. Siapkan data untuk View (MVC: View hanya menerima variabel bersih)
        $data = [
            'pageTitle' => 'Dashboard Penulis - karyaNovel',
            'user' => $currentUser,
            'stats' => $stats,
            'novels' => $myNovels,
            'recentChapters' => $recentChapters,
            'recentAiLogs' => $recentAiLogs
        ];

        $this->view('dashboard/index', $data, 'main');
    }

    /**
     * Endpoint API JSON untuk Sinkronisasi Statistik Dashboard Penulis secara Real-Time
     * Route: GET /dashboard/stats_api
     */
    public function stats_api(): void {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];
        $stats = $this->novelModel->getAuthorDashboardStats($userId);

        $this->json([
            'success' => true,
            'stats' => $stats,
            'timestamp' => time()
        ]);
    }
}
