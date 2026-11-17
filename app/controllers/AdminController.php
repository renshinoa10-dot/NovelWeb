<?php
/**
 * karyaNovel - AdminController
 * Mengatur panel administrasi, manajemen pengguna, manajemen novel, dan moderasi konten
 */

require_once APPROOT . '/services/AiService.php';

class AdminController extends Controller {
    private User $userModel;
    private Novel $novelModel;
    private Chapter $chapterModel;
    private AiHistory $aiHistoryModel;
    private Setting $settingModel;
    private NotificationLog $notificationLogModel;

    public function __construct() {
        // Proteksi ketat: Hanya user dengan role 'admin' yang dapat mengakses seluruh fitur controller ini
        $this->requireAdmin();

        $this->userModel = $this->model('User');
        $this->novelModel = $this->model('Novel');
        $this->chapterModel = $this->model('Chapter');
        $this->aiHistoryModel = $this->model('AiHistory');
        $this->settingModel = $this->model('Setting');
        $this->notificationLogModel = $this->model('NotificationLog');
    }

    /**
     * Halaman Dashboard Utama Admin & Hub Manajemen
     *
     * @param string $tab
     */
    public function index(string $tab = 'dashboard'): void {
        // Anti-cache headers untuk memastikan Admin selalu melihat data database terkini
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

        $currentUser = Session::user();

        // 1. Data Statistik Pilar Utama Dashboard (Agregasi database real-time)
        $userBreakdown = $this->userModel->getUsersBreakdown();
        $totalUsers = $userBreakdown['total'];
        $novelStats = $this->novelModel->getGlobalStats();
        $totalNovels = $novelStats['total_novels'];
        $publishedNovels = $novelStats['published_novels'];
        $draftNovels = $novelStats['draft_novels'];
        $chapterStats = $this->chapterModel->getGlobalStats();
        $totalChapters = $chapterStats['total_chapters'];
        $totalAiLogs = $this->aiHistoryModel->count();

        // 2. Data Pengguna (User Management)
        $usersList = $this->userModel->getAllUsers(100);

        // 3. Data Novel (Novel Management)
        $novelsList = $this->novelModel->getAllWithAuthor(100);

        // 4. Data Konten & Moderasi Bab (Content Management)
        $filterNovelId = isset($_GET['novel_id']) && is_numeric($_GET['novel_id']) ? (int)$_GET['novel_id'] : null;
        $chaptersList = $this->chapterModel->getAllWithNovelAndAuthor($filterNovelId, 100);

        // 5. Data Konfigurasi AI & Chatbot (Settings Tab)
        $aiService = new AiService();
        $allSettings = $this->settingModel->getAllSettings();
        $currentProvider = $allSettings['ai_provider'] ?? env('AI_PROVIDER', 'gemini');
        $currentApiKey = $allSettings['ai_api_key'] ?? env('AI_API_KEY', '');
        $currentModel = $allSettings['ai_model'] ?? env('AI_MODEL', 'gemini-3.5-flash-lite');
        $currentTimeout = (int)($allSettings['ai_timeout'] ?? env('AI_TIMEOUT', 30));
        $currentMaxPrompt = (int)($allSettings['ai_max_prompt_length'] ?? env('AI_MAX_PROMPT_LENGTH', 2000));
        $curatedModels = $aiService->getCuratedModels($currentProvider);

        // 6. Data Konfigurasi & Statistik Notifikasi WhatsApp Fonnte
        $fonnteService = new FonnteService();
        $fonnteConfig = [
            'apiKey' => $allSettings['fonnte_api_key'] ?? env('FONNTE_API_KEY', 'NM8K5TKmpDu1gDaVaNyj'),
            'targetPhone' => $allSettings['fonnte_target_phone'] ?? env('FONNTE_TARGET_PHONE', '089634700208'),
            'enabled' => filter_var($allSettings['fonnte_enabled'] ?? env('FONNTE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'notifyNewNovel' => filter_var($allSettings['fonnte_notify_new_novel'] ?? env('FONNTE_NOTIFY_NEW_NOVEL', true), FILTER_VALIDATE_BOOLEAN),
            'notifyNewChapter' => filter_var($allSettings['fonnte_notify_new_chapter'] ?? env('FONNTE_NOTIFY_NEW_CHAPTER', true), FILTER_VALIDATE_BOOLEAN),
            'notifySystem' => filter_var($allSettings['fonnte_notify_system'] ?? env('FONNTE_NOTIFY_SYSTEM', true), FILTER_VALIDATE_BOOLEAN),
        ];

        // Tentukan tab aktif terlebih dahulu
        $validTabs = ['dashboard', 'users', 'novels', 'content', 'settings', 'notifications'];
        $activeTab = in_array($tab, $validTabs, true) ? $tab : 'dashboard';
        if (isset($_GET['tab']) && in_array($_GET['tab'], $validTabs, true)) {
            $activeTab = $_GET['tab'];
        }

        $notificationStats = $this->notificationLogModel->getSummaryStats();
        $notificationLogs = $this->notificationLogModel->getRecent(50);
        $fonnteDevice = ($activeTab === 'notifications') ? $fonnteService->getDeviceStatus() : null;

        $data = [
            'pageTitle' => 'Pusat Kontrol Administrator - karyaNovel',
            'admin' => $currentUser,
            'activeTab' => $activeTab,
            'filterNovelId' => $filterNovelId,
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalNovels' => $totalNovels,
                'publishedNovels' => $publishedNovels,
                'draftNovels' => $draftNovels,
                'totalChapters' => $totalChapters,
                'totalAiLogs' => $totalAiLogs,
                'totalNotifications' => $notificationStats['total'],
                'successNotifications' => $notificationStats['success_count'],
                'failedNotifications' => $notificationStats['failed_count']
            ],
            'users' => $usersList,
            'novels' => $novelsList,
            'chapters' => $chaptersList,
            'aiConfig' => [
                'provider' => $currentProvider,
                'apiKey' => $currentApiKey,
                'model' => $currentModel,
                'timeout' => $currentTimeout,
                'maxPromptLength' => $currentMaxPrompt,
                'curatedModels' => $curatedModels,
                'isConfigured' => !empty($currentApiKey),
                'updatedAt' => $allSettings['updated_at'] ?? null
            ],
            'fonnteConfig' => $fonnteConfig,
            'fonnteDevice' => $fonnteDevice,
            'notificationLogs' => $notificationLogs,
            'notificationStats' => $notificationStats
        ];

        $this->view('admin/index', $data, 'main');
    }

    /**
     * Endpoint Khusus: Manajemen Pengguna (/admin/users)
     */
    public function users(): void {
        $this->index('users');
    }

    /**
     * Endpoint Khusus: Manajemen Novel (/admin/novels)
     */
    public function novels(): void {
        $this->index('novels');
    }

    /**
     * Endpoint Khusus: Moderasi Konten Bab (/admin/content)
     */
    public function content(): void {
        $this->index('content');
    }

    /**
     * Endpoint Khusus: Konfigurasi AI & Chatbot (/admin/settings)
     */
    public function settings(): void {
        $this->index('settings');
    }

    /**
     * Endpoint Khusus: Manajemen Notifikasi WhatsApp Fonnte (/admin/notifications)
     */
    public function notifications(): void {
        $this->index('notifications');
    }

    /**
     * Simpan Perubahan Pengaturan WhatsApp Gateway Fonnte
     */
    public function update_fonnte_settings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/notifications');
            return;
        }

