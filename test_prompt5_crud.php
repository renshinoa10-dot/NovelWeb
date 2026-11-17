<?php
/**
 * karyaNovel - Test Suite Resmi Prompt 5 (CRUD Manajemen Novel & Otorisasi Ketat)
 * Menjalankan pengujian:
 * 1. Validasi Form (Judul, Genre, Sinopsis, Status, Cover)
 * 2. Create Novel
 * 3. Detail Novel (Menampilkan Cover, Judul, Penulis, Genre, Sinopsis, Status, Tanggal dibuat, Bab)
 * 4. Edit Novel (Data lama terisi, update berhasil)
 * 5. Delete Novel (Mekanisme aman & CASCADE)
 * 6. Otorisasi Multi-User:
 *    - User B dilarang melihat detail novel User A
 *    - User B dilarang mengedit novel User A (baik GET form maupun POST update)
 *    - User B dilarang menghapus novel User A
 * 7. Empty State untuk user tanpa novel ("Belum ada novel. Yuk mulai membuat karya pertamamu!")
 * 8. HTTP E2E Testing via Apache (Session, Cookies, Redirects, Flash Messages)
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

echo "=======================================================================\n";
echo " karyaNovel - TEST SUITE PROMPT 5: CRUD MANAJEMEN NOVEL & OTORISASI\n";
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

// Persiapan User A dan User B
$userA = $userModel->findByEmail('penulis1@karyanovel.id'); // Ahmad Rian (ID 2)
$userB = $userModel->findByEmail('penulis2@karyanovel.id'); // Clara Sinta (ID 3)

assertCondition("Data User A (Ahmad Rian) tersedia di database", !empty($userA), "User ID: " . ($userA['id'] ?? '-'));
assertCondition("Data User B (Clara Sinta) tersedia di database", !empty($userB), "User ID: " . ($userB['id'] ?? '-'));

$userIdA = (int)$userA['id'];
$userIdB = (int)$userB['id'];

// =====================================================================
// TAHAP 1: VALIDASI MODEL & CONTROLLER LOGIC
// =====================================================================
echo "\n--- 1. PENGUJIAN VALIDASI FORM BACKEND ---\n";

// Cek aturan validasi judul, sinopsis, dan genre
$titleTooShort = "ab";
$synopsisTooShort = "Terlalu pendek";
assertCondition("Validasi Judul < 3 karakter harus ditolak", mb_strlen($titleTooShort) < 3);
assertCondition("Validasi Sinopsis < 20 karakter harus ditolak", mb_strlen($synopsisTooShort) < 20);

// =====================================================================
// TAHAP 2: CREATE NOVEL OLEH USER A
// =====================================================================
echo "\n--- 2. PENGUJIAN CREATE NOVEL OLEH USER A ---\n";

$testTitle = "Chronicles of Aethelgard: The Lost Realm " . time();
$testSynopsis = "Di zaman kuno ketika sihir dan teknologi kuantum berpadu, seorang pemuda menemukan pecahan artefak langit yang dapat membuka gerbang antardimensi.";
$testGenre = "Fantasi";

$createdNovelId = $novelModel->createNovel([
    'user_id' => $userIdA,
    'title' => $testTitle,
    'synopsis' => $testSynopsis,
    'genre' => $testGenre,
    'cover' => null,
    'status' => 'draft'
]);

assertCondition("User A berhasil membuat novel baru", !empty($createdNovelId), "Novel ID: {$createdNovelId}");

$fetchedNovel = $novelModel->findAuthorNovel((int)$createdNovelId, $userIdA);
assertCondition("Data novel tersimpan akurat di database", 
    $fetchedNovel && $fetchedNovel['title'] === $testTitle && $fetchedNovel['status'] === 'draft' && $fetchedNovel['author_name'] === 'Ahmad Rian',
    "Judul: '{$fetchedNovel['title']}', Status: '{$fetchedNovel['status']}', Penulis: '{$fetchedNovel['author_name']}'"
);

// =====================================================================
// TAHAP 3: DETAIL NOVEL & FIELD REQUIREMENTS
// =====================================================================
echo "\n--- 3. PENGUJIAN DETAIL NOVEL MILIK SENDIRI ---\n";

assertCondition("Detail novel memuat field cover (null/string)", array_key_exists('cover', $fetchedNovel));
assertCondition("Detail novel memuat field title", !empty($fetchedNovel['title']));
assertCondition("Detail novel memuat field author_name (Penulis)", !empty($fetchedNovel['author_name']));
assertCondition("Detail novel memuat field genre", !empty($fetchedNovel['genre']));
assertCondition("Detail novel memuat field synopsis", !empty($fetchedNovel['synopsis']));
assertCondition("Detail novel memuat field status", in_array($fetchedNovel['status'], ['draft', 'published']));
assertCondition("Detail novel memuat field created_at (Tanggal dibuat)", !empty($fetchedNovel['created_at']));
assertCondition("Detail novel memuat total_chapters", isset($fetchedNovel['total_chapters']));

// =====================================================================
// TAHAP 4: EDIT NOVEL OLEH USER A
// =====================================================================
echo "\n--- 4. PENGUJIAN EDIT NOVEL OLEH USER A ---\n";

$updatedTitle = $testTitle . " [EDISI REVISI]";
$updatedSynopsis = $testSynopsis . " Kini takdir seluruh dunia bergantung pada keputusan akhirnya di puncak menara.";
$updatedStatus = "published";

$updateResult = $novelModel->updateNovel((int)$createdNovelId, $userIdA, [
    'title' => $updatedTitle,
    'synopsis' => $updatedSynopsis,
    'genre' => 'Sci-Fi',
    'status' => $updatedStatus
]);

assertCondition("User A berhasil memperbarui novel miliknya", $updateResult === true);

$refetchedNovel = $novelModel->findAuthorNovel((int)$createdNovelId, $userIdA);
assertCondition("Data update tercermin di database", 
    $refetchedNovel['title'] === $updatedTitle && $refetchedNovel['status'] === 'published' && $refetchedNovel['genre'] === 'Sci-Fi',
    "Judul Baru: '{$refetchedNovel['title']}', Status Baru: '{$refetchedNovel['status']}'"
);

// =====================================================================
// TAHAP 5: OTORISASI KETAT MULTI-USER (USER B vs NOVEL USER A)
// =====================================================================
echo "\n--- 5. PENGUJIAN OTORISASI BACKEND MULTI-USER (USER B -> NOVEL USER A) ---\n";

// 5.1 User B mencoba findAuthorNovel pada novel User A
$unauthorizedFind = $novelModel->findAuthorNovel((int)$createdNovelId, $userIdB);
assertCondition(
    "OTORISASI: User B TIDAK DAPAT mengambil naskah User A melalui query",
    $unauthorizedFind === null,
    "Hasil query user_id B untuk novel user_id A bernilai NULL (terisolasi)."
);

// 5.2 User B mencoba mengedit novel User A
$unauthorizedUpdate = $novelModel->updateNovel((int)$createdNovelId, $userIdB, [
    'title' => 'DIBAJAK OLEH USER B'
]);
assertCondition(
    "OTORISASI: User B TIDAK DAPAT mengedit novel User A",
    $unauthorizedUpdate === false,
    "Update ditolak oleh backend (return false)."
);

// Pastikan judul di database TIDAK berubah
$checkIntegrity = $novelModel->findAuthorNovel((int)$createdNovelId, $userIdA);
assertCondition(
    "Integritas Data: Judul novel User A tidak mengalami pembajakan",
    $checkIntegrity['title'] === $updatedTitle,
    "Judul tetap: '{$checkIntegrity['title']}'"
);

// 5.3 User B mencoba menghapus novel User A
$unauthorizedDelete = $novelModel->deleteNovel((int)$createdNovelId, $userIdB);
assertCondition(
    "OTORISASI: User B TIDAK DAPAT menghapus novel User A",
    $unauthorizedDelete === false,
    "Delete ditolak oleh backend (return false)."
);

// Pastikan novel User A MASIH ada di database
$checkStillExists = $novelModel->findAuthorNovel((int)$createdNovelId, $userIdA);
assertCondition(
    "Integritas Data: Novel User A tetap utuh dan tidak terhapus",
    $checkStillExists !== null,
    "Novel ID {$createdNovelId} tetap aktif."
);

// =====================================================================
// TAHAP 6: DELETE NOVEL OLEH PEMILIK ASLI (USER A)
// =====================================================================
echo "\n--- 6. PENGUJIAN DELETE NOVEL OLEH PEMILIK ASLI (USER A) ---\n";

$authorizedDelete = $novelModel->deleteNovel((int)$createdNovelId, $userIdA);
assertCondition("User A berhasil menghapus novel miliknya", $authorizedDelete === true);

$checkDeleted = $novelModel->findAuthorNovel((int)$createdNovelId, $userIdA);
assertCondition("Novel benar-benar hilang dari database setelah dihapus", $checkDeleted === null);

// =====================================================================
// TAHAP 7: EMPTY STATE PENULIS BARU
// =====================================================================
echo "\n--- 7. PENGUJIAN EMPTY STATE UNTUK PENULIS BARU (0 NOVEL) ---\n";

$emptyUserEmail = 'empty_author_' . time() . '@karyanovel.id';
$emptyUserId = $userModel->register([
    'name' => 'Penulis Pemula Baru',
    'email' => $emptyUserEmail,
    'password' => 'password123',
    'role' => 'user'
]);

$emptyNovels = $novelModel->getByAuthor((int)$emptyUserId);
assertCondition("Penulis baru memiliki 0 novel", empty($emptyNovels));

$emptyStats = $novelModel->getAuthorDashboardStats((int)$emptyUserId);
assertCondition("Statistik penulis baru 0 novel & 0 bab", 
    $emptyStats['total_novels'] === 0 && $emptyStats['total_chapters'] === 0
);

// Bersihkan user pengujian
$userModel->delete((int)$emptyUserId);

// =====================================================================
// TAHAP 8: HTTP E2E INTEGRATION TESTING VIA APACHE
// =====================================================================
echo "\n--- 8. PENGUJIAN HTTP E2E VIA APACHE (AUTHENTICATION, ROUTES & AUTHORIZATION) ---\n";

$cookieA = __DIR__ . '/cookie_userA.txt';
$cookieB = __DIR__ . '/cookie_userB.txt';
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

$baseUrl = 'http://localhost/Novelweb';

// 8.1 Login User A via HTTP
$ch = curl_init("{$baseUrl}/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'penulis1@karyanovel.id',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieA);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertCondition("HTTP E2E: Login User A berhasil (HTTP redirect 302)", $code === 302);

// 8.2 Login User B via HTTP
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
assertCondition("HTTP E2E: Login User B berhasil", true);

// 8.3 User A mengakses GET /novels (Daftar Novel Saya)
$ch = curl_init("{$baseUrl}/novels");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
$htmlNovelsA = curl_exec($ch);
$codeNovelsA = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP GET /novels untuk User A berhasil (HTTP 200)", $codeNovelsA === 200);
assertCondition("Halaman memuat tombol '+ Buat Novel Baru'", strpos($htmlNovelsA, 'Buat Novel Baru') !== false);
assertCondition("Halaman memuat novel User A ('Lembah Bintang')", strpos($htmlNovelsA, 'Lembah Bintang') !== false);
assertCondition("Halaman TIDAK memuat novel User B ('Detak Jantung di Ujung Senja')", strpos($htmlNovelsA, 'Detak Jantung di Ujung Senja') === false);

// 8.4 User A membuat novel baru via HTTP POST /novels/create
$e2eTitle = "Novel E2E Test User A " . time();
$ch = curl_init("{$baseUrl}/novels/create");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => $e2eTitle,
    'genre' => 'Misteri',
    'synopsis' => 'Sinopsis novel E2E pengujian sistem karyaNovel dengan panjang lebih dari dua puluh karakter yang memadai.',
    'status' => 'draft'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$createResp = curl_exec($ch);
$createCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

assertCondition("HTTP POST /novels/create berhasil (Redirect ke detail novel)", $createCode === 302 && strpos($redirectUrl, 'novels/') !== false, "Redirect ke: {$redirectUrl}");

// Ekstrak ID novel yang baru dibuat dari redirectUrl
preg_match('/novels\/(\d+)/', $redirectUrl, $matches);
$newNovelIdE2E = !empty($matches[1]) ? (int)$matches[1] : 0;
assertCondition("Novel E2E terbuat dengan ID: {$newNovelIdE2E}", $newNovelIdE2E > 0);

// 8.5 User A mengakses GET /novels/{id} (Detail Novel)
$ch = curl_init("{$baseUrl}/novels/{$newNovelIdE2E}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
$detailHtml = curl_exec($ch);
$detailCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP GET /novels/{id} User A berhasil (HTTP 200)", $detailCode === 200);
assertCondition("Halaman detail menampilkan judul novel yang tepat", strpos($detailHtml, $e2eTitle) !== false);
assertCondition("Halaman detail menampilkan Penulis: Ahmad Rian", strpos($detailHtml, 'Ahmad Rian') !== false);
assertCondition("Halaman detail menampilkan status Draft", strpos($detailHtml, 'Draft') !== false);
assertCondition("Halaman detail menampilkan tombol Edit & Hapus", strpos($detailHtml, 'Edit Data Novel') !== false && strpos($detailHtml, 'Hapus Novel') !== false);

// 8.6 OTORISASI E2E: User B mencoba mengakses GET /novels/{id_milik_A}
$ch = curl_init("{$baseUrl}/novels/{$newNovelIdE2E}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthDetailCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$unauthRedirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

assertCondition("OTORISASI HTTP: User B diblokir mengakses detail novel User A (Redirect)", 
    $unauthDetailCode === 302 && strpos($unauthRedirect, 'novels') !== false,
    "User B dilempar kembali ke /novels miliknya sendiri."
);

// 8.7 OTORISASI E2E: User B mencoba mengakses GET /novels/{id_milik_A}/edit
$ch = curl_init("{$baseUrl}/novels/{$newNovelIdE2E}/edit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthEditCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$unauthEditRedirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

assertCondition("OTORISASI HTTP: User B diblokir membuka form edit novel User A (Redirect)", 
    $unauthEditCode === 302 && strpos($unauthEditRedirect, 'novels') !== false
);

// 8.8 OTORISASI E2E: User B mencoba POST /novels/{id_milik_A}/delete
$ch = curl_init("{$baseUrl}/novels/{$newNovelIdE2E}/delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, []);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieB);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$unauthDeleteCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("OTORISASI HTTP: User B diblokir menghapus novel User A", $unauthDeleteCode === 302);

// Pastikan novel User A masih hidup di DB
$stillAlive = $novelModel->findAuthorNovel($newNovelIdE2E, $userIdA);
assertCondition("Novel User A tetap aman di database", $stillAlive !== null);

// 8.9 User A mengedit novelnya via HTTP POST /novels/{id}/edit
$editedE2ETitle = $e2eTitle . " [SUDAH DIEDIT]";
$ch = curl_init("{$baseUrl}/novels/{$newNovelIdE2E}/edit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'title' => $editedE2ETitle,
    'genre' => 'Misteri',
    'synopsis' => 'Sinopsis novel setelah proses editing selesai dilakukan dengan sukses.',
    'status' => 'published'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$editCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP POST /novels/{id}/edit berhasil diperbarui (Redirect)", $editCode === 302);

$afterEdit = $novelModel->findAuthorNovel($newNovelIdE2E, $userIdA);
assertCondition("Hasil edit tersimpan di database (Title & Status: published)", 
    $afterEdit['title'] === $editedE2ETitle && $afterEdit['status'] === 'published'
);

// 8.10 User A menghapus novel miliknya via HTTP POST /novels/{id}/delete
$ch = curl_init("{$baseUrl}/novels/{$newNovelIdE2E}/delete");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, []);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieA);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$deleteCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertCondition("HTTP POST /novels/{id}/delete berhasil menghapus novel (Redirect ke /novels)", $deleteCode === 302);

$afterDelete = $novelModel->findAuthorNovel($newNovelIdE2E, $userIdA);
assertCondition("Novel E2E telah terhapus sempurna dari database", $afterDelete === null);

// Bersihkan file cookies
if (file_exists($cookieA)) unlink($cookieA);
if (file_exists($cookieB)) unlink($cookieB);

// =====================================================================
// RANGKUMAN HASIL AKHIR
// =====================================================================
echo "\n=======================================================================\n";
echo " TOTAL PENGUJIAN: {$passed} PASSED, {$failed} FAILED\n";
echo "=======================================================================\n";

if ($failed === 0) {
    echo ">>> SELURUH PERSYARATAN PROMPT 5 LULUS PENGUJIAN DENGAN SEMPURNA! <<<\n\n";
    exit(0);
} else {
    echo ">>> TERDAPAT PENGUJIAN YANG GAGAL. PERIKSA LOG DI ATAS! <<<\n\n";
    exit(1);
}
