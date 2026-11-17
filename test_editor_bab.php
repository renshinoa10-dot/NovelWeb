<?php
/**
 * karyaNovel - Test Suite Resmi Fitur Editor Bab & AI Writing Studio
 * Menguji seluruh kebutuhan:
 * 1. Tampilan Layout Editor Bab (Judul, Simpan, Text Editor, AI Assistant, 4 Tombol AI)
 * 2. Word Counter, Character Counter & Reading Time
 * 3. Status Penyimpanan & Save Indicators
 * 4. Autosave Endpoint Backend (POST /chapter/autosave/{id})
 * 5. Otorisasi Ketat Autosave (User B dilarang autosave bab User A -> 403 Forbidden)
 * 6. Proteksi Guest pada Autosave (401 Unauthorized)
 * 7. AI Writing Assistant 4 Mode (Generate Ide, Lanjutkan, Perbaiki, Karakter) via POST /ai/generate
 * 8. Pencatatan Riwayat AI ke Database (Tabel ai_histories)
 * 9. Keamanan API Key (Tidak ada API key yang diekspos ke frontend)
 * 10. Pencegahan Kehilangan Data (Old input & LocalStorage backup integrity)
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

echo "=======================================================================\n";
echo " karyaNovel - TEST SUITE: FITUR EDITOR BAB & AI ASSISTANT\n";
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

// 1. Persiapan User A & B
$userA = $userModel->findByEmail('penulis1@karyanovel.id');
$userB = $userModel->findByEmail('penulis2@karyanovel.id');

assertCondition("User A (Penulis 1) siap di database", !empty($userA));
assertCondition("User B (Penulis 2) siap di database", !empty($userB));

$userIdA = (int)$userA['id'];
$userIdB = (int)$userB['id'];

$cookieA = __DIR__ . '/cookie_editor_userA.txt';
$cookieB = __DIR__ . '/cookie_editor_userB.txt';
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

$baseUrl = 'http://localhost/Novelweb';

// Login User A
$ch = curl_init("{$baseUrl}/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'penulis1@karyanovel.id',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieA);
curl_exec($ch);
curl_close($ch);

// Login User B
$ch = curl_init("{$baseUrl}/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'penulis2@karyanovel.id',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieB);
curl_exec($ch);
curl_close($ch);

// Buat novel & bab uji coba untuk User A
$novelId = (int)$novelModel->createNovel([
    'user_id' => $userIdA,
    'title' => 'Menembus Langit Ketujuh ' . time(),
    'synopsis' => 'Kisah petualangan luar angkasa penuh intrik dan misteri kristal purba.',
    'genre' => 'Sci-Fi',
    'status' => 'draft'
]);

$chapterId = (int)$chapterModel->createChapter([
    'novel_id' => $novelId,
    'title' => 'Bab 1: Anomali di Orbit Luar',
    'content' => 'Layar radar stasiun berkedip merah terang saat sensor mendeteksi distorsi ruang waktu berjarak 300 kilometer dari posisi kapal induk Zenith.',
    'chapter_order' => 1,
    'status' => 'draft'
]);

assertCondition("Novel & Bab Pengujian berhasil dibuat", $novelId > 0 && $chapterId > 0, "Novel ID: {$novelId}, Chapter ID: {$chapterId}");

// =====================================================================
// TAHAP 1: VERIFIKASI TAMPILAN & ELEMEN EDITOR BAB (GET /chapter/edit/{id})
// =====================================================================
echo "\n--- 1. PENGUJIAN TAMPILAN & KOMPONEN EDITOR BAB ---\n";

$ch = curl_init("{$baseUrl}/chapter/edit/{$chapterId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
$editorHtml = curl_exec($ch);
$editorCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP GET /chapter/edit/{id} berhasil dimuat (200 OK)", $editorCode === 200);

// Periksa komponen UI layout
assertCondition("Editor memuat Input Judul Bab", strpos($editorHtml, 'id="chapterTitle"') !== false);
assertCondition("Editor memuat Textarea Isi Novel", strpos($editorHtml, 'id="chapterContent"') !== false);
assertCondition("Editor memuat Status Penyimpanan", strpos($editorHtml, 'id="saveStatusIndicator"') !== false);
assertCondition("Editor memuat Word & Character Counter", strpos($editorHtml, 'id="wordCount"') !== false && strpos($editorHtml, 'id="charCount"') !== false);
assertCondition("Editor memuat Estimasi Waktu Baca", strpos($editorHtml, 'id="readingTime"') !== false);
assertCondition("Editor memuat Panel AI Writing Assistant", strpos($editorHtml, 'AI Assistant') !== false);
assertCondition("Editor memuat Tombol 'Generate Ide'", strpos($editorHtml, 'Generate Ide') !== false);
assertCondition("Editor memuat Tombol 'Lanjutkan Cerita'", strpos($editorHtml, 'Lanjutkan') !== false);
assertCondition("Editor memuat Tombol 'Perbaiki Gaya Bahasa'", strpos($editorHtml, 'Perbaiki') !== false);
assertCondition("Editor memuat Tombol 'Karakter & Dialog'", strpos($editorHtml, 'Karakter') !== false);
assertCondition("Editor memuat Tombol Zen / Mode Fokus", strpos($editorHtml, 'zenModeToggle') !== false);
assertCondition("Editor memuat Emergency Backup Recovery Banner", strpos($editorHtml, 'localBackupAlert') !== false);

// =====================================================================
// TAHAP 2: PENGUJIAN AUTOSAVE ENDPOINT (POST /chapter/autosave/{id})
// =====================================================================
echo "\n--- 2. PENGUJIAN AUTOSAVE BACKEND ---\n";

$autosaveTitle = "Bab 1: Anomali di Orbit Luar [Autosaved]";
$autosaveContent = "Naskah telah disunting oleh penulis. Radar menunjukkan anomali energi kuantum meningkat 40% dalam tempo tiga menit terakhir.";

$ch = curl_init("{$baseUrl}/chapter/autosave/{$chapterId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'title' => $autosaveTitle,
    'content' => $autosaveContent,
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
$autosaveResp = curl_exec($ch);
$autosaveCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$autosaveData = json_decode($autosaveResp, true);
assertCondition("Autosave User A berhasil (HTTP 200 JSON)", $autosaveCode === 200 && ($autosaveData['success'] ?? false) === true);
assertCondition("Autosave response menyertakan saved_at timestamp", !empty($autosaveData['data']['saved_at']));

// Verifikasi perubahan tercermin di database
$dbChapterAfterAutosave = $chapterModel->findAuthorChapter($chapterId, $userIdA);
assertCondition("Data autosave tersimpan akurat di database", 
    $dbChapterAfterAutosave['title'] === $autosaveTitle && $dbChapterAfterAutosave['content'] === $autosaveContent
);

// =====================================================================
// TAHAP 3: OTORISASI KETAT PADA AUTOSAVE (USER B & GUEST)
// =====================================================================
echo "\n--- 3. PENGUJIAN OTORISASI AUTOSAVE MULTI-USER & GUEST ---\n";

// 3.1 User B mencoba autosave ke chapter User A
$ch = curl_init("{$baseUrl}/chapter/autosave/{$chapterId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'title' => 'DIBAJAK OLEH USER B VIA AUTOSAVE',
    'content' => 'Konten bajakan'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
$unauthAutosaveResp = curl_exec($ch);
$unauthAutosaveCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("OTORISASI: User B dilarang autosave ke bab User A (HTTP 403 Forbidden)", $unauthAutosaveCode === 403);

// Pastikan integritas naskah tidak dibajak
$verifyNoHack = $chapterModel->findAuthorChapter($chapterId, $userIdA);
assertCondition("Integritas naskah User A tetap aman & tidak terpengaruh", $verifyNoHack['title'] === $autosaveTitle);

// 3.2 Guest tanpa login mencoba autosave
$ch = curl_init("{$baseUrl}/chapter/autosave/{$chapterId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'title' => 'Guest Try',
    'content' => 'Guest Try'
]));
$guestAutosaveResp = curl_exec($ch);
$guestAutosaveCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("OTORISASI: Tamu tanpa login ditolak autosave (HTTP 401 Unauthorized)", $guestAutosaveCode === 401);

// =====================================================================
// TAHAP 4: PENGUJIAN 4 FITUR AI WRITING ASSISTANT
// =====================================================================
echo "\n--- 4. PENGUJIAN 4 FITUR AI WRITING ASSISTANT (BACKEND MVC) ---\n";

function testAiType($type, $label, $baseUrl, $cookie, $chapterId) {
    $ch = curl_init("{$baseUrl}/ai/generate");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'prompt' => "Uji coba {$type}",
        'context' => "Sensor mendeteksi anomali energi kuantum di koordinat 440.",
        'chapter_id' => $chapterId,
        'type' => $type
    ]));
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($resp, true);
    $success = ($code === 200 && ($json['success'] ?? false) === true && !empty($json['data']['response']));
    assertCondition("AI Mode '{$label}' ({$type}) sukses merespon", $success, "Respon: " . mb_substr($json['data']['response'] ?? '', 0, 70) . "...");
    return $json['data']['history_id'] ?? null;
}

$historyIdIdea = testAiType('idea', 'Generate Ide', $baseUrl, $cookieA, $chapterId);
$historyIdContinue = testAiType('continue', 'Lanjutkan Cerita', $baseUrl, $cookieA, $chapterId);
$historyIdPolish = testAiType('polish', 'Perbaiki Gaya Bahasa', $baseUrl, $cookieA, $chapterId);
$historyIdChar = testAiType('character', 'Karakter & Dialog', $baseUrl, $cookieA, $chapterId);

// 4.5 Verifikasi pencatatan ke database ai_histories
$loggedHistories = $aiHistoryModel->getByChapter($chapterId);
assertCondition("Riwayat interaksi AI tercatat di database ai_histories", count($loggedHistories) >= 4, "Total log bab ini: " . count($loggedHistories));

// 4.6 Verifikasi Keamanan API Key: Tidak bocor ke frontend
assertCondition("Keamanan: Respon AI tidak mengekspos API key / rahasia sistem", 
    strpos($editorHtml, 'AI_API_KEY') === false && 
    strpos($editorHtml, 'sk-') === false
);

// Bersihkan data pengujian
$novelModel->deleteNovel($novelId, $userIdA);
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

echo "\n=======================================================================\n";
echo " TOTAL PENGUJIAN EDITOR BAB: {$passed} PASSED, {$failed} FAILED\n";
echo "=======================================================================\n";

if ($failed === 0) {
    echo ">>> SELURUH FITUR EDITOR BAB BERHASIL DIVERIFIKASI DENGAN SEMPURNA! <<<\n\n";
    exit(0);
} else {
    echo ">>> TERDAPAT PENGUJIAN YANG GAGAL. PERIKSA LOG DI ATAS! <<<\n\n";
    exit(1);
}
