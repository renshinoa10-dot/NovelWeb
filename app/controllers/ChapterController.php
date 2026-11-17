<?php
/**
 * karyaNovel - ChapterController
 * Mengatur pengelolaan CRUD bab novel oleh penulis serta pembacaan bab oleh publik
 */

class ChapterController extends Controller {
    private Chapter $chapterModel;
    private Novel $novelModel;

    public function __construct() {
        $this->chapterModel = $this->model('Chapter');
        $this->novelModel = $this->model('Novel');
    }

    /**
     * Membaca satu bab novel (Publik / Preview Penulis)
     *
     * @param int|string|null $id
     */
    public function read($id = null): void {
        if (!$id) {
            redirect('novel');
        }

        $chapter = $this->chapterModel->getWithNovel((int)$id);
        if (!$chapter) {
            $this->notFound('Bab yang Anda tuju belum dipublikasikan atau tidak tersedia.');
            return;
        }

        $currentUser = Session::user();
        $isAuthor = ($currentUser && (int)$currentUser['id'] === (int)$chapter['author_id']);
        $isPublic = ($chapter['status'] === 'published' && ($chapter['novel_status'] ?? '') === 'published');

        if (!$isPublic && !$isAuthor) {
            $this->notFound('Bab yang Anda tuju belum dipublikasikan atau tidak tersedia untuk publik.');
            return;
        }

        // Ambil data novel induk
        $novel = $this->novelModel->getWithAuthor((int)$chapter['novel_id']);

        // Ambil bab sebelumnya dan selanjutnya
        $adjacent = $this->chapterModel->getAdjacentChapters((int)$chapter['novel_id'], (int)$chapter['chapter_order'], !$isAuthor);

        // Ambil seluruh daftar bab untuk quick jump dropdown
        $allChapters = $this->chapterModel->getByNovel((int)$chapter['novel_id'], $isAuthor ? null : 'published');

        $data = [
            'pageTitle' => "Bab {$chapter['chapter_order']}: {$chapter['title']} - {$chapter['novel_title']}",
            'chapter' => $chapter,
            'novel' => $novel,
            'prevChapter' => $adjacent['prev'],
            'nextChapter' => $adjacent['next'],
            'allChapters' => $allChapters,
            'isAuthor' => $isAuthor,
            'user' => $currentUser
        ];

        $this->view('chapter/read', $data, 'main');
    }

    /**
     * Alias method show untuk RESTful GET /chapter/{id}
     */
    public function show($id = null): void {
        $this->read($id);
    }

    /**
     * Alias method detail untuk /chapter/detail/{id}
     */
    public function detail($id = null): void {
        $this->read($id);
    }

    /**
     * Halaman Pengelolaan & Daftar Bab Naskah untuk Penulis
     *
     * @param int|string|null $novelId
     */
    public function manage($novelId = null): void {
        $this->index($novelId);
    }

    public function index($novelId = null): void {
        $this->requireAuth();

        if (!$novelId) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        $novel = $this->novelModel->findAuthorNovel((int)$novelId, $userId);
        if (!$novel) {
            set_flash('Novel tidak ditemukan atau Anda tidak memiliki akses ke naskah ini.', 'danger');
            redirect('novels');
        }

        $chapters = $this->chapterModel->getByNovel((int)$novelId);

        $data = [
            'pageTitle' => "Manajemen Bab: {$novel['title']} - karyaNovel",
            'novel' => $novel,
            'chapters' => $chapters,
            'user' => $currentUser
        ];

        $this->view('chapter/index', $data, 'main');
    }

