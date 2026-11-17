<?php
/**
 * karyaNovel - Master Comprehensive End-to-End Test Suite
 * Menguji seluruh fitur aplikasi secara terpadu melalui HTTP & Logic
 */

require_once __DIR__ . '/app/config/config.php';
require_once APPROOT . '/helpers/helpers.php';
require_once APPROOT . '/core/Session.php';
require_once APPROOT . '/core/Database.php';
require_once APPROOT . '/core/Model.php';
require_once APPROOT . '/core/Controller.php';
require_once APPROOT . '/models/User.php';
require_once APPROOT . '/models/Novel.php';
require_once APPROOT . '/models/Chapter.php';
require_once APPROOT . '/models/AiHistory.php';
require_once APPROOT . '/services/AiService.php';

$baseUrl = 'http://localhost/Novelweb';
$results = [];

function recordTest(string $category, string $feature, bool $status, string $details = ''): void {
    global $results;
    $results[] = [
        'category' => $category,
        'feature' => $feature,
        'status' => $status ? 'PASS' : 'FAIL',
        'details' => $details
    ];
    $icon = $status ? '[PASS]' : '[FAIL]';
    echo " {$icon} [{$category}] {$feature}" . ($details ? " -> {$details}" : "") . "\n";
}

function curlReq(string $method, string $url, array $data = [], ?string $cookieFile = null, array $headers = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($headers) && in_array('Content-Type: application/json', $headers)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    return ['code' => $code, 'body' => $body, 'redirect' => $redirectUrl];
}

function getCsrf(string $url, ?string $cookieFile = null): string {
    $resp = curlReq('GET', $url, [], $cookieFile);
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $resp['body'], $m)) {
        return $m[1];
    }
    return '';
}

function loginUser(string $email, string $password, string $cookieFile): bool {
    global $baseUrl;
    $csrf = getCsrf("{$baseUrl}/auth/login", $cookieFile);
    $resp = curlReq('POST', "{$baseUrl}/auth/login", [
        'email' => $email,
        'password' => $password,
        'csrf_token' => $csrf
    ], $cookieFile);
    return ($resp['code'] === 302);
}

echo "========================================================\n";
echo "   KARYANOVEL - MASTER COMPREHENSIVE TEST SUITE\n";
echo "========================================================\n\n";

$db = Database::getInstance();
$userModel = new User();
$novelModel = new Novel();
$chapterModel = new Chapter();
$aiHistoryModel = new AiHistory();

// Cookie files for testing
$cookieAuthor = __DIR__ . '/cookie_master_author.txt';
$cookieOther = __DIR__ . '/cookie_master_other.txt';
$cookieAdmin = __DIR__ . '/cookie_master_admin.txt';
$cookieGuest = __DIR__ . '/cookie_master_guest.txt';

@unlink($cookieAuthor);
@unlink($cookieOther);
@unlink($cookieAdmin);
@unlink($cookieGuest);

// =============================================================================
// 1. AUTH TESTING
// =============================================================================
echo "--- 1. Testing AUTH Features ---\n";

$testEmailAuthor = 'master_author_' . time() . '@karyanovel.test';
$testEmailOther = 'master_other_' . time() . '@karyanovel.test';
$testEmailAdmin = 'master_admin_' . time() . '@karyanovel.test';
$testPass = 'Password123!';

// 1.1 Register Author
$csrfReg = getCsrf("{$baseUrl}/auth/register", $cookieAuthor);
$regResp = curlReq('POST', "{$baseUrl}/auth/register", [
    'name' => 'Penulis Master',
    'email' => $testEmailAuthor,
    'password' => $testPass,
    'confirm_password' => $testPass,
    'csrf_token' => $csrfReg
], $cookieAuthor);
recordTest('AUTH', 'Register', $regResp['code'] === 302, 'HTTP ' . $regResp['code']);

// Register User Other (for multi-tenant tests)
$csrfRegOther = getCsrf("{$baseUrl}/auth/register", $cookieOther);
$regOtherResp = curlReq('POST', "{$baseUrl}/auth/register", [
    'name' => 'User Kedua',
    'email' => $testEmailOther,
    'password' => $testPass,
    'confirm_password' => $testPass,
    'csrf_token' => $csrfRegOther
], $cookieOther);
recordTest('AUTH', 'Register User Lain', $regOtherResp['code'] === 302, 'HTTP ' . $regOtherResp['code']);

