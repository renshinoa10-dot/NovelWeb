<?php
/**
 * karyaNovel - Test Suite Revisi Total Chatbot
 * Pengujian komprehensif tampilan popup, identitas role, integrasi database, dan pembatasan konteks
 */

define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/helpers/helpers.php';
require_once ROOT_PATH . '/app/core/Session.php';
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/core/Model.php';
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Novel.php';
require_once ROOT_PATH . '/app/models/Chapter.php';
require_once ROOT_PATH . '/app/controllers/ChatbotController.php';
require_once ROOT_PATH . '/app/controllers/HomeController.php';

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertTest(string $title, bool $condition, string $details = ''): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$title}\n";
        if ($details) echo "         -> {$details}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$title}\n";
        if ($details) echo "         -> {$details}\n";
    }
}

echo "=======================================================================\n";
echo " karyaNovel - TEST SUITE: REVISI TOTAL CHATBOT ASSISTANT\n";
echo "=======================================================================\n\n";

// =============================================================================
// 1. PENGUJIAN TAMPILAN POPUP FLOATING (UI & CSS)
// =============================================================================
echo "--- 1. PENGUJIAN TAMPILAN POPUP FLOATING ---\n";

Session::start();
ob_start();
$home = new HomeController();
$home->index();
$homeHtml = ob_get_clean();
$css = file_get_contents(ROOT_PATH . '/public/css/style.css');
$js = file_get_contents(ROOT_PATH . '/public/js/chatbot.js');

// 1.1 Ikon Chatbot di pojok kanan bawah dengan position: fixed
assertTest(
    "1.1 Ikon Chatbot menggunakan position: fixed dan mengambang di pojok kanan bawah",
    str_contains($css, '.kn-chatbot-wrapper') &&
    str_contains($css, 'position: fixed') &&
    str_contains($css, 'bottom:') &&
    str_contains($css, 'right:'),
    "Wrapper dan tombol trigger diposisikan fixed di sudut kanan bawah"
);

// 1.2 Panel Chatbot popup tertutup secara default (menggunakan d-none, z-index tinggi, layout 0 impact)
assertTest(
    "1.2 Panel Chatbot berupa popup mengambang dengan class d-none dan z-index tinggi",
    str_contains($homeHtml, 'class="kn-chatbot-panel d-none"') &&
    str_contains($css, 'z-index: 100001'),
    "Panel tidak mengambil ruang layout halaman utama saat tertutup"
);

// 1.3 Pointer-events wrapper diatur none agar tidak memblokir interaksi halaman saat tertutup
assertTest(
    "1.3 Wrapper tidak memblokir klik halaman saat popup tertutup (pointer-events: none)",
    str_contains($css, 'pointer-events: none') &&
    str_contains($css, 'pointer-events: auto'),
    "Wrapper transparan terhadap klik, hanya tombol dan panel yang interaktif"
);

// 1.4 Dimensi panel 360-400px x 480-600px desktop dan responsif mobile
assertTest(
    "1.4 Dimensi panel sesuai standar (lebar 380px, tinggi 560px, responsif mobile)",
    str_contains($css, 'width: 380px') &&
    str_contains($css, 'height: 560px') &&
    str_contains($css, '@media (max-width: 576px)'),
    "Ukuran panel pas di desktop dan otomatis menyesuaikan di smartphone"
);

// 1.5 Header memiliki 'karyaNovel Assistant', tombol minimize, tombol close, input, dan send button
assertTest(
    "1.5 Komponen Header & Kontrol Chatbot lengkap",
    str_contains($homeHtml, 'karyaNovel Assistant') &&
    str_contains($homeHtml, 'knChatbotMinimizeBtn') &&
    str_contains($homeHtml, 'knChatbotCloseBtn') &&
    str_contains($homeHtml, 'knChatbotInput') &&
    str_contains($homeHtml, 'knChatbotSendBtn'),
    "Header, tombol tutup, tombol kecilkan, input form, dan send button tersedia"
);

// 1.6 Script JS mendukung toggle buka/tutup, stopPropagation, Escape key, dan click outside
assertTest(
    "1.6 Logika interaksi popup JavaScript lengkap dan aman dari konflik event",
    str_contains($js, 'stopPropagation()') &&
    str_contains($js, "e.key === 'Escape'") &&
    str_contains($js, 'sessionStorage'),
    "Popup toggle bekerja mulus tanpa menutup otomatis secara tak sengaja"
);

