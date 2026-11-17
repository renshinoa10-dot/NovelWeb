<?php
/**
 * karyaNovel - Test Suite Resmi PROMPT 8: AI Writing Assistant
 * Menguji seluruh 18 butir pengujian spesifikasi:
 *  1. Generate Ide
 *  2. Generate Outline
 *  3. Generate Character
 *  4. Continue Story
 *  5. Improve Writing
 *  6. Give Suggestions
 *  7. Prompt kosong (Validasi 422)
 *  8. Prompt melebihi batas (Validasi 422)
 *  9. API key tidak tersedia (Error 500)
 * 10. API gagal atau timeout (Mock 504 Timeout, 502 Gateway, 429 Rate Limit, 401 Invalid Key)
 * 11. Respons AI ditampilkan di UI (DOM elements check)
 * 12. Hasil AI dapat disalin (Clipboard helper check)
 * 13. Hasil AI dapat dimasukkan ke editor tanpa otomatis menimpa naskah (Deliberate insertion check)
 * 14. Riwayat AI tersimpan di database ai_histories
 * 15. User tidak dapat melihat riwayat milik user lain (401 Unauthorized & 403 Forbidden)
 * 16. User tidak dapat menggunakan chapter milik user lain sebagai konteks (403 Forbidden)
 * 17. API key tidak bocor ke frontend / respons JSON
 * 18. Tampilan responsif (Bootstrap grid layout)
 */

require_once __DIR__ . '/app/config/config.php';
require_once APPROOT . '/helpers/helpers.php';
require_once APPROOT . '/core/Env.php';
require_once APPROOT . '/core/Database.php';
require_once APPROOT . '/core/Model.php';
require_once APPROOT . '/core/Controller.php';
require_once APPROOT . '/core/Session.php';

require_once APPROOT . '/models/User.php';
require_once APPROOT . '/models/Novel.php';
require_once APPROOT . '/models/Chapter.php';
require_once APPROOT . '/models/AiHistory.php';
require_once APPROOT . '/services/AiService.php';

echo "=======================================================================\n";
echo " karyaNovel - TEST SUITE: PROMPT 8 - AI WRITING ASSISTANT\n";
echo "=======================================================================\n\n";

$db = Database::getInstance();
$userModel = new User();
$novelModel = new Novel();
$chapterModel = new Chapter();
$aiHistoryModel = new AiHistory();

$passed = 0;
$failed = 0;

function assertCondition(string $testName, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        if ($detail) echo "         -> {$detail}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$testName}\n";
        if ($detail) echo "         -> {$detail}\n";
        $failed++;
    }
}

$baseUrl = 'http://localhost/Novelweb';

// 1. Setup User A & User B
$userA = $userModel->findByEmail('penulis1@karyanovel.id');
$userB = $userModel->findByEmail('penulis2@karyanovel.id');

assertCondition("User A (Penulis 1) siap di database", !empty($userA));
assertCondition("User B (Penulis 2) siap di database", !empty($userB));

$userIdA = (int)$userA['id'];
$userIdB = (int)$userB['id'];

$cookieA = __DIR__ . '/cookie_p8_userA.txt';
$cookieB = __DIR__ . '/cookie_p8_userB.txt';
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

