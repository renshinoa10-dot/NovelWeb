<section class="py-4 editor-workspace">
    <div class="container-fluid px-lg-4">

        <!-- Local Backup Restore Banner (Emergency Recovery) -->
        <div id="localBackupAlert" class="alert alert-purple-subtle border border-purple-glow d-none align-items-center justify-content-between rounded-4 py-2 px-3 mb-3 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2 small text-purple-light">
                <i class="bi bi-shield-check-fill fs-5"></i>
                <span>Ditemukan draf cadangan naskah di penyimpanan lokal browser Anda yang belum sempat tersimpan ke database.</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-xs btn-primary-gradient rounded-pill px-3 py-1" onclick="restoreFromLocalBackup()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Pulihkan Draf
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" onclick="dismissLocalBackup()">
                    Abaikan
                </button>
            </div>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 small rounded-3 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= sanitize($errors['general']); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form Editor Naskah -->
        <form action="<?= base_url('chapter/edit/' . $chapter['id']); ?>" method="POST" id="novelEditorForm" novalidate>
            <?= csrf_field(); ?>
            <input type="hidden" name="_method" value="POST">

            <!-- 1. STICKY TOP HEADER BAR -->
            <div class="editor-header-bar mb-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    
                    <!-- Left: Navigation & Chapter Title -->
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <a href="<?= base_url('novels/' . $novel['id']); ?>" class="btn btn-sm btn-ghost-light rounded-pill px-2 py-1" title="Kembali ke Detail Novel">
                            <i class="bi bi-arrow-left fs-5"></i>
                        </a>

                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <span class="badge bg-purple-subtle text-purple-light px-3 py-2 rounded-pill small fw-semibold text-nowrap d-none d-md-inline-block">
                                <i class="bi bi-book me-1"></i> <?= sanitize($novel['title']); ?>
                            </span>

                            <!-- Judul Bab Input -->
                            <div class="flex-grow-1 position-relative">
                                <input type="text" name="title" id="chapterTitle" class="form-control editor-title-input w-100 <?= !empty($errors['title']) ? 'is-invalid' : ''; ?>" placeholder="Ketik Judul Bab di sini..." value="<?= sanitize($oldInput['title'] ?? ''); ?>" required autofocus maxlength="200" title="Klik untuk menyunting judul bab">
                                <?php if (!empty($errors['title'])): ?>
                                    <div class="invalid-feedback d-block small ps-2"><?= sanitize($errors['title']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Chapter Order, Status, Save State & Action Buttons -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between justify-content-lg-end gap-2">
                        
                        <!-- Status Penyimpanan (Live Autosave Indicator) -->
                        <div id="saveStatusIndicator" class="badge bg-dark border border-secondary-subtle px-3 py-2 rounded-pill small d-flex align-items-center gap-1 text-muted" title="Status sinkronisasi penyimpanan naskah">
                            <i class="bi bi-cloud-check text-success"></i>
                            <span id="saveStatusText">Tersimpan</span>
                        </div>

                        <!-- Nomor Urutan Bab -->
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary-subtle" style="width: 110px;" title="Nomor Bab">
                            <span class="input-group-text bg-dark border-0 text-muted small px-2">Bab #</span>
                            <input type="number" name="chapter_order" id="chapterOrder" class="form-control bg-dark border-0 text-white text-center px-1" min="1" value="<?= (int)($oldInput['chapter_order'] ?? $chapter['chapter_order']); ?>" required>
                        </div>

                        <!-- Pilihan Status Naskah (Draft / Published) -->
                        <select name="status" id="chapterStatus" class="form-select form-select-sm bg-dark border-secondary-subtle text-white rounded-pill px-3 py-1" style="width: 135px;" title="Pilih Status Publikasi">
                            <option value="draft" <?= ($oldInput['status'] === 'draft') ? 'selected' : ''; ?>>📝 Draft</option>
                            <option value="published" <?= ($oldInput['status'] === 'published') ? 'selected' : ''; ?>>🌐 Published</option>
                        </select>

                        <!-- Preview Tombol Baca Publik -->
                        <a href="<?= base_url('chapter/read/' . $chapter['id']); ?>" class="btn btn-sm btn-outline-info rounded-pill px-3 d-none d-sm-inline-flex align-items-center" target="_blank" title="Pratinjau tampilan pembaca">
                            <i class="bi bi-eye me-1"></i> Preview
                        </a>

                        <!-- Quick Mobile Trigger to AI Assistant -->
                        <button type="button" class="btn btn-sm btn-outline-purple rounded-pill px-3 d-lg-none" onclick="document.getElementById('aiAssistantPanel').scrollIntoView({behavior: 'smooth'})" title="Buka AI Assistant">
                            <i class="bi bi-stars"></i> AI
                        </button>

                        <!-- Tombol Simpan Naskah -->
                        <div class="btn-group">
                            <button type="submit" name="submit_action" value="draft" class="btn btn-sm btn-glass-secondary rounded-pill px-3 me-1" onclick="document.getElementById('chapterStatus').value='draft';" title="Simpan sebagai draft pribadi">
                                <i class="bi bi-pencil me-1"></i> Simpan Draft
                            </button>
                            <button type="submit" name="submit_action" value="save" class="btn btn-sm btn-primary-gradient rounded-pill px-4 shadow" title="Simpan semua perubahan ke database (Ctrl+S)">
                                <i class="bi bi-check2-circle me-1"></i> Simpan
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2. WORKSPACE AREA: DUA KOLOM (EDITOR & AI ASSISTANT) -->
            <div class="row g-4 align-items-start">
                
                <!-- KOLOM KIRI: TEXT EDITOR AREA (ISI NOVEL) -->
                <div class="col-lg-8 col-xl-9 editor-main-column">
                    <div class="editor-card overflow-hidden">
                        
                        <!-- Toolbar Format & Kenyamanan Menulis -->
                        <div class="editor-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <!-- Format & Font Switcher -->
                            <div class="d-flex align-items-center gap-1">
                                <!-- Font Switcher -->
                                <div class="btn-group btn-group-sm me-2" role="group" aria-label="Gaya Huruf">
                                    <button type="button" class="btn btn-xs btn-outline-secondary active" id="btnFontSerif" onclick="setFontStyle('serif')" title="Gaya Font Serif Sastra">
                                        <i class="bi bi-type-serif me-1"></i> Serif
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btnFontSans" onclick="setFontStyle('sans')" title="Gaya Font Sans Modern">
                                        <i class="bi bi-type me-1"></i> Sans
                                    </button>
                                </div>

                                <!-- Font Size Switcher -->
                                <div class="btn-group btn-group-sm me-2" role="group" aria-label="Ukuran Huruf">
                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="changeFontSize(-1)" title="Perkecil Huruf">A-</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="changeFontSize(1)" title="Perbesar Huruf">A+</button>
                                </div>

                                <!-- Tombol Sisipkan Cepat Tanda Baca Sastra -->
                                <div class="d-none d-md-inline-flex align-items-center gap-1 border-start border-secondary-subtle ps-2">
                                    <button type="button" class="btn btn-xs btn-ghost-light py-0 px-2" onclick="insertSymbol('“', '”')" title="Kutip Dialog Sastra">“ ”</button>
                                    <button type="button" class="btn btn-xs btn-ghost-light py-0 px-2" onclick="insertSymbol('— ')" title="Tanda Pisah Dialog (Em-dash)">—</button>
                                    <button type="button" class="btn btn-xs btn-ghost-light py-0 px-2" onclick="insertSymbol('… ')" title="Tanda Elipsis / Jeda Cerita">…</button>
                                    <button type="button" class="btn btn-xs btn-ghost-light py-0 px-2" onclick="insertSymbol('\n\n* * *\n\n')" title="Pemisah Adegan (Scene Break)">* * *</button>
                                </div>
                            </div>

                            <!-- Word Counter & Zen Mode Toggle -->
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span class="d-flex align-items-center gap-1 text-light">
                                    <i class="bi bi-file-earmark-text text-primary"></i>
                                    <strong id="wordCount">0</strong> kata
                                </span>
                                <span>•</span>
                                <span class="d-none d-sm-inline"><strong id="charCount">0</strong> karakter</span>
                                <span class="d-none d-md-inline">•</span>
                                <span class="d-none d-md-inline text-info" id="readingTime">~0 menit baca</span>

                                <!-- Tombol Mode Fokus / Zen Mode -->
                                <button type="button" id="zenModeToggle" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 ms-1 text-light" onclick="toggleZenMode()" title="Mode Fokus Menulis (Sembunyikan panel lain)">
                                    <i class="bi bi-arrows-fullscreen me-1"></i> <span id="zenBtnText">Fokus</span>
                                </button>
                            </div>
                        </div>

                        <!-- Area Penulisan Naskah (Textarea Editor) -->
                        <div class="p-0 position-relative">
                            <textarea name="content" id="chapterContent" class="form-control editor-textarea font-serif-novel w-100 <?= !empty($errors['content']) ? 'is-invalid' : ''; ?>" placeholder="Mulai tuliskan kisah memikatmu di sini... (AI Assistant siap membantu kapan saja di panel kanan)" style="font-size: 1.15rem;"><?= sanitize($oldInput['content'] ?? ''); ?></textarea>
                            
                            <?php if (!empty($errors['content'])): ?>
                                <div class="invalid-feedback d-block p-3 border-top border-danger-subtle bg-danger-subtle text-danger">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> <?= sanitize($errors['content']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Footer Editor Bar -->
                        <div class="p-3 bg-dark border-top border-secondary-subtle d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 small text-muted">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-keyboard"></i>
                                <span>Tips Pintasan: <kbd class="bg-secondary text-white py-0 px-1 rounded">Ctrl+S</kbd> Simpan Naskah | <kbd class="bg-secondary text-white py-0 px-1 rounded">Tab</kbd> Indentasi Paragraf</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span id="lastSavedLabel">Terakhir disimpan: <?= format_date($chapter['updated_at'] ?? $chapter['created_at'], true); ?></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- KOLOM KANAN: AI WRITING ASSISTANT SIDEBAR -->
                <div class="col-lg-4 col-xl-3 editor-sidebar-column">
                    <div class="editor-sidebar-ai">
                        <div class="ai-card p-3 p-xl-4" id="aiAssistantPanel">
                            
                            <!-- Header AI Assistant -->
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="brand-logo-icon bg-primary text-white" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="bi bi-stars"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-0">AI Assistant</h6>
                                        <span class="text-purple-light small" style="font-size: 0.72rem;">Mitra Kreatif Naskah</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0 d-lg-none" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" title="Kembali ke Editor">
                                        <i class="bi bi-arrow-up"></i> Editor
                                    </button>
                                    <span class="badge bg-purple-glow text-white rounded-pill px-2 py-1 small" style="font-size: 0.7rem;">
                                        Aktif
                                    </span>
                                </div>
                            </div>

                            <p class="text-muted small mb-3">
                                AI bertindak sebagai asisten kreatif Anda. Pilih jenis bantuan di bawah atau ketik instruksi khusus untuk naskah ini:
                            </p>

                            <!-- Custom Prompt Input (Hingga 2000 karakter) -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label text-light small fw-medium mb-0">
                                        <i class="bi bi-chat-left-text text-purple-light me-1"></i> Instruksi / Prompt Penulis:
                                    </label>
                                    <span class="text-muted small" style="font-size: 0.7rem;"><span id="aiPromptCount">0</span>/2000</span>
                                </div>
                                <textarea id="aiCustomPrompt" rows="3" class="form-control form-control-sm bg-dark border-secondary-subtle text-white rounded-3 mb-2" placeholder="Contoh: Berikan 5 ide novel bergenre fantasy tentang seorang pemuda yang menemukan dunia rahasia..." maxlength="2000"></textarea>
                                
                                <!-- Quick Template Pills -->
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.68rem;" onclick="setAiPrompt('Berikan 5 ide novel bergenre fantasy tentang seorang pemuda yang menemukan dunia rahasia.')">💡 5 Ide Rahasia</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.68rem;" onclick="setAiPrompt('Rancang outline 4 babak dengan eskalasi konflik yang intens.')">📑 Outline 4 Babak</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.68rem;" onclick="setAiPrompt('Buat profil tokoh pendamping misterius yang memiliki rahasia masa lalu.')">👤 Tokoh Misterius</button>
                                </div>
                            </div>

                            <!-- 6 TOMBOL BANTUAN AI UTAMA -->
                            <div class="d-flex flex-column gap-2 mb-3">
                                <!-- 1. Generate Ide -->
                                <button type="button" class="ai-btn-action" onclick="triggerAi('idea')">
                                    <i class="bi bi-lightbulb-fill text-warning fs-6"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-white">Generate Ide</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Saran alur, premis cerita & 5 ide unik</span>
                                    </div>
                                </button>

                                <!-- 2. Generate Outline -->
                                <button type="button" class="ai-btn-action" onclick="triggerAi('outline')">
                                    <i class="bi bi-diagram-3-fill text-primary fs-6"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-white">Generate Outline</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Struktur 4 babak & eskalasi konflik</span>
                                    </div>
                                </button>

                                <!-- 3. Karakter & Dialog -->
                                <button type="button" class="ai-btn-action" onclick="triggerAi('character')">
                                    <i class="bi bi-person-heart text-danger fs-6"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-white">Karakter & Dialog</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Profil tokoh, emosi & cuplikan dialog</span>
                                    </div>
                                </button>

                                <!-- 4. Lanjutkan Cerita -->
                                <button type="button" class="ai-btn-action" onclick="triggerAi('continue')">
                                    <i class="bi bi-magic text-info fs-6"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-white">Lanjutkan Cerita</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Tulis kelanjutan adegan berikutnya</span>
                                    </div>
                                </button>

                                <!-- 5. Perbaiki Gaya Bahasa -->
                                <button type="button" class="ai-btn-action" onclick="triggerAi('improve')">
                                    <i class="bi bi-brush-fill text-success fs-6"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-white">Perbaiki Gaya Bahasa</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Poles diksi sastra & show don't tell</span>
                                    </div>
                                </button>

                                <!-- 6. Beri Saran & Masukan -->
                                <button type="button" class="ai-btn-action" onclick="triggerAi('suggestions')">
                                    <i class="bi bi-chat-quote-fill text-warning-emphasis fs-6"></i>
                                    <div class="d-flex flex-column">
                                        <span class="text-white">Beri Saran & Masukan</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">Evaluasi pacing, subteks & adegan</span>
                                    </div>
                                </button>
                            </div>

                            <!-- Error Notification Card -->
                            <div id="aiErrorBox" class="alert alert-danger d-none rounded-3 py-2 px-3 mb-3 small border border-danger-subtle">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                        <span id="aiErrorMsg">Gagal memproses permintaan AI.</span>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white p-1" style="font-size: 0.65rem;" onclick="document.getElementById('aiErrorBox').classList.add('d-none');"></button>
                                </div>
                            </div>

                            <!-- Loading State Card -->
                            <div id="aiLoadingBox" class="alert alert-purple-subtle d-none rounded-3 py-3 px-3 mb-3 text-center border border-purple-glow">
                                <div class="spinner-border spinner-border-sm text-purple-light mb-2" role="status"></div>
                                <div class="text-white small fw-semibold" id="aiLoadingMsg">AI sedang menganalisis naskah...</div>
                                <span class="text-muted" style="font-size: 0.72rem;">Menyesuaikan dengan genre <?= sanitize($novel['genre']); ?></span>
                            </div>

                            <!-- AI Output Result Card -->
                            <div id="aiResultContainer" class="d-none">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-purple-light small fw-bold" id="aiResultTypeTitle">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i> Hasil AI
                                    </span>
                                    <button type="button" class="btn btn-xs btn-link text-muted text-decoration-none p-0" onclick="closeAiResult()">
                                        &times; Tutup
                                    </button>
                                </div>

                                <div id="aiResultText" class="ai-output-box mb-3 font-serif-novel"></div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary-gradient rounded-pill w-100 shadow-sm" onclick="insertAiText()">
                                        <i class="bi bi-plus-circle me-1"></i> Sisipkan ke Naskah
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="copyAiText()" title="Salin ke papan klip">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Riwayat Sesi AI (History Drawer Toggle) -->
                            <div class="pt-3 mt-3 border-top border-secondary-subtle">
                                <button type="button" class="btn btn-xs btn-ghost-light w-100 d-flex align-items-center justify-content-between text-muted" data-bs-toggle="collapse" data-bs-target="#aiSessionHistoryCollapse">
                                    <span><i class="bi bi-clock-history me-1"></i> Riwayat Sesi Ini (<span id="historyCount">0</span>)</span>
                                    <i class="bi bi-chevron-down small"></i>
                                </button>
                                <div class="collapse mt-2" id="aiSessionHistoryCollapse">
                                    <div id="aiSessionHistoryList" class="d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;">
                                        <div class="text-muted small text-center py-2" id="noHistoryText">Belum ada riwayat pada sesi ini.</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</section>

<!-- Scripts Khusus Editor Bab (Autosave, Word Counter, Keyboard Shortcuts & AI Integration) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chapterId = <?= (int)$chapter['id']; ?>;
    const novelId = <?= (int)$novel['id']; ?>;
    const contentArea = document.getElementById('chapterContent');
    const titleInput = document.getElementById('chapterTitle');
    const statusSelect = document.getElementById('chapterStatus');
    const wordCounter = document.getElementById('wordCount');
    const charCounter = document.getElementById('charCount');
    const readingTime = document.getElementById('readingTime');
    const saveIndicator = document.getElementById('saveStatusIndicator');
    const saveStatusText = document.getElementById('saveStatusText');
    const lastSavedLabel = document.getElementById('lastSavedLabel');

    // Kunci LocalStorage untuk backup darurat browser
    const LOCAL_STORAGE_KEY = 'karyanovel_backup_chap_' + chapterId;
    let isDirty = false;
    let autosaveTimer = null;
    let lastSavedContent = contentArea ? contentArea.value : '';
    let lastSavedTitle = titleInput ? titleInput.value : '';

    // =========================================================================
    // 1. WORD, CHARACTER & READING TIME COUNTER
    // =========================================================================
    function updateWritingStats() {
        if (!contentArea) return;
        const text = contentArea.value.trim();
        const chars = contentArea.value.length;
        const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
        const estMinutes = Math.ceil(words / 180); // Rata-rata 180 kata/menit membaca santai

        if (wordCounter) wordCounter.textContent = words.toLocaleString('id-ID');
        if (charCounter) charCounter.textContent = chars.toLocaleString('id-ID');
        if (readingTime) readingTime.textContent = '~' + estMinutes + ' menit baca';
    }

    if (contentArea) {
        contentArea.addEventListener('input', function() {
            updateWritingStats();
            markAsDirty();
            saveToLocalBackup();
            scheduleAutosave();
        });
        updateWritingStats();
    }

    if (titleInput) {
        titleInput.addEventListener('input', function() {
            markAsDirty();
            saveToLocalBackup();
            scheduleAutosave();
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            markAsDirty();
            scheduleAutosave();
        });
    }

    // =========================================================================
    // 2. STATUS PENYIMPANAN & AUTOSAVE ENGINE (DEBOUNCED & AMAN)
    // =========================================================================
    function markAsDirty() {
        if (!isDirty) {
            isDirty = true;
            if (saveIndicator) {
                saveIndicator.className = 'badge bg-dark border border-warning text-warning px-3 py-2 rounded-pill small d-flex align-items-center gap-1';
                saveIndicator.innerHTML = '<i class="bi bi-pencil-fill"></i> <span id="saveStatusText">Perubahan belum disimpan</span>';
            }
        }
    }

    function setSaveStatus(status, text) {
        if (!saveIndicator) return;
        if (status === 'saving') {
            saveIndicator.className = 'badge bg-dark border border-info text-info px-3 py-2 rounded-pill small d-flex align-items-center gap-1 save-pulse';
            saveIndicator.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...';
        } else if (status === 'saved') {
            isDirty = false;
            saveIndicator.className = 'badge bg-dark border border-success text-success px-3 py-2 rounded-pill small d-flex align-items-center gap-1';
            saveIndicator.innerHTML = '<i class="bi bi-cloud-check-fill"></i> Tersimpan (' + (text || 'Baru saja') + ')';
            if (lastSavedLabel) lastSavedLabel.textContent = 'Terakhir disimpan: ' + new Date().toLocaleTimeString('id-ID') + ' WIB';
        } else if (status === 'error') {
            saveIndicator.className = 'badge bg-dark border border-danger text-danger px-3 py-2 rounded-pill small d-flex align-items-center gap-1';
            saveIndicator.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Gagal simpan (tersimpan di lokal)';
        }
    }

    // Jadwalkan autosave 15 detik setelah user berhenti mengetik
    function scheduleAutosave() {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(function() {
            if (isDirty) {
                performAutosave();
            }
        }, 15000);
    }

    // Eksekusi Autosave Asinkron ke backend
    window.performAutosave = function() {
        if (!contentArea || !titleInput) return;
        const currentTitle = titleInput.value.trim();
        const currentContent = contentArea.value;
        const currentStatus = statusSelect ? statusSelect.value : 'draft';

        if (!currentTitle) return; // Jangan autosave jika judul dikosongkan

        setSaveStatus('saving');

        fetch('<?= base_url('chapter/autosave/' . $chapter['id']); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: currentTitle,
                content: currentContent,
                status: currentStatus
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                lastSavedContent = currentContent;
                lastSavedTitle = currentTitle;
                setSaveStatus('saved', data.data.saved_at);
                // Bersihkan backup lokal jika sudah aman di database
                localStorage.removeItem(LOCAL_STORAGE_KEY);
            } else {
                setSaveStatus('error');
            }
        })
        .catch(err => {
            setSaveStatus('error');
        });
    };

    // Autosave interval rutin tiap 45 detik HANYA JIKA ada perubahan (tidak membebani server)
    setInterval(function() {
        if (isDirty) {
            performAutosave();
        }
    }, 45000);

    // =========================================================================
    // 3. EMERGENCY LOCALSTORAGE BACKUP (Jangan Kehilangan Tulisan User)
    // =========================================================================
    function saveToLocalBackup() {
        try {
            const backupData = {
                title: titleInput ? titleInput.value : '',
                content: contentArea ? contentArea.value : '',
                timestamp: Date.now()
            };
            localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(backupData));
        } catch (e) {
            // Ignore quota errors
        }
    }

    // Periksa apakah ada draf lokal browser yang lebih panjang atau lebih baru dari server
    (function checkLocalBackupOnLoad() {
        try {
            const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
            if (!raw) return;
            const backup = JSON.parse(raw);
            const currentContentLen = contentArea ? contentArea.value.length : 0;

            // Jika draf lokal memiliki konten lebih panjang dan berbeda dari database
            if (backup && backup.content && backup.content.length > (currentContentLen + 20) && backup.content !== contentArea.value) {
                const alertEl = document.getElementById('localBackupAlert');
                if (alertEl) {
                    alertEl.classList.remove('d-none');
                    alertEl.classList.add('d-flex');
                }
            }
        } catch (e) {}
    })();

    window.restoreFromLocalBackup = function() {
        try {
            const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
            if (!raw) return;
            const backup = JSON.parse(raw);
            if (backup.content && contentArea) {
                contentArea.value = backup.content;
            }
            if (backup.title && titleInput && !titleInput.value) {
                titleInput.value = backup.title;
            }
            updateWritingStats();
            markAsDirty();
            dismissLocalBackup();
            setSaveStatus('saved', 'Draf dipulihkan');
        } catch (e) {}
    };

    window.dismissLocalBackup = function() {
        const alertEl = document.getElementById('localBackupAlert');
        if (alertEl) alertEl.classList.add('d-none');
        localStorage.removeItem(LOCAL_STORAGE_KEY);
    };

    // Bersihkan backup lokal saat form disubmit manual
    const form = document.getElementById('novelEditorForm');
    if (form) {
        form.addEventListener('submit', function() {
            localStorage.removeItem(LOCAL_STORAGE_KEY);
        });
    }

    // =========================================================================
    // 4. KEYBOARD SHORTCUTS & TEXT INSERTION HELPERS
    // =========================================================================
    // Shortcut Ctrl+S (Simpan Cepat)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            performAutosave();
        }
    });

    // Tab key indents in textarea
    if (contentArea) {
        contentArea.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = this.value.substring(0, start) + "\t" + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 1;
                markAsDirty();
            }
        });
    }

    // Insert punctuation / dialogue symbols
    window.insertSymbol = function(prefix, suffix = '') {
        if (!contentArea) return;
        contentArea.focus();
        const start = contentArea.selectionStart;
        const end = contentArea.selectionEnd;
        const selectedText = contentArea.value.substring(start, end);
        const replacement = prefix + selectedText + suffix;

        contentArea.setRangeText(replacement, start, end, 'end');
        if (!selectedText && suffix) {
            contentArea.selectionStart = contentArea.selectionEnd = start + prefix.length;
        }
        contentArea.dispatchEvent(new Event('input'));
    };

    // Font Style Toggle (Serif vs Sans)
    window.setFontStyle = function(type) {
        if (!contentArea) return;
        const btnSerif = document.getElementById('btnFontSerif');
        const btnSans = document.getElementById('btnFontSans');

        if (type === 'serif') {
            contentArea.classList.remove('font-sans-novel');
            contentArea.classList.add('font-serif-novel');
            if (btnSerif) btnSerif.classList.add('active');
            if (btnSans) btnSans.classList.remove('active');
        } else {
            contentArea.classList.remove('font-serif-novel');
            contentArea.classList.add('font-sans-novel');
            if (btnSans) btnSans.classList.add('active');
            if (btnSerif) btnSerif.classList.remove('active');
        }
    };

    // Font Size Toggle
    let currentFontSizeRem = 1.15;
    window.changeFontSize = function(delta) {
        if (!contentArea) return;
        currentFontSizeRem = Math.max(0.9, Math.min(1.5, currentFontSizeRem + (delta * 0.1)));
        contentArea.style.fontSize = currentFontSizeRem + 'rem';
    };

    // Zen Mode (Fullscreen Distraction-Free Focus)
    window.toggleZenMode = function() {
        document.body.classList.toggle('zen-active');
        const btnText = document.getElementById('zenBtnText');
        const isZen = document.body.classList.contains('zen-active');
        if (btnText) btnText.textContent = isZen ? 'Keluar' : 'Fokus';
    };

    // =========================================================================
    // 5. AI WRITING ASSISTANT INTEGRATION (6 FITUR UTAMA)
    // =========================================================================
    let currentAiGeneratedText = '';
    let isAiProcessing = false;
    const aiPromptInput = document.getElementById('aiCustomPrompt');
    const aiPromptCount = document.getElementById('aiPromptCount');
    const aiButtons = document.querySelectorAll('.ai-btn-action');

    if (aiPromptInput && aiPromptCount) {
        aiPromptInput.addEventListener('input', function() {
            aiPromptCount.textContent = this.value.length;
        });
    }

    window.setAiPrompt = function(text) {
        if (aiPromptInput) {
            aiPromptInput.value = text;
            if (aiPromptCount) aiPromptCount.textContent = text.length;
            aiPromptInput.focus();
        }
    };

    function setAiButtonsLoading(loading) {
        isAiProcessing = loading;
        aiButtons.forEach(btn => {
            btn.disabled = loading;
            if (loading) {
                btn.style.opacity = '0.6';
                btn.style.pointerEvents = 'none';
            } else {
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            }
        });
    }

    function showAiError(msg) {
        const errBox = document.getElementById('aiErrorBox');
        const errMsg = document.getElementById('aiErrorMsg');
        if (errBox && errMsg) {
            errMsg.textContent = msg || 'Terjadi kendala saat memproses permintaan AI.';
            errBox.classList.remove('d-none');
        }
    }

    function hideAiError() {
        const errBox = document.getElementById('aiErrorBox');
        if (errBox) errBox.classList.add('d-none');
    }

    window.triggerAi = function(type) {
        if (isAiProcessing) return; // Cegah pengiriman ganda

        hideAiError();

        const customPrompt = aiPromptInput ? aiPromptInput.value.trim() : '';
        const titleVal = titleInput ? titleInput.value.trim() : '';
        const contextVal = contentArea ? contentArea.value.slice(-800) : '';

        // Tentukan prompt kontekstual jika input khusus kosong
        const defaultPrompts = {
            'idea': 'Berikan 5 ide pengembangan cerita unik dan berbobot untuk novel ini.',
            'outline': 'Rancang outline babak dengan eskalasi konflik yang intens dan terstruktur.',
            'character': 'Buat profil karakter tokoh lengkap dengan motivasi dan konflik mendalam.',
            'continue': (contextVal ? 'Lanjutkan cerita berdasarkan potongan adegan terakhir naskah.' : 'Tuliskan adegan pembuka naskah bab yang memikat.'),
            'improve': (contextVal ? 'Perbaiki gaya bahasa, tata bahasa, dan keterbacaan naskah ini agar lebih bernilai sastra.' : 'Poles teks naskah dengan prinsip show, don\'t tell.'),
            'suggestions': (contextVal ? 'Berikan evaluasi dan masukan penyuntingan menyeluruh untuk adegan naskah ini.' : 'Berikan saran pengembangan konflik dan karakter untuk bab ini.')
        };

        const finalPrompt = customPrompt || defaultPrompts[type] || (titleVal ? ('Bantuan AI untuk bab: ' + titleVal) : 'Bantuan penulisan naskah novel');

        const loadingBox = document.getElementById('aiLoadingBox');
        const loadingMsg = document.getElementById('aiLoadingMsg');
        const resultContainer = document.getElementById('aiResultContainer');

        if (loadingBox) loadingBox.classList.remove('d-none');
        if (resultContainer) resultContainer.classList.add('d-none');

        const typeLabels = {
            'idea': 'Merancang 5 ide alur & plot twist...',
            'outline': 'Merancang struktur outline & eskalasi konflik...',
            'character': 'Merancang profil tokoh utuh & dialog...',
            'continue': 'Menganalisis naskah & merangkai kelanjutan cerita...',
            'improve': 'Memoles diksi, tata bahasa & show don\'t tell...',
            'polish': 'Memoles diksi, tata bahasa & show don\'t tell...',
            'suggestions': 'Mengevaluasi konflik, karakter, alur & adegan...'
        };
        if (loadingMsg) loadingMsg.textContent = typeLabels[type] || 'Menghubungi AI Assistant...';

        setAiButtonsLoading(true);

        fetch('<?= base_url('ai/generate'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                prompt: finalPrompt,
                context: contextVal,
                chapter_id: chapterId,
                type: type
            })
        })
        .then(res => res.json())
        .then(data => {
            if (loadingBox) loadingBox.classList.add('d-none');
            setAiButtonsLoading(false);

            if (data.success && data.data && data.data.response) {
                currentAiGeneratedText = data.data.response;
                displayAiResult(data.data.type || type, currentAiGeneratedText);
                addToHistoryList(data.data.type || type, currentAiGeneratedText, data.data.created_at || 'Baru saja');
            } else {
                showAiError(data.message || 'Gagal memproses permintaan AI. Silakan periksa instruksi atau coba lagi.');
            }
        })
        .catch(err => {
            if (loadingBox) loadingBox.classList.add('d-none');
            setAiButtonsLoading(false);
            showAiError('Terjadi kendala koneksi ke server AI. Naskah Anda tetap aman.');
        });
    };

    function displayAiResult(type, text) {
        const resultContainer = document.getElementById('aiResultContainer');
        const resultTextEl = document.getElementById('aiResultText');
        const typeTitleEl = document.getElementById('aiResultTypeTitle');

        const titleMap = {
            'idea': '<i class="bi bi-lightbulb-fill text-warning me-1"></i> Rekomendasi 5 Ide Cerita',
            'outline': '<i class="bi bi-diagram-3-fill text-primary me-1"></i> Rancangan Outline Babak',
            'character': '<i class="bi bi-person-heart text-danger me-1"></i> Profil Tokoh Lengkap & Dialog',
            'continue': '<i class="bi bi-magic text-info me-1"></i> Kelanjutan Naskah Cerita',
            'improve': '<i class="bi bi-brush-fill text-success me-1"></i> Peningkatan Diksi & Tata Bahasa',
            'polish': '<i class="bi bi-brush-fill text-success me-1"></i> Peningkatan Diksi & Tata Bahasa',
            'suggestions': '<i class="bi bi-chat-quote-fill text-warning-emphasis me-1"></i> Masukan & Evaluasi Adegan'
        };

        if (typeTitleEl) typeTitleEl.innerHTML = titleMap[type] || 'Hasil AI Assistant';
        if (resultTextEl) resultTextEl.innerHTML = text.replace(/\n/g, '<br>');
        if (resultContainer) resultContainer.classList.remove('d-none');
    }

    window.closeAiResult = function() {
        const resultContainer = document.getElementById('aiResultContainer');
        if (resultContainer) resultContainer.classList.add('d-none');
    };

    // Keamanan Naskah: Hasil AI hanya disisipkan jika penulis secara sadar mengklik tombol Sisipkan
    window.insertAiText = function() {
        if (!contentArea || !currentAiGeneratedText) return;
        contentArea.focus();

        const start = contentArea.selectionStart;
        const end = contentArea.selectionEnd;

        // Jika ada blok teks yang disorot user, timpa teks tersebut. Jika tidak, sisipkan di posisi kursor
        if (start !== end) {
            contentArea.setRangeText(currentAiGeneratedText, start, end, 'end');
        } else {
            const pos = start ?? contentArea.value.length;
            const before = contentArea.value.substring(0, pos);
            const after = contentArea.value.substring(pos);
            const needsPrefixNl = (before.length > 0 && !before.endsWith('\n\n') && !before.endsWith('\n'));
            const prefix = needsPrefixNl ? (before.endsWith('\n') ? '\n' : '\n\n') : '';
            const suffix = (after.length > 0 && !after.startsWith('\n')) ? '\n\n' : '';

            contentArea.value = before + prefix + currentAiGeneratedText + suffix + after;
            const newCursor = (before + prefix + currentAiGeneratedText).length;
            contentArea.selectionStart = contentArea.selectionEnd = newCursor;
        }

        updateWritingStats();
        markAsDirty();
        saveToLocalBackup();
        closeAiResult();
    };

    window.copyAiText = function() {
        if (!currentAiGeneratedText) return;
        navigator.clipboard.writeText(currentAiGeneratedText).then(function() {
            alert('Teks hasil AI berhasil disalin ke papan klip (clipboard)!');
        });
    };

    function addToHistoryList(type, text, timeStr) {
        const historyList = document.getElementById('aiSessionHistoryList');
        const noHistoryText = document.getElementById('noHistoryText');
        if (noHistoryText) noHistoryText.remove();

        const historyCount = document.getElementById('historyCount');
        if (historyCount) {
            const currentTotal = parseInt(historyCount.textContent || '0', 10);
            historyCount.textContent = (currentTotal + 1);
        }

        if (historyList) {
            const item = document.createElement('div');
            item.className = 'card bg-dark border-secondary-subtle p-2 rounded-3 text-start small mb-2';
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-purple-subtle text-purple-light" style="font-size: 0.65rem;">${type.toUpperCase()}</span>
                    <span class="text-muted" style="font-size: 0.65rem;">${timeStr}</span>
                </div>
                <p class="text-light mb-2 text-truncate" style="font-size: 0.75rem;">${text.replace(/\n/g, ' ')}</p>
                <div class="d-flex gap-1 justify-content-end">
                    <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" style="font-size: 0.68rem;" onclick="displayAiResult('${type}', ${JSON.stringify(text)})">
                        <i class="bi bi-eye me-1"></i> Tinjau
                    </button>
                    <button type="button" class="btn btn-xs btn-primary-gradient py-0 px-2" style="font-size: 0.68rem;" onclick="currentAiGeneratedText = ${JSON.stringify(text)}; insertAiText();">
                        <i class="bi bi-plus me-1"></i> Sisipkan
                    </button>
                </div>
            `;
            historyList.prepend(item);
        }
    }

    // Muat Riwayat AI dari Database Saat Halaman Dibuka
    (function loadInitialAiHistory() {
        fetch('<?= base_url('ai/history?chapter_id=' . $chapter['id']); ?>')
        .then(res => res.json())
        .then(data => {
            if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                const historyCount = document.getElementById('historyCount');
                if (historyCount) historyCount.textContent = data.data.length;

                const noHistoryText = document.getElementById('noHistoryText');
                if (noHistoryText) noHistoryText.remove();

                const historyList = document.getElementById('aiSessionHistoryList');
                if (historyList) {
                    historyList.innerHTML = '';
                    data.data.forEach(item => {
                        const card = document.createElement('div');
                        card.className = 'card bg-dark border-secondary-subtle p-2 rounded-3 text-start small mb-2';
                        const formattedTime = item.created_at ? item.created_at.split(' ')[1] || item.created_at : 'Tersimpan';
                        card.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-purple-subtle text-purple-light" style="font-size: 0.65rem;">RIWAYAT</span>
                                <span class="text-muted" style="font-size: 0.65rem;">${formattedTime}</span>
                            </div>
                            <p class="text-light mb-2 text-truncate" style="font-size: 0.75rem;">${item.prompt || item.response.substring(0, 50)}</p>
                            <div class="d-flex gap-1 justify-content-end">
                                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" style="font-size: 0.68rem;" onclick="displayAiResult('Riwayat', ${JSON.stringify(item.response)})">
                                    <i class="bi bi-eye me-1"></i> Tinjau
                                </button>
                                <button type="button" class="btn btn-xs btn-primary-gradient py-0 px-2" style="font-size: 0.68rem;" onclick="currentAiGeneratedText = ${JSON.stringify(item.response)}; insertAiText();">
                                    <i class="bi bi-plus me-1"></i> Sisipkan
                                </button>
                            </div>
                        `;
                        historyList.appendChild(card);
                    });
                }
            }
        })
        .catch(err => {
            // Abaikan kegagalan jaringan saat inisialisasi riwayat
        });
    })();
});
</script>
