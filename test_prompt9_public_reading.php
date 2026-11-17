<?php
/**
 * karyaNovel - Test Suite Resmi PROMPT 9: Publikasi Novel & Halaman Baca
 * Menguji seluruh spesifikasi:
 * - TEST A: Novel Draft (Tidak muncul publik, detail ditolak 404, bab ditolak 404)
 * - TEST B: Novel Published dengan Bab Draft (Novel muncul, bab draft sembunyi, bab draft ditolak 404)
 * - TEST C: Novel Published dengan Bab Published (Detail muncul, bab berurutan, halaman baca 200, navigasi adjacent aktif)
 * - TEST D: URL Manipulation (ID novel draft 404, ID bab draft 404, ID bab novel lain 404, bab published dari novel draft 404)
 * - TEST E: UI & Integrasi (Toggle status author, otorisasi pemilik, navigasi breadcrumbs & baca, pagination)
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

echo "=======================================================================\n";
echo " karyaNovel - TEST SUITE: PROMPT 9 - PUBLIKASI NOVEL & HALAMAN BACA\n";
echo "=======================================================================\n\n";

$db = Database::getInstance();
$userModel = new User();
$novelModel = new Novel();
$chapterModel = new Chapter();

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

// 1. Identifikasi Penulis & Sesi HTTP
$authorA = $userModel->findByEmail('penulis1@karyanovel.id');
$authorB = $userModel->findByEmail('penulis2@karyanovel.id');

assertCondition("Penulis A (Ahmad Rian) tersedia di database", !empty($authorA), "ID: " . ($authorA['id'] ?? '-'));
assertCondition("Penulis B (Clara Sinta) tersedia di database", !empty($authorB), "ID: " . ($authorB['id'] ?? '-'));

$userIdA = (int)$authorA['id'];
$userIdB = (int)$authorB['id'];

$cookieA = __DIR__ . '/cookie_userA_p9.txt';
$cookieB = __DIR__ . '/cookie_userB_p9.txt';
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

$baseUrl = 'http://localhost/Novelweb';

function httpGet(string $url, ?string $cookieFile = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $body, 'redirect' => $redirectUrl];
}

function httpPost(string $url, array $data, ?string $cookieFile = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $body, 'redirect' => $redirectUrl];
}

// Login Author A
$loginA = httpPost("{$baseUrl}/auth/login", [
    'email' => 'penulis1@karyanovel.id',
    'password' => 'password123'
], $cookieA);
assertCondition("Login Author A berhasil (302 Redirect)", $loginA['code'] === 302);

// Login Author B
$loginB = httpPost("{$baseUrl}/auth/login", [
    'email' => 'penulis2@karyanovel.id',
    'password' => 'password123'
], $cookieB);
assertCondition("Login Author B berhasil (302 Redirect)", $loginB['code'] === 302);

echo "\n--- TEST A: NOVEL DRAFT (PERLINDUNGAN AKSES PUBLIK) ---\n";
// Buat Novel Draft baru oleh Author A
$novelDraftId = (int)$novelModel->createNovel([
    'user_id' => $userIdA,
    'title' => 'Naskah Rahasia Dimensi Tujuh',
    'synopsis' => 'Naskah eksperimental yang masih dalam tahap draft rahasia dan belum boleh dibaca publik.',
    'genre' => 'Sci-Fi',
    'status' => 'draft'
]);
assertCondition("Novel Draft berhasil dibuat oleh Penulis A", $novelDraftId > 0, "ID: {$novelDraftId}");

// Buat Bab 1 (Published) dan Bab 2 (Draft) di novel draft ini
$chapDraftNovel1 = (int)$chapterModel->createChapter([
    'novel_id' => $novelDraftId,
    'title' => 'Prolog: Sinyal Pertama',
    'content' => 'Di tengah kegelapan stasiun luar angkasa, detektor kuantum mulai memancarkan frekuensi aneh...',
    'chapter_order' => 1,
    'status' => 'published'
]);
$chapDraftNovel2 = (int)$chapterModel->createChapter([
    'novel_id' => $novelDraftId,
    'title' => 'Bab 1: Menembus Horizon Gelap',
    'content' => 'Arka memasukkan koordinat lompatan melintasi wormhole kedua...',
    'chapter_order' => 2,
    'status' => 'draft'
]);

// 1. Cek daftar katalog publik: Novel Draft TIDAK boleh muncul
$publicCatalog = httpGet("{$baseUrl}/novel");
assertCondition("Katalog publik dimuat (200 OK)", $publicCatalog['code'] === 200);
assertCondition("Novel Draft TIDAK muncul di daftar novel publik", !str_contains($publicCatalog['body'], 'Naskah Rahasia Dimensi Tujuh'));

// 2. Akses publik langsung ke detail novel draft
$publicDetail = httpGet("{$baseUrl}/novel/detail/{$novelDraftId}");
assertCondition("Pengunjung publik DITOLAK membuka detail Novel Draft (HTTP 404)", $publicDetail['code'] === 404);

$publicDetailNovels = httpGet("{$baseUrl}/novels/{$novelDraftId}");
assertCondition("Pengunjung publik DITOLAK membuka /novels/{id_draft} (HTTP 404)", $publicDetailNovels['code'] === 404);

// 3. Akses publik ke bab dari novel draft (walaupun status bab published)
$publicReadNovelChap = httpGet("{$baseUrl}/novels/{$novelDraftId}/read/{$chapDraftNovel1}");
assertCondition("Pengunjung publik DITOLAK membaca Bab Published dari Novel Draft (/novels/{id}/read/{chap}) (HTTP 404)", $publicReadNovelChap['code'] === 404);

$publicReadChapDirect = httpGet("{$baseUrl}/chapter/read/{$chapDraftNovel1}");
assertCondition("Pengunjung publik DITOLAK membaca Bab Published dari Novel Draft (/chapter/read/{id}) (HTTP 404)", $publicReadChapDirect['code'] === 404);

// 4. Penulis asli BISA melihat detail draft dan preview bab miliknya
$authorDetail = httpGet("{$baseUrl}/novels/{$novelDraftId}", $cookieA);
assertCondition("Penulis asli DAPAT mengakses detail naskah draft miliknya (HTTP 200)", $authorDetail['code'] === 200);

$authorRead = httpGet("{$baseUrl}/novels/{$novelDraftId}/read/{$chapDraftNovel1}", $cookieA);
assertCondition("Penulis asli DAPAT mengakses mode pratinjau bab miliknya (HTTP 200)", $authorRead['code'] === 200);


echo "\n--- TEST B: NOVEL PUBLISHED DENGAN BAB DRAFT ---\n";
// Buat Novel Published oleh Author A
$novelPubId = (int)$novelModel->createNovel([
    'user_id' => $userIdA,
    'title' => 'Kronik Lembah Kencana',
    'synopsis' => 'Petualangan mencari mustika air di lembah kencana yang legendaris.',
    'genre' => 'Petualangan',
    'status' => 'published'
]);
assertCondition("Novel Published berhasil dibuat", $novelPubId > 0, "ID: {$novelPubId}");

// Bab 1: Published
$chapPub1 = (int)$chapterModel->createChapter([
    'novel_id' => $novelPubId,
    'title' => 'Bab 1: Peta Kuno di Tangan Pengelana',
    'content' => 'Gurat tinta emas pada perkamen tua itu menunjukkan mata air di balik air terjun kabut.',
    'chapter_order' => 1,
    'status' => 'published'
]);
// Bab 2: Draft (Rahasia / Belum siap tayang)
$chapDraft2 = (int)$chapterModel->createChapter([
    'novel_id' => $novelPubId,
    'title' => 'Bab 2: Jejak Harimau Salju (Draft)',
    'content' => 'Naskah bab 2 ini masih buram dan belum boleh dibaca publik.',
    'chapter_order' => 2,
    'status' => 'draft'
]);
// Bab 3: Published
$chapPub3 = (int)$chapterModel->createChapter([
    'novel_id' => $novelPubId,
    'title' => 'Bab 3: Gerbang Menuju Danau Kaca',
    'content' => 'Danau kaca memantulkan cahaya bintang dengan sempurna bagaikan cermin raksasa para dewa.',
    'chapter_order' => 3,
    'status' => 'published'
]);

// 1. Novel muncul di katalog publik
$catalogAfterPub = httpGet("{$baseUrl}/novel");
assertCondition("Novel Published muncul di katalog publik", str_contains($catalogAfterPub['body'], 'Kronik Lembah Kencana'));

// 2. Detail novel dapat dibuka oleh publik
$pubNovelDetail = httpGet("{$baseUrl}/novels/{$novelPubId}");
assertCondition("Pengunjung publik DAPAT membuka detail Novel Published (HTTP 200)", $pubNovelDetail['code'] === 200);
assertCondition("Bab 1 (Published) muncul di daftar bab publik", str_contains($pubNovelDetail['body'], 'Bab 1: Peta Kuno di Tangan Pengelana'));
assertCondition("Bab 3 (Published) muncul di daftar bab publik", str_contains($pubNovelDetail['body'], 'Bab 3: Gerbang Menuju Danau Kaca'));
assertCondition("Bab 2 (Draft) TIDAK MUNCUL di daftar bab publik", !str_contains($pubNovelDetail['body'], 'Bab 2: Jejak Harimau Salju (Draft)'));

// 3. Akses publik langsung ke Bab Draft via URL ditolak (HTTP 404)
$readDraftDirect = httpGet("{$baseUrl}/novels/{$novelPubId}/read/{$chapDraft2}");
assertCondition("Publik DITOLAK membaca Bab Draft via /novels/{id}/read/{draftId} (HTTP 404)", $readDraftDirect['code'] === 404);

$readDraftDirectChap = httpGet("{$baseUrl}/chapter/read/{$chapDraft2}");
assertCondition("Publik DITOLAK membaca Bab Draft via /chapter/read/{draftId} (HTTP 404)", $readDraftDirectChap['code'] === 404);

// 4. Pengguna lain (User B) juga DITOLAK membaca Bab Draft
$userBReadDraft = httpGet("{$baseUrl}/novels/{$novelPubId}/read/{$chapDraft2}", $cookieB);
assertCondition("Pengguna lain (User B) DITOLAK membaca Bab Draft milik User A (HTTP 404)", $userBReadDraft['code'] === 404);


echo "\n--- TEST C: NOVEL PUBLISHED DENGAN BAB PUBLISHED & NAVIGASI ---\n";
// 1. Publik dapat membaca Bab 1 Published
$readBab1 = httpGet("{$baseUrl}/novels/{$novelPubId}/read/{$chapPub1}");
assertCondition("Publik DAPAT membaca Bab Published (HTTP 200)", $readBab1['code'] === 200);
assertCondition("Halaman baca menampilkan judul bab", str_contains($readBab1['body'], 'Bab 1: Peta Kuno di Tangan Pengelana'));
assertCondition("Halaman baca menampilkan judul novel", str_contains($readBab1['body'], 'Kronik Lembah Kencana'));
assertCondition("Halaman baca menampilkan nama penulis", str_contains($readBab1['body'], 'Ahmad Rian'));
assertCondition("Halaman baca menampilkan konten cerita yang disanitasi", str_contains($readBab1['body'], 'Gurat tinta emas pada perkamen tua'));

// 2. Navigasi: Bab Sebelumnya disabled pada Bab 1
assertCondition("Tombol Bab Sebelumnya disabled pada bab pertama", str_contains($readBab1['body'], 'disabled') && str_contains($readBab1['body'], 'Bab Sebelumnya'));

// 3. Navigasi: Bab Selanjutnya pada Bab 1 harus melompati Bab 2 (karena Draft) dan langsung menuju Bab 3 (Published)
assertCondition("Navigasi bab selanjutnya melompati bab draft dan mengarah ke Bab 3", str_contains($readBab1['body'], "/novels/{$novelPubId}/read/{$chapPub3}"));

// 4. Publik membaca Bab 3
$readBab3 = httpGet("{$baseUrl}/novels/{$novelPubId}/read/{$chapPub3}");
assertCondition("Publik DAPAT membaca Bab 3 (HTTP 200)", $readBab3['code'] === 200);
// Navigasi mundur dari Bab 3 harus menuju Bab 1 (karena Bab 2 Draft)
assertCondition("Navigasi bab sebelumnya dari Bab 3 melompati draft dan kembali ke Bab 1", str_contains($readBab3['body'], "/novels/{$novelPubId}/read/{$chapPub1}"));
assertCondition("Tombol Bab Selanjutnya disabled pada bab terakhir", str_contains($readBab3['body'], 'disabled') && str_contains($readBab3['body'], 'Bab Selanjutnya'));


echo "\n--- TEST D: URL MANIPULATION & ACCESS CONTROL ---\n";
// Buat Novel B milik Author B
$novelBId = (int)$novelModel->createNovel([
    'user_id' => $userIdB,
    'title' => 'Serenade Hujan di Florence',
    'synopsis' => 'Kisah cinta manis berbalut seni lukis klasik di kota Florence.',
    'genre' => 'Romansa',
    'status' => 'published'
]);
$chapNovelB = (int)$chapterModel->createChapter([
    'novel_id' => $novelBId,
    'title' => 'Bab 1: Kanvas yang Tertinggal',
    'content' => 'Aroma cat minyak dan pelarut terpentin tercium pekat dari dalam studio tua itu.',
    'chapter_order' => 1,
    'status' => 'published'
]);

// 1. Cross-Novel Manipulation: Mencoba akses Bab milik Novel B melalui URL Novel A (/novels/{novelPubId}/read/{chapNovelB})
$crossNovelTamper = httpGet("{$baseUrl}/novels/{$novelPubId}/read/{$chapNovelB}");
assertCondition("Manipulasi URL: Mengakses bab milik novel lain DITOLAK (HTTP 404)", $crossNovelTamper['code'] === 404);

// 2. Akses ID novel yang tidak ada di database
$invalidNovel = httpGet("{$baseUrl}/novels/999999");
assertCondition("Akses ID novel tidak valid DITOLAK (HTTP 404)", $invalidNovel['code'] === 404);

// 3. Akses ID bab yang tidak ada di database
$invalidChapter = httpGet("{$baseUrl}/novels/{$novelPubId}/read/999999");
assertCondition("Akses ID bab tidak valid DITOLAK (HTTP 404)", $invalidChapter['code'] === 404);

// 4. Manipulasi URL: Bab published pada Novel Draft tetap ditolak bagi pembaca publik
$draftNovelPubChapTamper = httpGet("{$baseUrl}/novels/{$novelDraftId}/read/{$chapDraftNovel1}");
assertCondition("Manipulasi URL: Bab Published dari Novel Draft DITOLAK bagi pembaca publik (HTTP 404)", $draftNovelPubChapTamper['code'] === 404);


echo "\n--- TEST E: UI, PUBLIKASI NOVEL & INTEGRASI ---\n";
// 1. Author A mengubah status novel miliknya dari Draft menjadi Published
$toggleToPub = httpPost("{$baseUrl}/novels/{$novelDraftId}/toggle_status", [], $cookieA);
assertCondition("Author A dapat mengubah status novel miliknya (HTTP 302 Redirect)", $toggleToPub['code'] === 302);

$updatedNovel = $novelModel->find($novelDraftId);
assertCondition("Status novel di database berubah menjadi 'published'", $updatedNovel['status'] === 'published');

// Setelah dipublikasikan, novel draft sekarang DAPAT diakses publik
$publicNowAccess = httpGet("{$baseUrl}/novels/{$novelDraftId}");
assertCondition("Publik kini DAPAT mengakses novel yang telah dipublikasikan (HTTP 200)", $publicNowAccess['code'] === 200);

// Bab 1 yang published sekarang dapat dibaca publik
$publicNowReadChap = httpGet("{$baseUrl}/novels/{$novelDraftId}/read/{$chapDraftNovel1}");
assertCondition("Publik kini DAPAT membaca bab published setelah novel dipublikasikan (HTTP 200)", $publicNowReadChap['code'] === 200);

// Tetapi Bab 2 yang berstatus Draft tetap DITOLAK
$publicStillDenyDraftChap = httpGet("{$baseUrl}/novels/{$novelDraftId}/read/{$chapDraftNovel2}");
assertCondition("Bab Draft tetap DITOLAK meskipun novel induk sudah published (HTTP 404)", $publicStillDenyDraftChap['code'] === 404);

// 2. Author A mengembalikan novel ke status Draft (Unpublish)
$toggleBackToDraft = httpPost("{$baseUrl}/novels/{$novelDraftId}/toggle_status", [], $cookieA);
assertCondition("Author A dapat mengembalikan status novel ke Draft (HTTP 302 Redirect)", $toggleBackToDraft['code'] === 302);

$novelBackDraft = $novelModel->find($novelDraftId);
assertCondition("Status novel di database kembali menjadi 'draft'", $novelBackDraft['status'] === 'draft');

// Publik kembali DITOLAK
$publicAgainDenied = httpGet("{$baseUrl}/novels/{$novelDraftId}");
assertCondition("Publik kembali DITOLAK setelah novel dikembalikan ke Draft (HTTP 404)", $publicAgainDenied['code'] === 404);

// 3. Otorisasi Ketat: User B dilarang mengubah status novel milik User A
$userBTamperStatus = httpPost("{$baseUrl}/novels/{$novelDraftId}/toggle_status", [], $cookieB);
$novelAfterTamper = $novelModel->find($novelDraftId);
assertCondition("Otorisasi: User B dilarang mengubah status publikasi novel milik User A", $novelAfterTamper['status'] === 'draft');

// 4. UI Elements Check: Cek tampilan tombol publikasi pada halaman detail penulis
$authorView = httpGet("{$baseUrl}/novels/{$novelDraftId}", $cookieA);
assertCondition("UI Penulis: Tombol 'Publikasikan Novel' tersedia saat novel berstatus Draft", str_contains($authorView['body'], 'Publikasikan Novel'));

// Ubah ke published lagi untuk cek tombol unpublish di UI
httpPost("{$baseUrl}/novels/{$novelDraftId}/toggle_status", [], $cookieA);
$authorViewPub = httpGet("{$baseUrl}/novels/{$novelDraftId}", $cookieA);
assertCondition("UI Penulis: Tombol 'Kembalikan ke Draft' tersedia saat novel berstatus Published", str_contains($authorViewPub['body'], 'Kembalikan ke Draft'));

// 5. Cek Pagination dan Filter Genre di Katalog Publik
$genreFilterResp = httpGet("{$baseUrl}/novel?genre=Sci-Fi");
assertCondition("Katalog publik mendukung filter genre (HTTP 200)", $genreFilterResp['code'] === 200);
assertCondition("Halaman filter genre menampilkan judul filter yang sesuai", str_contains($genreFilterResp['body'], 'Genre: Sci-Fi'));

// Kembalikan novel uji coba ke draft
httpPost("{$baseUrl}/novels/{$novelDraftId}/toggle_status", [], $cookieA);

echo "\n=======================================================================\n";
echo " TOTAL PENGUJIAN PROMPT 9: {$passed} PASSED, {$failed} FAILED\n";
echo "=======================================================================\n";

if ($failed === 0) {
    echo ">>> SELURUH FITUR PUBLIKASI NOVEL & HALAMAN BACA LULUS 100%! <<<\n\n";
} else {
    echo ">>> TERDAPAT PENGUJIAN YANG GAGAL. SILAKAN PERIKSA DETAIL DI ATAS. <<<\n\n";
}