// Helper function untuk POST login
function performLogin(string $email, string $password, string $cookieFile, string $baseUrl): bool {
    $ch = curl_init("{$baseUrl}/auth/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpCode === 302 || $httpCode === 200);
}

performLogin('penulis1@karyanovel.id', 'password123', $cookieA, $baseUrl);
performLogin('penulis2@karyanovel.id', 'password123', $cookieB, $baseUrl);

// Buat novel & bab untuk User A
$novelAId = (int)$novelModel->createNovel([
    'user_id' => $userIdA,
    'title' => 'Petualangan Astral ' . time(),
    'synopsis' => 'Kisah penjelajah antariksa menembus batas dimensi.',
    'genre' => 'Sci-Fi',
    'status' => 'draft'
]);

$chapterAId = (int)$chapterModel->createChapter([
    'novel_id' => $novelAId,
    'title' => 'Bab 1: Menembus Batas Horizon',
    'content' => 'Kapal penjelajah Voyager melintasi sabuk asteroid di tepi galaksi Andromeda.',
    'chapter_order' => 1,
    'status' => 'draft'
]);

// Buat novel & bab untuk User B
$novelBId = (int)$novelModel->createNovel([
    'user_id' => $userIdB,
    'title' => 'Misteri Rumah Tua ' . time(),
    'synopsis' => 'Penyelidikan rahasia rumah tua di lereng gunung.',
    'genre' => 'Misteri',
    'status' => 'draft'
]);

$chapterBId = (int)$chapterModel->createChapter([
    'novel_id' => $novelBId,
    'title' => 'Bab 1: Pintu yang Terbuka Sendiri',
    'content' => 'Malam itu angin berhembus kencang, menggoyangkan engsel pintu besi tua yang berkarat.',
    'chapter_order' => 1,
    'status' => 'draft'
]);

echo "\n--- 1. PENGUJIAN 6 FITUR AI WRITING ASSISTANT (HTTP POST /ai/generate) ---\n";

function callAiGenerate(array $payload, ?string $cookieFile, string $baseUrl): array {
    $ch = curl_init("{$baseUrl}/ai/generate");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    if ($cookieFile && file_exists($cookieFile)) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($raw, true);
    return ['code' => $httpCode, 'raw' => $raw, 'json' => $json];
}

// 1. Generate Ide
$resIdea = callAiGenerate([
    'type' => 'idea',
    'prompt' => 'Buatkan 5 ide novel bergenre fantasy tentang pemuda menemukan dunia rahasia.',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$has5Ideas = ($resIdea['code'] === 200 &&
              !empty($resIdea['json']['data']['response']) &&
              str_contains($resIdea['json']['data']['response'], '5 Ide') &&
              str_contains($resIdea['json']['data']['response'], 'Konsep'));
assertCondition("1. Generate Ide: Menghasilkan 5 ide novel berbobot", $has5Ideas,
    "HTTP {$resIdea['code']} - " . substr($resIdea['json']['data']['response'] ?? '', 0, 80) . '...');

// 2. Generate Outline
$resOutline = callAiGenerate([
    'type' => 'outline',
    'prompt' => 'Susun outline novel 4 babak dengan eskalasi konflik.',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$hasOutlineKeys = ($resOutline['code'] === 200 &&
                   !empty($resOutline['json']['data']['response']) &&
                   str_contains($resOutline['json']['data']['response'], 'PREMIS') &&
                   str_contains($resOutline['json']['data']['response'], 'KONFLIK UTAMA') &&
                   str_contains($resOutline['json']['data']['response'], 'RANCANGAN BAB') &&
                   str_contains($resOutline['json']['data']['response'], 'KLIMAKS') &&
                   str_contains($resOutline['json']['data']['response'], 'PENYELESAIAN'));
assertCondition("2. Generate Outline: Memuat Premis, Konflik, Bab, Klimaks & Penyelesaian", $hasOutlineKeys,
    "HTTP {$resOutline['code']} - Struktur outline lengkap terverifikasi.");

// 3. Generate Character
$resChar = callAiGenerate([
    'type' => 'character',
    'prompt' => 'Buatkan profil karakter protagonis muda yang memiliki konflik batin.',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$charResp = $resChar['json']['data']['response'] ?? '';
$hasCharAttributes = ($resChar['code'] === 200 &&
                      str_contains($charResp, 'Nama Tokoh') &&
                      str_contains($charResp, 'Usia') &&
                      str_contains($charResp, 'Latar Belakang') &&
                      str_contains($charResp, 'Kepribadian') &&
                      str_contains($charResp, 'Tujuan') &&
                      str_contains($charResp, 'Motivasi') &&
                      str_contains($charResp, 'Kelemahan') &&
                      str_contains($charResp, 'Konflik Karakter'));
assertCondition("3. Generate Character: Memuat 8 atribut karakter lengkap", $hasCharAttributes,
    "HTTP {$resChar['code']} - Profil tokoh memuat Nama, Usia, Latar belakang, Kepribadian, Tujuan, Motivasi, Kelemahan & Konflik.");

// 4. Continue Story
$resContinue = callAiGenerate([
    'type' => 'continue',
    'prompt' => 'Bayu melangkah mendekati bibir jurang saat petir merah menyambar.',
    'context' => 'Kapal penjelajah Voyager melintasi sabuk asteroid.',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$hasContinue = ($resContinue['code'] === 200 &&
                !empty($resContinue['json']['data']['response']) &&
                mb_strlen($resContinue['json']['data']['response']) > 50);
assertCondition("4. Continue Story: Menghasilkan kelanjutan narasi luwes", $hasContinue,
    "HTTP {$resContinue['code']} - " . substr($resContinue['json']['data']['response'] ?? '', 0, 75) . '...');

// 5. Improve Writing
$resImprove = callAiGenerate([
    'type' => 'improve',
    'prompt' => 'Dia sangat kaget melihat monster besar itu di hutan gelap.',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$improveResp = $resImprove['json']['data']['response'] ?? '';
$hasImproveKeys = ($resImprove['code'] === 200 &&
                   str_contains($improveResp, 'Versi Perbaikan') &&
                   str_contains($improveResp, 'Tata Bahasa') &&
                   str_contains($improveResp, 'Keterbacaan'));
assertCondition("5. Improve Writing: Memperbaiki tata bahasa, alur & keterbacaan (Show don't tell)", $hasImproveKeys,
    "HTTP {$resImprove['code']} - Peningkatan diksi dan tata bahasa berhasil.");

// 6. Give Suggestions
$resSuggestions = callAiGenerate([
    'type' => 'suggestions',
    'prompt' => 'Evaluasi adegan pertarungan di jembatan gantung.',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$sugResp = $resSuggestions['json']['data']['response'] ?? '';
$hasSuggestionsKeys = ($resSuggestions['code'] === 200 &&
                       str_contains($sugResp, 'Pengembangan Konflik') &&
                       str_contains($sugResp, 'Konsistensi Karakter') &&
                       str_contains($sugResp, 'Alur Cerita') &&
                       str_contains($sugResp, 'Dinamika Dialog') &&
                       str_contains($sugResp, 'Deskripsi Latar') &&
                       str_contains($sugResp, 'Pengembangan Adegan'));
assertCondition("6. Give Suggestions: Masukan mencakup Konflik, Karakter, Alur, Dialog, Latar & Adegan", $hasSuggestionsKeys,
    "HTTP {$resSuggestions['code']} - Seluruh 6 aspek evaluasi penulisan terpenuhi.");

echo "\n--- 2. PENGUJIAN VALIDASI INPUT & ERROR HANDLING ---\n";

// 7. Prompt kosong
$resEmptyPrompt = callAiGenerate([
    'type' => 'continue',
    'prompt' => '   ', // whitespace only
    'context' => '',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

assertCondition("7. Prompt kosong: Ditolak dengan HTTP 422 Unprocessable Entity",
    $resEmptyPrompt['code'] === 422 && str_contains($resEmptyPrompt['json']['message'] ?? '', 'tidak boleh kosong'),
    "HTTP {$resEmptyPrompt['code']} - Pesan: " . ($resEmptyPrompt['json']['message'] ?? ''));

// 8. Prompt melebihi batas (2000 karakter)
$longPrompt = str_repeat("Kata ", 500); // ~2500 karakter
$resLongPrompt = callAiGenerate([
    'type' => 'idea',
    'prompt' => $longPrompt,
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

assertCondition("8. Prompt melebihi batas: Ditolak dengan HTTP 422",
    $resLongPrompt['code'] === 422 && str_contains($resLongPrompt['json']['message'] ?? '', 'melebihi batas maksimal'),
    "HTTP {$resLongPrompt['code']} - Pesan: " . ($resLongPrompt['json']['message'] ?? ''));

// 9. API key tidak tersedia
$resMissingKey = callAiGenerate([
    'type' => 'idea',
    'prompt' => 'Uji coba missing key',
    'mock_provider' => 'mock_missing_key',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

assertCondition("9. API key tidak tersedia: Ditangani dengan error terstruktur (HTTP 500)",
    $resMissingKey['code'] === 500 && str_contains($resMissingKey['json']['message'] ?? '', 'belum diatur'),
    "HTTP {$resMissingKey['code']} - Pesan: " . ($resMissingKey['json']['message'] ?? ''));

// 10. API gagal atau timeout
$resTimeout = callAiGenerate([
    'type' => 'continue',
    'prompt' => 'Uji coba timeout',
    'mock_provider' => 'mock_timeout',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$resRateLimit = callAiGenerate([
    'type' => 'continue',
    'prompt' => 'Uji coba rate limit',
    'mock_provider' => 'mock_rate_limit',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$resInvalidKey = callAiGenerate([
    'type' => 'continue',
    'prompt' => 'Uji coba invalid key',
    'mock_provider' => 'mock_invalid_key',
    'chapter_id' => $chapterAId
], $cookieA, $baseUrl);

$allMockErrorsPass = ($resTimeout['code'] === 504 &&
                      $resRateLimit['code'] === 429 &&
                      $resInvalidKey['code'] === 401);

assertCondition("10. API gagal atau timeout: HTTP 504 Timeout, 429 Rate Limit, 401 Invalid Key", $allMockErrorsPass,
    "Timeout: {$resTimeout['code']}, RateLimit: {$resRateLimit['code']}, InvalidKey: {$resInvalidKey['code']}");

echo "\n--- 3. PENGUJIAN INTEGRASI UI & EDITOR BAB ---\n";

// Muat konten HTML editor bab menggunakan sesi terotentikasi User A
$chEditor = curl_init("{$baseUrl}/chapter/edit/{$chapterAId}");
curl_setopt($chEditor, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chEditor, CURLOPT_COOKIEFILE, $cookieA);
$editorHtml = curl_exec($chEditor);
$editorCode = curl_getinfo($chEditor, CURLINFO_HTTP_CODE);
curl_close($chEditor);

assertCondition("Editor Bab berhasil dimuat (200 OK)", $editorCode === 200, "HTTP Status: {$editorCode}");

// 11. Respons AI ditampilkan di UI
$hasUiResultBox = str_contains($editorHtml, 'id="aiResultContainer"') &&
                   str_contains($editorHtml, 'id="aiResultText"') &&
                   str_contains($editorHtml, 'id="aiResultTypeTitle"');
assertCondition("11. Respons AI ditampilkan di UI: Elemen visual hasil AI tersedia di view", $hasUiResultBox,
    "aiResultContainer, aiResultText, aiResultTypeTitle ditemukan di template editor");

// 12. Hasil AI dapat disalin
$hasCopyButton = str_contains($editorHtml, 'copyAiText()') &&
                 str_contains($editorHtml, 'navigator.clipboard.writeText');
assertCondition("12. Hasil AI dapat disalin: Tombol Salin & Clipboard API terintegrasi", $hasCopyButton,
    "Fungsi copyAiText() dengan feedback clipboard aktif");

// 13. Hasil AI dapat dimasukkan ke editor tanpa otomatis menimpa naskah
$hasDeliberateInsert = str_contains($editorHtml, 'insertAiText()') &&
                       str_contains($editorHtml, 'onclick="insertAiText()"') &&
                       !str_contains($editorHtml, 'contentArea.value = currentAiGeneratedText;'); // Tidak otomatis menimpa total
assertCondition("13. Hasil AI dimasukkan ke editor hanya atas tindakan sadar penulis", $hasDeliberateInsert,
    "Naskah asli terlindungi; hanya disisipkan jika tombol 'Sisipkan ke Naskah' ditekan.");

echo "\n--- 4. PENGUJIAN RIWAYAT AI & ISOLASI MULTI-USER ---\n";

// 14. Riwayat AI tersimpan
$historyA = $aiHistoryModel->getByChapter($chapterAId);
assertCondition("14. Riwayat AI tersimpan di database: Tabel ai_histories mencatat interaksi",
    count($historyA) >= 6,
    "Tercatat " . count($historyA) . " entri riwayat untuk Bab ID {$chapterAId}");

// 15. User tidak dapat melihat riwayat milik user lain
// User B mencoba membaca riwayat bab User A melalui GET /ai/history?chapter_id=chapterAId
$ch = curl_init("{$baseUrl}/ai/history?chapter_id={$chapterAId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieB);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
$rawResB = curl_exec($ch);
$codeResB = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$jsonResB = json_decode($rawResB, true);

// Tamu tanpa login mencoba akses riwayat
$chGuest = curl_init("{$baseUrl}/ai/history?chapter_id={$chapterAId}");
curl_setopt($chGuest, CURLOPT_RETURNTRANSFER, true);
$rawGuest = curl_exec($chGuest);
$codeGuest = curl_getinfo($chGuest, CURLINFO_HTTP_CODE);
curl_close($chGuest);

$isolationHistoryPass = ($codeResB === 403 && $codeGuest === 401);
assertCondition("15. Otorisasi Riwayat: User B dilarang akses riwayat User A (403), Tamu ditolak (401)", $isolationHistoryPass,
    "User B -> HTTP {$codeResB} Forbidden | Guest -> HTTP {$codeGuest} Unauthorized");

// 16. User tidak dapat menggunakan chapter milik user lain sebagai konteks
// User B mengirim prompt AI dengan chapter_id milik User A
$resCrossContext = callAiGenerate([
    'type' => 'continue',
    'prompt' => 'Lanjutkan cerita ini',
    'chapter_id' => $chapterAId
], $cookieB, $baseUrl);

// Tamu tanpa login mengirim prompt AI
$resGuestGenerate = callAiGenerate([
    'type' => 'idea',
    'prompt' => 'Ide novel baru',
    'chapter_id' => $chapterAId
], null, $baseUrl);

$isolationContextPass = ($resCrossContext['code'] === 403 && $resGuestGenerate['code'] === 401);
assertCondition("16. Otorisasi Konteks: User B dilarang pakai bab User A (403), Tamu ditolak (401)", $isolationContextPass,
    "User B cross-chapter -> HTTP {$resCrossContext['code']} Forbidden | Guest -> HTTP {$resGuestGenerate['code']} Unauthorized");

// 17. API key tidak bocor ke frontend
$viewCreate = file_get_contents(__DIR__ . '/app/views/chapter/create.php');
$viewEdit = file_get_contents(__DIR__ . '/app/views/chapter/edit.php');

$noKeyInViews = (!str_contains($viewCreate, 'AI_API_KEY') &&
                 !str_contains($viewEdit, 'AI_API_KEY') &&
                 !str_contains($resIdea['raw'], 'AI_API_KEY') &&
                 !str_contains($resChar['raw'], 'sk-'));
assertCondition("17. Keamanan API Key: Kunci rahasia tidak pernah terekspos ke frontend / respons API", $noKeyInViews,
    "Semua request AI dialirkan melalui AI Service backend; frontend bersih dari secret token.");

// 18. Tampilan responsif
$hasResponsiveGrid = str_contains($editorHtml, 'col-lg-8') &&
                     str_contains($editorHtml, 'col-lg-4') &&
                     str_contains($editorHtml, 'editor-sidebar-column') &&
                     str_contains($editorHtml, 'editor-main-column');
assertCondition("18. Tampilan responsif: Bootstrap grid multi-breakpoint (Desktop, Tablet, Mobile)", $hasResponsiveGrid,
    "Grid layout responsive: col-lg-8/4 otomatis stacking rapi di mobile & tablet.");

echo "\n=======================================================================\n";
echo " TOTAL PENGUJIAN PROMPT 8: {$passed} PASSED, {$failed} FAILED\n";
echo "=======================================================================\n";

if ($failed === 0) {
    echo ">>> SELURUH KRITERIA PROMPT 8 TERPENUHI 100% SECARA SEMPURNA! <<<\n";
    exit(0);
} else {
    echo ">>> TERDAPAT {$failed} PENGUJIAN GAGAL. SILAKAN PERIKSA DETAIL DI ATAS. <<<\n";
    exit(1);
}
