<?php
/**
 * Test HTTP Endpoints via Apache for Prompt 10: Admin Panel
 */

$baseUrl = 'http://localhost/Novelweb';
$cookieJarGuest = tempnam(sys_get_temp_dir(), 'kn_guest_');
$cookieJarUser = tempnam(sys_get_temp_dir(), 'kn_user_');
$cookieJarAdmin = tempnam(sys_get_temp_dir(), 'kn_admin_');

function httpReq(string $url, string $method = 'GET', array $postData = [], ?string $cookieFile = null): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't auto follow so we see 302 redirects
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    
    $rawResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $headers = substr($rawResponse, 0, $headerSize);
    $body = substr($rawResponse, $headerSize);
    
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'headers' => $headers,
        'body' => $body
    ];
}

echo "========================================================\n";
echo "   HTTP LIVE ENDPOINT SECURITY TESTS FOR ADMIN PANEL\n";
echo "========================================================\n\n";

// 1. Guest request to /admin
$guestRes = httpReq("{$baseUrl}/admin", 'GET', [], $cookieJarGuest);
echo "1. Guest GET /admin -> HTTP Code: {$guestRes['code']}\n";
preg_match('/Location:\s*([^\r\n]+)/i', $guestRes['headers'], $locMatch);
$location = $locMatch[1] ?? 'None';
echo "   Redirect Location: {$location}\n";
$guestBlocked = ($guestRes['code'] === 302 && str_contains($location, 'login'));
echo "   [TEST] Guest blocked and redirected to login: " . ($guestBlocked ? "PASSED" : "FAILED") . "\n\n";

// 2. Login as regular user
$loginUserRes = httpReq("{$baseUrl}/auth/login", 'POST', [
    'email' => 'penulis1@karyanovel.id',
    'password' => 'password123'
], $cookieJarUser);

// User request to /admin
$userRes = httpReq("{$baseUrl}/admin", 'GET', [], $cookieJarUser);
echo "2. Regular User GET /admin -> HTTP Code: {$userRes['code']}\n";
preg_match('/Location:\s*([^\r\n]+)/i', $userRes['headers'], $locUserMatch);
$userLocation = $locUserMatch[1] ?? 'None';
echo "   Redirect Location: {$userLocation}\n";
$userBlocked = ($userRes['code'] === 302 && str_contains($userLocation, 'dashboard'));
echo "   [TEST] Regular user blocked and redirected to dashboard: " . ($userBlocked ? "PASSED" : "FAILED") . "\n\n";

// 3. Login as admin
$loginAdminRes = httpReq("{$baseUrl}/auth/login", 'POST', [
    'email' => 'admin@karyanovel.id',
    'password' => 'password123'
], $cookieJarAdmin);

// Admin request to /admin
$adminRes = httpReq("{$baseUrl}/admin", 'GET', [], $cookieJarAdmin);
echo "3. Administrator GET /admin -> HTTP Code: {$adminRes['code']}\n";
$adminGranted = ($adminRes['code'] === 200 && str_contains($adminRes['body'], 'Panel Administrator'));
echo "   [TEST] Admin granted access to Dashboard: " . ($adminGranted ? "PASSED" : "FAILED") . "\n\n";

// 4. Admin request to /admin/users
$adminUsersRes = httpReq("{$baseUrl}/admin/users", 'GET', [], $cookieJarAdmin);
$adminUsersGranted = ($adminUsersRes['code'] === 200 && str_contains($adminUsersRes['body'], 'Manajemen Pengguna'));
echo "4. Administrator GET /admin/users -> HTTP Code: {$adminUsersRes['code']}\n";
echo "   [TEST] Admin granted access to User Management: " . ($adminUsersGranted ? "PASSED" : "FAILED") . "\n\n";

// 5. Admin request to /admin/novels
$adminNovelsRes = httpReq("{$baseUrl}/admin/novels", 'GET', [], $cookieJarAdmin);
$adminNovelsGranted = ($adminNovelsRes['code'] === 200 && str_contains($adminNovelsRes['body'], 'Manajemen & Moderasi Seluruh Novel'));
echo "5. Administrator GET /admin/novels -> HTTP Code: {$adminNovelsRes['code']}\n";
echo "   [TEST] Admin granted access to Novel Management: " . ($adminNovelsGranted ? "PASSED" : "FAILED") . "\n\n";

// 6. Admin request to /admin/content
$adminContentRes = httpReq("{$baseUrl}/admin/content", 'GET', [], $cookieJarAdmin);
$adminContentGranted = ($adminContentRes['code'] === 200 && str_contains($adminContentRes['body'], 'Moderasi Konten & Manajemen Bab'));
echo "6. Administrator GET /admin/content -> HTTP Code: {$adminContentRes['code']}\n";
echo "   [TEST] Admin granted access to Content Management: " . ($adminContentGranted ? "PASSED" : "FAILED") . "\n\n";

// Cleanup cookies
@unlink($cookieJarGuest);
@unlink($cookieJarUser);
@unlink($cookieJarAdmin);

echo "========================================================\n";
$allHttpPassed = ($guestBlocked && $userBlocked && $adminGranted && $adminUsersGranted && $adminNovelsGranted && $adminContentGranted);
echo "   HTTP LIVE TESTS RESULT: " . ($allHttpPassed ? "ALL PASSED!" : "FAILED") . "\n";
echo "========================================================\n";
