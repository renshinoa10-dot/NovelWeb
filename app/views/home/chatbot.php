<?php
/**
 * karyaNovel - Floating Chatbot Assistant Component
 * Mendukung deteksi sesi role: Admin Assistant, Penulis Assistant, Pembaca Assistant, dan Pembaca Publik
 */
$isLoggedIn = Session::isLoggedIn();
$currentUser = $isLoggedIn ? Session::user() : null;
$userName = $currentUser['name'] ?? 'Tamu';

// Resolusi Role Dinamis untuk UI Assistant
$userRoleKey = 'guest';
$modeTitle = 'Pembaca Publik';
$modeBadgeClass = 'bg-secondary-subtle text-light border border-secondary-subtle';
$modeSubtext = 'Online • Panduan Pengunjung & Penjelajahan';

if ($isLoggedIn && $currentUser) {
    $currentRole = $currentUser['role'] ?? 'pembaca';
    if ($currentRole === 'admin') {
        $userRoleKey = 'admin';
        $modeTitle = 'Admin Assistant';
        $modeBadgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
        $modeSubtext = 'Online • Kendali Sistem & Analitik Operasional';
    } elseif ($currentRole === 'penulis') {
        $userRoleKey = 'penulis';
        $modeTitle = 'Penulis Assistant';
        $modeBadgeClass = 'bg-purple-subtle text-purple-light border border-purple-subtle';
        $modeSubtext = 'Online • Panduan Naskah & Asisten Penulisan';
    } else {
        $userRoleKey = 'pembaca';
        $modeTitle = 'Pembaca Assistant';
        $modeBadgeClass = 'bg-info-subtle text-info border border-info-subtle';
        $modeSubtext = 'Online • Eksplorasi Novel & Panduan Membaca';
    }
}
?>
<div class="kn-chatbot-wrapper" id="karyanovel-chatbot-widget" data-endpoint="<?= base_url('chatbot/message'); ?>" data-role-mode="<?= sanitize($userRoleKey); ?>">
    <!-- Floating Chat Trigger Button -->
    <button type="button" 
            class="kn-chatbot-trigger-btn" 
            id="chatbot-toggle"
            name="knChatbotTriggerBtn"
            data-testid="knChatbotTriggerBtn"
            aria-label="Chat dengan AI Assistant" 
            data-bs-toggle="tooltip" 
            data-bs-placement="left" 
            data-bs-title="Chat dengan AI Assistant"
            aria-expanded="false"
            aria-controls="chatbot-popup">
        <!-- Floating Active Pulse Glow -->
        <span class="kn-chatbot-trigger-pulse"></span>
        
        <!-- Icons: Default Robot Chat & Open Close State -->
        <span class="kn-chatbot-icon-closed">
            <i class="bi bi-robot"></i>
        </span>
        <span class="kn-chatbot-icon-open d-none">
            <i class="bi bi-x-lg"></i>
        </span>

        <!-- Sparkle Mini Badge -->
        <span class="kn-chatbot-badge-sparkle">
            <i class="bi bi-stars"></i>
        </span>
    </button>

    <!-- Chatbot Window Panel (Pop-up Modal on the Right) -->
    <section class="kn-chatbot-panel d-none" 
             id="chatbot-popup" 
             data-component="knChatbotPanel"
             role="dialog" 
             aria-modal="false" 
             aria-label="karyaNovel Assistant"
             aria-labelledby="knChatbotTitle"
             aria-hidden="true"
             style="display: none;">
        
        <!-- Header Panel -->
        <div class="kn-chatbot-header">
            <div class="d-flex align-items-center gap-2">
                <div class="kn-chatbot-avatar-header">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-white kn-chatbot-title" id="knChatbotTitle">karyaNovel Assistant</h6>
                        <span class="badge <?= $modeBadgeClass; ?> x-small" id="knChatbotRoleBadge"><?= sanitize($modeTitle); ?></span>
                    </div>
                    <div class="kn-chatbot-status d-flex align-items-center gap-1">
                        <span class="kn-status-dot"></span>
                        <span class="text-white-50 x-small" id="knChatbotModeSubtext"><?= sanitize($modeSubtext); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-1">
                <button type="button" 
                        class="kn-chatbot-action-btn" 
                        id="knChatbotMinimizeBtn" 
                        title="Perkecil Panel" 
                        aria-label="Perkecil Panel">
                    <i class="bi bi-dash-lg"></i>
                </button>
                <button type="button" 
                        class="kn-chatbot-action-btn" 
                        id="knChatbotCloseBtn" 
                        title="Tutup Panel" 
                        aria-label="Tutup Panel">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Conversation Messages Body -->
        <div class="kn-chatbot-body" id="knChatbotMessages">
            <!-- Initial Welcome Message from Assistant -->
            <div class="kn-message-row bot-row">
                <div class="kn-message-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="kn-message-content">
                    <div class="kn-message-bubble bot-bubble">
                        <?php if ($userRoleKey === 'admin'): ?>
                            <p class="mb-1">Halo <strong><?= sanitize($userName); ?></strong>! 👋 Saya <strong>karyaNovel Assistant</strong> (<span class="text-danger fw-bold">Admin Assistant</span>).</p>
                            <p class="mb-0 x-small text-white-50">Anda memiliki hak akses penuh ke analitik operasional: ringkasan pengguna, novel, bab, kategori, dan audit kelengkapan data platform.</p>
                        <?php elseif ($userRoleKey === 'penulis'): ?>
                            <p class="mb-1">Halo <strong><?= sanitize($userName); ?></strong>! 👋 Saya <strong>karyaNovel Assistant</strong> (<span class="text-purple-light fw-bold">Penulis Assistant</span>).</p>
                            <p class="mb-0 x-small text-white-50">Saya dapat memeriksa naskah novel Anda dari database, menghitung total bab pribadi, memberikan saran genre, serta memandu penulisan kreatif.</p>
                        <?php elseif ($userRoleKey === 'pembaca'): ?>
                            <p class="mb-1">Halo <strong><?= sanitize($userName); ?></strong>! 👋 Saya <strong>karyaNovel Assistant</strong> (<span class="text-info fw-bold">Pembaca Assistant</span>).</p>
                            <p class="mb-0 x-small text-white-50">Jelajahi ribuan bab seru! Tanyakan novel terbit, rekomendasi genre, sinopsis, atau panduan kenyamanan membaca.</p>
                        <?php else: ?>
                            <p class="mb-1">Halo! 👋 Saya <strong>karyaNovel Assistant</strong> (<span class="text-light fw-bold">Pembaca Publik</span>).</p>
                            <p class="mb-0 x-small text-white-50">Silakan jelajahi katalog novel yang telah diterbitkan, cari genre favorit, atau buat akun untuk mulai menulis karyamu sendiri.</p>
                        <?php endif; ?>
                    </div>
                    <span class="kn-message-time" id="knInitTime"><?= date('H:i'); ?></span>
                </div>
            </div>

            <!-- Quick Question Suggestions -->
            <div class="kn-quick-questions mt-3 mb-2" id="knQuickQuestions">
                <div class="x-small text-white-50 mb-2 fw-medium">
                    <i class="bi bi-lightbulb me-1 text-warning"></i> Pertanyaan Cepat (<?= sanitize($modeTitle); ?>):
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($userRoleKey === 'admin'): ?>
                        <!-- Pertanyaan Khusus Admin -->
                        <button type="button" class="kn-quick-pill" data-question="Ringkas statistik keseluruhan karyaNovel">
                            📈 Ringkasan Sistem
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Berapa jumlah pengguna terdaftar?">
                            👥 Jumlah Pengguna
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Berapa jumlah akun penulis dan pembaca?">
                            👤 Penulis & Pembaca
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Berapa jumlah novel di sistem?">
                            📚 Total Novel
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Siapa penulis dengan novel terbanyak?">
                            🌟 Penulis Terbanyak
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Tampilkan kategori novel beserta jumlah novelnya">
                            🏷️ Statistik Kategori
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Tampilkan statistik publikasi novel">
                            📊 Statistik Publikasi
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Apakah ada data novel yang belum lengkap?">
                            🔍 Audit Data Lengkap
                        </button>
                    <?php elseif ($userRoleKey === 'penulis'): ?>
                        <!-- Pertanyaan Khusus Penulis -->
                        <button type="button" class="kn-quick-pill" data-question="Siapa Anda?">
                            🤖 Siapa Anda?
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Siapa saya?">
                            👤 Siapa saya?
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Berapa novel yang sudah saya buat?">
                            📊 Novel Saya
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Tampilkan novel milik saya">
                            📁 Daftar Novel Saya
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Berapa bab yang sudah saya tulis?">
                            📝 Total Bab Saya
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Apa status publikasi novel saya?">
                            🚀 Status Publikasi
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Kapan novel saya dibuat atau diperbarui?">
                            🕒 Riwayat Waktu Naskah
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Kategori apa yang cocok untuk novel saya?">
                            💡 Rekomendasi Kategori
                        </button>
                    <?php elseif ($userRoleKey === 'pembaca'): ?>
                        <!-- Pertanyaan Khusus Pembaca -->
                        <button type="button" class="kn-quick-pill" data-question="Siapa Anda?">
                            🤖 Siapa Anda?
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Siapa saya?">
                            👤 Siapa saya?
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Novel apa saja yang tersedia?">
                            📚 Novel Tersedia
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Apa kategori novel yang tersedia?">
                            🏷️ Kategori Novel
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Tampilkan novel yang sudah diterbitkan">
                            ✨ Novel Terbit
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Cari novel bergenre fantasi">
                            🔮 Novel Fantasi
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Bagaimana cara membaca novel?">
                            📖 Panduan Membaca
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Bagaimana cara menjadi penulis di karyaNovel?">
                            ✍️ Jadi Penulis
                        </button>
                    <?php else: ?>
                        <!-- Pertanyaan Khusus Tamu / Pengunjung -->
                        <button type="button" class="kn-quick-pill" data-question="Siapa Anda?">
                            🤖 Siapa Anda?
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Siapa saya?">
                            👤 Siapa saya?
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Novel apa saja yang tersedia?">
                            📚 Novel Tersedia
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Apa kategori novel yang tersedia?">
                            🏷️ Kategori Novel
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Tampilkan novel yang sudah diterbitkan">
                            ✨ Novel Terbit
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Cari novel bergenre fantasi">
                            🔮 Novel Fantasi
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Bagaimana cara membuat akun?">
                            🔑 Cara Daftar Akun
                        </button>
                        <button type="button" class="kn-quick-pill" data-question="Bagaimana cara membaca novel?">
                            📖 Panduan Membaca
                        </button>
                    <?php endif; ?>

                    <!-- Panduan Menulis & Platform (Wajib Tersedia untuk Konsistensi Pengetahuan Sistem) -->
                    <button type="button" class="kn-quick-pill" data-question="Bagaimana cara membuat novel?">
                        📖 Cara Membuat Novel
                    </button>
                    <button type="button" class="kn-quick-pill" data-question="Bagaimana cara menambahkan bab?">
                        📝 Cara Tambah Bab
                    </button>
                    <button type="button" class="kn-quick-pill" data-question="Bagaimana cara menggunakan AI Writing Assistant?">
                        🤖 AI Writing Assistant
                    </button>
                    <button type="button" class="kn-quick-pill" data-question="Bagaimana cara memublikasikan novel?">
                        🚀 Cara Publikasi Novel
                    </button>
                </div>
            </div>
        </div>

        <!-- Typing Indicator (Hidden by default) -->
        <div class="kn-typing-indicator-row d-none" id="knTypingIndicator">
            <div class="kn-message-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div class="kn-typing-bubble">
                <span class="kn-typing-dot"></span>
                <span class="kn-typing-dot"></span>
                <span class="kn-typing-dot"></span>
            </div>
            <span class="x-small text-white-50 ms-2">karyaNovel Assistant sedang mengetik...</span>
        </div>

        <!-- Validation / Alert Message (Hidden by default) -->
        <div class="kn-chatbot-alert d-none" id="knChatbotAlert">
            <i class="bi bi-exclamation-circle-fill me-1"></i>
            <span id="knChatbotAlertText">Silakan ketik pertanyaan terlebih dahulu.</span>
        </div>

        <!-- Chat Input Footer -->
        <div class="kn-chatbot-footer">
            <form id="knChatbotForm" class="d-flex align-items-center gap-2 mb-0 no-loading-form" autocomplete="off">
                <input type="text" 
                       class="form-control kn-chatbot-input" 
                       id="knChatbotInput" 
                       placeholder="Ketik pertanyaanmu seputar naskah atau novel..." 
                       aria-label="Ketik pesan untuk karyaNovel Assistant"
                       maxlength="1000">
                <button type="submit" 
                       class="btn btn-primary-gradient kn-chatbot-send-btn no-loading" 
                       id="knChatbotSendBtn" 
                       aria-label="Kirim Pesan"
                       title="Kirim (Enter)">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
            <div class="kn-chatbot-footer-subtext text-center mt-1">
                <span class="x-small text-white-50">Tekan <strong>Enter</strong> untuk mengirim pesan</span>
            </div>
        </div>
    </section>
</div>
