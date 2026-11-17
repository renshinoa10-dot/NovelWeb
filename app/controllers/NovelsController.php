<?php
/**
 * karyaNovel - NovelsController
 * Mengatur pengelolaan CRUD novel oleh penulis (Daftar, Buat, Detail, Edit, Hapus)
 */

class NovelsController extends Controller {
    private Novel $novelModel;
    private Chapter $chapterModel;

    // Daftar Genre Standar karyaNovel
    private array $availableGenres = [
        'Fantasi', 'Romansa', 'Sci-Fi', 'Misteri', 'Horor',
        'Aksi & Silat', 'Petualangan', 'Drama', 'Komedi', 'Thriller'
    ];

    public function __construct() {
        // Model diinisialisasi untuk public maupun author
        $this->novelModel = $this->model('Novel');
        $this->chapterModel = $this->model('Chapter');
    }

    /**
     * Halaman Daftar Novel (/novels)
     * - Untuk Pembaca / Publik: Menampilkan katalog novel berstatus 'published' dengan pagination & filter
     * - Untuk Penulis: Menampilkan dashboard manajemen naskah internal penulis
     */
    public function index(): void {
        // Gatekeeper: Pengguna wajib login sebelum mengakses katalog naskah / novel
        if (!Session::isLoggedIn()) {
            set_flash('Silakan masuk atau daftar terlebih dahulu untuk mengakses novel karyaNovel.', 'info');
            redirect('auth/login');
            return;
        }

        $currentUser = Session::user();
        if ($currentUser && isset($currentUser['is_verified']) && (int)$currentUser['is_verified'] === 0) {
            redirect('auth/verify_otp');
            return;
        }

        // Mode Manajemen Novel Penulis (Hanya jika login dan tanpa filter publik eksplisit)
        $isAuthorManagement = ($currentUser && !isset($_GET['public']) && !isset($_GET['tab']) && !isset($_GET['genre']) && !isset($_GET['page']));

        if ($isAuthorManagement) {
            $userId = (int)$currentUser['id'];

            $statusFilter = isset($_GET['status']) ? trim(sanitize($_GET['status'])) : null;
            if (!in_array($statusFilter, ['draft', 'published'])) {
                $statusFilter = null;
            }

            $search = isset($_GET['q']) ? trim(sanitize($_GET['q'])) : null;

            // Ambil data novel milik penulis saat ini saja
            $myNovels = $this->novelModel->getByAuthor($userId, $statusFilter);

            // Jika ada pencarian di daftar internal
            if ($search) {
                $myNovels = array_filter($myNovels, function ($n) use ($search) {
                    return (stripos($n['title'], $search) !== false || stripos($n['synopsis'], $search) !== false);
                });
            }

            $stats = $this->novelModel->getAuthorDashboardStats($userId);

            $data = [
                'pageTitle' => 'Manajemen Novel Saya - karyaNovel',
                'user' => $currentUser,
                'novels' => $myNovels,
                'stats' => $stats,
                'currentStatus' => $statusFilter,
                'searchQuery' => $search
            ];

            $this->view('novels/index', $data, 'main');
            return;
        }

        // Halaman Jelajah / Katalog Novel Publik (/novels)
        // Hanya novel berstatus 'published' yang tampil
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 9;
        $genre = isset($_GET['genre']) ? trim(sanitize($_GET['genre'])) : null;
        $search = isset($_GET['q']) ? trim(sanitize($_GET['q'])) : null;

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
            'user' => $currentUser
        ];