// Register Admin in DB
$adminId = (int)$userModel->register([
    'name' => 'Admin Master',
    'email' => $testEmailAdmin,
    'password' => $testPass,
    'role' => 'admin'
]);
recordTest('AUTH', 'Create Admin User', $adminId > 0, "Admin ID: #{$adminId}");

// 1.2 Invalid Login Test
$csrfInv = getCsrf("{$baseUrl}/auth/login", $cookieGuest);
$invLogin = curlReq('POST', "{$baseUrl}/auth/login", [
    'email' => $testEmailAuthor,
    'password' => 'WrongPassword!',
    'csrf_token' => $csrfInv
], $cookieGuest);
$invLoginCheck = ($invLogin['code'] === 200 && str_contains($invLogin['body'], 'Kombinasi alamat email atau kata sandi tidak cocok'));
recordTest('AUTH', 'Invalid Login Handling', $invLoginCheck, 'Proper error feedback displayed');

// 1.3 Valid Login Test
// Logout author first to test clean login
$logoutResp = curlReq('GET', "{$baseUrl}/auth/logout", [], $cookieAuthor);
recordTest('AUTH', 'Logout', $logoutResp['code'] === 302 && str_contains($logoutResp['redirect'] ?? '', 'login'), 'Session destroyed and redirected');

$valLogin = loginUser($testEmailAuthor, $testPass, $cookieAuthor);
recordTest('AUTH', 'Login', $valLogin, 'Redirected to dashboard');

// Admin Login
$valAdminLogin = loginUser($testEmailAdmin, $testPass, $cookieAdmin);
recordTest('AUTH', 'Admin Login', $valAdminLogin, 'Admin session authenticated');

// 1.4 Unauthorized Access Guard
$guestAccess = curlReq('GET', "{$baseUrl}/dashboard");
recordTest('AUTH', 'Unauthorized Access Guard', $guestAccess['code'] === 302 && str_contains($guestAccess['redirect'] ?? '', 'login'), 'Protected route redirects to login');

// Get Author User ID
$authorUser = $userModel->findByEmail($testEmailAuthor);
$authorUserId = (int)$authorUser['id'];

// =============================================================================
// 2. NOVEL TESTING
// =============================================================================
echo "\n--- 2. Testing NOVEL Features ---\n";

// 2.1 Create Novel (Draft)
$csrfCreateNovel = getCsrf("{$baseUrl}/novels/create", $cookieAuthor);
$novelTitle = 'Mahkota Bayangan ' . time();
$createNovelResp = curlReq('POST', "{$baseUrl}/novels/create", [
    'title' => $novelTitle,
    'genre' => 'Fantasi',
    'synopsis' => 'Kisah epik perebutan tahta kerajaan kuno yang diselimuti kabut mistis dan konspirasi rahasia kerajaan.',
    'status' => 'draft',
    'csrf_token' => $csrfCreateNovel
], $cookieAuthor);

recordTest('NOVEL', 'Create Draft Novel', $createNovelResp['code'] === 302, 'HTTP 302 Redirect');
preg_match('/novels\/(\d+)/', $createNovelResp['redirect'] ?? '', $matches);
$novelId = (int)($matches[1] ?? 0);

if ($novelId === 0) {
    // Fallback: cari ID dari database
    $latestNovel = $novelModel->whereFirst('user_id', $authorUserId, '=', 'id DESC');
    $novelId = (int)($latestNovel['id'] ?? 0);
}

// 2.2 Read Novel Detail (Author Workspace)
$novelDetailResp = curlReq('GET', "{$baseUrl}/novels/{$novelId}", [], $cookieAuthor);
$hasNovelDetail = ($novelDetailResp['code'] === 200 && str_contains($novelDetailResp['body'], $novelTitle));
recordTest('NOVEL', 'Read Novel Detail (Workspace)', $hasNovelDetail, "Novel ID: #{$novelId}");

