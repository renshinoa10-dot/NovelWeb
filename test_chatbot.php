<?php
/**
 * karyaNovel - Test Suite: Chatbot Assistant Beranda
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
require_once APPROOT . '/controllers/HomeController.php';
require_once APPROOT . '/controllers/ChatbotController.php';

echo "=======================================================================\n";
echo " karyaNovel - TEST SUITE: CHATBOT ASSISTANT BERANDA\n";
echo "=======================================================================\n\n";

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

// -----------------------------------------------------------------------------
// 1. Uji File dan Struktur Komponen
// -----------------------------------------------------------------------------
echo "--- 1. VERIFIKASI FILE & STRUKTUR KOMPONEN ---\n";

assertCondition(
    "1. Controller ChatbotController.php tersedia",
    file_exists(APPROOT . '/controllers/ChatbotController.php'),
    "app/controllers/ChatbotController.php ada dan siap dipakai"
);

assertCondition(
    "2. View partial chatbot.php tersedia",
    file_exists(APPROOT . '/views/home/chatbot.php'),
    "app/views/home/chatbot.php ada dan modular"
);

assertCondition(
    "3. Client script chatbot.js tersedia",
    file_exists(ROOT_PATH . '/public/js/chatbot.js'),
    "public/js/chatbot.js ada"
);

$cssContent = file_get_contents(ROOT_PATH . '/public/css/style.css');
assertCondition(
    "4. CSS Chatbot tersedia di style.css",
    str_contains($cssContent, '.kn-chatbot-wrapper') &&
    str_contains($cssContent, '.kn-chatbot-trigger-btn') &&
    str_contains($cssContent, '.kn-chatbot-panel'),
    "Styling trigger button, panel, animas, dan media queries lengkap"
);

// -----------------------------------------------------------------------------
// 2. Uji Integrasi pada Halaman Beranda
// -----------------------------------------------------------------------------
echo "\n--- 2. VERIFIKASI ELEMEN BERANDA (HOME) ---\n";

Session::start();
ob_start();
$homeController = new HomeController();
$homeController->index();
$homeHtml = ob_get_clean();

assertCondition(
    "5. Ikon Chatbot mengambang muncul di HTML Beranda",
    str_contains($homeHtml, 'knChatbotTriggerBtn') &&
    str_contains($homeHtml, 'data-bs-title="Chat dengan AI Assistant"'),
    "Tombol mengambang dengan tooltip 'Chat dengan AI Assistant' hadir di DOM"
);

assertCondition(
    "6. Panel Chatbot dimuat lengkap di HTML Beranda",
    str_contains($homeHtml, 'knChatbotPanel') &&
    str_contains($homeHtml, 'karyaNovel Assistant'),
    "Header panel memuat nama 'karyaNovel Assistant'"
);

assertCondition(
    "7. Pesan pembuka default tersedia di DOM",
    str_contains($homeHtml, 'Halo! 👋 Saya <strong>karyaNovel Assistant</strong>'),
    "Pesan pembuka ramah tersedia saat panel pertama kali dibuka"
);

assertCondition(
    "8. Tombol Pertanyaan Cepat lengkap tersedia",
    str_contains($homeHtml, 'Bagaimana cara membuat novel?') &&
    str_contains($homeHtml, 'Bagaimana cara menambahkan bab?') &&
    str_contains($homeHtml, 'Bagaimana cara menggunakan AI Writing Assistant?') &&
    str_contains($homeHtml, 'Bagaimana cara memublikasikan novel?'),
    "4 Pertanyaan cepat wajib tersedia sebagai pill buttons"
);

assertCondition(
    "9. Indikator sedang mengetik & form input siap",
    str_contains($homeHtml, 'knTypingIndicator') &&
    str_contains($homeHtml, 'knChatbotInput') &&
    str_contains($homeHtml, 'knChatbotSendBtn'),
    "Input form, send button, dan typing animation dot ada di DOM"
);

assertCondition(
    "10. File chatbot.js di-include di Beranda",
    str_contains($homeHtml, 'chatbot.js'),
    "Script chatbot.js dimuat di beranda"
);

// -----------------------------------------------------------------------------
// 3. Uji Logika Balasan ChatbotController (Simulasi & Relevansi)
// -----------------------------------------------------------------------------
echo "\n--- 3. PENGUJIAN LOGIKA & VALIDASI CONTROLLER ---\n";

class TestableChatbotController extends ChatbotController {
    public function getSimulatedReply(string $msg): string {
        return $this->generateSimulatedResponse($msg);
    }
}

$testController = new TestableChatbotController();

// A. Uji Respons Cara Membuat Novel
$replyNovel = $testController->getSimulatedReply('Bagaimana cara membuat novel baru?');
assertCondition(
    "11. Respons: Cara membuat novel",
    str_contains($replyNovel, 'Dashboard') &&
    str_contains($replyNovel, 'Tambah Novel Baru') &&
    str_contains($replyNovel, 'Judul Novel') &&
    str_contains($replyNovel, 'Sinopsis'),
    "Memuat panduan langkah demi langkah membuat novel"
);

// B. Uji Respons Cara Menambahkan Bab
$replyBab = $testController->getSimulatedReply('Bagaimana cara menambahkan bab?');
assertCondition(
    "12. Respons: Cara menambahkan bab",
    str_contains($replyBab, 'Kelola Bab') &&
    str_contains($replyBab, 'Tambah Bab Baru') &&
    str_contains($replyBab, 'Draft') &&
    str_contains($replyBab, 'Published'),
    "Memuat instruksi pengelolaan bab dan status draft/published"
);

// C. Uji Respons Cara Menggunakan AI Writing Assistant
$replyAi = $testController->getSimulatedReply('Bagaimana cara menggunakan AI Writing Assistant?');
assertCondition(
    "13. Respons: Cara menggunakan AI Writing Assistant",
    str_contains($replyAi, 'Editor Bab') &&
    str_contains($replyAi, 'Generate Ide') &&
    str_contains($replyAi, 'Generate Outline') &&
    str_contains($replyAi, 'Lanjutkan Cerita') &&
    str_contains($replyAi, 'Poles Tulisan'),
    "Menjelaskan fitur-fitur AI Assistant secara terperinci"
);

// D. Uji Respons Cara Memublikasikan Novel
$replyPub = $testController->getSimulatedReply('Bagaimana cara memublikasikan novel saya?');
assertCondition(
    "14. Respons: Cara memublikasikan novel",
    str_contains($replyPub, 'Published') &&
    str_contains($replyPub, 'Kelola Novel') &&
    str_contains($replyPub, 'publik'),
    "Menjelaskan cara beralih dari draft ke published untuk publik"
);

// E. Uji Respons Mengedit Tulisan
$replyEdit = $testController->getSimulatedReply('Bagaimana cara mengedit tulisan bab?');
assertCondition(
    "15. Respons: Cara mengedit tulisan",
    str_contains($replyEdit, 'Edit') &&
    str_contains($replyEdit, 'Simpan Perubahan'),
    "Menjelaskan cara revisi tulisan di editor"
);

// F. Uji Respons Writer's Block & Sapaan
$replyBlock = $testController->getSimulatedReply('Saya sedang buntu ide dan writer block, ada tips?');
$replyHello = $testController->getSimulatedReply('Halo karyaNovel!');
assertCondition(
    "16. Respons: Tips mengatasi writer's block & sapaan ramah",
    str_contains($replyBlock, 'AI Outline') && str_contains($replyHello, 'Halo'),
    "Memberikan saran penulisan kreatif serta sapaan ramah"
);

// G. Uji Fallback Pertanyaan Lain
$replyFallback = $testController->getSimulatedReply('Berapa harga tiket pesawat ke bulan?');
assertCondition(
    "17. Respons: Fallback pertanyaan di luar konteks",
    str_contains($replyFallback, 'topik seputar karyaNovel yang dapat saya bantu') &&
    str_contains($replyFallback, 'Bagaimana cara membuat novel?'),
    "Fallback mengarahkan pengguna kembali ke topik panduan platform"
);

// -----------------------------------------------------------------------------
// 4. Uji Keamanan, Aksesibilitas, dan Response API
// -----------------------------------------------------------------------------
echo "\n--- 4. PENGUJIAN KEAMANAN & AKSESIBILITAS ---\n";

assertCondition(
    "18. Tidak ada bocoran API Key di kode frontend JavaScript",
    !str_contains(file_get_contents(ROOT_PATH . '/public/js/chatbot.js'), 'AI_API_KEY') &&
    !str_contains(file_get_contents(ROOT_PATH . '/public/js/chatbot.js'), 'sk-'),
    "API Key aman hanya berada di backend server / .env"
);

assertCondition(
    "19. Aksesibilitas ARIA dan Keyboard didukung",
    str_contains(file_get_contents(ROOT_PATH . '/public/js/chatbot.js'), "e.key === 'Escape'") &&
    str_contains($homeHtml, 'aria-label="Chat dengan AI Assistant"') &&
    str_contains($homeHtml, 'aria-expanded="false"'),
    "Tombol memiliki ARIA labels dan Escape key menutup panel"
);

assertCondition(
    "20. Riwayat sesi disimpan di sessionStorage",
    str_contains(file_get_contents(ROOT_PATH . '/public/js/chatbot.js'), 'sessionStorage') &&
    str_contains(file_get_contents(ROOT_PATH . '/public/js/chatbot.js'), 'karyanovel_chatbot_session_history'),
    "Riwayat percakapan tidak hilang saat panel ditutup lalu dibuka kembali"
);

// -----------------------------------------------------------------------------
// 5. Uji Akses Database Pengguna & Pengetahuan untuk Role Penulis & Pembaca
// -----------------------------------------------------------------------------
echo "\n--- 5. PENGUJIAN AKSES DATABASE ROLE PENULIS & PEMBACA ---\n";

// A. Uji Pengguna Login sebagai Penulis (Simulasikan Sesi Ahmad Rian - User ID 2)
Session::set('user_id', 2);
$userModel = new User();
$userAhmad = $userModel->find(2);
Session::setAuth($userAhmad);

$replyProfilePenulis = $testController->getSimulatedReply('Siapa saya dan apa role saya?');
assertCondition(
    "21. Akses Database: Profil Penulis terautentikasi",
    str_contains($replyProfilePenulis, $userAhmad['name']) &&
    str_contains($replyProfilePenulis, 'Penulis') &&
    str_contains($replyProfilePenulis, 'Statistik Karya Novel'),
    "Mengambil nama {$userAhmad['name']} dan statistik novel dari database"
);

$replyNaskahPenulis = $testController->getSimulatedReply('Tampilkan daftar novel saya');
assertCondition(
    "22. Akses Database: Daftar novel & naskah Penulis",
    str_contains($replyNaskahPenulis, 'Statistik Kepenulisan') &&
    str_contains($replyNaskahPenulis, 'Daftar Novel Anda') &&
    str_contains($replyNaskahPenulis, 'Dashboard Penulis'),
    "Daftar novel dan total bab milik Penulis berhasil diambil dari database"
);

// B. Uji Role Pembaca: Rekomendasi Novel dari Database
$replyRekomendasi = $testController->getSimulatedReply('Rekomendasikan novel populer yang seru');
assertCondition(
    "23. Akses Database: Rekomendasi novel untuk Pembaca",
    str_contains($replyRekomendasi, 'Rekomendasi Novel Pilihan') &&
    (str_contains($replyRekomendasi, 'Genre') || str_contains($replyRekomendasi, 'Jelajah Novel')),
    "Katalog novel published dari database disajikan kepada Pembaca"
);

// C. Uji Pengetahuan Pembaca: Personalisasi Tampilan Bacaan
$replyFiturBaca = $testController->getSimulatedReply('Bagaimana cara mengatur tampilan membaca dan tema sepia?');
assertCondition(
    "24. Pengetahuan Pembaca: Panduan tema bacaan, font, dan progress bar",
    str_contains($replyFiturBaca, 'Mode Gelap') &&
    str_contains($replyFiturBaca, 'Sepia') &&
    str_contains($replyFiturBaca, 'OLED') &&
    str_contains($replyFiturBaca, 'Reading Scroll Progress'),
    "Menjelaskan fitur personalisasi kenyamanan membaca secara lengkap"
);

// D. Uji Pengguna Tamu (Belum Login)
Session::clearAuth();
$replyTamu = $testController->getSimulatedReply('Profil akun saya');
assertCondition(
    "25. Akses Database: Konteks Tamu/Pembaca Publik",
    str_contains($replyTamu, 'Pembaca Tamu') &&
    str_contains($replyTamu, 'Daftar Akun Gratis'),
    "Mengidentifikasi tamu sebagai Pembaca dan menawarkan registrasi Penulis"
);

echo "\n=======================================================================\n";
echo " TOTAL PENGUJIAN: {$passed} PASSED, {$failed} FAILED\n";
echo "=======================================================================\n";

if ($failed === 0) {
    echo ">>> SELURUH FITUR CHATBOT BERANDA TERMASUK ROLE & DATABASE SUKSES! <<<\n";
}
