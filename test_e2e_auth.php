<?php
/**
 * karyaNovel - End-to-End HTTP Authentication & Protection Test
 */

echo "=======================================================\n";
echo " karyaNovel - End-to-End HTTP Auth & Role Guard Tests\n";
echo "=======================================================\n\n";

$cookieJarUser = __DIR__ . '/user_cookie.txt';
$cookieJarAdmin = __DIR__ . '/admin_cookie.txt';

if (file_exists($cookieJarUser)) unlink($cookieJarUser);
if (file_exists($cookieJarAdmin)) unlink($cookieJarAdmin);

function httpPost($url, $data, $cookieJar = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $httpCode, 'redirect' => $redirectUrl, 'body' => $response];
}

function httpGet($url, $cookieJar = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $httpCode, 'redirect' => $redirectUrl, 'body' => $response];
}

// 1. Test POST Register user baru
$randUser = 'user_' . rand(1000, 9999);
$resRegister = httpPost('http://127.0.0.1:8000/auth/register', [
    'name' => 'Penulis Baru ' . $randUser,
    'email' => $randUser . '@karyanovel.id',
    'password' => 'password123',
    'confirm_password' => 'password123'
], $cookieJarUser);

echo "1. Register Baru:\n";
echo "   Status Code: {$resRegister['code']} (Expected: 302)\n";
echo "   Redirect To: {$resRegister['redirect']} (Expected: .../dashboard)\n";

// 2. Akses /dashboard dengan cookie User
$resDashboard = httpGet('http://127.0.0.1:8000/dashboard', $cookieJarUser);
echo "\n2. User mengakses /dashboard:\n";
echo "   Status Code: {$resDashboard['code']} (Expected: 200)\n";

// 3. User biasa mencoba mengakses /admin -> Harus ditolak dan redirect ke /dashboard
$resAdminAccessByUser = httpGet('http://127.0.0.1:8000/admin', $cookieJarUser);
echo "\n3. User biasa mencoba mengakses /admin:\n";
echo "   Status Code: {$resAdminAccessByUser['code']} (Expected: 302)\n";
echo "   Redirect To: {$resAdminAccessByUser['redirect']} (Expected: .../dashboard)\n";

// 4. Test POST Login Admin
$resAdminLogin = httpPost('http://127.0.0.1:8000/auth/login', [
    'email' => 'admin@karyanovel.id',
    'password' => 'password123'
], $cookieJarAdmin);

echo "\n4. Login sebagai Admin:\n";
echo "   Status Code: {$resAdminLogin['code']} (Expected: 302)\n";
echo "   Redirect To: {$resAdminLogin['redirect']} (Expected: .../admin)\n";

// 5. Admin mengakses /admin
$resAdminAccess = httpGet('http://127.0.0.1:8000/admin', $cookieJarAdmin);
echo "\n5. Admin mengakses /admin:\n";
echo "   Status Code: {$resAdminAccess['code']} (Expected: 200)\n";

// 6. Test Logout
$resLogout = httpGet('http://127.0.0.1:8000/auth/logout', $cookieJarAdmin);
echo "\n6. Admin Logout:\n";
echo "   Status Code: {$resLogout['code']} (Expected: 302)\n";
echo "   Redirect To: {$resLogout['redirect']} (Expected: .../auth/login)\n";

// 7. Coba akses /admin lagi setelah logout -> Harus redirect ke /auth/login
$resAfterLogout = httpGet('http://127.0.0.1:8000/admin', $cookieJarAdmin);
echo "\n7. Akses /admin setelah logout:\n";
echo "   Status Code: {$resAfterLogout['code']} (Expected: 302)\n";
echo "   Redirect To: {$resAfterLogout['redirect']} (Expected: .../auth/login)\n\n";

if (file_exists($cookieJarUser)) unlink($cookieJarUser);
if (file_exists($cookieJarAdmin)) unlink($cookieJarAdmin);

echo ">>> E2E HTTP AUTHENTICATION TESTS COMPLETED! <<<\n";