// 2.3 Update Novel
$csrfEditNovel = getCsrf("{$baseUrl}/novels/{$novelId}/edit", $cookieAuthor);
$updatedNovelTitle = $novelTitle . ' [Revisi]';
$updateNovelResp = curlReq('POST', "{$baseUrl}/novels/{$novelId}/edit", [
    'title' => $updatedNovelTitle,
    'genre' => 'Fantasi',
    'synopsis' => 'Sinopsis diperbarui dengan lebih banyak rincian intrik politik dan karakter utama.',
    'status' => 'draft',
    'csrf_token' => $csrfEditNovel
], $cookieAuthor);

$novelInDb = $novelModel->find($novelId);
recordTest('NOVEL', 'Update Novel', $novelInDb['title'] === $updatedNovelTitle, 'Title updated in database');

// 2.4 Toggle Status (Draft -> Published)
$csrfToggle = getCsrf("{$baseUrl}/novels/{$novelId}", $cookieAuthor);
$togglePubResp = curlReq('POST', "{$baseUrl}/novels/{$novelId}/toggle_status", [
    'csrf_token' => $csrfToggle
], $cookieAuthor);
$novelInDbAfterPub = $novelModel->find($novelId);
recordTest('NOVEL', 'Publish Novel (Draft -> Published)', ($novelInDbAfterPub['status'] ?? '') === 'published', 'Status changed to published');

// 2.5 Toggle Status Back (Published -> Draft)
$csrfToggle2 = getCsrf("{$baseUrl}/novels/{$novelId}", $cookieAuthor);
$toggleDraftResp = curlReq('POST', "{$baseUrl}/novels/{$novelId}/toggle_status", [
    'csrf_token' => $csrfToggle2
], $cookieAuthor);
$novelInDbAfterDraft = $novelModel->find($novelId);
recordTest('NOVEL', 'Unpublish Novel (Published -> Draft)', ($novelInDbAfterDraft['status'] ?? '') === 'draft', 'Status changed back to draft');

// Jadikan published lagi agar dapat diuji di halaman baca & katalog publik
$novelModel->changeStatus($novelId, 'published');

// =============================================================================
// 3. CHAPTER & EDITOR TESTING
// =============================================================================
echo "\n--- 3. Testing CHAPTER & EDITOR Features ---\n";

// 3.1 Create Chapter (Published)
$csrfChapCreate = getCsrf("{$baseUrl}/chapter/create/{$novelId}", $cookieAuthor);
$chap1Title = 'Bab 1: Gerbang Kabut';
$chap1Content = 'Matahari terbenam perlahan di balik benteng hitam, menyisakan bayang-bayang panjang di atas tanah gersang.';
$createChap1Resp = curlReq('POST', "{$baseUrl}/chapter/create/{$novelId}", [
    'title' => $chap1Title,
    'content' => $chap1Content,
    'chapter_order' => 1,
    'status' => 'published',
    'csrf_token' => $csrfChapCreate
], $cookieAuthor);

$chaptersOfNovel = $chapterModel->getByNovel($novelId);
$chap1Id = (int)($chaptersOfNovel[0]['id'] ?? 0);
recordTest('CHAPTER', 'Create Chapter 1 (Published)', $chap1Id > 0 && ($chaptersOfNovel[0]['status'] ?? '') === 'published', "Chapter ID: #{$chap1Id}");

// Create Chapter 2 (Draft)
$csrfChapCreate2 = getCsrf("{$baseUrl}/chapter/create/{$novelId}", $cookieAuthor);
$createChap2Resp = curlReq('POST', "{$baseUrl}/chapter/create/{$novelId}", [
    'title' => 'Bab 2: Bisikan di Lorong Gelap',
    'content' => 'Suara langkah kaki pelan terdengar di lorong bawah tanah yang lembap dan dingin.',
    'chapter_order' => 2,
    'status' => 'draft',
    'csrf_token' => $csrfChapCreate2
], $cookieAuthor);

$chaptersOfNovel2 = $chapterModel->getByNovel($novelId);
$chap2Id = 0;
foreach ($chaptersOfNovel2 as $c) {
    if ($c['chapter_order'] == 2) $chap2Id = (int)$c['id'];
}
recordTest('CHAPTER', 'Create Chapter 2 (Draft)', $chap2Id > 0, "Chapter ID: #{$chap2Id}");