    /**
     * Form & Proses Pembuatan Bab Baru (/chapter/create/{novel_id})
     *
     * @param int|string|null $novelId
     */
    public function create($novelId = null): void {
        $this->requireAuth();

        if (!$novelId) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        // Otorisasi: Pastikan novel milik user yang sedang login
        $novel = $this->novelModel->findAuthorNovel((int)$novelId, $userId);
        if (!$novel) {
            set_flash('Akses ditolak. Novel tidak ditemukan atau bukan milik Anda.', 'danger');
            redirect('novels');
        }

        $nextOrder = $this->chapterModel->getNextOrder((int)$novelId);
        $errors = [];
        $oldInput = [
            'title' => '',
            'content' => '',
            'chapter_order' => $nextOrder,
            'status' => 'draft'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) return;
            $title = trim(sanitize($_POST['title'] ?? ''));
            // Content naskah cerita dibersihkan dengan trim, tags diperbolehkan/disanitasi
            $content = trim($_POST['content'] ?? '');
            $chapterOrder = isset($_POST['chapter_order']) && is_numeric($_POST['chapter_order']) 
                ? (int)$_POST['chapter_order'] 
                : $nextOrder;
            if ($chapterOrder < 1) $chapterOrder = 1;

            $status = trim($_POST['status'] ?? 'draft');
            if (isset($_POST['submit_action']) && in_array($_POST['submit_action'], ['draft', 'published'])) {
                $status = $_POST['submit_action'];
            }
            if (!in_array($status, ['draft', 'published'])) {
                $status = 'draft';
            }

            $oldInput = [
                'title' => $title,
                'content' => $content,
                'chapter_order' => $chapterOrder,
                'status' => $status
            ];

            // 1. Validasi Judul Bab
            if (empty($title)) {
                $errors['title'] = 'Judul bab wajib diisi.';
            } elseif (mb_strlen($title) < 2 || mb_strlen($title) > 200) {
                $errors['title'] = 'Judul bab harus antara 2 hingga 200 karakter.';
            }

            // 2. Validasi Isi Konten Bab (Draft boleh kosong, publikasi wajib isi)
            if ($status === 'published') {
                if (empty($content)) {
                    $errors['content'] = 'Isi naskah cerita bab wajib ditulis sebelum dipublikasikan.';
                } elseif (mb_strlen($content) < 10) {
                    $errors['content'] = 'Isi naskah terlalu pendek (minimal 10 karakter).';
                }
            }

            if (empty($errors)) {
                $newChapterId = $this->chapterModel->createChapter([
                    'novel_id' => (int)$novelId,
                    'title' => $title,
                    'content' => $content,
                    'chapter_order' => $chapterOrder,
                    'status' => $status
                ]);

                if ($newChapterId) {
                    // Forward notifikasi WhatsApp jika bab baru langsung dipublikasikan
                    if ($status === 'published') {
                        try {
                            $fonnteService = new FonnteService();
                            $fonnteService->sendNewChapterNotification([
                                'id' => $newChapterId,
                                'novel_id' => (int)$novelId,
                                'title' => $title,
                                'chapter_order' => $chapterOrder
                            ], $novel, $currentUser);
                        } catch (Throwable $e) {
                            error_log("Fonnte notification error on create chapter: " . $e->getMessage());
                        }
                    }

                    $statusMsg = ($status === 'published') ? 'dan dipublikasikan!' : 'sebagai draft.';
                    set_flash("Bab #{$chapterOrder} '{$title}' berhasil disimpan {$statusMsg}", 'success');
                    redirect('novels/' . $novelId);
                } else {
                    $errors['general'] = 'Gagal menyimpan bab ke database. Silakan coba lagi.';
                }
            }
        }

        $data = [
            'pageTitle' => "Tulis Bab Baru - {$novel['title']} - karyaNovel",
            'novel' => $novel,
            'errors' => $errors,
            'oldInput' => $oldInput,
            'nextOrder' => $nextOrder
        ];

