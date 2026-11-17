<?php
/**
 * karyaNovel - AiController
 * Mengatur integrasi AI Writing Assistant, validasi keamanan, otorisasi, dan pencatatan riwayat prompt ke database ai_histories
 */

require_once APPROOT . '/services/AiService.php';

class AiController extends Controller {
    private AiHistory $aiHistoryModel;
    private AiService $aiService;

    public function __construct(?AiService $aiService = null) {
        $this->aiHistoryModel = $this->model('AiHistory');
        $this->aiService = $aiService ?? new AiService();
    }

    /**
     * Endpoint API JSON untuk generate rekomendasi/asisten kepenulisan AI
     * 6 Fitur Utama:
     * 1. idea (Generate Ide)
     * 2. outline (Generate Outline)
     * 3. character (Generate Character)
     * 4. continue (Continue Story)
     * 5. improve / polish (Improve Writing)
     * 6. suggestions / suggestion (Give Suggestions)
     */
    public function generate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Metode request tidak diizinkan.'], 405);
            return;
        }

        // Keamanan: Wajib Autentikasi Backend (Tolak Tamu / Tanpa Sesi Login)
        if (!Session::isLoggedIn()) {
            $this->json([
                'success' => false,
                'message' => 'Sesi login telah berakhir atau belum masuk. Silakan login terlebih dahulu.'
            ], 401);
            return;
        }

        $userId = (int)Session::userId();

        // Rate Limiting Berbasis Sesi (Maksimal 30 request / menit per user)
        $windowStart = Session::get('ai_window_start', time());
        $requestCount = Session::get('ai_request_count', 0);

        if (time() - $windowStart > 60) {
            Session::set('ai_window_start', time());
            Session::set('ai_request_count', 1);
        } else {
            if ($requestCount >= 30) {
                $this->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan bantuan AI dalam waktu singkat. Mohon tunggu beberapa saat sebelum mencoba kembali.'
                ], 429);
                return;
            }
            Session::set('ai_request_count', $requestCount + 1);
        }

        // Ambil payload JSON atau POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $prompt = trim($input['prompt'] ?? '');
        $type = strtolower(trim($input['type'] ?? 'continue'));
        $context = trim($input['context'] ?? '');
        $chapterId = !empty($input['chapter_id']) && is_numeric($input['chapter_id']) ? (int)$input['chapter_id'] : null;
        $novelId = !empty($input['novel_id']) && is_numeric($input['novel_id']) ? (int)$input['novel_id'] : null;

        // Mendukung simulasi testing provider (mock) jika dikirimkan oleh unit test
        if (!empty($input['mock_provider'])) {
            $mockService = new AiService(['provider' => $input['mock_provider']]);
        } else {
            $mockService = $this->aiService;
        }

        // Validasi input melalui AIService independen
        $validationError = $mockService->validate($type, $prompt, $context);
        if ($validationError !== null) {
            $this->json([
                'success' => false,
                'message' => $validationError
            ], 422);
            return;
        }

        // Otorisasi Kepemilikan Bab / Novel (User B dilarang mengakses data User A)
        $metadata = [
            'genre' => 'Fiksi Populer',
            'novel_title' => 'Naskah'
        ];

        if ($chapterId) {
            $chapterModel = $this->model('Chapter');
            // Pastikan bab tersebut milik pengguna yang sedang login
            $chapterInfo = $chapterModel->findAuthorChapter($chapterId, $userId);
            if (!$chapterInfo) {
                $this->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk menggunakan bab novel ini sebagai konteks AI.'
                ], 403);
                return;
            }

            // Ambil info judul & genre dari bab milik user sendiri
            $fullChapter = $chapterModel->getWithNovel($chapterId);
            if ($fullChapter) {
                if (!empty($fullChapter['novel_genre'])) {
                    $metadata['genre'] = $fullChapter['novel_genre'];
                }
                if (!empty($fullChapter['novel_title'])) {
                    $metadata['novel_title'] = $fullChapter['novel_title'];
                }
            }
        } elseif ($novelId) {
            $novelModel = $this->model('Novel');
            $novelInfo = $novelModel->getNovelById($novelId);
            if (!$novelInfo || (int)$novelInfo['user_id'] !== $userId) {
                $this->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses data novel ini.'
                ], 403);
                return;
            }

            if (!empty($novelInfo['genre'])) {
                $metadata['genre'] = $novelInfo['genre'];
            }
            if (!empty($novelInfo['title'])) {
                $metadata['novel_title'] = $novelInfo['title'];
            }
        }

        // Jalankan generasi teks AI
        $result = $mockService->generate($type, $prompt, $context, $metadata);

        if (!$result['success']) {
            $httpCode = $result['code'] ?? 500;
            $this->json([
                'success' => false,
                'message' => $result['error'] ?? 'Terjadi kendala saat memproses permintaan AI. Silakan coba kembali.'
            ], $httpCode);
            return;
        }

        $generatedResponse = $result['response'];

        // Catat interaksi prompt & response ke riwayat database ai_histories
        $loggedPrompt = !empty($prompt) ? $prompt : "Bantuan AI ({$result['type']})";
        $historyId = $this->aiHistoryModel->log(
            $userId,
            $chapterId,
            $loggedPrompt,
            $generatedResponse
        );

        // Kembalikan response JSON bersih (keamanan: jangan mengekspos API key atau secret)
        $this->json([
            'success' => true,
            'data' => [
                'history_id' => $historyId,
                'type' => $result['type'],
                'prompt_type' => $result['type'], // untuk backwards compatibility dengan editor bab
                'prompt' => $prompt,
                'response' => $generatedResponse,
                'provider' => $result['provider'],
                'model' => $result['model'],
                'created_at' => date('H:i:s')
            ]
        ]);
    }

    /**
     * Endpoint API JSON untuk mengambil riwayat prompt AI (Hanya milik user aktif)
     */
    public function history(): void {
        // Wajib Login
        if (!Session::isLoggedIn()) {
            $this->json([
                'success' => false,
                'message' => 'Sesi login telah berakhir. Silakan login terlebih dahulu.'
            ], 401);
            return;
        }

        $userId = (int)Session::userId();
        $chapterId = isset($_GET['chapter_id']) && is_numeric($_GET['chapter_id']) ? (int)$_GET['chapter_id'] : null;

        if ($chapterId) {
            // Verifikasi bahwa bab tersebut milik user sendiri
            $chapterModel = $this->model('Chapter');
            $chapter = $chapterModel->findAuthorChapter($chapterId, $userId);
            if (!$chapter) {
                $this->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk melihat riwayat bab ini.'
                ], 403);
                return;
            }
            $histories = $this->aiHistoryModel->getByChapter($chapterId, $userId);
        } else {
            $histories = $this->aiHistoryModel->getByUser($userId, 20);
        }

        $this->json([
            'success' => true,
            'data' => $histories
        ]);
    }
}