// 3.2 Read Chapter (Author View)
$readChapterAuthor = curlReq('GET', "{$baseUrl}/chapter/read/{$chap1Id}", [], $cookieAuthor);
recordTest('CHAPTER', 'Read Chapter (Author View)', $readChapterAuthor['code'] === 200 && str_contains($readChapterAuthor['body'], $chap1Title), 'Title displayed');

// 3.3 Editor: Update Chapter (Save & Update)
$csrfEditChap = getCsrf("{$baseUrl}/chapter/edit/{$chap1Id}", $cookieAuthor);
$updatedChap1Content = $chap1Content . "\n\nSeorang kesatria berbaju zirah perak menatap horizon dengan tatapan waspada.";
$updateChapResp = curlReq('POST', "{$baseUrl}/chapter/edit/{$chap1Id}", [
    'title' => $chap1Title,
    'content' => $updatedChap1Content,
    'chapter_order' => 1,
    'status' => 'published',
    'submit_action' => 'published',
    'csrf_token' => $csrfEditChap
], $cookieAuthor);

$chapInDb = $chapterModel->find($chap1Id);
recordTest('EDITOR', 'Editor Save & Update', str_contains($chapInDb['content'] ?? '', 'kesatria berbaju zirah'), 'Content updated');

// 3.4 Editor: Word count verification in UI
$editChapView = curlReq('GET', "{$baseUrl}/chapter/edit/{$chap1Id}", [], $cookieAuthor);
$wordCountInView = str_contains($editChapView['body'], 'wordCount') || str_contains($editChapView['body'], 'charCount');
recordTest('EDITOR', 'Word Count Feature', $wordCountInView, 'Word/char counters integrated in editor view');

// 3.5 Editor: Autosave API
$autosaveResp = curlReq('POST', "{$baseUrl}/chapter/autosave/{$chap1Id}", [
    'title' => $chap1Title,
    'content' => $updatedChap1Content . " [Autosaved]",
    'status' => 'published'
], $cookieAuthor, ['Content-Type: application/json']);

$autosaveData = json_decode($autosaveResp['body'], true);
$autosaveSuccess = ($autosaveResp['code'] === 200 && ($autosaveData['success'] ?? false) === true);
recordTest('EDITOR', 'Autosave API Endpoint', $autosaveSuccess, 'Autosave returned 200 JSON');

// 3.6 Chapter Ordering (Move Up / Down)
$csrfChapCreate3 = getCsrf("{$baseUrl}/chapter/create/{$novelId}", $cookieAuthor);
$createChap3Resp = curlReq('POST', "{$baseUrl}/chapter/create/{$novelId}", [
    'title' => 'Bab 3: Sumpah Darah',
    'content' => 'Di altar pemujaan, upacara kuno dimulai di bawah sinar rembulan perak.',
    'chapter_order' => 3,
    'status' => 'published',
    'csrf_token' => $csrfChapCreate3
], $cookieAuthor);

$chaptersOfNovel3 = $chapterModel->getByNovel($novelId);
$chap3Id = 0;
foreach ($chaptersOfNovel3 as $c) {
    if ($c['chapter_order'] == 3) $chap3Id = (int)$c['id'];
}

$moveUpResp = curlReq('GET', "{$baseUrl}/chapter/move_up/{$chap3Id}", [], $cookieAuthor);
$chap3AfterMove = $chapterModel->find($chap3Id);
recordTest('CHAPTER', 'Chapter Ordering (Move Up)', ($chap3AfterMove['chapter_order'] ?? 0) == 2, "Chapter 3 moved up to order #{$chap3AfterMove['chapter_order']}");

// 3.7 Delete Chapter
$csrfChapDelete = getCsrf("{$baseUrl}/novels/{$novelId}", $cookieAuthor);
$deleteChapResp = curlReq('POST', "{$baseUrl}/chapter/delete/{$chap2Id}", [
    '_method' => 'DELETE',
    'csrf_token' => $csrfChapDelete
], $cookieAuthor);
$chap2Deleted = ($chapterModel->find($chap2Id) === null);
recordTest('CHAPTER', 'Delete Chapter', $chap2Deleted, 'Chapter removed and sequence updated');