// =============================================================================
// 2. PENGUJIAN IDENTITAS CHATBOT & PENGGUNA (ROLE BASED)
// =============================================================================
echo "\n--- 2. PENGUJIAN IDENTITAS CHATBOT & IDENTITAS PENGGUNA ---\n";

$controller = new ChatbotController();
$userModel = new User();

// 2.1 Pertanyaan "Siapa Anda?"
$replyWhoAreYou = $controller->generateDatabaseResponse('Siapa Anda?', $controller->getUserDatabaseContext(), $controller->getPlatformDatabaseContext());
assertTest(
    "2.1 Pertanyaan 'Siapa Anda?' dijawab dengan identitas resmi karyaNovel Assistant",
    str_contains($replyWhoAreYou, 'Saya karyaNovel Assistant, asisten virtual yang membantu pengguna menggunakan fitur karyaNovel'),
    "Respons: " . substr($replyWhoAreYou, 0, 110) . "..."
);

// 2.2 Pertanyaan "Kamu siapa?"
$replyKamuSiapa = $controller->generateDatabaseResponse('kamu siapa?', $controller->getUserDatabaseContext(), $controller->getPlatformDatabaseContext());
assertTest(
    "2.2 Pertanyaan 'Kamu siapa?' dijawab konsisten",
    str_contains($replyKamuSiapa, 'karyaNovel Assistant'),
    "Identitas asisten konsisten pada berbagai variasi pertanyaan"
);

// 2.3 Pertanyaan "Siapa saya?" saat BELUM LOGIN (Pengunjung / Tamu)
Session::clearAuth();
$guestUserContext = $controller->getUserDatabaseContext();
$replyGuest = $controller->generateDatabaseResponse('Siapa saya?', $guestUserContext, $controller->getPlatformDatabaseContext());
assertTest(
    "2.3 Pertanyaan 'Siapa saya?' saat belum login (Tamu / Pengunjung)",
    str_contains($replyGuest, 'Anda sedang menggunakan karyaNovel sebagai pengunjung. Silakan masuk untuk mengakses informasi akun pribadi.') &&
    str_contains($replyGuest, 'Pembaca Tamu'),
    "Respons menolak akses data pribadi secara santun dan mengarahkan untuk login"
);

// 2.4 Pertanyaan "Siapa saya?" saat LOGIN sebagai PENULIS (User Biasa: Ahmad Rian)
$userPenulis = $userModel->find(2); // ID 2: Ahmad Rian (role: user)
Session::setAuth($userPenulis);
$penulisContext = $controller->getUserDatabaseContext();
$replyPenulis = $controller->generateDatabaseResponse('Siapa saya?', $penulisContext, $controller->getPlatformDatabaseContext());
assertTest(
    "2.4 Pertanyaan 'Siapa saya?' saat login sebagai Penulis (Ahmad Rian)",
    str_contains($replyPenulis, "Anda sedang login sebagai **{$userPenulis['name']}**, dengan role **Penulis**.") &&
    str_contains($replyPenulis, 'Statistik Karya Novel'),
    "Nama dan role diambil secara dinamis dari database sesi terotentikasi"
);

// 2.5 Pertanyaan "Saya login sebagai apa?" saat LOGIN sebagai ADMINISTRATOR
$userAdmin = $userModel->find(1); // ID 1: Administrator karyaNovel (role: admin)
Session::setAuth($userAdmin);
$adminContext = $controller->getUserDatabaseContext();
$replyAdmin = $controller->generateDatabaseResponse('Saya login sebagai apa?', $adminContext, $controller->getPlatformDatabaseContext());
assertTest(
    "2.5 Pertanyaan 'Saya login sebagai apa?' saat login sebagai Administrator",
    str_contains($replyAdmin, "Anda sedang login sebagai **{$userAdmin['name']}**, dengan role **Administrator**.") &&
    str_contains($replyAdmin, 'Administrator'),
    "Role Administrator terdeteksi akurat dan menampilkan hak akses admin"
);

// =============================================================================
// 3. PENGUJIAN INTEGRASI DATABASE & OTORISASI DATA
// =============================================================================
echo "\n--- 3. PENGUJIAN INTEGRASI DATABASE & OTORISASI DATA ---\n";

// Kembalikan sesi ke Penulis (Ahmad Rian - User ID 2)
Session::setAuth($userPenulis);
$penulisContext = $controller->getUserDatabaseContext();
$platformContext = $controller->getPlatformDatabaseContext();

