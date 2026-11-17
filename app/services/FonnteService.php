<?php
/**
 * karyaNovel - FonnteService
 * Layanan integrasi WhatsApp Gateway resmi karyaNovel menggunakan API Fonnte.
 * Mengelola pengiriman notifikasi otomatis untuk:
 * 1. Update Cerita Baru (New Novel)
 * 2. Bab / Chapter Baru (New Chapter)
 * 3. Notifikasi Pengumuman Sistem / Broadcast
 * 4. Uji Coba Koneksi (Ping Test) & Pengecekan Device WhatsApp
 */

if (!function_exists('base_url') && defined('APPROOT')) {
    require_once APPROOT . '/helpers/helpers.php';
}

if (!class_exists('Setting') && defined('APPROOT') && file_exists(APPROOT . '/models/Setting.php')) {
    require_once APPROOT . '/models/Setting.php';
}

if (!class_exists('NotificationLog') && defined('APPROOT') && file_exists(APPROOT . '/models/NotificationLog.php')) {
    require_once APPROOT . '/models/NotificationLog.php';
}

class FonnteService {
    private string $apiKey;
    private string $defaultTarget;
    private int $timeout = 15;
    private ?NotificationLog $logModel = null;

    private const API_SEND_URL = 'https://api.fonnte.com/send';
    private const API_DEVICE_URL = 'https://api.fonnte.com/device';

    public function __construct(?string $apiKey = null) {
        // Ambil API key dari parameter, DB Setting, atau fallback ke konstanta/env
        if ($apiKey !== null && trim($apiKey) !== '') {
            $this->apiKey = trim($apiKey);
        } else {
            $this->apiKey = (string)Setting::get('fonnte_api_key', defined('FONNTE_API_KEY') ? FONNTE_API_KEY : '');
        }

        // Ambil nomor target default untuk forwarding
        $this->defaultTarget = (string)Setting::get('fonnte_target_phone', defined('FONNTE_TARGET_PHONE') ? FONNTE_TARGET_PHONE : '089634700208');

        // Inisialisasi model log jika class tersedia
        if (class_exists('NotificationLog')) {
            $this->logModel = new NotificationLog();
        }
    }