        if (!$this->validateCsrf()) return;

        $apiKey = trim($_POST['fonnte_api_key'] ?? '');
        $targetPhone = trim($_POST['fonnte_target_phone'] ?? '');
        $enabled = isset($_POST['fonnte_enabled']) ? '1' : '0';
        $notifyNewNovel = isset($_POST['fonnte_notify_new_novel']) ? '1' : '0';
        $notifyNewChapter = isset($_POST['fonnte_notify_new_chapter']) ? '1' : '0';
        $notifySystem = isset($_POST['fonnte_notify_system']) ? '1' : '0';

        // Simpan ke Database settings
        $this->settingModel->set('fonnte_api_key', $apiKey, 'API Key Fonnte WhatsApp Gateway');
        $this->settingModel->set('fonnte_target_phone', $targetPhone, 'Nomor Target Default WhatsApp Forward');
        $this->settingModel->set('fonnte_enabled', $enabled, 'Status Aktif Notifikasi Fonnte');
        $this->settingModel->set('fonnte_notify_new_novel', $notifyNewNovel, 'Forward Notifikasi Cerita Baru');
        $this->settingModel->set('fonnte_notify_new_chapter', $notifyNewChapter, 'Forward Notifikasi Bab Baru');
        $this->settingModel->set('fonnte_notify_system', $notifySystem, 'Forward Notifikasi Sistem');