// =============================================================================
// 4. AI WRITING ASSISTANT TESTING
// =============================================================================
echo "\n--- 4. Testing AI ASSISTANT Features ---\n";

$aiFeatures = [
    'idea' => 'Generate Idea',
    'outline' => 'Generate Outline',
    'character' => 'Generate Character',
    'continue' => 'Continue Story',
    'improve' => 'Improve Writing',
    'suggestions' => 'Suggestions'
];

foreach ($aiFeatures as $type => $label) {
    $aiResp = curlReq('POST', "{$baseUrl}/ai/generate", [
        'type' => $type,
        'prompt' => "Bantu buatkan {$label} untuk novel fantasi kerajaan",
        'context' => 'Konteks naskah saat ini di istana',
        'chapter_id' => $chap1Id,
        'mock_provider' => 'mock'
    ], $cookieAuthor, ['Content-Type: application/json']);

    $jsonAi = json_decode($aiResp['body'], true);
    $ok = ($aiResp['code'] === 200 && ($jsonAi['success'] ?? false) === true && !empty($jsonAi['data']['response']));
    recordTest('AI', $label, $ok, "HTTP {$aiResp['code']}");
}

// 4.7 AI API Error simulation (Timeout, RateLimit, 401)
$aiTimeout = curlReq('POST', "{$baseUrl}/ai/generate", [
    'type' => 'continue',
    'prompt' => 'Lanjutkan',
    'mock_provider' => 'mock_timeout'
], $cookieAuthor, ['Content-Type: application/json']);
recordTest('AI', 'API Error (Timeout 504)', $aiTimeout['code'] === 504, 'HTTP 504 handled');

$aiRateLimit = curlReq('POST', "{$baseUrl}/ai/generate", [
    'type' => 'continue',
    'prompt' => 'Lanjutkan',
    'mock_provider' => 'mock_rate_limit'
], $cookieAuthor, ['Content-Type: application/json']);
recordTest('AI', 'API Error (Rate Limit 429)', $aiRateLimit['code'] === 429, 'HTTP 429 handled');

// 4.8 AI Invalid Input
$aiEmptyPrompt = curlReq('POST', "{$baseUrl}/ai/generate", [
    'type' => 'continue',
    'prompt' => '',
    'chapter_id' => $chap1Id
], $cookieAuthor, ['Content-Type: application/json']);
recordTest('AI', 'Invalid Input (Empty Prompt)', $aiEmptyPrompt['code'] === 422, 'HTTP 422 Unprocessable Entity');

// 4.9 AI History
$aiHistResp = curlReq('GET', "{$baseUrl}/ai/history?chapter_id={$chap1Id}", [], $cookieAuthor);
$jsonHist = json_decode($aiHistResp['body'], true);
$hasHistory = ($aiHistResp['code'] === 200 && ($jsonHist['success'] ?? false) === true && is_array($jsonHist['data']));
recordTest('AI', 'AI History Retrieval', $hasHistory, 'History records fetched');

// =============================================================================
// 5. PUBLIC CATALOG & READING TESTING
// =============================================================================
echo "\n--- 5. Testing PUBLIC Features ---\n";

// 5.1 Novel List (Catalog)
$pubList = curlReq('GET', "{$baseUrl}/novel");
$hasPubList = ($pubList['code'] === 200 && str_contains($pubList['body'], $updatedNovelTitle));
recordTest('PUBLIC', 'Novel List (Catalog)', $hasPubList, 'Catalog displays published novel');

// 5.2 Novel Detail (Public)
$pubDetail = curlReq('GET', "{$baseUrl}/novel/detail/{$novelId}");
$hasPubDetail = ($pubDetail['code'] === 200 && str_contains($pubDetail['body'], $updatedNovelTitle));
recordTest('PUBLIC', 'Novel Detail (Public)', $hasPubDetail, 'Public view displays novel metadata');

// 5.3 Reading Chapter (Public)
$pubRead = curlReq('GET', "{$baseUrl}/novels/{$novelId}/read/{$chap1Id}");
$hasPubRead = ($pubRead['code'] === 200 && str_contains($pubRead['body'], $chap1Title));
recordTest('PUBLIC', 'Reading Chapter (Public)', $hasPubRead, 'Reader view loaded');

