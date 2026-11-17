<?php
/**
 * karyaNovel - NovelController
 * Mengatur halaman jelajah novel publik, detail novel, dan daftar bab
 */

class NovelController extends Controller {
    private Novel $novelModel;
    private Chapter $chapterModel;

    public function __construct() {
        $this->novelModel = $this->model('Novel');
        $this->chapterModel = $this->model('Chapter');
    }

    /**
     * Halaman Jelajah / Katalog Novel Publik
     */
    public function index(): void {
        $genre = isset($_GET['genre']) ? trim(sanitize($_GET['genre'])) : null;
        $search = isset($_GET['q']) ? trim(sanitize($_GET['q'])) : null;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 9;

        $paginationData = $this->novelModel->getPublishedPaginated($page, $perPage, $genre, $search);
        $genresSummary = $this->novelModel->getGenresSummary();

        $data = [
            'pageTitle' => ($genre ? "Katalog Novel Genre {$genre}" : "Jelajah Seluruh Novel") . ' - karyaNovel',
            'novels' => $paginationData['data'],
            'pagination' => $paginationData,
            'currentGenre' => $genre,
            'searchQuery' => $search,
            'genresSummary' => $genresSummary,
            'isPublicView' => true,
            'user' => Session::user()
        ];

        $this->view('novel/index', $data, 'main');
    }

    /**
     * Halaman Detail Novel & Daftar Bab
     *
     * @param int|string $id
     */
    public function detail($id = null): void {
        if (!$id) {
            redirect('novel');
        }

        $novel = $this->novelModel->getWithAuthor((int)$id);
        $currentUser = Session::user();
        $isAuthor = ($currentUser && $novel && (int)$currentUser['id'] === (int)$novel['user_id']);

        if (!$novel || ($novel['status'] !== 'published' && !$isAuthor)) {
            $this->notFound('Novel yang Anda tuju belum dipublikasikan atau tidak tersedia.');
            return;
        }

        $chapters = $this->chapterModel->getByNovel((int)$id, 'published');

        $data = [
            'pageTitle' => sanitize($novel['title']) . ' - karyaNovel',
            'novel' => $novel,
            'chapters' => $chapters,
            'isAuthor' => $isAuthor,
            'user' => $currentUser
        ];

        $this->view('novel/detail', $data, 'main');
    }

    /**
     * Filter berdasarkan Genre
     *
     * @param string|null $genreName
     */
    public function genre($genreName = null): void {
        if ($genreName) {
            $_GET['genre'] = urldecode($genreName);
        }
        $this->index();
    }

    /**
     * Meneruskan akses pembuatan novel ke NovelsController
     */
    public function create(): void {
        redirect('novels/create');
    }

    /**
     * Meneruskan akses manajemen novel ke NovelsController
     */
    public function manage(): void {
        redirect('novels');
    }

    /**
     * Halaman Membaca Bab Novel (/novel/{id}/read/{chapter})
     *
     * @param int|string|null $novelId
     * @param int|string|null $chapterIdentifier
     */
    public function read($novelId = null, $chapterIdentifier = null): void {
        require_once APPROOT . '/controllers/NovelsController.php';
        $controller = new NovelsController();
        $controller->read($novelId, $chapterIdentifier);
    }
}
