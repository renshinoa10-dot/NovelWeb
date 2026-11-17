<?php
/**
 * karyaNovel - Comprehensive Security Audit Test Suite
 * Memvalidasi 13 Pilar Keamanan Aplikasi karyaNovel
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
require_once APPROOT . '/controllers/AuthController.php';
require_once APPROOT . '/controllers/NovelsController.php';
require_once APPROOT . '/controllers/ChapterController.php';
require_once APPROOT . '/controllers/AiController.php';
require_once APPROOT . '/controllers/AdminController.php';

$totalPassed = 0;
$totalFailed = 0;

function assertTest(bool $condition, string $message): void {
    global $totalPassed, $totalFailed;
    if ($condition) {
        echo " [PASS] {$message}\n";
        $totalPassed++;
    } else {
        echo " [FAIL] {$message}\n";
        $totalFailed++;
    }
}

echo "========================================================\n";
echo "   KARYANOVEL - COMPREHENSIVE SECURITY AUDIT SUITE\n";
echo "========================================================\n\n";

$db = Database::getInstance();
$userModel = new User();
$novelModel = new Novel();
$chapterModel = new Chapter();
$aiHistoryModel = new AiHistory();

// Setup Test Users: User A (Penulis 1), User B (Penulis 2/Attacker), Admin
$userAEmail = 'audit_user_a@karyanovel.test';
$userBEmail = 'audit_user_b@karyanovel.test';
$adminEmail = 'audit_admin@karyanovel.test';

// Bersihkan data tes sebelumnya
$db->query("DELETE FROM users WHERE email IN (:e1, :e2, :e3)");
$db->bind(':e1', $userAEmail);
$db->bind(':e2', $userBEmail);
$db->bind(':e3', $adminEmail);
$db->execute();

$userAId = (int)$userModel->register([
    'name' => 'Penulis Audit A',
    'email' => $userAEmail,
    'password' => 'Rahasia123!',
    'role' => 'user'
]);

$userBId = (int)$userModel->register([
    'name' => 'Penulis Audit B',
    'email' => $userBEmail,
    'password' => 'Rahasia123!',
    'role' => 'user'
]);

$adminId = (int)$userModel->register([
    'name' => 'Admin Audit',
    'email' => $adminEmail,
    'password' => 'Admin123!',
    'role' => 'admin'
]);

$userA = $userModel->find($userAId);
$userB = $userModel->find($userBId);
$admin = $userModel->find($adminId);

// -----------------------------------------------------------------------------
echo "--- 1. Testing Authentication & Session Management ---\n";
// -----------------------------------------------------------------------------
$authA = $userModel->authenticate($userAEmail, 'Rahasia123!');
assertTest($authA !== null, "Authentication succeeds with correct credentials");
assertTest(!isset($authA['password']), "Password hash is stripped from authenticate() output");

$authFail = $userModel->authenticate($userAEmail, 'WrongPassword');
assertTest($authFail === null, "Authentication fails with incorrect password");

// Session fixation & hardening
Session::setAuth($authA);
assertTest(Session::isLoggedIn(), "Session::isLoggedIn() is true after setAuth");
assertTest(Session::userId() === $userAId, "Session::userId() matches logged in user ID");
$sessionUser = Session::user();
assertTest(!isset($sessionUser['password']), "Password hash is stripped from Session data");

// -----------------------------------------------------------------------------
echo "\n--- 2. Testing Password Handling ---\n";
// -----------------------------------------------------------------------------
$db->query("SELECT password FROM users WHERE id = :id");
$db->bind(':id', $userAId);
$row = $db->single();
assertTest(str_starts_with($row['password'], '$2y$'), "Password is encrypted with Bcrypt algorithm ($2y$)");
assertTest(password_verify('Rahasia123!', $row['password']), "Password verifies with password_verify");
assertTest(!password_verify('PlainPassword', $row['password']), "Bcrypt rejects incorrect plain password");

// -----------------------------------------------------------------------------
echo "\n--- 3. Testing SQL Injection Resilience ---\n";
// -----------------------------------------------------------------------------
$sqliPayload = "admin' OR '1'='1";
$sqliUser = $userModel->findByEmail($sqliPayload);
assertTest($sqliUser === null, "Prepared statement blocks SQL injection on findByEmail");

$sqliCaught = false;
try {
    $userModel->where("id; DROP TABLE users; --", 1);
} catch (InvalidArgumentException $e) {
    $sqliCaught = true;
}
assertTest($sqliCaught, "Model::where rejects invalid column identifier to prevent SQL injection");

$operatorCaught = false;
try {
    $userModel->where("id", 1, "UNION SELECT * --");
} catch (InvalidArgumentException $e) {
    $operatorCaught = true;
}
assertTest($operatorCaught, "Model::where rejects unsafe SQL operators");

// -----------------------------------------------------------------------------
echo "\n--- 4. Testing IDOR / Access Control on Novels ---\n";
// -----------------------------------------------------------------------------
$novelAId = (int)$novelModel->createNovel([
    'user_id' => $userAId,
    'title' => 'Novel Milik User A',
    'synopsis' => 'Sinopsis rahasia penulis A',
    'genre' => 'Fantasi',
    'status' => 'draft'
]);

// User A dapat menemukan novel miliknya
$foundByA = $novelModel->findAuthorNovel($novelAId, $userAId);
assertTest($foundByA !== null && (int)$foundByA['id'] === $novelAId, "Author A can access their own novel");

// User B (Attacker) TIDAK BISA mengakses naskah novel milik User A
$foundByB = $novelModel->findAuthorNovel($novelAId, $userBId);
assertTest($foundByB === null, "IDOR Guard: User B cannot access User A's novel via findAuthorNovel");

// User B mencoba update novel User A
$hackedUpdate = $novelModel->updateNovel($novelAId, $userBId, ['title' => 'DIUBAH HACKER B']);
assertTest($hackedUpdate === false, "IDOR Guard: User B cannot update User A's novel");
$checkNovel = $novelModel->find($novelAId);
assertTest($checkNovel['title'] === 'Novel Milik User A', "Novel title unchanged after unauthorized edit attempt");

// User B mencoba delete novel User A
$hackedDelete = $novelModel->deleteNovel($novelAId, $userBId);
assertTest($hackedDelete === false, "IDOR Guard: User B cannot delete User A's novel");
assertTest($novelModel->find($novelAId) !== null, "Novel still exists after unauthorized delete attempt");

// -----------------------------------------------------------------------------
echo "\n--- 5. Testing IDOR / Access Control on Chapters ---\n";
// -----------------------------------------------------------------------------
$chapterAId = (int)$chapterModel->createChapter([
    'novel_id' => $novelAId,
    'title' => 'Bab 1 Rahasia User A',
    'content' => 'Isi rahasia karya penulis A yang belum terbit.',
    'chapter_order' => 1,
    'status' => 'draft'
]);

// Author A bisa mengakses bab miliknya
$chapByA = $chapterModel->findAuthorChapter($chapterAId, $userAId);
assertTest($chapByA !== null, "Author A can access their own chapter");

// User B tidak bisa mengakses bab milik User A
$chapByB = $chapterModel->findAuthorChapter($chapterAId, $userBId);
assertTest($chapByB === null, "IDOR Guard: User B cannot access User A's chapter");

// User B mencoba update bab milik User A
$hackedChapUpdate = $chapterModel->updateChapter($chapterAId, $userBId, ['title' => 'DIBAJAK HACKER']);
assertTest($hackedChapUpdate === false, "IDOR Guard: User B cannot update User A's chapter");

// User B mencoba delete bab milik User A
$hackedChapDelete = $chapterModel->deleteChapter($chapterAId, $userBId);
assertTest($hackedChapDelete === false, "IDOR Guard: User B cannot delete User A's chapter");
assertTest($chapterModel->find($chapterAId) !== null, "Chapter still exists after unauthorized delete attempt");

// -----------------------------------------------------------------------------
echo "\n--- 6. Testing AI Assistant Endpoint Security & Multi-tenant Isolation ---\n";
// -----------------------------------------------------------------------------
$aiService = new AiService(['provider' => 'mock']);

// A. Input validation: Empty prompt
$emptyPromptErr = $aiService->validate('continue', '   ', '');
assertTest($emptyPromptErr !== null, "AI Service rejects empty prompt (Validation Error 422)");

// B. Input validation: Prompt length exceeding limit
$longPrompt = str_repeat('A', 2500);
$longPromptErr = $aiService->validate('continue', $longPrompt, '');
assertTest($longPromptErr !== null, "AI Service rejects prompts exceeding max limit");

// C. Context length exceeding limit
$longContext = str_repeat('B', 6000);
$longContextErr = $aiService->validate('continue', 'Lanjutkan', $longContext);
assertTest($longContextErr !== null, "AI Service rejects context exceeding max limit");

// D. Multi-tenant Isolation: User B dilarang mengakses riwayat AI User A
$aiHistoryModel->log($userAId, $chapterAId, 'Instruksi AI User A', 'Hasil respons AI untuk User A');
$userAHistories = $aiHistoryModel->getByUser($userAId);
assertTest(count($userAHistories) > 0, "User A has logged AI history");

$userBHistories = $aiHistoryModel->getByUser($userBId);
assertTest(count($userBHistories) === 0, "Multi-tenant: User B has 0 history entries from User A");

$chapterAHistoriesSeenByB = $aiHistoryModel->getByChapter($chapterAId, $userBId);
assertTest(count($chapterAHistoriesSeenByB) === 0, "Multi-tenant: User B cannot fetch User A's chapter AI history");

// -----------------------------------------------------------------------------
echo "\n--- 7. Testing CSRF Protection & Token Validation ---\n";
// -----------------------------------------------------------------------------
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$validToken = $_SESSION['csrf_token'];

assertTest(verify_csrf_token($validToken), "verify_csrf_token accepts correct token");
assertTest(!verify_csrf_token('invalid_csrf_token_12345'), "verify_csrf_token rejects forged token");
assertTest(!verify_csrf_token(''), "verify_csrf_token rejects empty token");

$field = csrf_field();
assertTest(str_contains($field, 'name="csrf_token"'), "csrf_field() renders hidden input with token");
assertTest(str_contains($field, $validToken), "csrf_field() includes current valid token value");

// -----------------------------------------------------------------------------
echo "\n--- 8. Testing XSS Prevention & Sanitization ---\n";
// -----------------------------------------------------------------------------
$xssPayload = "<script>alert('XSS')</script>";
$sanitized = sanitize($xssPayload);
assertTest(!str_contains($sanitized, '<script>'), "sanitize() escapes <script> tags");
assertTest(str_contains($sanitized, '&lt;script&gt;'), "sanitize() encodes HTML entities");

// Flash notification XSS protection
set_flash("Pesan dengan {$xssPayload}", 'danger');
$renderedFlash = render_flash();
assertTest(!str_contains($renderedFlash, '<script>'), "render_flash() neutralizes XSS payloads");
assertTest(str_contains($renderedFlash, '&lt;script&gt;'), "render_flash() properly HTML-escapes flash messages");

// -----------------------------------------------------------------------------
echo "\n--- 9. Testing File Upload & Directory Security ---\n";
// -----------------------------------------------------------------------------
// Verifikasi file .htaccess di folder uploads
$uploadsHtaccess = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.htaccess';
assertTest(file_exists($uploadsHtaccess), "public/uploads/.htaccess exists");
$htaccessContent = file_get_contents($uploadsHtaccess);
assertTest(str_contains($htaccessContent, 'Deny from all'), "public/uploads/.htaccess contains Deny from all for script files");
assertTest(str_contains($htaccessContent, 'Options -Indexes'), "public/uploads/.htaccess disables directory indexing");

// -----------------------------------------------------------------------------
echo "\n--- 10. Testing Admin Panel Authorization ---\n";
// -----------------------------------------------------------------------------
Session::setAuth($userA); // Regular user
assertTest(!Session::isAdmin(), "Regular user role 'user' returns false for Session::isAdmin()");

Session::setAuth($admin); // Administrator
assertTest(Session::isAdmin(), "Admin user role 'admin' returns true for Session::isAdmin()");

// Bersihkan data tes
$novelModel->deleteNovel($novelAId, $userAId);
$db->query("DELETE FROM users WHERE id IN (:u1, :u2, :u3)");
$db->bind(':u1', $userAId);
$db->bind(':u2', $userBId);
$db->bind(':u3', $adminId);
$db->execute();

echo "\n========================================================\n";
echo "   SECURITY AUDIT RESULTS: {$totalPassed} PASSED, {$totalFailed} FAILED\n";
if ($totalFailed === 0) {
    echo "   STATUS: ALL SECURITY AUDIT TESTS PASSED!\n";
} else {
    echo "   STATUS: SOME SECURITY AUDIT TESTS FAILED!\n";
}
echo "========================================================\n";