        $this->view('novel/index', $data, 'main');
    }

    /**
     * Form & Proses Pembuatan Novel Baru (/novels/create)
     */
    public function create(): void {
        $this->requireAuth();

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        $errors = [];
        $oldInput = [
            'title' => '',
            'synopsis' => '',
            'genre' => '',
            'status' => 'draft'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) return;
            $title = trim(sanitize($_POST['title'] ?? ''));
            $synopsis = trim(sanitize($_POST['synopsis'] ?? ''));
            $genre = trim(sanitize($_POST['genre'] ?? ''));
            $status = trim(sanitize($_POST['status'] ?? 'draft'));

            $oldInput = [
                'title' => $title,
                'synopsis' => $synopsis,
                'genre' => $genre,
                'status' => $status
            ];

            // 1. Validasi Judul
            if (empty($title)) {
                $errors['title'] = 'Judul novel wajib diisi.';
            } elseif (mb_strlen($title) < 3 || mb_strlen($title) > 200) {
                $errors['title'] = 'Panjang judul harus antara 3 hingga 200 karakter.';
            }

            // 2. Validasi Sinopsis
            if (empty($synopsis)) {
                $errors['synopsis'] = 'Sinopsis naskah cerita wajib diisi.';
            } elseif (mb_strlen($synopsis) < 20) {
                $errors['synopsis'] = 'Sinopsis terlalu pendek (minimal 20 karakter).';
            } elseif (mb_strlen($synopsis) > 5000) {
                $errors['synopsis'] = 'Sinopsis maksimal 5000 karakter.';
            }

            // 3. Validasi Genre
            if (empty($genre)) {
                $errors['genre'] = 'Genre novel wajib dipilih.';
            }

            // 4. Validasi Status
            if (!in_array($status, ['draft', 'published'])) {
                $status = 'draft';
            }

            // 5. Validasi & Penanganan Upload Cover Gambar
            $coverPath = null;
            if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
                $coverUpload = $this->handleCoverUpload($_FILES['cover']);
                if ($coverUpload['success']) {
                    $coverPath = $coverUpload['path'];
                } else {
                    $errors['cover'] = $coverUpload['error'];
                }
            }

            // Jika tidak ada error, simpan novel ke database
            if (empty($errors)) {
                $newNovelId = $this->novelModel->createNovel([
                    'user_id' => $userId,
                    'title' => $title,
                    'synopsis' => $synopsis,
                    'genre' => $genre,
                    'cover' => $coverPath,
                    'status' => $status
                ]);

                if ($newNovelId) {
                    // Forward notifikasi WhatsApp jika novel langsung dipublikasikan
                    if ($status === 'published') {
                        try {
                            $fonnteService = new FonnteService();
                            $fonnteService->sendNewNovelNotification([
                                'id' => $newNovelId,
                                'title' => $title,
                                'synopsis' => $synopsis,
                                'genre' => $genre,
                                'cover' => $coverPath,
                                'status' => $status
                            ], $currentUser);
                        } catch (Throwable $e) {
                            error_log("Fonnte notification error on create novel: " . $e->getMessage());
                        }
                    }

                    set_flash("Novel '{$title}' berhasil dibuat! Anda dapat mulai menulis bab pertamanya.", 'success');
                    redirect('novels/' . $newNovelId);
                } else {
                    $errors['general'] = 'Gagal menyimpan novel ke database. Coba lagi.';
                    set_flash('Gagal menyimpan novel ke database. Coba lagi.', 'danger');
                }
            }
        }

        $data = [
            'pageTitle' => 'Buat Proyek Novel Baru - karyaNovel',
            'availableGenres' => $this->availableGenres,
            'errors' => $errors,
            'oldInput' => $oldInput
        ];

        $this->view('novels/create', $data, 'main');
    }

    /**
     * Halaman Detail Novel (/novels/{id})
     * - Jika status 'draft': Hanya penulis asli yang boleh melihat (detail naskah internal).
     * - Jika status 'published': Terbuka untuk umum (Cover, Judul, Penulis, Genre, Sinopsis, Daftar Bab).
     *
     * @param int|string $id
     */
    public function show($id = null): void {
        $this->detail($id);
    }

    public function detail($id = null): void {
        if (!Session::isLoggedIn()) {
            set_flash('Silakan masuk terlebih dahulu untuk melihat detail novel.', 'info');
            redirect('auth/login');
            return;
        }

        if (!$id) {
            redirect('novels');
        }

        $novelId = (int)$id;
        $novel = $this->novelModel->getWithAuthor($novelId);

        if (!$novel) {
            $this->notFound('Novel yang Anda tuju tidak ditemukan atau sudah dihapus.');
            return;
        }

        $currentUser = Session::user();
        $isAuthor = ($currentUser && (int)$currentUser['id'] === (int)$novel['user_id']);

        // Proteksi Otorisasi Naskah Draft:
        // Jika status novel masih draft, HANYA penulis yang boleh melihat
        if ($novel['status'] === 'draft') {
            if (!$isAuthor) {
                if ($currentUser) {
                    // Penulis lain diarahkan kembali ke manajemen novel miliknya dengan flash message
                    set_flash('Novel tidak ditemukan atau Anda tidak memiliki hak akses ke naskah ini.', 'danger');
                    redirect('novels');
                    return;
                }
                // Pengunjung publik umum ditolak dengan status 404 Not Found
                $this->notFound('Novel yang Anda tuju belum dipublikasikan atau tidak tersedia.');
                return;
            }

            // Penulis melihat naskah draft miliknya sendiri -> tampilkan detail manajemen naskah internal
            $chapters = $this->chapterModel->getByNovel($novelId);
            $data = [
                'pageTitle' => "Detail Naskah: {$novel['title']} - karyaNovel",
                'novel' => $novel,
                'chapters' => $chapters,
                'user' => $currentUser,
                'isAuthor' => true
            ];
            $this->view('novels/detail', $data, 'main');
            return;
        }

        // Jika penulis yang membuka: tampilkan ruang kerja manajemen naskah internal
        if ($isAuthor) {
            $allChapters = $this->chapterModel->getByNovel($novelId);
            $data = [
                'pageTitle' => "Detail Naskah: {$novel['title']} - karyaNovel",
                'novel' => $novel,
                'chapters' => $allChapters,
                'user' => $currentUser,
                'isAuthor' => true
            ];
            $this->view('novels/detail', $data, 'main');
            return;
        }

        // Tampilan Publik Detail Novel (/novels/{id}) untuk pembaca umum:
        // Menampilkan Cover, Judul, Penulis, Genre, Sinopsis, Daftar bab (published)
        $chapters = $this->chapterModel->getByNovel($novelId, 'published');
        $data = [
            'pageTitle' => sanitize($novel['title']) . ' - karyaNovel',
            'novel' => $novel,
            'chapters' => $chapters,
            'isAuthor' => false,
            'user' => $currentUser
        ];

        $this->view('novel/detail', $data, 'main');
    }

    /**
     * Halaman Membaca Bab Novel (/novels/{id}/read/{chapter})
     * Menampilkan isi bab novel published dengan tipografi ramah baca
     *
     * @param int|string|null $novelId
     * @param int|string|null $chapterIdentifier nomor urut bab (chapter_order) atau ID bab
     */
    public function read($novelId = null, $chapterIdentifier = null): void {
        if (!Session::isLoggedIn()) {
            set_flash('Silakan masuk terlebih dahulu untuk membaca bab novel.', 'info');
            redirect('auth/login');
            return;
        }

        if (!$novelId) {
            $this->notFound('Novel tidak ditemukan.');
            return;
        }

        $novel = $this->novelModel->getWithAuthor((int)$novelId);
        if (!$novel) {
            $this->notFound('Novel yang Anda tuju tidak ditemukan atau tidak tersedia.');
            return;
        }

        $currentUser = Session::user();
        $isAuthor = ($currentUser && (int)$currentUser['id'] === (int)$novel['user_id']);

        // Proteksi 1: Jika novel berstatus draft dan bukan penulis, tolak dengan 404
        if ($novel['status'] === 'draft' && !$isAuthor) {
            $this->notFound('Novel ini masih berstatus draft dan belum dipublikasikan.');
            return;
        }

        // Cari chapter dalam novel ini
        if ($chapterIdentifier === null) {
            // Default ke bab published pertama (atau bab pertama jika author)
            $publishedChapters = $this->chapterModel->getByNovel((int)$novelId, $isAuthor ? null : 'published');
            if (empty($publishedChapters)) {
                $this->notFound('Belum ada bab yang dipublikasikan untuk novel ini.');
                return;
            }
            $chapter = $this->chapterModel->getWithNovel((int)$publishedChapters[0]['id']);
        } else {
            // Cek apakah identifier adalah ID bab yang ada di database
            $chapter = $this->chapterModel->getWithNovel((int)$chapterIdentifier);
            if ($chapter) {
                // Bab ditemukan berdasarkan ID. Validasi relasi novel: harus benar-benar terkait dengan novel pada URL!
                if ((int)$chapter['novel_id'] !== (int)$novelId) {
                    // Penolakan manipulasi URL: Bab milik novel lain!
                    $this->notFound('Bab yang Anda cari tidak terkait dengan novel ini.');
                    return;
                }
            } else {
                // Jika tidak ditemukan berdasarkan ID, periksa berdasarkan nomor urut (chapter_order) dalam novel ini
                $chapter = $this->chapterModel->findByOrder((int)$novelId, (int)$chapterIdentifier);
                if (!$chapter) {
                    $this->notFound('Bab yang Anda cari tidak ditemukan dalam novel ini.');
                    return;
                }
            }
        }

        if (!$chapter) {
            $this->notFound('Bab yang Anda cari tidak ditemukan dalam novel ini.');
            return;
        }

        // Proteksi 2: Jika bab berstatus draft dan bukan penulis, tolak dengan 404
        if ($chapter['status'] === 'draft' && !$isAuthor) {
            $this->notFound('Bab ini masih berstatus draft dan belum dapat dibaca oleh publik.');
            return;
        }

        // Proteksi 3: Jika novel induk berstatus draft dan bukan penulis, tolak dengan 404
        if ($novel['status'] === 'draft' && !$isAuthor) {
            $this->notFound('Novel ini masih berstatus draft dan belum dipublikasikan.');
            return;
        }

        // Ambil navigasi bab sebelumnya dan selanjutnya (HANYA bab published untuk publik)
        $adjacent = $this->chapterModel->getAdjacentChapters((int)$novelId, (int)$chapter['chapter_order'], !$isAuthor);

        // Ambil daftar bab untuk quick jump menu (HANYA bab published untuk publik)
        $allChapters = $this->chapterModel->getByNovel((int)$novelId, $isAuthor ? null : 'published');

        $data = [
            'pageTitle' => "Bab {$chapter['chapter_order']}: {$chapter['title']} - {$novel['title']}",
            'novel' => $novel,
            'chapter' => $chapter,
            'prevChapter' => $adjacent['prev'],
            'nextChapter' => $adjacent['next'],
            'allChapters' => $allChapters,
            'isAuthor' => $isAuthor,
            'user' => $currentUser
        ];

        $this->view('chapter/read', $data, 'main');
    }

    /**
     * Mengubah status publikasi novel (Draft <-> Published)
     * Dapat diakses via POST atau GET /novels/{id}/toggle_status
     *
     * @param int|string|null $id
     */
    public function toggle_status($id = null): void {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) return;
        }

        if (!$id) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];
        $novelId = (int)$id;

        $novel = $this->novelModel->findAuthorNovel($novelId, $userId);
        if (!$novel) {
            set_flash('Akses ditolak. Novel tidak ditemukan atau bukan milik Anda.', 'danger');
            redirect('novels');
            return;
        }

        $newStatus = $this->novelModel->toggleStatus($novelId, $userId);
        if ($newStatus === 'published') {
            // Forward notifikasi WhatsApp saat novel dipublikasikan
            try {
                $fonnteService = new FonnteService();
                $novel['id'] = $novelId;
                $fonnteService->sendNewNovelNotification($novel, $currentUser);
            } catch (Throwable $e) {
                error_log("Fonnte notification error on publish novel: " . $e->getMessage());
            }

            set_flash("Novel '{$novel['title']}' berhasil dipublikasikan! Naskah kini dapat dibaca oleh publik.", 'success');
        } else {
            set_flash("Status novel '{$novel['title']}' diubah menjadi Draft. Naskah disembunyikan dari halaman publik.", 'warning');
        }

        redirect('novels/' . $novelId);
    }

    /**
     * Alias publikasi langsung (/novels/{id}/publish)
     */
    public function publish($id = null): void {
        $this->toggle_status($id);
    }

    /**
     * Form & Proses Edit Novel (/novels/{id}/edit)
     *
     * @param int|string $id
     */
    public function edit($id = null): void {
        $this->requireAuth();

        if (!$id) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];
        $novelId = (int)$id;

        // Otorisasi kepemilikan
        $novel = $this->novelModel->findAuthorNovel($novelId, $userId);

        if (!$novel) {
            set_flash('Anda tidak memiliki izin untuk menyunting novel ini.', 'danger');
            redirect('novels');
        }

        $errors = [];
        $oldInput = [
            'title' => $novel['title'],
            'synopsis' => $novel['synopsis'],
            'genre' => $novel['genre'],
            'status' => $novel['status']
        ];

        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $methodOverride = strtoupper($_POST['_method'] ?? '');
        $isUpdate = ($reqMethod === 'POST' || in_array($reqMethod, ['PUT', 'PATCH']) || in_array($methodOverride, ['PUT', 'PATCH']));

        if ($isUpdate) {
            if (!$this->validateCsrf()) return;
            $title = trim(sanitize($_POST['title'] ?? ''));
            $synopsis = trim(sanitize($_POST['synopsis'] ?? ''));
            $genre = trim(sanitize($_POST['genre'] ?? ''));
            $status = trim(sanitize($_POST['status'] ?? 'draft'));

            $oldInput = [
                'title' => $title,
                'synopsis' => $synopsis,
                'genre' => $genre,
                'status' => $status
            ];

            // Validasi input
            if (empty($title) || mb_strlen($title) < 3 || mb_strlen($title) > 200) {
                $errors['title'] = 'Judul novel harus antara 3 hingga 200 karakter.';
            }

            if (empty($synopsis) || mb_strlen($synopsis) < 20 || mb_strlen($synopsis) > 5000) {
                $errors['synopsis'] = 'Sinopsis harus antara 20 hingga 5000 karakter.';
            }

            if (empty($genre)) {
                $errors['genre'] = 'Genre novel wajib dipilih.';
            }

            if (!in_array($status, ['draft', 'published'])) {
                $status = 'draft';
            }

            $updateData = [
                'title' => $title,
                'synopsis' => $synopsis,
                'genre' => $genre,
                'status' => $status
            ];

            // Upload cover baru jika ada
            if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
                $coverUpload = $this->handleCoverUpload($_FILES['cover']);
                if ($coverUpload['success']) {
                    // Hapus cover lama jika bukan default
                    $this->deletePhysicalCover($novel['cover']);
                    $updateData['cover'] = $coverUpload['path'];
                } else {
                    $errors['cover'] = $coverUpload['error'];
                }
            }

            if (empty($errors)) {
                $updated = $this->novelModel->updateNovel($novelId, $userId, $updateData);

                if ($updated) {
                    // Forward notifikasi WhatsApp jika status berubah dari draft ke published
                    if ($novel['status'] !== 'published' && $status === 'published') {
                        try {
                            $fonnteService = new FonnteService();
                            $updateData['id'] = $novelId;
                            $updateData['cover'] = $updateData['cover'] ?? $novel['cover'];
                            $fonnteService->sendNewNovelNotification($updateData, $currentUser);
                        } catch (Throwable $e) {
                            error_log("Fonnte notification error on edit novel: " . $e->getMessage());
                        }
                    }

                    set_flash("Novel '{$title}' berhasil diperbarui!", 'success');
                    redirect('novels/' . $novelId);
                } else {
                    $errors['general'] = 'Gagal memperbarui data novel.';
                    set_flash('Gagal memperbarui data novel.', 'danger');
                }
            }
        }

        $data = [
            'pageTitle' => "Edit Novel: {$novel['title']} - karyaNovel",
            'novel' => $novel,
            'availableGenres' => $this->availableGenres,
            'errors' => $errors,
            'oldInput' => $oldInput
        ];

        $this->view('novels/edit', $data, 'main');
    }

    /**
     * Alias method update untuk RESTful PUT/PATCH /novels/{id}
     */
    public function update($id = null): void {
        $this->edit($id);
    }

    /**
     * Alias method store untuk RESTful POST /novels
     */
    public function store(): void {
        $this->create();
    }

    /**
     * Proses Hapus Novel (/novels/{id}/delete atau DELETE /novels/{id})
     *
     * @param int|string $id
     */
    public function delete($id = null): void {
        $this->requireAuth();

        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $methodOverride = strtoupper($_POST['_method'] ?? '');
        $isDelete = ($reqMethod === 'POST' || $reqMethod === 'DELETE' || $methodOverride === 'DELETE');

        if (!$id || !$isDelete) {
            redirect('novels');
        }
        if (!$this->validateCsrf()) return;

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];
        $novelId = (int)$id;

        $novel = $this->novelModel->findAuthorNovel($novelId, $userId);

        if (!$novel) {
            set_flash('Akses ditolak. Novel tidak ditemukan atau bukan milik Anda.', 'danger');
            redirect('novels');
        }

        // Hapus file cover fisik dari server jika ada
        $this->deletePhysicalCover($novel['cover']);

        // Hapus record dari database (chapters dihapus otomatis via CASCADE)
        $deleted = $this->novelModel->deleteNovel($novelId, $userId);

        if ($deleted) {
            set_flash("Novel '{$novel['title']}' beserta seluruh babnya berhasil dihapus.", 'success');
        } else {
            set_flash("Gagal menghapus novel.", 'danger');
        }

        redirect('novels');
    }

    /**
     * Endpoint Sub-Resource Bab Novel (/novels/{novel_id}/chapters/...)
     * Mendukung standar RESTful /novels/{novel}/chapters
     *
     * @param int|string|null $novelId
     * @param string|null $arg1 'create', chapterId, dll
     * @param string|null $arg2 'edit', 'delete', dll
     */
    public function chapters($novelId = null, $arg1 = null, $arg2 = null): void {
        if (!$novelId) {
            redirect('novels');
        }

        require_once APPROOT . '/controllers/ChapterController.php';
        $chapterController = new ChapterController();

        $httpMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (isset($_POST['_method'])) {
            $override = strtoupper(trim($_POST['_method']));
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'])) {
                $httpMethod = $override;
            }
        }

        // Case: GET /novels/{novel}/chapters/create
        if ($arg1 === 'create') {
            $chapterController->create($novelId);
            return;
        }

        // Case: /novels/{novel}/chapters/{chapterId} atau /novels/{novel}/chapters/{chapterId}/edit dsb
        if (is_numeric($arg1)) {
            $chapterId = (int)$arg1;
            $subAction = strtolower($arg2 ?? '');

            if ($httpMethod === 'DELETE' || $subAction === 'delete') {
                $chapterController->delete($chapterId);
                return;
            }

            if (in_array($httpMethod, ['PUT', 'PATCH']) || $subAction === 'edit') {
                $chapterController->edit($chapterId);
                return;
            }

            // Default GET /novels/{novel}/chapters/{chapterId} -> baca/tampil bab
            $chapterController->read($chapterId);
            return;
        }

        // Case: POST /novels/{novel}/chapters (simpan bab baru)
        if ($httpMethod === 'POST') {
            $chapterController->create($novelId);
            return;
        }

        // Default: GET /novels/{novel}/chapters -> daftar & manajemen bab
        $chapterController->index($novelId);
    }

    /**
     * Helper untuk memproses upload file gambar cover dengan validasi MIME & integritas gambar
     */
    private function handleCoverUpload(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Terjadi kesalahan saat mengunggah file gambar.'];
        }

        // Cek ukuran file (Maksimal 2MB = 2 * 1024 * 1024)
        if ($file['size'] > 2097152) {
            return ['success' => false, 'error' => 'Ukuran gambar cover maksimal 2 MB.'];
        }

        // Cek ekstensi file dasar dari nama file yang diunggah
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileInfo = pathinfo($file['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (!in_array($ext, $allowedExtensions, true)) {
            return ['success' => false, 'error' => 'Format file tidak diizinkan. Gunakan JPG, PNG, atau WEBP.'];
        }

        // Validasi MIME Type sebenarnya menggunakan finfo (anti-spoofing)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : mime_content_type($file['tmp_name']);
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!array_key_exists($mimeType, $allowedMimeTypes)) {
            return ['success' => false, 'error' => 'Tipe konten file tidak valid. File harus berupa gambar asli.'];
        }

        // Validasi integritas header gambar (mencegah polyglot/embedded scripts)
        $imageSize = @getimagesize($file['tmp_name']);
        if ($imageSize === false && $mimeType !== 'image/webp') {
            return ['success' => false, 'error' => 'Struktur file gambar rusak atau tidak valid.'];
        }

        // Tentukan ekstensi aman berdasarkan MIME type yang telah divalidasi
        $safeExt = $allowedMimeTypes[$mimeType];
        $fileName = 'cover_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
        $uploadDir = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'covers';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Set permission aman pada file cover
            chmod($destination, 0644);
            $relativePath = 'uploads/covers/' . $fileName;
            return ['success' => true, 'path' => $relativePath];
        }

        return ['success' => false, 'error' => 'Gagal memindahkan file yang diunggah ke folder penyimpanan.'];
    }

    /**
     * Menghapus file cover fisik dari direktori uploads secara aman (mencegah path traversal)
     */
    private function deletePhysicalCover(?string $coverPath): void {
        if (empty($coverPath)) {
            return;
        }

        $fileName = basename($coverPath);
        $fullPath = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'covers' . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