// 3.1 Daftar novel publik dari database
$replyKatalog = $controller->generateDatabaseResponse('Novel apa saja yang tersedia?', $penulisContext, $platformContext);
$firstPublishedTitle = $platformContext['published_novels'][0]['title'] ?? 'Kronik Lembah Kencana';
assertTest(
    "3.1 Daftar novel publik diambil dari database (Status: published)",
    str_contains($replyKatalog, $firstPublishedTitle) && str_contains($replyKatalog, 'Rekomendasi Novel Pilihan'),
    "Menampilkan judul novel terbit riil dari tabel novels ({$firstPublishedTitle})"
);

// 3.2 Menanyakan penulis novel yang benar-benar ada di database
$replyPenulisNovel = $controller->generateDatabaseResponse('Siapa penulis novel Lembah Bintang?', $penulisContext, $platformContext);
assertTest(
    "3.2 Menanyakan penulis novel spesifik (Lembah Bintang: Menembus Horizon)",
    str_contains($replyPenulisNovel, 'Ahmad Rian') && str_contains($replyPenulisNovel, 'Lembah Bintang'),
    "Mengambil nama pengarang 'Ahmad Rian' dari relasi database users & novels"
);

// 3.3 Menanyakan kategori novel dari database
$replyKategori = $controller->generateDatabaseResponse('Apa kategori novel yang tersedia?', $penulisContext, $platformContext);
assertTest(
    "3.3 Kategori novel diambil secara dinamis dari database",
    str_contains($replyKategori, 'Kategori & Genre Novel yang Tersedia') &&
    (str_contains($replyKategori, 'Sci-Fi') || str_contains($replyKategori, 'Romansa') || str_contains($replyKategori, 'Aksi & Silat') || str_contains($replyKategori, 'Petualangan')),
    "Daftar genre riil ditampilkan beserta jumlah novel terbit"
);

// 3.4 Menanyakan novel pribadi milik pengguna yang sedang login (Ahmad Rian)
$replyNovelPribadi = $controller->generateDatabaseResponse('Berapa novel yang sudah saya buat?', $penulisContext, $platformContext);
$expectedTotalNovels = $penulisContext['author_stats']['total_novels'];
assertTest(
    "3.4 Menanyakan jumlah novel pribadi pemilik akun (Ahmad Rian)",
    str_contains($replyNovelPribadi, "total **{$expectedTotalNovels} novel**") &&
    str_contains($replyNovelPribadi, 'Terbit/Published'),
    "Menghitung novel riil milik Ahmad Rian dari database (Total: {$expectedTotalNovels} novel)"
);

// 3.5 Menanyakan bab yang sudah ditulis pengguna yang sedang login
$replyBabPribadi = $controller->generateDatabaseResponse('Berapa bab yang sudah saya tulis?', $penulisContext, $platformContext);
$expectedTotalChapters = $penulisContext['author_stats']['total_chapters'];
assertTest(
    "3.5 Menanyakan total bab pribadi (Ahmad Rian)",
    str_contains($replyBabPribadi, "total **{$expectedTotalChapters} bab**"),
    "Menghitung total bab riil milik Ahmad Rian dari relasi chapters & novels (Total: {$expectedTotalChapters} bab)"
);

// 3.6 Menanyakan status publikasi novel pribadi
$replyStatusPublikasi = $controller->generateDatabaseResponse('Bagaimana status publikasi novel saya?', $penulisContext, $platformContext);
$firstUserNovelTitle = $penulisContext['novels'][0]['title'] ?? 'Kronik Lembah Kencana';
assertTest(
    "3.6 Status publikasi novel pribadi ditampilkan lengkap",
    str_contains($replyStatusPublikasi, $firstUserNovelTitle) &&
    str_contains($replyStatusPublikasi, 'PUBLISHED') &&
    str_contains($replyStatusPublikasi, 'Total Novel:'),
    "Menyajikan rincian status masing-masing naskah ({$firstUserNovelTitle})"
);

// 3.7 Uji isolasi data pribadi saat logout (Tamu mencoba bertanya data pribadi)
Session::clearAuth();
$replyTamuNovel = $controller->generateDatabaseResponse('Berapa novel yang sudah saya buat?', $guestUserContext, $platformContext);
assertTest(
    "3.7 Akses data pribadi ditolak jika tidak terotentikasi",
    str_contains($replyTamuNovel, 'Anda sedang menggunakan karyaNovel sebagai pengunjung. Silakan masuk untuk mengakses informasi akun pribadi.'),
    "Privasi data akun terlindungi dari akses anonim"
);