        // Sinkronisasi ke file .env
        Setting::syncToEnvFile([
            'FONNTE_API_KEY' => $apiKey,
            'FONNTE_TARGET_PHONE' => $targetPhone,
            'FONNTE_ENABLED' => ($enabled === '1') ? 'true' : 'false',
            'FONNTE_NOTIFY_NEW_NOVEL' => ($notifyNewNovel === '1') ? 'true' : 'false',
            'FONNTE_NOTIFY_NEW_CHAPTER' => ($notifyNewChapter === '1') ? 'true' : 'false',
            'FONNTE_NOTIFY_SYSTEM' => ($notifySystem === '1') ? 'true' : 'false'
        ]);

        set_flash('Pengaturan WhatsApp Gateway Fonnte berhasil diperbarui!', 'success');
        redirect('admin/notifications');
    }

    /**
     * Kirim Notifikasi Sistem / Broadcast via WhatsApp
     */
    public function send_system_notification(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/notifications');
            return;
        }

        if (!$this->validateCsrf()) return;

        $title = trim(sanitize($_POST['title'] ?? ''));
        $message = trim($_POST['message'] ?? '');
        $target = trim($_POST['target'] ?? '');

        if (empty($title) || empty($message)) {
            set_flash('Judul dan isi pesan notifikasi pengumuman sistem wajib diisi.', 'danger');
            redirect('admin/notifications');
            return;
        }

        $fonnteService = new FonnteService();
        $targetNumber = !empty($target) ? $target : null;
        $result = $fonnteService->sendSystemNotification($title, $message, $targetNumber);

        if ($result['success']) {
            set_flash("Pengumuman sistem '{$title}' berhasil diforward ke WhatsApp (" . ($result['target'] ?? '') . ")!", 'success');
        } else {
            set_flash("Gagal mengirim pengumuman WhatsApp: " . ($result['message'] ?? 'Periksa log atau kuota Fonnte'), 'danger');
        }

        redirect('admin/notifications');
    }

    /**
     * Kirim Pesan Uji Coba (Test Message) ke WhatsApp
     */
    public function test_fonnte(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/notifications');
            return;
        }

        if (!$this->validateCsrf()) return;

        $target = trim($_POST['target_phone'] ?? '');
        $fonnteService = new FonnteService();
        $result = $fonnteService->sendTestNotification(!empty($target) ? $target : null);

        if ($result['success']) {
            set_flash("Uji coba pesan berhasil terkirim ke antrean WhatsApp Fonnte untuk nomor: " . ($result['target'] ?? '') . "!", 'success');
        } else {
            set_flash("Uji coba pesan gagal: " . ($result['message'] ?? 'Periksa koneksi atau API Key Fonnte.'), 'danger');
        }

        redirect('admin/notifications');
    }

    /**
     * Endpoint AJAX: Memeriksa live status device WhatsApp di Fonnte
     */
    public function fetch_fonnte_device(): void {
        $fonnteService = new FonnteService();
        $status = $fonnteService->getDeviceStatus();
        $this->json($status);
    }

    /**
     * Bersihkan Riwayat Log Notifikasi
     */
    public function clear_notification_logs(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/notifications');
            return;
        }

        if (!$this->validateCsrf()) return;

        try {
            Database::getInstance()->query("TRUNCATE TABLE `notification_logs`");
            Database::getInstance()->execute();
            set_flash('Seluruh riwayat log notifikasi WhatsApp berhasil dibersihkan.', 'success');
        } catch (Throwable $e) {
            set_flash('Gagal membersihkan riwayat log notifikasi.', 'danger');
        }

        redirect('admin/notifications');
    }

    /**
     * Simpan Perubahan Konfigurasi API AI Key & Model
     */
    public function update_settings(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/settings');
            return;
        }

        if (!$this->validateCsrf()) return;

        $provider = sanitize(strtolower(trim($_POST['ai_provider'] ?? 'gemini')));
        if (!in_array($provider, ['gemini', 'openai'], true)) {
            $provider = 'gemini';
        }

        $apiKey = trim($_POST['ai_api_key'] ?? '');
        $selectedModel = sanitize(trim($_POST['ai_model'] ?? ''));
        $customModel = sanitize(trim($_POST['ai_model_custom'] ?? ''));

        // Jika user memilih custom model atau menginput model kustom
        $finalModel = (!empty($customModel) && ($selectedModel === 'custom' || empty($selectedModel))) 
            ? $customModel 
            : $selectedModel;

        if (empty($finalModel)) {
            $finalModel = ($provider === 'gemini') ? 'gemini-2.5-flash' : 'gpt-4o-mini';
        }

        $timeout = max(5, min(120, (int)($_POST['ai_timeout'] ?? 30)));
        $maxPromptLength = max(500, min(10000, (int)($_POST['ai_max_prompt_length'] ?? 2000)));

        // Simpan ke Database
        $this->settingModel->set('ai_provider', $provider, 'Penyedia API AI (gemini/openai)');
        $this->settingModel->set('ai_api_key', $apiKey, 'API Key Penyedia AI untuk Chatbot');
        $this->settingModel->set('ai_model', $finalModel, 'Model AI Aktif');
        $this->settingModel->set('ai_timeout', (string)$timeout, 'Batas Waktu Timeout Request AI (detik)');
        $this->settingModel->set('ai_max_prompt_length', (string)$maxPromptLength, 'Batas Maksimal Karakter Prompt AI');

        // Sinkronisasi ke file .env jika ada
        Setting::syncToEnvFile([
            'AI_PROVIDER' => $provider,
            'AI_API_KEY' => $apiKey,
            'AI_MODEL' => $finalModel,
            'AI_TIMEOUT' => (string)$timeout,
            'AI_MAX_PROMPT_LENGTH' => (string)$maxPromptLength
        ]);

        $statusMsg = !empty($apiKey) 
            ? "Konfigurasi AI Chatbot berhasil disimpan! Provider: " . strtoupper($provider) . " | Model aktif: '{$finalModel}'."
            : "Konfigurasi disimpan. Peringatan: API Key masih kosong, chatbot akan menggunakan mode fallback lokal.";

        set_flash($statusMsg, 'success');
        redirect('admin/settings');
    }

    /**
     * Endpoint AJAX: Mengambil daftar model yang tersedia untuk API Key yang diinput
     * Route: POST /admin/fetch_models
     */
    public function fetch_models(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Metode request tidak diizinkan.'], 405);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $apiKey = trim($input['api_key'] ?? $input['apiKey'] ?? '');
        // Jika tidak diinput, gunakan API key yang tersimpan di database/.env
        if (empty($apiKey)) {
            $apiKey = (string)app_setting('ai_api_key', defined('AI_API_KEY') ? AI_API_KEY : '');
        }

        $provider = strtolower(trim($input['provider'] ?? 'gemini'));
        if (empty($provider)) {
            $provider = (string)app_setting('ai_provider', 'gemini');
        }

        $aiService = new AiService();
        $result = $aiService->fetchAvailableModels($apiKey, $provider);
        $result['current_model'] = (string)app_setting('ai_model', defined('AI_MODEL') ? AI_MODEL : 'gemini-3.5-flash-lite');

        $this->json($result);
    }

    /**
     * Endpoint AJAX: Uji Koneksi API Key & Model secara langsung
     * Route: POST /admin/test_ai_connection
     */
    public function test_ai_connection(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Metode request tidak diizinkan.'], 405);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $apiKey = trim($input['api_key'] ?? $input['apiKey'] ?? '');
        if (empty($apiKey)) {
            $apiKey = (string)app_setting('ai_api_key', defined('AI_API_KEY') ? AI_API_KEY : '');
        }

        if (empty($apiKey)) {
            $this->json([
                'success' => false,
                'error' => 'API Key belum diisi. Masukkan API Key terlebih dahulu untuk menguji koneksi.'
            ], 422);
            return;
        }

        $provider = strtolower(trim($input['provider'] ?? 'gemini'));
        $model = trim($input['model'] ?? '');
        if (empty($model)) {
            $model = (string)app_setting('ai_model', 'gemini-2.5-flash');
        }

        $customPrompt = !empty($input['prompt']) ? trim($input['prompt']) : null;

        $aiService = new AiService();
        $result = $aiService->testConnection($apiKey, $provider, $model, $customPrompt);

        $this->json($result);
    }

    /**
     * Mengubah role pengguna (User <-> Admin)
     *
     * @param int|string|null $userId
     */
    public function change_role($userId = null): void {
        if (!$userId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users');
        }
        if (!$this->validateCsrf()) return;

        $userId = (int)$userId;
        $currentUser = Session::user();

        // Keamanan: Cegah admin mendegradasi akunnya sendiri
        if ($userId === (int)$currentUser['id']) {
            set_flash('Tindakan ditolak. Anda tidak dapat mengubah role akun administrator Anda sendiri.', 'warning');
            redirect('admin/users');
            return;
        }

        $targetUser = $this->userModel->find($userId);
        if (!$targetUser) {
            set_flash('Pengguna tidak ditemukan.', 'danger');
            redirect('admin/users');
            return;
        }

        $newRole = sanitize($_POST['role'] ?? '');
        if (!in_array($newRole, ['user', 'admin'])) {
            $newRole = ($targetUser['role'] === 'admin') ? 'user' : 'admin';
        }

        $updated = $this->userModel->changeRole($userId, $newRole);
        if ($updated) {
            $roleLabel = ($newRole === 'admin') ? 'Administrator' : 'Penulis Biasa';
            set_flash("Role akun '{$targetUser['name']}' berhasil diubah menjadi: {$roleLabel}.", 'success');
        } else {
            set_flash('Gagal mengubah role pengguna.', 'danger');
        }

        $this->redirectBackOr('admin/users');
    }

    /**
     * Menghapus akun pengguna dari sistem
     *
     * @param int|string|null $userId
     */
    public function delete_user($userId = null): void {
        if (!$userId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users');
        }
        if (!$this->validateCsrf()) return;

        $userId = (int)$userId;
        $currentUser = Session::user();

        // Keamanan: Cegah admin menghapus akunnya sendiri
        if ($userId === (int)$currentUser['id']) {
            set_flash('Tindakan ditolak. Anda tidak dapat menghapus akun Anda sendiri saat sedang login.', 'danger');
            redirect('admin/users');
            return;
        }

        $targetUser = $this->userModel->find($userId);
        if (!$targetUser) {
            set_flash('Pengguna tidak ditemukan.', 'danger');
            redirect('admin/users');
            return;
        }

        $deleted = $this->userModel->delete($userId);
        if ($deleted) {
            set_flash("Pengguna '{$targetUser['name']}' (#{$userId}) beserta seluruh naskah dan bab terkait berhasil dihapus dari sistem.", 'success');
        } else {
            set_flash('Gagal menghapus pengguna.', 'danger');
        }

        $this->redirectBackOr('admin/users');
    }

    /**
     * Moderasi status publikasi novel (Draft <-> Published)
     *
     * @param int|string|null $novelId
     */
    public function toggle_novel_status($novelId = null): void {
        if (!$novelId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/novels');
        }
        if (!$this->validateCsrf()) return;

        $novelId = (int)$novelId;
        $novel = $this->novelModel->find($novelId);

        if (!$novel) {
            set_flash('Novel tidak ditemukan.', 'danger');
            redirect('admin/novels');
            return;
        }

        $newStatus = ($novel['status'] === 'published') ? 'draft' : 'published';
        $this->novelModel->changeStatus($novelId, $newStatus);
        
        if ($newStatus === 'published') {
            try {
                $author = $this->userModel->find((int)$novel['user_id']);
                $fonnteService = new FonnteService();
                $fonnteService->sendNewNovelNotification($novel, $author ?: []);
            } catch (Throwable $e) {
                error_log("Fonnte error on admin publish novel: " . $e->getMessage());
            }
        }

        $badge = ($newStatus === 'published') ? 'Published (Dapat dibaca publik)' : 'Draft (Disembunyikan dari publik)';
        set_flash("Status publikasi novel '{$novel['title']}' diubah menjadi: {$badge}.", 'info');

        $this->redirectBackOr('admin/novels');
    }

    /**
     * Menghapus novel yang melanggar aturan (Moderasi Konten Novel)
     *
     * @param int|string|null $novelId
     */
    public function delete_novel($novelId = null): void {
        if (!$novelId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/novels');
        }
        if (!$this->validateCsrf()) return;

        $novelId = (int)$novelId;
        $novel = $this->novelModel->find($novelId);

        if (!$novel) {
            set_flash('Novel tidak ditemukan.', 'danger');
            redirect('admin/novels');
            return;
        }

        $title = $novel['title'];
        $deleted = $this->novelModel->adminDeleteNovel($novelId);

        if ($deleted) {
            set_flash("Novel '{$title}' yang melanggar aturan beserta seluruh babnya berhasil dihapus permanen oleh Administrator.", 'success');
        } else {
            set_flash('Gagal menghapus novel.', 'danger');
        }

        $this->redirectBackOr('admin/novels');
    }

    /**
     * Moderasi status publikasi bab (Draft <-> Published)
     *
     * @param int|string|null $chapterId
     */
    public function toggle_chapter_status($chapterId = null): void {
        if (!$chapterId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/content');
        }
        if (!$this->validateCsrf()) return;

        $chapterId = (int)$chapterId;
        $chapter = $this->chapterModel->find($chapterId);

        if (!$chapter) {
            set_flash('Bab naskah tidak ditemukan.', 'danger');
            redirect('admin/content');
            return;
        }

        $newStatus = $this->chapterModel->adminToggleStatus($chapterId);

        if ($newStatus === 'published') {
            try {
                $novel = $this->novelModel->find((int)$chapter['novel_id']);
                $author = $novel ? $this->userModel->find((int)$novel['user_id']) : null;
                $fonnteService = new FonnteService();
                $fonnteService->sendNewChapterNotification($chapter, $novel ?: [], $author ?: []);
            } catch (Throwable $e) {
                error_log("Fonnte error on admin publish chapter: " . $e->getMessage());
            }
        }

        $badge = ($newStatus === 'published') ? 'Published' : 'Draft';
        set_flash("Status Bab '{$chapter['title']}' diubah menjadi: {$badge}.", 'info');

        $this->redirectBackOr('admin/content');
    }

    /**
     * Menghapus bab naskah yang melanggar aturan (Moderasi Bab)
     *
     * @param int|string|null $chapterId
     */
    public function delete_chapter($chapterId = null): void {
        if (!$chapterId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/content');
        }
        if (!$this->validateCsrf()) return;

        $chapterId = (int)$chapterId;
        $chapter = $this->chapterModel->find($chapterId);

        if (!$chapter) {
            set_flash('Bab naskah tidak ditemukan.', 'danger');
            redirect('admin/content');
            return;
        }

        $chapterTitle = $chapter['title'];
        $deleted = $this->chapterModel->adminDeleteChapter($chapterId);

        if ($deleted) {
            set_flash("Bab naskah '{$chapterTitle}' yang melanggar aturan berhasil dihapus permanen oleh Administrator.", 'success');
        } else {
            set_flash('Gagal menghapus bab naskah.', 'danger');
        }

        $this->redirectBackOr('admin/content');
    }

    /**
     * Endpoint API JSON untuk Sinkronisasi Statistik Dashboard Admin secara Real-Time
     * Route: GET /admin/stats_api
     */
    public function stats_api(): void {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $userBreakdown = $this->userModel->getUsersBreakdown();
        $novelStats = $this->novelModel->getGlobalStats();
        $chapterStats = $this->chapterModel->getGlobalStats();
        $totalAiLogs = $this->aiHistoryModel->count();
        $notificationStats = $this->notificationLogModel->getSummaryStats();

        $this->json([
            'success' => true,
            'stats' => [
                'totalUsers' => $userBreakdown['total'],
                'totalAdmins' => $userBreakdown['admin'],
                'totalAuthors' => $userBreakdown['penulis'],
                'totalReaders' => $userBreakdown['pembaca'],
                'totalNovels' => $novelStats['total_novels'],
                'publishedNovels' => $novelStats['published_novels'],
                'draftNovels' => $novelStats['draft_novels'],
                'totalChapters' => $chapterStats['total_chapters'],
                'publishedChapters' => $chapterStats['published_chapters'],
                'draftChapters' => $chapterStats['draft_chapters'],
                'totalAiLogs' => $totalAiLogs,
                'totalNotifications' => $notificationStats['total'],
                'successNotifications' => $notificationStats['success_count'],
            ],
            'timestamp' => time()
        ]);
    }

    /**
     * Helper redirect kembali ke referer yang valid atau URL default
     *
     * @param string $fallback
     */
    private function redirectBackOr(string $fallback = 'admin'): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer) && str_contains($referer, 'admin')) {
            header("Location: " . $referer);
            exit;
        }
        redirect($fallback);
    }
}