    /**
     * Memeriksa apakah fitur notifikasi WhatsApp aktif secara global
     */
    public function isEnabled(): bool {
        $val = Setting::get('fonnte_enabled', defined('FONNTE_ENABLED') ? FONNTE_ENABLED : true);
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Memeriksa apakah notifikasi cerita baru aktif
     */
    public function isNovelNotifyEnabled(): bool {
        if (!$this->isEnabled()) return false;
        $val = Setting::get('fonnte_notify_new_novel', defined('FONNTE_NOTIFY_NEW_NOVEL') ? FONNTE_NOTIFY_NEW_NOVEL : true);
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Memeriksa apakah notifikasi chapter baru aktif
     */
    public function isChapterNotifyEnabled(): bool {
        if (!$this->isEnabled()) return false;
        $val = Setting::get('fonnte_notify_new_chapter', defined('FONNTE_NOTIFY_NEW_CHAPTER') ? FONNTE_NOTIFY_NEW_CHAPTER : true);
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Memeriksa apakah notifikasi sistem aktif
     */
    public function isSystemNotifyEnabled(): bool {
        if (!$this->isEnabled()) return false;
        $val = Setting::get('fonnte_notify_system', defined('FONNTE_NOTIFY_SYSTEM') ? FONNTE_NOTIFY_SYSTEM : true);
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Mendapatkan nomor target default
     */
    public function getDefaultTarget(): string {
        return $this->defaultTarget;
    }

    /**
     * Mendapatkan API Key yang sedang aktif
     */
    public function getApiKey(): string {
        return $this->apiKey;
    }

    /**
     * Memeriksa status device WhatsApp yang terhubung di Fonnte
     *
     * @return array
     */
    public function getDeviceStatus(): array {
        if (empty($this->apiKey)) {
            return [
                'status' => false,
                'message' => 'API Key Fonnte belum dikonfigurasi.',
                'device_status' => 'disconnected'
            ];
        }

        $response = $this->callApi(self::API_DEVICE_URL, [], 'POST');
        if (!$response['success']) {
            return [
                'status' => false,
                'message' => $response['message'],
                'device_status' => 'error'
            ];
        }

        $data = $response['data'];
        return [
            'status' => true,
            'device' => $data['device'] ?? '-',
            'device_status' => $data['device_status'] ?? 'unknown',
            'name' => $data['name'] ?? '-',
            'package' => $data['package'] ?? 'Free',
            'quota' => $data['quota'] ?? 0,
            'expired' => $data['expired'] ?? '-',
            'raw' => $data
        ];
    }

    /**
     * Mengirim pesan teks / multimedia WhatsApp ke satu atau banyak nomor target
     *
     * @param string|array $target Nomor target (misal '08123456789' atau '081...,082...')
     * @param string $message Isi pesan
     * @param string $type Jenis log notifikasi ('new_novel', 'new_chapter', 'system', 'test')
     * @param string|null $title Judul notifikasi untuk pencatatan log
     * @param array $options Opsi tambahan (e.g. ['url' => 'http...', 'countryCode' => '62'])
     * @return array
     */
    public function sendMessage($target, string $message, string $type = 'system', ?string $title = null, array $options = []): array {
        // Tentukan nomor penerima (gunakan default jika kosong)
        if (is_array($target)) {
            $targetStr = implode(',', array_filter(array_map('trim', $target)));
        } else {
            $targetStr = trim((string)$target);
        }

        if (empty($targetStr)) {
            $targetStr = $this->defaultTarget;
        }

        if (empty($targetStr)) {
            $errorMsg = 'Nomor tujuan / Target WhatsApp tidak boleh kosong.';
            $this->logMessage($type, '-', $title, $message, 'failed', json_encode(['error' => $errorMsg]));
            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }

        if (empty($this->apiKey)) {
            $errorMsg = 'API Key Fonnte belum dikonfigurasi.';
            $this->logMessage($type, $targetStr, $title, $message, 'failed', json_encode(['error' => $errorMsg]));
            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }

        // Siapkan payload POST Fonnte
        $payload = [
            'target' => $targetStr,
            'message' => $message,
            'countryCode' => $options['countryCode'] ?? '62'
        ];

        // Lampirkan gambar jika disediakan dan valid
        if (!empty($options['url'])) {
            $payload['url'] = $options['url'];
        }

        // Eksekusi HTTP Request ke Fonnte
        $apiResult = $this->callApi(self::API_SEND_URL, $payload, 'POST');
        $isSuccess = $apiResult['success'];

        // Jika Fonnte menolak karena URL lampiran tidak publik (misal localhost/IP lokal), kirim ulang otomatis tanpa parameter url
        if (!$isSuccess && !empty($payload['url'])) {
            $errDetail = strtolower($apiResult['message'] ?? '');
            if (str_contains($errDetail, 'url') || str_contains($errDetail, 'public')) {
                unset($payload['url']);
                $retryResult = $this->callApi(self::API_SEND_URL, $payload, 'POST');
                if ($retryResult['success']) {
                    $isSuccess = true;
                    $apiResult = $retryResult;
                }
            }
        }

        $rawResponseStr = json_encode($apiResult['data'] ?? ['message' => $apiResult['message']]);

        // Simpan ke notification_logs
        $this->logMessage($type, $targetStr, $title, $message, $isSuccess ? 'success' : 'failed', $rawResponseStr);

        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Pesan berhasil diforward ke WhatsApp.' : ($apiResult['message'] ?? 'Gagal mengirim pesan WhatsApp.'),
            'target' => $targetStr,
            'data' => $apiResult['data'] ?? null
        ];
    }

    /**
     * Forward notifikasi publikasi cerita / novel baru
     *
     * @param array $novel Data novel (title, synopsis, genre, cover, id, dll)
     * @param array|null $author Data penulis (name, email)
     * @param string|null $target Target opsional
     * @return array
     */
    public function sendNewNovelNotification(array $novel, ?array $author = null, ?string $target = null): array {
        if (!$this->isNovelNotifyEnabled()) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'Notifikasi novel baru dinonaktifkan di pengaturan.'
            ];
        }

        $title = $novel['title'] ?? 'Cerita Tanpa Judul';
        $genre = $novel['genre'] ?? 'Umum';
        $authorName = $author['name'] ?? 'Penulis karyaNovel';
        $novelId = $novel['id'] ?? '';
        $novelUrl = base_url('novels/' . $novelId);

        // Sinopsis singkat (potong jika terlalu panjang)
        $synopsisRaw = strip_tags($novel['synopsis'] ?? '');
        $synopsis = mb_strlen($synopsisRaw) > 280 ? mb_substr($synopsisRaw, 0, 277) . '...' : $synopsisRaw;

        // Susun template WhatsApp yang rapi dan memikat
        $message = "📚 *KARYANOVEL - UPDATE CERITA BARU!* 📚\n\n";
        $message .= "Halo Sahabat Pembaca! Cerita baru yang memikat baru saja dipublikasikan di *karyaNovel*:\n\n";
        $message .= "📖 *Judul:* *{$title}*\n";
        $message .= "✍️ *Penulis:* {$authorName}\n";
        $message .= "🏷️ *Genre:* {$genre}\n\n";
        if (!empty($synopsis)) {
            $message .= "📝 *Sinopsis:*\n_{$synopsis}_\n\n";
        }
        $message .= "🔗 *Mulai Membaca Sekarang:*\n{$novelUrl}\n\n";
        $message .= "────────────────────\n";
        $message .= "_Notifikasi Otomatis karyaNovel WhatsApp Gateway_";

        $options = [];
        // Jika novel memiliki cover yang dapat diakses publik via URL
        if (!empty($novel['cover'])) {
            $coverUrl = filter_var($novel['cover'], FILTER_VALIDATE_URL) 
                ? $novel['cover'] 
                : asset(ltrim(str_replace('public/', '', $novel['cover']), '/'));
            
            // Fonnte memerlukan URL gambar yang dapat diakses dari internet publik (bukan localhost)
            $host = parse_url($coverUrl, PHP_URL_HOST);
            $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0']) 
                || (is_string($host) && (str_ends_with($host, '.test') || str_ends_with($host, '.local')));

            if (filter_var($coverUrl, FILTER_VALIDATE_URL) && !$isLocalHost) {
                $options['url'] = $coverUrl;
            }
        }

        return $this->sendMessage($target, $message, 'new_novel', "Novel Baru: {$title}", $options);
    }

    /**
     * Forward notifikasi rilis bab / chapter baru
     *
     * @param array $chapter Data bab (title, chapter_order, id, novel_id)
     * @param array $novel Data novel induk (title, genre, id)
     * @param array|null $author Data penulis (name)
     * @param string|null $target Target opsional
     * @return array
     */
    public function sendNewChapterNotification(array $chapter, array $novel, ?array $author = null, ?string $target = null): array {
        if (!$this->isChapterNotifyEnabled()) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'Notifikasi chapter baru dinonaktifkan di pengaturan.'
            ];
        }