// 5.4 Draft Protection (Public cannot read draft chapter)
$chapDraftId = (int)$chapterModel->createChapter([
    'novel_id' => $novelId,
    'title' => 'Bab Draft Rahasia',
    'content' => 'Konten rahasia yang tidak boleh dibaca publik.',
    'chapter_order' => 10,
    'status' => 'draft'
]);
$pubDraftRead = curlReq('GET', "{$baseUrl}/novels/{$novelId}/read/{$chapDraftId}");
recordTest('PUBLIC', 'Draft Protection (HTTP 404 for Draft Chapter)', $pubDraftRead['code'] === 404, "HTTP {$pubDraftRead['code']} Not Found");

// =============================================================================
// 6. ADMIN PANEL TESTING
// =============================================================================
echo "\n--- 6. Testing ADMIN Features ---\n";

// 6.1 Admin Authorization Guard (Guest blocked)
$adminGuest = curlReq('GET', "{$baseUrl}/admin");
recordTest('ADMIN', 'Guest Access Guard', $adminGuest['code'] === 302, 'Guest redirected to login');

// 6.2 Admin Authorization Guard (Regular user blocked)
$adminRegular = curlReq('GET', "{$baseUrl}/admin", [], $cookieAuthor);
recordTest('ADMIN', 'Regular User Access Guard', $adminRegular['code'] === 302, 'Regular user blocked');

// 6.3 Admin Dashboard Access
$adminDash = curlReq('GET', "{$baseUrl}/admin", [], $cookieAdmin);
$adminDashOk = ($adminDash['code'] === 200 && str_contains($adminDash['body'], 'Pusat Kontrol Administrator'));
recordTest('ADMIN', 'Admin Dashboard Access', $adminDashOk, 'Admin control center loaded');

// 6.4 Admin User Management
$adminUsers = curlReq('GET', "{$baseUrl}/admin/users", [], $cookieAdmin);
$adminUsersOk = ($adminUsers['code'] === 200 && str_contains($adminUsers['body'], 'Manajemen Pengguna'));
recordTest('ADMIN', 'User Management View', $adminUsersOk, 'User management tab rendered');

// 6.5 Admin Novel Management
$adminNovels = curlReq('GET', "{$baseUrl}/admin/novels", [], $cookieAdmin);
$adminNovelsOk = ($adminNovels['code'] === 200 && str_contains($adminNovels['body'], 'Manajemen & Moderasi Seluruh Novel'));
recordTest('ADMIN', 'Novel Management View', $adminNovelsOk, 'Novel management tab rendered');

// 6.6 Admin Content / Chapter Management
$adminContent = curlReq('GET', "{$baseUrl}/admin/content", [], $cookieAdmin);
$adminContentOk = ($adminContent['code'] === 200 && str_contains($adminContent['body'], 'Moderasi Konten & Manajemen Bab'));
recordTest('ADMIN', 'Content Moderation View', $adminContentOk, 'Content moderation tab rendered');

// =============================================================================
// CLEANUP TEST DATA
// =============================================================================
$novelModel->deleteNovel($novelId, $authorUserId);
$otherUser = $userModel->findByEmail($testEmailOther);
$otherUserId = (int)($otherUser['id'] ?? 0);
$db->query("DELETE FROM users WHERE id = :u1 OR id = :u2 OR id = :u3");
$db->bind(':u1', $authorUserId);
$db->bind(':u2', $otherUserId);
$db->bind(':u3', $adminId);
$db->execute();

@unlink($cookieAuthor);
@unlink($cookieOther);
@unlink($cookieAdmin);
@unlink($cookieGuest);

// =============================================================================
// REKAPITULASI HASIL TEST
// =============================================================================
$passCount = 0;
$failCount = 0;
foreach ($results as $r) {
    if ($r['status'] === 'PASS') $passCount++;
    else $failCount++;
}

echo "\n========================================================\n";
echo "   REKAPITULASI MASTER TEST SUITE: {$passCount} PASSED, {$failCount} FAILED\n";
echo "========================================================\n";