// 3.8 Uji pertanyaan data yang TIDAK ADA di database (tidak boleh mengarang data palsu)
$replyDataPalsu = $controller->generateDatabaseResponse('Siapa penulis novel Harry Potter dan Batu Bertuah?', $penulisContext, $platformContext);
assertTest(
    "3.8 Pertanyaan novel yang tidak ada di database tidak membuat data palsu",
    str_contains($replyDataPalsu, 'belum tersedia di database karyaNovel') ||
    str_contains($replyDataPalsu, 'tidak ditemukan'),
    "Chatbot jujur mengakui data belum tersedia di database"
);

// =============================================================================
// 4. PENGUJIAN BATASAN KONTEKS KARYANOVEL (OUT OF CONTEXT REJECTION)
// =============================================================================
echo "\n--- 4. PENGUJIAN BATASAN KONTEKS KARYANOVEL ---\n";

// 4.1 Pertanyaan di luar konteks: Bitcoin / Cryptocurrency
$replyBitcoin = $controller->generateDatabaseResponse('Berapa harga Bitcoin hari ini?', $penulisContext, $platformContext);
assertTest(
    "4.1 Pertanyaan Bitcoin ditolak dengan sopan sesuai batasan konteks",
    str_contains($replyBitcoin, 'Maaf, saya adalah karyaNovel Assistant yang berfokus pada penggunaan karyaNovel, penulisan novel, dan informasi yang tersedia di platform ini.') &&
    str_contains($replyBitcoin, 'Saya belum dapat membantu pertanyaan di luar konteks tersebut.'),
    "Respons penolakan ramah dan sopan"
);

// 4.2 Pertanyaan di luar konteks: Berita Politik
$replyPolitik = $controller->generateDatabaseResponse('Jelaskan berita politik terbaru dan pemilu hari ini', $penulisContext, $platformContext);
assertTest(
    "4.2 Pertanyaan Politik ditolak secara terstruktur",
    str_contains($replyPolitik, 'Saya belum dapat membantu pertanyaan di luar konteks tersebut.') &&
    str_contains($replyPolitik, 'Bagaimana cara membuat novel?'),
    "Mengarahkan pengguna kembali ke fitur-fitur karyaNovel"
);

// 4.3 Panduan karyaNovel tetap dijawab: Cara membuat novel
$replyBuatNovel = $controller->generateDatabaseResponse('Bagaimana cara membuat novel?', $penulisContext, $platformContext);
assertTest(
    "4.3 Panduan platform dijawab lengkap (Cara membuat novel)",
    str_contains($replyBuatNovel, 'Dashboard') &&
    str_contains($replyBuatNovel, 'Tambah Novel Baru') &&
    str_contains($replyBuatNovel, 'Judul Novel'),
    "Langkah-langkah terstruktur disajikan kepada pengguna"
);

// 4.4 Penulisan Kreatif tetap didukung dalam konteks karyaNovel (Ide Cerita / Karakter)
$replyIdeCerita = $controller->generateDatabaseResponse('Bantu saya buat ide cerita fantasi dan karakter detektif', $penulisContext, $platformContext);
assertTest(
    "4.4 Penulisan kreatif (ide cerita, karakter, premis) tetap dilayani dalam konteks platform",
    str_contains($replyIdeCerita, 'Bantuan Penulisan Kreatif karyaNovel') &&
    str_contains($replyIdeCerita, 'AI Outline'),
    "Chatbot tidak menolak pertanyaan penulisan kreatif seputar novel"
);

echo "\n=======================================================================\n";
echo " HASIL PENGUJIAN REVISI CHATBOT: {$passedTests} PASSED, {$failedTests} FAILED (TOTAL: {$totalTests})\n";
echo "=======================================================================\n";

if ($failedTests === 0) {
    echo ">>> SEMUA KRITERIA REVISI TOTAL CHATBOT KARYANOVEL SUKSES 100%! <<<\n";
    exit(0);
} else {
    echo ">>> ADA PENGUJIAN YANG GAGAL. SILAKAN PERIKSA DETAIL DI ATAS. <<<\n";
    exit(1);
}