        $novelTitle = $novel['title'] ?? 'Novel';
        $chapterTitle = $chapter['title'] ?? 'Bab Baru';
        $order = $chapter['chapter_order'] ?? 1;
        $authorName = $author['name'] ?? 'Penulis';
        $genre = $novel['genre'] ?? '';
        $chapterId = $chapter['id'] ?? '';
        $readUrl = base_url('chapter/read/' . $chapterId);

        // Template WhatsApp untuk Bab Baru
        $message = "✨ *KARYANOVEL - BAB BARU TELAH RILIS!* ✨\n\n";
        $message .= "Kabar gembira! Lanjutan bab naskah favoritmu kini sudah tersedia:\n\n";
        $message .= "📖 *Novel:* *{$novelTitle}*\n";
        $message .= "📑 *Bab:* *Bab {$order}: {$chapterTitle}*\n";
        $message .= "✍️ *Penulis:* {$authorName}\n";
        if (!empty($genre)) {
            $message .= "🏷️ *Genre:* {$genre}\n";
        }
        $message .= "\n🔗 *Baca Bab Ini Sekarang:*\n{$readUrl}\n\n";
        $message .= "────────────────────\n";
        $message .= "_Notifikasi Otomatis karyaNovel WhatsApp Gateway_";

        return $this->sendMessage($target, $message, 'new_chapter', "Bab Baru: {$novelTitle} (Bab {$order})");
    }

    /**
     * Forward notifikasi pengumuman sistem / broadcast
     *
     * @param string $title Judul pengumuman
     * @param string $content Isi pesan pengumuman sistem
     * @param string|null $target Target opsional
     * @return array
     */
    public function sendSystemNotification(string $title, string $content, ?string $target = null): array {
        if (!$this->isSystemNotifyEnabled()) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'Notifikasi sistem dinonaktifkan di pengaturan.'
            ];
        }

        $currentTime = date('d-m-Y H:i') . ' WIB';
        $portalUrl = base_url();

        // Template WhatsApp Notifikasi Sistem
        $message = "🔔 *PENGUMUMAN SISTEM KARYANOVEL* 🔔\n\n";
        $message .= "📢 *{$title}*\n\n";
        $message .= "{$content}\n\n";
        $message .= "🕒 *Waktu:* {$currentTime}\n";
        $message .= "🌐 *Portal:* {$portalUrl}\n\n";
        $message .= "────────────────────\n";
        $message .= "_Pesan Resmi Tim Administrator karyaNovel_";

        return $this->sendMessage($target, $message, 'system', $title);
    }

    /**
     * Kirim pesan uji coba (Test Ping) untuk verifikasi integrasi Fonnte
     *
     * @param string|null $target
     * @return array
     */
    public function sendTestNotification(?string $target = null): array {
        $currentTime = date('d-m-Y H:i:s') . ' WIB';
        $appName = defined('APP_NAME') ? APP_NAME : 'karyaNovel';

        $message = "🧪 *UJI COBA NOTIFIKASI WHATSAPP - {$appName}* 🧪\n\n";
        $message .= "Halo! Pesan ini adalah uji coba konektivitas resmi dari *{$appName}* via layanan WhatsApp Gateway *Fonnte*.\n\n";
        $message .= "✅ *Status:* Terkoneksi & Berhasil Terkirim\n";
        $message .= "🕒 *Waktu Uji Coba:* {$currentTime}\n";
        $message .= "🔑 *API Gateway:* Fonnte Official Service\n\n";
        $message .= "Jika Anda menerima pesan ini, fitur forward notifikasi cerita baru, chapter baru, dan pengumuman sistem sudah siap digunakan!\n\n";
        $message .= "────────────────────\n";
        $message .= "_{$appName} Notification Assistant_";

        return $this->sendMessage($target, $message, 'test', "Uji Coba Integrasi Fonnte ({$currentTime})");
    }

    /**
     * Mengirimkan kode OTP verifikasi pendaftaran akun via WhatsApp Fonnte
     *
     * @param string $targetPhone Nomor WhatsApp tujuan
     * @param string $otpCode Kode OTP numerik
     * @param string $recipientName Nama pengguna
     * @return array
     */
    public function sendOtpVerification(string $targetPhone, string $otpCode, string $recipientName = 'Pengguna'): array {
        $appName = defined('APP_NAME') ? APP_NAME : 'karyaNovel';
        $message = "🔐 *KODE VERIFIKASI OTP {$appName}*\n\n" .
                   "Halo *{$recipientName}*,\n\n" .
                   "Terima kasih telah mendaftar di *{$appName}*! Berikut adalah kode OTP verifikasi pendaftaran akun Anda:\n\n" .
                   "🔢 *{$otpCode}*\n\n" .
                   "⏰ *Penting:* Kode ini berlaku selama *10 menit*. Jangan berikan kode ini kepada siapa pun demi keamanan akun Anda.\n\n" .
                   "Salam hangat,\n" .
                   "Tim {$appName} ✨";

        return $this->sendMessage($targetPhone, $message, 'otp', "Kode OTP Verifikasi: {$recipientName}");
    }

    /**
     * Internal: Eksekusi cURL HTTP Request ke Fonnte API
     *
     * @param string $url
     * @param array $postData
     * @param string $method
     * @return array
     */
    private function callApi(string $url, array $postData = [], string $method = 'POST'): array {
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'message' => 'Ekstensi PHP cURL tidak aktif pada server.',
                'data' => null
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $headers = [
            'Authorization: ' . $this->apiKey
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($postData)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $rawResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'message' => 'Koneksi cURL ke Fonnte gagal: ' . $curlError,
                'data' => null
            ];
        }

        $decoded = json_decode($rawResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Respon Fonnte bukan JSON valid (HTTP ' . $httpCode . '): ' . substr($rawResponse, 0, 150),
                'data' => null
            ];
        }

        // Fonnte standard response format: status (true/false)
        $isFonnteOk = isset($decoded['status']) && ($decoded['status'] === true || $decoded['status'] === 'true');
        $detailMsg = $decoded['detail'] ?? ($decoded['reason'] ?? ($decoded['message'] ?? ''));

        if (!$isFonnteOk) {
            return [
                'success' => false,
                'message' => !empty($detailMsg) ? $detailMsg : 'Fonnte mengembalikan status gagal.',
                'data' => $decoded
            ];
        }

        return [
            'success' => true,
            'message' => !empty($detailMsg) ? $detailMsg : 'Sukses',
            'data' => $decoded
        ];
    }

    /**
     * Internal: Catat riwayat log notifikasi
     */
    private function logMessage(string $type, string $target, ?string $title, string $message, string $status, ?string $response): void {
        if ($this->logModel) {
            try {
                $this->logModel->record($type, $target, $title, $message, $status, $response);
            } catch (Throwable $e) {
                error_log("Error recording notification log: " . $e->getMessage());
            }
        }
    }
}