        $this->view('chapter/create', $data, 'main');
    }

    /**
     * Form & Proses Sunting / Tulis Bab (/chapter/edit/{chapter_id})
     *
     * @param int|string|null $chapterId
     */
    public function edit($chapterId = null): void {
        $this->requireAuth();

        if (!$chapterId) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        // Otorisasi kepemilikan bab melalui relasi novel
        $chapter = $this->chapterModel->findAuthorChapter((int)$chapterId, $userId);
        if (!$chapter) {
            set_flash('Akses ditolak. Bab naskah tidak ditemukan atau Anda tidak memiliki hak izin.', 'danger');
            redirect('novels');
        }

        $novel = $this->novelModel->findAuthorNovel((int)$chapter['novel_id'], $userId);
        $totalChapters = (int)($novel['total_chapters'] ?? 1);

        $errors = [];
        $oldInput = [
            'title' => $chapter['title'],
            'content' => $chapter['content'],
            'chapter_order' => (int)$chapter['chapter_order'],
            'status' => $chapter['status']
        ];

        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $methodOverride = strtoupper($_POST['_method'] ?? '');
        $isUpdate = ($reqMethod === 'POST' || in_array($reqMethod, ['PUT', 'PATCH']) || in_array($methodOverride, ['PUT', 'PATCH']));

        if ($isUpdate) {
            if (!$this->validateCsrf()) return;
            $title = trim(sanitize($_POST['title'] ?? ''));
            $content = trim($_POST['content'] ?? '');
            $chapterOrder = isset($_POST['chapter_order']) && is_numeric($_POST['chapter_order']) 
                ? (int)$_POST['chapter_order'] 
                : (int)$chapter['chapter_order'];
            if ($chapterOrder < 1) $chapterOrder = 1;

            $status = trim($_POST['status'] ?? 'draft');
            if (isset($_POST['submit_action']) && in_array($_POST['submit_action'], ['draft', 'published'])) {
                $status = $_POST['submit_action'];
            }
            if (!in_array($status, ['draft', 'published'])) {
                $status = 'draft';
            }

            $oldInput = [
                'title' => $title,
                'content' => $content,
                'chapter_order' => $chapterOrder,
                'status' => $status
            ];

            // Validasi
            if (empty($title) || mb_strlen($title) < 2 || mb_strlen($title) > 200) {
                $errors['title'] = 'Judul bab harus antara 2 hingga 200 karakter.';
            }

            // Validasi Isi Konten Bab (Draft boleh kosong, publikasi wajib isi)
            if ($status === 'published') {
                if (empty($content)) {
                    $errors['content'] = 'Isi naskah cerita bab wajib ditulis sebelum dipublikasikan.';
                } elseif (mb_strlen($content) < 10) {
                    $errors['content'] = 'Isi naskah minimal 10 karakter untuk publikasi.';
                }
            }

            if (empty($errors)) {
                $updated = $this->chapterModel->updateChapter((int)$chapterId, $userId, [
                    'title' => $title,
                    'content' => $content,
                    'chapter_order' => $chapterOrder,
                    'status' => $status
                ]);

                if ($updated) {
                    // Forward notifikasi WhatsApp jika status bab berubah dari draft menjadi published
                    if ($chapter['status'] !== 'published' && $status === 'published') {
                        try {
                            $fonnteService = new FonnteService();
                            $fonnteService->sendNewChapterNotification([
                                'id' => (int)$chapterId,
                                'novel_id' => (int)$chapter['novel_id'],
                                'title' => $title,
                                'chapter_order' => $chapterOrder
                            ], $novel, $currentUser);
                        } catch (Throwable $e) {
                            error_log("Fonnte notification error on edit chapter: " . $e->getMessage());
                        }
                    }

                    set_flash("Bab '{$title}' berhasil diperbarui!", 'success');
                    redirect('novels/' . $chapter['novel_id']);
                } else {
                    $errors['general'] = 'Gagal memperbarui data bab.';
                }
            }
        }

        $data = [
            'pageTitle' => "Edit Bab {$chapter['chapter_order']}: {$chapter['title']} - karyaNovel",
            'chapter' => $chapter,
            'novel' => $novel,
            'totalChapters' => $totalChapters,
            'errors' => $errors,
            'oldInput' => $oldInput
        ];

        $this->view('chapter/edit', $data, 'main');
    }

    /**
     * Alias method untuk tombol "Tulis" bab
     */
    public function write($chapterId = null): void {
        $this->edit($chapterId);
    }

    /**
     * Endpoint API JSON Autosave Naskah Bab (/chapter/autosave/{chapter_id})
     * Menyimpan perubahan naskah secara aman dan berkala tanpa me-reload halaman
     *
     * @param int|string|null $chapterId
     */
    public function autosave($chapterId = null): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Metode request tidak diizinkan.'], 405);
            return;
        }

        if (!$chapterId) {
            $this->json(['success' => false, 'message' => 'ID bab naskah tidak valid.'], 400);
            return;
        }

        if (!Session::isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Sesi login telah berakhir. Silakan login kembali.'], 401);
            return;
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        // Otorisasi kepemilikan bab
        $chapter = $this->chapterModel->findAuthorChapter((int)$chapterId, $userId);
        if (!$chapter) {
            $this->json(['success' => false, 'message' => 'Akses ditolak atau bab naskah tidak ditemukan.'], 403);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $title = isset($input['title']) ? trim(sanitize($input['title'])) : $chapter['title'];
        $content = isset($input['content']) ? trim($input['content']) : $chapter['content'];
        $status = isset($input['status']) && in_array($input['status'], ['draft', 'published']) 
            ? $input['status'] 
            : $chapter['status'];

        if (empty($title)) {
            $this->json(['success' => false, 'message' => 'Judul bab tidak boleh kosong.'], 422);
            return;
        }

        $updated = $this->chapterModel->updateChapter((int)$chapterId, $userId, [
            'title' => $title,
            'content' => $content,
            'status' => $status
        ]);

        if ($updated) {
            $this->json([
                'success' => true,
                'message' => 'Naskah berhasil disimpan otomatis.',
                'data' => [
                    'chapter_id' => (int)$chapterId,
                    'title' => $title,
                    'status' => $status,
                    'saved_at' => date('H:i:s'),
                    'saved_date' => date('d M Y, H:i:s')
                ]
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal memperbarui naskah ke database.'], 500);
        }
    }

    /**
     * Proses Hapus Bab (/chapter/delete/{chapter_id})
     *
     * @param int|string|null $chapterId
     */
    public function delete($chapterId = null): void {
        $this->requireAuth();

        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $methodOverride = strtoupper($_POST['_method'] ?? '');
        $isDelete = ($reqMethod === 'POST' || $reqMethod === 'DELETE' || $methodOverride === 'DELETE');

        if (!$chapterId || !$isDelete) {
            redirect('novels');
        }
        if (!$this->validateCsrf()) return;

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        $chapter = $this->chapterModel->findAuthorChapter((int)$chapterId, $userId);
        if (!$chapter) {
            set_flash('Akses ditolak. Bab naskah tidak ditemukan atau bukan milik Anda.', 'danger');
            redirect('novels');
        }

        $novelId = (int)$chapter['novel_id'];
        $chapterTitle = $chapter['title'];
        $chapterOrder = $chapter['chapter_order'];

        // Hapus bab dan resequence nomor bab lain otomatis
        $deleted = $this->chapterModel->deleteChapter((int)$chapterId, $userId);

        if ($deleted) {
            set_flash("Bab #{$chapterOrder} '{$chapterTitle}' berhasil dihapus. Urutan bab lain telah dirapikan otomatis.", 'success');
        } else {
            set_flash('Gagal menghapus bab.', 'danger');
        }

        $this->redirectBackOrNovel($novelId);
    }

    /**
     * Mengatur Urutan Bab: Naikkan posisi bab (Move Up)
     *
     * @param int|string|null $chapterId
     */
    public function move_up($chapterId = null): void {
        $this->requireAuth();

        if (!$chapterId) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        $chapter = $this->chapterModel->findAuthorChapter((int)$chapterId, $userId);
        if (!$chapter) {
            set_flash('Akses ditolak. Bab naskah tidak ditemukan.', 'danger');
            redirect('novels');
        }

        $this->chapterModel->moveUp((int)$chapterId, $userId);
        set_flash("Urutan Bab '{$chapter['title']}' berhasil dinaikkan.", 'success');
        $this->redirectBackOrNovel((int)$chapter['novel_id']);
    }

    /**
     * Mengatur Urutan Bab: Turunkan posisi bab (Move Down)
     *
     * @param int|string|null $chapterId
     */
    public function move_down($chapterId = null): void {
        $this->requireAuth();

        if (!$chapterId) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        $chapter = $this->chapterModel->findAuthorChapter((int)$chapterId, $userId);
        if (!$chapter) {
            set_flash('Akses ditolak. Bab naskah tidak ditemukan.', 'danger');
            redirect('novels');
        }

        $this->chapterModel->moveDown((int)$chapterId, $userId);
        set_flash("Urutan Bab '{$chapter['title']}' berhasil diturunkan.", 'success');
        $this->redirectBackOrNovel((int)$chapter['novel_id']);
    }

    /**
     * Mengubah Status Publikasi Bab (Draft <-> Published)
     *
     * @param int|string|null $chapterId
     */
    public function toggle_status($chapterId = null): void {
        $this->requireAuth();

        if (!$chapterId) {
            redirect('novels');
        }

        $currentUser = Session::user();
        $userId = (int)$currentUser['id'];

        $chapter = $this->chapterModel->findAuthorChapter((int)$chapterId, $userId);
        if (!$chapter) {
            set_flash('Akses ditolak. Bab naskah tidak ditemukan.', 'danger');
            redirect('novels');
        }

        $newStatus = $this->chapterModel->toggleStatus((int)$chapterId, $userId);
        if ($newStatus === 'published') {
            // Forward notifikasi WhatsApp saat bab dipublikasikan
            try {
                $novel = $this->novelModel->find((int)$chapter['novel_id']);
                $fonnteService = new FonnteService();
                $fonnteService->sendNewChapterNotification($chapter, $novel ?: [], $currentUser);
            } catch (Throwable $e) {
                error_log("Fonnte notification error on toggle chapter: " . $e->getMessage());
            }
        }

        $badgeText = ($newStatus === 'published') ? 'Published (Dapat dibaca)' : 'Draft (Rahasia)';
        set_flash("Status Bab '{$chapter['title']}' diubah menjadi: {$badgeText}.", 'info');
        $this->redirectBackOrNovel((int)$chapter['novel_id']);
    }

    /**
     * Helper redirect kembali ke halaman referer (chapter list atau novel detail)
     */
    private function redirectBackOrNovel(int $novelId): void {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        if (str_contains($referrer, 'chapter')) {
            redirect('chapter/index/' . $novelId);
        } else {
            redirect('novels/' . $novelId);
        }
    }
}
