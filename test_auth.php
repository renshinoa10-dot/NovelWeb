<?php
/**
 * karyaNovel - Test Suite Otentikasi & Role Management
 * Menjalankan pengujian otomatis untuk Register, Login, Logout, dan Route Guards
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
echo " karyaNovel - Automated Auth & Role Verification\n";
echo "=======================================================\n\n";

$userModel = new User();
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
// TEST 1: Register Validation & Password Hashing
// -------------------------------------------------------------
echo "1. PENGUJIAN REGISTRASI (REGISTER):\n";

$testEmail = 'test_author_' . time() . '@karyanovel.id';
$testPass = 'rahasia123';

// Cek format email dan keunikan
assertTest(
    "Deteksi email unik",
    $userModel->isEmailTaken('admin@karyanovel.id') === true,
    "Email admin@karyanovel.id terdeteksi sudah terdaftar."
);

assertTest(
    "Deteksi email baru belum terdaftar",
    $userModel->isEmailTaken($testEmail) === false,
    "Email {$testEmail} siap didaftarkan."
);

// Registrasi user baru
$newUserId = $userModel->register([
    'name' => 'Penulis Uji Coba',
    'email' => $testEmail,
    'password' => $testPass,
    'role' => 'user'
]);

assertTest(
    "Registrasi user baru berhasil disimpan ke database",
    !empty($newUserId) && (int)$newUserId > 0,
    "User baru terdaftar dengan ID: #{$newUserId}"
);

// Verifikasi password tidak disimpan dalam bentuk plaintext di database
$createdUser = $userModel->find($newUserId);
$isPlaintext = ($createdUser['password'] === $testPass);
$isBcrypt = (str_starts_with($createdUser['password'], '$2y$'));

assertTest(
    "Password di-hash dengan Bcrypt dan BUKAN plaintext",
    !$isPlaintext && $isBcrypt,
    "Hash tersimpan: " . substr($createdUser['password'], 0, 15) . "..."
);

// -------------------------------------------------------------
// TEST 2: Login & Authentication
// -------------------------------------------------------------
echo "\n2. PENGUJIAN LOGIN (AUTHENTICATION):\n";

// Login gagal dengan password salah
$failLogin = $userModel->authenticate($testEmail, 'passwordsalah');
assertTest(
    "Login gagal jika kata sandi salah",
    $failLogin === null,
    "Sistem menolak otentikasi dengan benar."
);

// Login sukses user biasa
$successLogin = $userModel->authenticate($testEmail, $testPass);
assertTest(
    "Login sukses dengan email & kata sandi yang cocok",
    $successLogin !== null && $successLogin['email'] === $testEmail,
    "Berhasil login sebagai: {$successLogin['name']} (Role: {$successLogin['role']})"
);

// Login sukses admin
$adminLogin = $userModel->authenticate('admin@karyanovel.id', 'password123');
assertTest(
    "Login akun admin (admin@karyanovel.id)",
    $adminLogin !== null && $adminLogin['role'] === 'admin',
    "Berhasil login sebagai Admin (Role: {$adminLogin['role']})"
);

// -------------------------------------------------------------
// TEST 3: Session & Role Management
// -------------------------------------------------------------
echo "\n3. PENGUJIAN SESI & ROLE MANAGEMENT:\n";

// Simulasi setAuth user biasa
Session::setAuth($successLogin);
assertTest("Session::isLoggedIn() aktif untuk user", Session::isLoggedIn() === true);
assertTest("Session::isAdmin() bernilai FALSE untuk user biasa", Session::isAdmin() === false);
assertTest("Session::userId() mengembalikan ID user", Session::userId() === (int)$newUserId);

// Simulasi setAuth admin
Session::setAuth($adminLogin);
assertTest("Session::isAdmin() bernilai TRUE untuk admin", Session::isAdmin() === true);

// Simulasi Logout
Session::destroy();
Session::start();
assertTest("Session::isLoggedIn() bernilai FALSE setelah logout", Session::isLoggedIn() === false);

// -------------------------------------------------------------
// TEST 4: HTTP Request & Route Protection Tests
// -------------------------------------------------------------
echo "\n4. PENGUJIAN HTTP ENDPOINTS & PROTEKSI RUTE:\n";

// Jalankan curl untuk pengujian respons HTTP
// A. Guest mengakses /dashboard -> Harus redirect 302
$ch = curl_init(base_url('dashboard'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest(
    "Guest mengakses /dashboard diarahkan (Redirect 302)",
    $httpCode === 302,
    "HTTP status: {$httpCode} (Redirect ke /auth/login)"
);

// B. Guest mengakses /admin -> Harus redirect 302
$ch = curl_init(base_url('admin'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

assertTest(
    "Guest mengakses /admin diarahkan (Redirect 302)",
    $httpCode === 302,
    "HTTP status: {$httpCode} (Redirect ke /auth/login)"
);

// Bersihkan data uji coba
$userModel->delete($newUserId);

echo "\n=======================================================\n";
echo " HASIL PENGUJIAN: {$passed} Passed, {$failed} Failed\n";
echo "=======================================================\n";

if ($failed === 0) {
    echo ">>> SELURUH FITUR AUTHENTICATION & ROLE TELAH TERVERIFIKASI SEMPURNA! <<<\n\n";
}
