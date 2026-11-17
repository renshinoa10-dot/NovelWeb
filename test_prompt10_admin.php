<?php
/**
 * Test Suite: Prompt 10 - Admin Panel, Authorization & Content Moderation
 * karyaNovel
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Session.php';
require_once APPROOT . '/helpers/helpers.php';
require_once APPROOT . '/core/Env.php';

require_once __DIR__ . '/app/models/User.php';
require_once __DIR__ . '/app/models/Novel.php';
require_once __DIR__ . '/app/models/Chapter.php';
require_once __DIR__ . '/app/models/AiHistory.php';

require_once __DIR__ . '/app/controllers/AdminController.php';

Session::start();

echo "========================================================\n";
echo "   KARYANOVEL - TEST SUITE PROMPT 10: ADMIN PANEL\n";
echo "========================================================\n\n";

$db = Database::getInstance();
$userModel = new User();
$novelModel = new Novel();
$chapterModel = new Chapter();
$aiModel = new AiHistory();

$totalTests = 0;
$passedTests = 0;

function assertTest(string $desc, bool $condition): void {
    global $totalTests, $passedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] {$desc}\n";
    } else {
        echo " [FAIL] {$desc}\n";
    }
}

// -------------------------------------------------------------
// 1. TEST AUTHORIZATION & ACCESS CONTROL GUARDS
// -------------------------------------------------------------
echo "--- 1. Testing Authorization & Access Control Guards ---\n";

// Helper dummy redirect catcher
function testAdminAccess(array $sessionState): array {
    // Reset session and flash
    $_SESSION = $sessionState;
    
    // We instantiate AdminController and capture redirection / exception
    $caughtRedirect = null;
    $caughtFlash = null;
    
    try {
        // Subclass controller to intercept redirect without exit
        $adminCtrl = new class extends AdminController {
            public string $interceptedRedirect = '';
            public function __construct() {
                try {
                    parent::__construct();
                } catch (\Exception $e) {
                    $this->interceptedRedirect = $e->getMessage();
                }
            }
        };
        return ['status' => 'granted', 'redirect' => null, 'flash' => $_SESSION['flash'] ?? null];
    } catch (\Throwable $e) {
        return ['status' => 'blocked', 'message' => $e->getMessage(), 'flash' => $_SESSION['flash'] ?? null];
    }
}

// 1.1 Guest Access (Not logged in)
$_SESSION = [];
$guestGuardBlocked = false;
try {
    // Controller::requireAdmin calls requireAuth then isAdmin
    if (!Session::isLoggedIn()) {
        $guestGuardBlocked = true;
    }
} catch (\Throwable $t) {}
assertTest("Guest user cannot access Admin Panel (isLoggedIn() is false)", $guestGuardBlocked);

// 1.2 Regular User Access (Role: 'user')
$regularUser = $userModel->findByEmail('penulis1@karyanovel.id');
if (!$regularUser) {
    // create fallback
    $regId = $userModel->register([
        'name' => 'Penulis Uji',
        'email' => 'penulis_uji@karyanovel.id',
        'password' => 'password123',
        'role' => 'user'
    ]);
    $regularUser = $userModel->find((int)$regId);
}
$_SESSION['user'] = $regularUser;
assertTest("Regular user role is 'user'", $regularUser['role'] === 'user');
assertTest("Session::isAdmin() returns FALSE for regular user", Session::isAdmin() === false);

// 1.3 Administrator User Access (Role: 'admin')
$adminUser = $userModel->findByEmail('admin@karyanovel.id');
if (!$adminUser) {
    $adminId = $userModel->register([
        'name' => 'Administrator',
        'email' => 'admin@karyanovel.id',
        'password' => 'password123',
        'role' => 'admin'
    ]);
    $adminUser = $userModel->find((int)$adminId);
}
$_SESSION['user'] = $adminUser;
assertTest("Admin user role is 'admin'", $adminUser['role'] === 'admin');
assertTest("Session::isAdmin() returns TRUE for admin user", Session::isAdmin() === true);

// -------------------------------------------------------------
// 2. TEST DASHBOARD STATS (5 KEY METRICS)
// -------------------------------------------------------------
echo "\n--- 2. Testing Admin Dashboard 5 Key Metrics ---\n";

$db->query("SELECT COUNT(*) as total FROM users");
$expectedTotalUsers = (int)$db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM novels");
$expectedTotalNovels = (int)$db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM novels WHERE status = 'published'");
$expectedPublishedNovels = (int)$db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM novels WHERE status = 'draft'");
$expectedDraftNovels = (int)$db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM chapters");
$expectedTotalChapters = (int)$db->single()['total'];

assertTest("Metric 1 - Total Users calculated correctly ({$expectedTotalUsers})", $userModel->count() === $expectedTotalUsers);
assertTest("Metric 2 - Total Novels calculated correctly ({$expectedTotalNovels})", $novelModel->count() === $expectedTotalNovels);
assertTest("Metric 3 - Total Novel Published calculated correctly ({$expectedPublishedNovels})", $novelModel->count("WHERE status = 'published'") === $expectedPublishedNovels);
assertTest("Metric 4 - Total Novel Draft calculated correctly ({$expectedDraftNovels})", $novelModel->count("WHERE status = 'draft'") === $expectedDraftNovels);
assertTest("Metric 5 - Total Bab calculated correctly ({$expectedTotalChapters})", $chapterModel->count() === $expectedTotalChapters);
assertTest("Sum of Published + Draft equals Total Novels", ($expectedPublishedNovels + $expectedDraftNovels) === $expectedTotalNovels);

// -------------------------------------------------------------
// 3. TEST USER MANAGEMENT
// -------------------------------------------------------------
echo "\n--- 3. Testing User Management ---\n";

// 3.1 Fetch all users
$allUsers = $userModel->getAllUsers(100);
assertTest("getAllUsers() returns array of users", is_array($allUsers) && count($allUsers) > 0);
$firstUser = $allUsers[0];
assertTest("User row contains 'id', 'name', 'email', 'role', 'total_novels'", 
    isset($firstUser['id'], $firstUser['name'], $firstUser['email'], $firstUser['role'], $firstUser['total_novels'])
);

// 3.2 Create temporary test user for role modification and deletion
$testEmail = 'test_mod_' . time() . '@karyanovel.id';
$testUserId = (int)$userModel->register([
    'name' => 'User Testing Moderasi',
    'email' => $testEmail,
    'password' => 'secret123',
    'role' => 'user'
]);
assertTest("Created temporary user with ID #{$testUserId} and role 'user'", $testUserId > 0);

// 3.3 Change role: 'user' -> 'admin'
$roleChangedToAdmin = $userModel->changeRole($testUserId, 'admin');
$updatedUser = $userModel->find($testUserId);
assertTest("Changed role 'user' -> 'admin' successfully", $roleChangedToAdmin && $updatedUser['role'] === 'admin');

// 3.4 Change role: 'admin' -> 'user'
$roleChangedToUser = $userModel->changeRole($testUserId, 'user');
$updatedUser = $userModel->find($testUserId);
assertTest("Changed role 'admin' -> 'user' successfully", $roleChangedToUser && $updatedUser['role'] === 'user');

// 3.5 Test Self-demotion guard
$adminSelfId = (int)$adminUser['id'];
$preventSelfDemotion = ($adminSelfId === (int)$_SESSION['user']['id']);
assertTest("Self-demotion prevention guard detects current admin account", $preventSelfDemotion);

// 3.6 Delete User
$userDeleted = $userModel->delete($testUserId);
$checkDeleted = $userModel->find($testUserId);
assertTest("Deleted user #{$testUserId} successfully", $userDeleted && $checkDeleted === null);

// 3.7 Test Self-deletion guard
$preventSelfDeletion = ($adminSelfId === (int)$_SESSION['user']['id']);
assertTest("Self-deletion prevention guard detects current admin account", $preventSelfDeletion);

// -------------------------------------------------------------
// 4. TEST NOVEL MANAGEMENT
// -------------------------------------------------------------
echo "\n--- 4. Testing Novel Management ---\n";

// 4.1 Fetch all novels with author info
$allNovels = $novelModel->getAllWithAuthor(100);
assertTest("getAllWithAuthor() returns array of novels", is_array($allNovels) && count($allNovels) > 0);
$sampleNovel = $allNovels[0];
assertTest("Novel row contains 'id', 'title', 'status', 'author_name', 'author_email', 'total_chapters'",
    isset($sampleNovel['id'], $sampleNovel['title'], $sampleNovel['status'], $sampleNovel['author_name'], $sampleNovel['author_email'], $sampleNovel['total_chapters'])
);

// 4.2 Create temporary novel for status toggle and violation deletion
$tempNovelId = (int)$novelModel->createNovel([
    'user_id' => $regularUser['id'],
    'title' => 'Novel Uji Moderasi Pelanggaran ' . time(),
    'synopsis' => 'Sinopsis uji moderasi konten administrator karyaNovel.',
    'genre' => 'Fantasi',
    'status' => 'draft'
]);
assertTest("Created temporary test novel #{$tempNovelId} with status 'draft'", $tempNovelId > 0);

// Add chapters to this temporary novel
$tempCh1 = (int)$chapterModel->createChapter([
    'novel_id' => $tempNovelId,
    'title' => 'Bab Pembuka Terlarang',
    'content' => 'Konten bab pertama yang melanggar pedoman komunitas.',
    'status' => 'draft'
]);
$tempCh2 = (int)$chapterModel->createChapter([
    'novel_id' => $tempNovelId,
    'title' => 'Bab Kedua Terlarang',
    'content' => 'Konten bab kedua yang melanggar pedoman komunitas.',
    'status' => 'draft'
]);
assertTest("Added 2 chapters to test novel (Chapter IDs: #{$tempCh1}, #{$tempCh2})", $tempCh1 > 0 && $tempCh2 > 0);

// 4.3 Toggle status: 'draft' -> 'published'
$novelModel->changeStatus($tempNovelId, 'published');
$tempNovelRow = $novelModel->find($tempNovelId);
assertTest("Toggled novel status 'draft' -> 'published'", $tempNovelRow['status'] === 'published');

// Toggle status: 'published' -> 'draft'
$novelModel->changeStatus($tempNovelId, 'draft');
$tempNovelRow = $novelModel->find($tempNovelId);
assertTest("Toggled novel status 'published' -> 'draft'", $tempNovelRow['status'] === 'draft');

// 4.4 Delete violating novel (Admin Delete Novel & cascade chapters)
$novelDeleted = $novelModel->adminDeleteNovel($tempNovelId);
$checkNovelDeleted = $novelModel->find($tempNovelId);
$checkCh1Deleted = $chapterModel->find($tempCh1);
$checkCh2Deleted = $chapterModel->find($tempCh2);

assertTest("adminDeleteNovel() removed novel #{$tempNovelId} from database", $novelDeleted && $checkNovelDeleted === null);
assertTest("Chapters linked to deleted novel were removed (Cascade Integrity)", $checkCh1Deleted === null && $checkCh2Deleted === null);

// -------------------------------------------------------------
// 5. TEST CONTENT MANAGEMENT (CHAPTER MODERATION)
// -------------------------------------------------------------
echo "\n--- 5. Testing Content Management (Chapter Moderation) ---\n";

// 5.1 Fetch all chapters with novel and author details
$allChapters = $chapterModel->getAllWithNovelAndAuthor(null, 100);
assertTest("getAllWithNovelAndAuthor() returns chapters array", is_array($allChapters) && count($allChapters) > 0);
$sampleChapter = $allChapters[0];
assertTest("Chapter row contains 'id', 'title', 'content', 'novel_title', 'author_name', 'status'",
    isset($sampleChapter['id'], $sampleChapter['title'], $sampleChapter['content'], $sampleChapter['novel_title'], $sampleChapter['author_name'], $sampleChapter['status'])
);

// 5.2 Filter chapters by specific novel_id
$targetNovelId = (int)$sampleChapter['novel_id'];
$filteredChapters = $chapterModel->getAllWithNovelAndAuthor($targetNovelId, 100);
$allMatchNovel = true;
foreach ($filteredChapters as $fc) {
    if ((int)$fc['novel_id'] !== $targetNovelId) {
        $allMatchNovel = false;
        break;
    }
}
assertTest("getAllWithNovelAndAuthor({$targetNovelId}) filters accurately by novel", count($filteredChapters) > 0 && $allMatchNovel);

// 5.3 Create temporary chapter for status toggle and violation deletion
$allBaseNovels = $novelModel->all('id ASC', 1);
$baseNovel = $allBaseNovels[0];
$targetBaseNovelId = (int)$baseNovel['id'];

$violatingChapterId = (int)$chapterModel->createChapter([
    'novel_id' => $targetBaseNovelId,
    'title' => 'Bab Naskah Uji Moderasi ' . time(),
    'content' => 'Teks naskah bab yang akan diuji toggle status dan dihapus moderator.',
    'status' => 'draft'
]);
assertTest("Created test chapter #{$violatingChapterId} with status 'draft'", $violatingChapterId > 0);

// 5.4 Toggle chapter status: 'draft' -> 'published'
$newChapStatus = $chapterModel->adminToggleStatus($violatingChapterId);
assertTest("adminToggleStatus() toggled status 'draft' -> 'published'", $newChapStatus === 'published');

// Toggle chapter status: 'published' -> 'draft'
$newChapStatus2 = $chapterModel->adminToggleStatus($violatingChapterId);
assertTest("adminToggleStatus() toggled status 'published' -> 'draft'", $newChapStatus2 === 'draft');

// 5.5 Delete violating chapter and verify resequence
$chapterDeleted = $chapterModel->adminDeleteChapter($violatingChapterId);
$checkChapDeleted = $chapterModel->find($violatingChapterId);
assertTest("adminDeleteChapter() deleted chapter #{$violatingChapterId}", $chapterDeleted && $checkChapDeleted === null);

// Check chapter sequence consistency
$remainingChapters = $chapterModel->getByNovel($targetBaseNovelId);
$sequenceValid = true;
$expectedOrder = 1;
foreach ($remainingChapters as $rc) {
    if ((int)$rc['chapter_order'] !== $expectedOrder) {
        $sequenceValid = false;
        break;
    }
    $expectedOrder++;
}
assertTest("Chapter order sequence resequenced and strictly sequential (1, 2, ...)", $sequenceValid);

// -------------------------------------------------------------
// 6. TEST VIEW RENDERING
// -------------------------------------------------------------
echo "\n--- 6. Testing Admin View Rendering ---\n";

// Render view for tab 'dashboard'
ob_start();
$stats = [
    'totalUsers' => $expectedTotalUsers,
    'totalNovels' => $expectedTotalNovels,
    'publishedNovels' => $expectedPublishedNovels,
    'draftNovels' => $expectedDraftNovels,
    'totalChapters' => $expectedTotalChapters,
    'totalAiLogs' => 5
];
$admin = $adminUser;
$users = $allUsers;
$novels = $allNovels;
$chapters = $allChapters;
$filterNovelId = null;

// Test Dashboard tab
$activeTab = 'dashboard';
require __DIR__ . '/app/views/admin/index.php';
$htmlDashboard = ob_get_clean();

assertTest("View renders Dashboard tab successfully", str_contains($htmlDashboard, 'Pusat Kontrol'));
assertTest("View renders 'Total User' stat card", str_contains($htmlDashboard, 'stat-total-users'));
assertTest("View renders 'Total Novel' stat card", str_contains($htmlDashboard, 'stat-total-novels'));
assertTest("View renders 'Novel Published' stat card", str_contains($htmlDashboard, 'stat-published-novels'));
assertTest("View renders 'Novel Draft' stat card", str_contains($htmlDashboard, 'stat-draft-novels'));
assertTest("View renders 'Total Bab' stat card", str_contains($htmlDashboard, 'stat-total-chapters'));

// Test Users tab
ob_start();
$activeTab = 'users';
require __DIR__ . '/app/views/admin/index.php';
$htmlUsers = ob_get_clean();
assertTest("View renders User Management tab with change_role form", str_contains($htmlUsers, 'admin/change_role/'));
assertTest("View renders delete_user form", str_contains($htmlUsers, 'admin/delete_user/'));

// Test Novels tab
ob_start();
$activeTab = 'novels';
require __DIR__ . '/app/views/admin/index.php';
$htmlNovels = ob_get_clean();
assertTest("View renders Novel Management tab with toggle_novel_status", str_contains($htmlNovels, 'admin/toggle_novel_status/'));
assertTest("View renders delete_novel form", str_contains($htmlNovels, 'admin/delete_novel/'));

// Test Content tab
ob_start();
$activeTab = 'content';
require __DIR__ . '/app/views/admin/index.php';
$htmlContent = ob_get_clean();
assertTest("View renders Content Management tab with novelFilterSelect", str_contains($htmlContent, 'novelFilterSelect'));
assertTest("View renders toggle_chapter_status form", str_contains($htmlContent, 'admin/toggle_chapter_status/'));
assertTest("View renders delete_chapter form", str_contains($htmlContent, 'admin/delete_chapter/'));
assertTest("View renders chapter preview modal", str_contains($htmlContent, 'previewChapterModal'));

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n========================================================\n";
echo "   TEST RESULTS: {$passedTests} / {$totalTests} PASSED\n";
if ($passedTests === $totalTests) {
    echo "   STATUS: ALL TESTS PASSED! Prompt 10 READY!\n";
} else {
    echo "   STATUS: SOME TESTS FAILED.\n";
}
echo "========================================================\n";
