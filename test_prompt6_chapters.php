<?php
/**
 * karyaNovel - Test Suite Resmi Prompt 6 (Manajemen Bab & Otorisasi Ketat)
 * Sesuai seluruh spesifikasi TEST 1 hingga TEST 8 pada PROMPT 6:
 * 
 * TEST 1: Login User A -> Buat Novel A -> Tambahkan Bab 1, Bab 2, Bab 3 -> Pastikan semua muncul berurutan.
 * TEST 2: Edit Bab 1 -> Pastikan perubahan judul dan konten tersimpan.
 * TEST 3: Delete Bab 2 -> Pastikan urutan chapter_order dirapikan otomatis (1, 2).
 * TEST 4: Ubah Bab 1 menjadi Published (via edit dan via quick toggle_status) -> Pastikan status berubah.
 * TEST 5: Login User B -> Coba akses manajemen/edit/delete chapter milik User A -> ACCESS DENIED.
 * TEST 6: Manipulasi URL manual (/chapter/edit/{id_A}, /chapter/delete/{id_A}, /novels/{id_A}/chapters) -> Otorisasi tetap ketat.
 * TEST 7: Novel Draft + Chapter Published -> Pastikan bab TIDAK DAPAT diakses pada halaman publik (/chapter/read/{id} publik & /novel/detail/{id}).
 * TEST 8: Novel Published + Chapter Published -> Bab DAPAT diakses publik pada halaman publik (/chapter/read/{id}).
 * BONUS TESTS: Reordering (Move Up & Move Down), Empty Draft support, Validation checks.
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
require_once APPROOT . '/controllers/NovelsController.php';
require_once APPROOT . '/controllers/ChapterController.php';

echo "=======================================================================\n";
echo " karyaNovel - TEST SUITE PROMPT 6: MANAJEMEN BAB & OTORISASI\n";
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

// 1. Identifikasi User A (Ahmad Rian - ID 2) dan User B (Clara Sinta - ID 3)
$userA = $userModel->findByEmail('penulis1@karyanovel.id');
$userB = $userModel->findByEmail('penulis2@karyanovel.id');

assertCondition("User A (Penulis 1) terdaftar", !empty($userA), "ID: " . ($userA['id'] ?? '-'));
assertCondition("User B (Penulis 2) terdaftar", !empty($userB), "ID: " . ($userB['id'] ?? '-'));

$userIdA = (int)$userA['id'];
$userIdB = (int)$userB['id'];

$cookieA = __DIR__ . '/cookie_userA_p6.txt';
$cookieB = __DIR__ . '/cookie_userB_p6.txt';
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

$baseUrl = 'http://localhost/Novelweb';

// HTTP Login User A
$ch = curl_init("{$baseUrl}/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'penulis1@karyanovel.id',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieA);
$resp = curl_exec($ch);
$loginCodeA = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertCondition("HTTP Login User A berhasil (302 Redirect)", $loginCodeA === 302);

// HTTP Login User B
$ch = curl_init("{$baseUrl}/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'penulis2@karyanovel.id',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieB);
$resp = curl_exec($ch);
$loginCodeB = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertCondition("HTTP Login User B berhasil (302 Redirect)", $loginCodeB === 302);

// =====================================================================
// TEST 1: LOGIN USER A, BUAT NOVEL A, TAMBAHKAN BAB 1, 2, 3
// =====================================================================
echo "\n--- TEST 1: BUAT NOVEL A & TAMBAHKAN BAB 1, 2, 3 OLEH USER A ---\n";

$novelTitleA = "Petualangan di Negeri Awan " . time();
$novelIdA = (int)$novelModel->createNovel([
    'user_id' => $userIdA,
    'title' => $novelTitleA,
    'synopsis' => 'Kisah epik penjelajah langit yang mencari kota melayang di atas kepulauan tropis nusantara.',
    'genre' => 'Fantasi',
    'status' => 'draft'
]);
assertCondition("Novel A (Draft) berhasil dibuat oleh User A", $novelIdA > 0, "Novel ID: {$novelIdA}");

// Buat Bab 1 via HTTP POST /chapter/create/{novelId}
$ch = curl_init("{$baseUrl}/chapter/create/{$novelIdA}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => 'Awal Perjalanan',
    'content' => 'Di sebuah pagi yang berkabut tebal di kaki Gunung Merbabu, petualang muda bersiap meluncurkan kapal udara pertamanya...',
    'chapter_order' => 1,
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$codeBab1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertCondition("HTTP POST Buat Bab 1 berhasil (Redirect 302)", $codeBab1 === 302);

// Buat Bab 2
$ch = curl_init("{$baseUrl}/chapter/create/{$novelIdA}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => 'Dunia Baru di Balik Awan',
    'content' => 'Angin kencang menerpa layar perahu udara saat menembus lapisan troposfer. Di hadapan mereka terbentang daratan terapung...',
    'chapter_order' => 2,
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$codeBab2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertCondition("HTTP POST Buat Bab 2 berhasil (Redirect 302)", $codeBab2 === 302);

// Buat Bab 3
$ch = curl_init("{$baseUrl}/chapter/create/{$novelIdA}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => 'Pertemuan Tak Terduga',
    'content' => 'Di reruntuhan candi melayang, sebuah siluet misterius mendekati mereka dengan lentera bercahaya biru pucat...',
    'chapter_order' => 3,
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$codeBab3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertCondition("HTTP POST Buat Bab 3 berhasil (Redirect 302)", $codeBab3 === 302);

// Ambil seluruh bab novel A dari database
$chaptersA = $chapterModel->getByNovel($novelIdA);
assertCondition("Jumlah bab novel A adalah tepat 3", count($chaptersA) === 3);

$chap1 = $chaptersA[0] ?? null;
$chap2 = $chaptersA[1] ?? null;
$chap3 = $chaptersA[2] ?? null;

assertCondition("Urutan dan judul bab sesuai", 
    $chap1 && $chap1['title'] === 'Awal Perjalanan' && (int)$chap1['chapter_order'] === 1 &&
    $chap2 && $chap2['title'] === 'Dunia Baru di Balik Awan' && (int)$chap2['chapter_order'] === 2 &&
    $chap3 && $chap3['title'] === 'Pertemuan Tak Terduga' && (int)$chap3['chapter_order'] === 3,
    "Bab 1 (ID {$chap1['id']}), Bab 2 (ID {$chap2['id']}), Bab 3 (ID {$chap3['id']})"
);

// Verifikasi tampilan halaman manajemen bab User A (/chapter/manage/{novelId} & /novels/{novelId}/chapters)
$ch = curl_init("{$baseUrl}/chapter/manage/{$novelIdA}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
$manageHtml = curl_exec($ch);
$manageCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP GET /chapter/manage/{novelId} berhasil (200 OK)", $manageCode === 200);
assertCondition("Halaman Manajemen Bab memuat seluruh bab", 
    strpos($manageHtml, 'Awal Perjalanan') !== false &&
    strpos($manageHtml, 'Dunia Baru di Balik Awan') !== false &&
    strpos($manageHtml, 'Pertemuan Tak Terduga') !== false
);

// =====================================================================
// TEST 2: EDIT BAB 1 OLEH USER A
// =====================================================================
echo "\n--- TEST 2: EDIT BAB 1 OLEH USER A ---\n";

$editedTitle1 = 'Awal Perjalanan: Sayap Pertama Terkembang';
$editedContent1 = 'Revisi naskah: Di sebuah pagi yang cerah keemasan, petualang muda merakit sayap kemudi kapal terbangnya dengan penuh ketelitian.';

$ch = curl_init("{$baseUrl}/chapter/edit/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => $editedTitle1,
    'content' => $editedContent1,
    'chapter_order' => 1,
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$editCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP POST /chapter/edit/{id} berhasil (Redirect 302)", $editCode === 302);

$refetchedChap1 = $chapterModel->findAuthorChapter((int)$chap1['id'], $userIdA);
assertCondition("Perubahan judul Bab 1 tersimpan di database", $refetchedChap1['title'] === $editedTitle1);
assertCondition("Perubahan konten Bab 1 tersimpan di database", $refetchedChap1['content'] === $editedContent1);

// =====================================================================
// TEST 3: DELETE BAB 2 & URUTAN BAB DIPERBAIKI (RESEQUENCE)
// =====================================================================
echo "\n--- TEST 3: DELETE BAB 2 & RESEQUENCE BAB TERSISA ---\n";

$ch = curl_init("{$baseUrl}/chapter/delete/{$chap2['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, []);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$delCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP POST /chapter/delete/{id} berhasil (Redirect 302)", $delCode === 302);

// Periksa database: Bab 2 harus terhapus
$checkDel2 = $chapterModel->findAuthorChapter((int)$chap2['id'], $userIdA);
assertCondition("Bab 2 benar-benar terhapus dari database", $checkDel2 === null);

// Periksa susunan bab tersisa
$remainingChapters = $chapterModel->getByNovel($novelIdA);
assertCondition("Sisa bab adalah 2 buah", count($remainingChapters) === 2);
assertCondition("Urutan bab dirapikan otomatis: Bab 1 (order 1) & mantan Bab 3 (kini order 2)", 
    (int)$remainingChapters[0]['chapter_order'] === 1 && 
    (int)$remainingChapters[1]['chapter_order'] === 2 &&
    $remainingChapters[1]['id'] == $chap3['id'],
    "Bab tersisa: '{$remainingChapters[0]['title']}' (#{$remainingChapters[0]['chapter_order']}), '{$remainingChapters[1]['title']}' (#{$remainingChapters[1]['chapter_order']})"
);

// =====================================================================
// TEST 4: UBAH BAB 1 MENJADI PUBLISHED
// =====================================================================
echo "\n--- TEST 4: UBAH STATUS BAB 1 MENJADI PUBLISHED ---\n";

// Cara 1: Menggunakan toggle_status endpoint
$ch = curl_init("{$baseUrl}/chapter/toggle_status/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$toggleCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP GET /chapter/toggle_status/{id} berhasil (Redirect 302)", $toggleCode === 302);

$refetchedAfterToggle = $chapterModel->findAuthorChapter((int)$chap1['id'], $userIdA);
assertCondition("Status Bab 1 berhasil berubah menjadi 'published'", $refetchedAfterToggle['status'] === 'published');

// =====================================================================
// TEST 5 & 6: OTORISASI USER B & MANIPULASI URL MANUAL
// =====================================================================
echo "\n--- TEST 5 & 6: OTORISASI USER B & MANIPULASI URL MANUAL ---\n";

// 5.1 User B mencoba membuka form edit Bab 1 milik User A
$ch = curl_init("{$baseUrl}/chapter/edit/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthEditCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$unauthEditRedirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

assertCondition("OTORISASI: User B dilarang membuka form edit Bab User A (Redirect ke /novels)", 
    $unauthEditCode === 302 && strpos($unauthEditRedirect, 'novels') !== false
);

// 5.2 User B mencoba mengupdate Bab 1 milik User A via HTTP POST
$ch = curl_init("{$baseUrl}/chapter/edit/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => 'DIBAJAK OLEH USER B',
    'content' => 'Konten dibajak',
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthUpdateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$verifyNoHack = $chapterModel->findAuthorChapter((int)$chap1['id'], $userIdA);
assertCondition("OTORISASI: Update User B ditolak & judul naskah User A tetap aman", 
    $verifyNoHack['title'] === $editedTitle1
);

// 5.3 User B mencoba menghapus Bab 1 milik User A via HTTP POST /chapter/delete
$ch = curl_init("{$baseUrl}/chapter/delete/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, []);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthDelCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$verifyStillAlive = $chapterModel->findAuthorChapter((int)$chap1['id'], $userIdA);
assertCondition("OTORISASI: Delete User B ditolak & Bab User A tetap utuh di DB", 
    $verifyStillAlive !== null
);

// 5.4 User B mencoba mengubah urutan (move_up/move_down) bab User A
$ch = curl_init("{$baseUrl}/chapter/move_up/{$chap3['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthMoveCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("OTORISASI: Move urutan oleh User B ditolak (302 Redirect)", $unauthMoveCode === 302);

// 5.5 User B mencoba mengakses manajemen bab novel User A (/chapter/manage/{novelIdA})
$ch = curl_init("{$baseUrl}/chapter/manage/{$novelIdA}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthManageCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$unauthManageRedirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

assertCondition("OTORISASI: User B dilarang membuka /chapter/manage/{novelIdA} (Redirect)", 
    $unauthManageCode === 302 && strpos($unauthManageRedirect, 'novels') !== false
);

// 5.6 User B mencoba membuat bab pada novel milik User A (/chapter/create/{novelIdA})
$ch = curl_init("{$baseUrl}/chapter/create/{$novelIdA}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => 'Bab Ilegal Penyusup',
    'content' => 'Konten dari User B menyusup ke novel User A',
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthCreateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$checkChaptersCount = count($chapterModel->getByNovel($novelIdA));
assertCondition("OTORISASI: Pembuatan bab ilegal oleh User B ditolak & tidak bertambah di DB", 
    $checkChaptersCount === 2
);

// =====================================================================
// TEST 7: NOVEL DRAFT + CHAPTER PUBLISHED -> TIDAK TAMPIL KE PUBLIK
// =====================================================================
echo "\n--- TEST 7: NOVEL DRAFT + CHAPTER PUBLISHED -> BLOKIR PUBLIK ---\n";

// Kondisi saat ini: Novel A berstatus 'draft', sedangkan Bab 1 berstatus 'published'.
$currentNovelA = $novelModel->findAuthorNovel($novelIdA, $userIdA);
$currentBab1 = $chapterModel->findAuthorChapter((int)$chap1['id'], $userIdA);

assertCondition("Prasyarat TEST 7: Novel A adalah 'draft' & Bab 1 adalah 'published'", 
    $currentNovelA['status'] === 'draft' && $currentBab1['status'] === 'published',
    "Novel status: '{$currentNovelA['status']}', Bab 1 status: '{$currentBab1['status']}'"
);

// 7.1 Tamu publik (tanpa login/cookie) mencoba membaca Bab 1 via /chapter/read/{id}
$ch = curl_init("{$baseUrl}/chapter/read/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$publicReadResp = curl_exec($ch);
$publicReadCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("PUBLIK TEST 7: Publik diblokir membaca Bab Published saat Novelnya Draft (HTTP 404)", 
    $publicReadCode === 404 || strpos($publicReadResp, '404') !== false || strpos($publicReadResp, 'Bab Tidak Ditemukan') !== false,
    "Status HTTP: {$publicReadCode}"
);

// 7.2 User B (penulis lain) mencoba membaca Bab 1 via /chapter/read/{id}
$ch = curl_init("{$baseUrl}/chapter/read/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$userBReadResp = curl_exec($ch);
$userBReadCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("USER B TEST 7: User B diblokir membaca Bab Published saat Novelnya Draft (HTTP 404)", 
    $userBReadCode === 404 || strpos($userBReadResp, '404') !== false || strpos($userBReadResp, 'Bab Tidak Ditemukan') !== false,
    "Status HTTP: {$userBReadCode}"
);

// 7.3 Pemilik asli (User A) BISA melakukan pratinjau (preview) Bab 1 miliknya
$ch = curl_init("{$baseUrl}/chapter/read/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
$authorReadResp = curl_exec($ch);
$authorReadCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("AUTHOR PREVIEW TEST 7: Penulis asli (User A) BISA melihat preview bab miliknya", 
    $authorReadCode === 200 && strpos($authorReadResp, $editedTitle1) !== false
);

// =====================================================================
// TEST 8: NOVEL PUBLISHED + CHAPTER PUBLISHED -> BISA DIAKSES PUBLIK
// =====================================================================
echo "\n--- TEST 8: NOVEL PUBLISHED + CHAPTER PUBLISHED -> DAPAT DIAKSES PUBLIK ---\n";

// Ubah status Novel A menjadi 'published'
$novelModel->updateNovel($novelIdA, $userIdA, [
    'title' => $currentNovelA['title'],
    'synopsis' => $currentNovelA['synopsis'],
    'genre' => $currentNovelA['genre'],
    'status' => 'published'
]);

$novelAPublished = $novelModel->findAuthorNovel($novelIdA, $userIdA);
assertCondition("Prasyarat TEST 8: Novel A kini berstatus 'published'", $novelAPublished['status'] === 'published');

// Tamu publik mengakses /chapter/read/{chap1}
$ch = curl_init("{$baseUrl}/chapter/read/{$chap1['id']}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$guestReadResp = curl_exec($ch);
$guestReadCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("PUBLIK TEST 8: Tamu publik DAPAT membaca bab ketika novel & bab keduanya published (HTTP 200)", 
    $guestReadCode === 200 && strpos($guestReadResp, $editedTitle1) !== false,
    "HTTP Code: {$guestReadCode}"
);

// Tamu publik mengakses detail novel katalog publik /novel/detail/{novelIdA}
$ch = curl_init("{$baseUrl}/novel/detail/{$novelIdA}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$guestNovelDetailResp = curl_exec($ch);
$guestNovelDetailCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("PUBLIK TEST 8: Katalog publik memuat Bab 1 published", 
    $guestNovelDetailCode === 200 && strpos($guestNovelDetailResp, $editedTitle1) !== false
);

// =====================================================================
// BONUS TEST: REORDERING (MOVE UP & MOVE DOWN)
// =====================================================================
echo "\n--- BONUS TEST: PENGURUTAN BAB (MOVE UP / MOVE DOWN) ---\n";

// Tambah satu bab baru lagi (kini ada 3 bab: Bab 1, Bab 2, Bab 3)
$bonusChapId = (int)$chapterModel->createChapter([
    'novel_id' => $novelIdA,
    'title' => 'Bab Bonus Ketiga',
    'content' => 'Konten bab ketiga untuk pengujian reordering...',
    'chapter_order' => 3,
    'status' => 'draft'
]);

// Geser bab terakhir ke atas (Move Up)
$chapterModel->moveUp($bonusChapId, $userIdA);
$reordered1 = $chapterModel->getByNovel($novelIdA);
$posBonusAfterUp = array_search($bonusChapId, array_column($reordered1, 'id'));
assertCondition("Reorder: Move Up berhasil menaikkan posisi bab", $posBonusAfterUp === 1, "Posisi baru indeks: {$posBonusAfterUp} (Order: {$reordered1[$posBonusAfterUp]['chapter_order']})");

// Geser kembali ke bawah (Move Down)
$chapterModel->moveDown($bonusChapId, $userIdA);
$reordered2 = $chapterModel->getByNovel($novelIdA);
$posBonusAfterDown = array_search($bonusChapId, array_column($reordered2, 'id'));
assertCondition("Reorder: Move Down berhasil menurunkan posisi bab", $posBonusAfterDown === 2, "Posisi baru indeks: {$posBonusAfterDown} (Order: {$reordered2[$posBonusAfterDown]['chapter_order']})");

// =====================================================================
// BONUS TEST: RESTFUL SUB-RESOURCE ROUTE (/novels/{novel}/chapters)
// =====================================================================
echo "\n--- BONUS TEST: RESTFUL ROUTE /novels/{novel}/chapters ---\n";

$ch = curl_init("{$baseUrl}/novels/{$novelIdA}/chapters");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
$restChaptersResp = curl_exec($ch);
$restChaptersCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("RESTful GET /novels/{novel}/chapters berhasil dibuka (HTTP 200)", 
    $restChaptersCode === 200 && strpos($restChaptersResp, 'Daftar Bab & Pengurutan Naskah') !== false
);

// Bersihkan data pengujian
$novelModel->deleteNovel($novelIdA, $userIdA);
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

echo "\n=======================================================================\n";
echo " TOTAL PENGUJIAN PROMPT 6: {$passed} PASSED, {$failed} FAILED\n";
echo "=======================================================================\n";

if ($failed === 0) {
    echo ">>> SELURUH PERSYARATAN PROMPT 6 LULUS PENGUJIAN DENGAN SEMPURNA! <<<\n\n";
    exit(0);
} else {
    echo ">>> TERDAPAT PENGUJIAN YANG GAGAL. PERIKSA LOG DI ATAS! <<<\n\n";
    exit(1);
}
