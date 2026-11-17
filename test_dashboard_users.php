<?php
/**
 * karyaNovel - Test Dashboard Isolation & Multi-User Verification
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

echo "=======================================================\n";
echo " karyaNovel - Dashboard User Isolation Verification\n";
echo "=======================================================\n\n";

$userModel = new User();
$novelModel = new Novel();
$chapterModel = new Chapter();
$aiHistoryModel = new AiHistory();

$passed = 0;
$failed = 0;

function assertTest(string $title, bool $condition, string $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        if ($details) echo "         -> {$details}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$title}\n";
        if ($details) echo "         -> {$details}\n";
        $failed++;
    }
}

// -------------------------------------------------------------
// USER 1: Ahmad Rian (User ID 2)
// -------------------------------------------------------------
echo "1. PENGUJIAN DASHBOARD PENULIS 1 (Ahmad Rian - user_id = 2):\n";
$statsU1 = $novelModel->getAuthorDashboardStats(2);
$novelsU1 = $novelModel->getRecentByAuthor(2);

assertTest(
    "Statistik Total Novel Penulis 1",
    $statsU1['total_novels'] === 2,
    "Total: {$statsU1['total_novels']} novel (Expected: 2)"
);
assertTest(
    "Statistik Published Penulis 1",
    $statsU1['published_novels'] === 2,
    "Published: {$statsU1['published_novels']} (Expected: 2)"
);
assertTest(
    "Statistik Draft Penulis 1",
    $statsU1['draft_novels'] === 0,
    "Draft: {$statsU1['draft_novels']} (Expected: 0)"
);
assertTest(
    "Statistik Total Bab Penulis 1",
    $statsU1['total_chapters'] === 3,
    "Total Bab: {$statsU1['total_chapters']} (Expected: 3)"
);

// Pastikan Penulis 1 TIDAK melihat novel milik Penulis 2 (Clara Sinta)
$u1HasOtherNovel = false;
foreach ($novelsU1 as $n) {
    if ($n['user_id'] != 2) {
        $u1HasOtherNovel = true;
    }
}
assertTest(
    "Isolasi Novel Penulis 1: Tidak mengandung novel user lain",
    !$u1HasOtherNovel,
    "Seluruh novel di dashboard milik user_id = 2."
);

// -------------------------------------------------------------
// USER 2: Clara Sinta (User ID 3)
// -------------------------------------------------------------
echo "\n2. PENGUJIAN DASHBOARD PENULIS 2 (Clara Sinta - user_id = 3):\n";
$statsU2 = $novelModel->getAuthorDashboardStats(3);
$novelsU2 = $novelModel->getRecentByAuthor(3);

assertTest(
    "Statistik Total Novel Penulis 2",
    $statsU2['total_novels'] === 2,
    "Total: {$statsU2['total_novels']} novel (Expected: 2)"
);
assertTest(
    "Statistik Published Penulis 2",
    $statsU2['published_novels'] === 1,
    "Published: {$statsU2['published_novels']} (Expected: 1)"
);
assertTest(
    "Statistik Draft Penulis 2",
    $statsU2['draft_novels'] === 1,
    "Draft: {$statsU2['draft_novels']} (Expected: 1)"
);
assertTest(
    "Statistik Total Bab Penulis 2",
    $statsU2['total_chapters'] === 1,
    "Total Bab: {$statsU2['total_chapters']} (Expected: 1)"
);

$u2HasOtherNovel = false;
foreach ($novelsU2 as $n) {
    if ($n['user_id'] != 3) {
        $u2HasOtherNovel = true;
    }
}
assertTest(
    "Isolasi Novel Penulis 2: Tidak mengandung novel user lain",
    !$u2HasOtherNovel,
    "Seluruh novel di dashboard milik user_id = 3."
);

// -------------------------------------------------------------
// USER 3: User Baru dengan 0 Novel (Empty State)
// -------------------------------------------------------------
echo "\n3. PENGUJIAN DASHBOARD USER BARU (0 NOVEL / EMPTY STATE):\n";
$tempEmail = 'empty_author_' . time() . '@karyanovel.id';
$newUserId = $userModel->register([
    'name' => 'Penulis Baru Tanpa Novel',
    'email' => $tempEmail,
    'password' => 'password123',
    'role' => 'user'
]);

$statsU3 = $novelModel->getAuthorDashboardStats((int)$newUserId);
$novelsU3 = $novelModel->getRecentByAuthor((int)$newUserId);

assertTest(
    "Statistik 0 Novel untuk User Baru",
    $statsU3['total_novels'] === 0 && $statsU3['total_chapters'] === 0,
    "Total: 0 novel, 0 bab."
);
assertTest(
    "Daftar novel kosong (Empty State trigger)",
    empty($novelsU3),
    "Array novel kosong -> View akan menampilkan komponen Empty State."
);

// Bersihkan user temp
$userModel->delete($newUserId);

// -------------------------------------------------------------
// HTTP E2E TEST: Render Dashboard Penulis 1 & Penulis 2
// -------------------------------------------------------------
echo "\n4. PENGUJIAN HTTP E2E PERENDERAN DASHBOARD:\n";

$cookie1 = __DIR__ . '/c1.txt';
$cookie2 = __DIR__ . '/c2.txt';

// Login User 1
$ch = curl_init(base_url('auth/login'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['email' => 'penulis1@karyanovel.id', 'password' => 'password123']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie1);
curl_exec($ch);
curl_close($ch);

// Ambil halaman dashboard User 1
$ch = curl_init(base_url('dashboard'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie1);
$html1 = curl_exec($ch);
$code1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest(
    "Dashboard User 1 berhasil dimuat (HTTP 200)",
    $code1 === 200 && strpos($html1, 'Ahmad Rian') !== false,
    "Halaman menampilkan nama 'Ahmad Rian'."
);
assertTest(
    "Dashboard User 1 memuat judul novel miliknya",
    strpos($html1, 'Lembah Bintang') !== false && strpos($html1, 'Legenda Pedang Petir') !== false,
    "Judul novel milik User 1 ditemukan di HTML."
);
assertTest(
    "Dashboard User 1 TIDAK memuat judul novel User 2",
    strpos($html1, 'Detak Jantung di Ujung Senja') === false,
    "Novel User 2 berhasil diisolasi dan tidak tampil."
);

if (file_exists($cookie1)) unlink($cookie1);
if (file_exists($cookie2)) unlink($cookie2);

echo "\n=======================================================\n";
echo " HASIL PENGUJIAN DASHBOARD: {$passed} Passed, {$failed} Failed\n";
echo "=======================================================\n";

if ($failed === 0) {
    echo ">>> DASHBOARD PENULIS & ISOLASI DATA TERVERIFIKASI SEMPURNA! <<<\n\n";
}
