<section class="py-4 py-lg-5">
    <div class="container">
        <!-- Top Welcome Greeting & Quick Action Banner -->
        <div class="card bg-card-dark rounded-4 p-4 p-md-5 border border-purple-subtle mb-4 shadow-xl position-relative overflow-hidden">
            <div class="hero-glow-1"></div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 position-relative">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-purple-glow text-white px-3 py-1 rounded-pill">
                            <i class="bi bi-feather me-1"></i> Penulis karyaNovel
                        </span>
                        <span class="text-muted small">ID Penulis: #<?= (int)$user['id']; ?></span>
                    </div>
                    <h1 class="display-6 fw-extrabold text-white mb-1">
                        Selamat Datang, <span class="text-gradient"><?= sanitize($user['name']); ?></span>!
                    </h1>
                    <p class="text-muted mb-0">
                        Kelola proyek tulisanmu, kembangkan bab cerita, dan manfaatkan asisten AI untuk menyelesaikan novelmu.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= base_url('novels/create'); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow">
                        <i class="bi bi-plus-circle-fill"></i> Buat Novel Baru
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sidebar Navigation Menu -->
            <div class="col-lg-3">
                <div class="card bg-card-dark rounded-4 p-3 border border-secondary-subtle sticky-lg-top" style="top: 100px;">
                    <div class="p-3 border-bottom border-dark-subtle d-flex align-items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']); ?>&background=6366f1&color=fff" 
                             alt="<?= sanitize($user['name']); ?>" 
                             class="rounded-circle border border-primary" 
                             width="48" height="48">
                        <div class="overflow-hidden">
                            <h6 class="text-white fw-bold mb-0 text-truncate"><?= sanitize($user['name']); ?></h6>
                            <span class="text-muted small text-truncate d-block"><?= sanitize($user['email']); ?></span>
                        </div>
                    </div>

                    <div class="list-group list-group-flush pt-2">
                        <a href="<?= base_url('dashboard'); ?>" class="list-group-item list-group-item-action bg-transparent text-white active-nav-link d-flex align-items-center gap-3 py-2 px-3 rounded-3 border-0 mb-1">
                            <i class="bi bi-grid-1x2-fill text-primary"></i> Ringkasan Dashboard
                        </a>
                        <a href="<?= base_url('novels'); ?>" class="list-group-item list-group-item-action bg-transparent text-muted hover-light d-flex align-items-center gap-3 py-2 px-3 rounded-3 border-0 mb-1">
                            <i class="bi bi-journal-bookmark-fill text-info"></i> Novel Saya
                            <span class="badge bg-dark border border-secondary-subtle text-muted ms-auto" id="stat-sidebar-novels"><?= $stats['total_novels']; ?></span>
                        </a>
                        <a href="<?= base_url('novels/create'); ?>" class="list-group-item list-group-item-action bg-transparent text-muted hover-light d-flex align-items-center gap-3 py-2 px-3 rounded-3 border-0 mb-1">
                            <i class="bi bi-plus-circle text-success"></i> Tulis Novel Baru
                        </a>
                        <a href="#ai-history" class="list-group-item list-group-item-action bg-transparent text-muted hover-light d-flex align-items-center gap-3 py-2 px-3 rounded-3 border-0 mb-1">
                            <i class="bi bi-stars text-purple-light"></i> Riwayat AI Writing
                            <span class="badge bg-dark border border-secondary-subtle text-muted ms-auto"><?= count($recentAiLogs); ?></span>
                        </a>
                        <a href="<?= base_url('novel'); ?>" class="list-group-item list-group-item-action bg-transparent text-muted hover-light d-flex align-items-center gap-3 py-2 px-3 rounded-3 border-0 mb-1" target="_blank">
                            <i class="bi bi-compass text-warning"></i> Jelajah Novel Publik
                        </a>
                        <a href="<?= base_url('auth/logout'); ?>" class="list-group-item list-group-item-action bg-transparent text-danger hover-danger d-flex align-items-center gap-3 py-2 px-3 rounded-3 border-0 mt-3 border-top border-dark-subtle">
                            <i class="bi bi-box-arrow-right"></i> Keluar Akun
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Body -->
            <div class="col-lg-9">
                <!-- 4 Dashboard Statistic Cards -->
                <div class="row g-3 mb-4">
                    <!-- Stat 1: Total Novel -->
                    <div class="col-6 col-md-3">
                        <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Total Novel</span>
                                <div class="stat-icon-sm rounded-3 p-2 bg-primary-subtle text-primary">
                                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                                </div>
                            </div>
                            <h2 class="display-6 fw-bold text-white mb-0" id="stat-author-total-novels"><?= $stats['total_novels']; ?></h2>
                            <span class="text-muted small mt-1 d-block">Proyek Naskah</span>
                        </div>
                    </div>

                    <!-- Stat 2: Published -->
                    <div class="col-6 col-md-3">
                        <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Published</span>
                                <div class="stat-icon-sm rounded-3 p-2 bg-success-subtle text-success">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </div>
                            </div>
                            <h2 class="display-6 fw-bold text-success mb-0" id="stat-author-published-novels"><?= $stats['published_novels']; ?></h2>
                            <span class="text-muted small mt-1 d-block">Dapat Dibaca Publik</span>
                        </div>
                    </div>

                    <!-- Stat 3: Draft -->
                    <div class="col-6 col-md-3">
                        <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Draft</span>
                                <div class="stat-icon-sm rounded-3 p-2 bg-warning-subtle text-warning">
                                    <i class="bi bi-pencil-fill fs-5"></i>
                                </div>
                            </div>
                            <h2 class="display-6 fw-bold text-warning mb-0" id="stat-author-draft-novels"><?= $stats['draft_novels']; ?></h2>
                            <span class="text-muted small mt-1 d-block">Dalam Penulisan</span>
                        </div>
                    </div>

                    <!-- Stat 4: Total Chapters -->
                    <div class="col-6 col-md-3">
                        <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-medium">Jumlah Bab</span>
                                <div class="stat-icon-sm rounded-3 p-2 bg-info-subtle text-info">
                                    <i class="bi bi-file-earmark-text-fill fs-5"></i>
                                </div>
                            </div>
                            <h2 class="display-6 fw-bold text-white mb-0" id="stat-author-total-chapters"><?= $stats['total_chapters']; ?></h2>
                            <span class="text-muted small mt-1 d-block">Bab Tersimpan</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Bar -->
                <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle mb-4">
                    <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-lightning-charge-fill text-warning"></i> Aksi Cepat Penulis
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="<?= base_url('novels/create'); ?>" class="quick-action-card d-block p-3 rounded-3 text-decoration-none border border-secondary-subtle h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-plus-circle fs-3 text-primary"></i>
                                    <div>
                                        <h6 class="text-white fw-semibold mb-0">Tulis Novel Baru</h6>
                                        <span class="text-muted small">Mulai judul naskah baru</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= base_url('#ai-features'); ?>" class="quick-action-card d-block p-3 rounded-3 text-decoration-none border border-secondary-subtle h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-stars fs-3 text-purple-light"></i>
                                    <div>
                                        <h6 class="text-white fw-semibold mb-0">AI Writing Assistant</h6>
                                        <span class="text-muted small">Cari ide & alur cerita</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= base_url('novel'); ?>" class="quick-action-card d-block p-3 rounded-3 text-decoration-none border border-secondary-subtle h-100" target="_blank">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-compass fs-3 text-info"></i>
                                    <div>
                                        <h6 class="text-white fw-semibold mb-0">Jelajahi Novel</h6>
                                        <span class="text-muted small">Lihat karya terbit</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Daftar Novel Terbaru Penulis -->
                <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4" id="my-novels">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4 pb-3 border-bottom border-dark-subtle">
                        <div>
                            <h4 class="fw-bold text-white mb-0">
                                <i class="bi bi-journal-text text-primary me-2"></i> Daftar Novel Saya
                            </h4>
                            <span class="text-muted small">Menampilkan seluruh naskah yang Anda miliki</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('novels'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                <i class="bi bi-grid me-1"></i> Kelola Semua
                            </a>
                            <a href="<?= base_url('novels/create'); ?>" class="btn btn-primary-gradient btn-sm rounded-pill px-3">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Novel
                            </a>
                        </div>
                    </div>

                    <?php if (empty($novels)): ?>
                        <!-- EMPTY STATE -->
                        <div class="text-center py-5 px-3">
                            <div class="empty-state-icon mx-auto mb-3 text-muted">
                                <i class="bi bi-journal-plus display-1 opacity-50"></i>
                            </div>
                            <h4 class="text-white fw-bold mb-2">Belum ada novel. Yuk mulai membuat karya pertamamu!</h4>
                            <p class="text-muted max-w-700 mx-auto mb-4" style="max-width: 500px;">
                                Mulailah petualangan menulismu sekarang! Tuangkan ide cerita pertamamu dan biarkan AI Writing Assistant membantumu mengembangkan alur naskah.
                            </p>
                            <a href="<?= base_url('novels/create'); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-5 shadow">
                                <i class="bi bi-plus-circle-fill me-2"></i> + Buat Novel Baru
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- DAFTAR NOVEL -->
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($novels as $novel): ?>
                                <?php $coverUrl = novel_cover_url($novel['cover'] ?? null); ?>
                                <div class="card bg-dark rounded-4 p-3 p-md-4 border border-secondary-subtle hover-card-novel transition-all shadow-sm">
                                    <div class="row align-items-center g-3">
                                        <!-- Thumbnail Cover -->
                                        <div class="col-auto">
                                            <div class="rounded-3 overflow-hidden bg-secondary d-flex align-items-center justify-content-center text-light shadow-sm" style="width: 54px; height: 76px;">
                                                <?php if ($coverUrl): ?>
                                                    <img src="<?= $coverUrl; ?>" alt="<?= sanitize($novel['title']); ?>" class="w-100 h-100 object-fit-cover">
                                                <?php else: ?>
                                                    <i class="bi bi-book fs-3 text-white-50"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                                <h5 class="fw-bold text-white mb-0">
                                                    <?= sanitize($novel['title']); ?>
                                                </h5>
                                                <?php if ($novel['status'] === 'published'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                                        <i class="bi bi-check-circle me-1"></i> Published
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1 small">
                                                        <i class="bi bi-pencil me-1"></i> Draft
                                                    </span>
                                                <?php endif; ?>
                                                <span class="badge bg-dark border border-secondary-subtle text-purple-light px-2 py-1 small">
                                                    <?= sanitize($novel['genre']); ?>
                                                </span>
                                            </div>
                                            <p class="text-muted small mb-2 line-clamp-2">
                                                <?= sanitize($novel['synopsis']); ?>
                                            </p>
                                            <div class="d-flex align-items-center gap-3 text-muted small">
                                                <span><i class="bi bi-file-earmark-text text-info me-1"></i> <?= $novel['total_chapters']; ?> Bab</span>
                                                <span>•</span>
                                                <span><i class="bi bi-clock-history me-1"></i> Diperbarui: <?= format_date($novel['updated_at']); ?></span>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-auto text-md-end mt-2 mt-md-0">
                                            <div class="d-inline-flex flex-wrap gap-2">
                                                <?php if ($novel['status'] === 'published'): ?>
                                                    <a href="<?= base_url('novels/' . $novel['id']); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3" title="Lihat Halaman Publik" target="_blank">
                                                        <i class="bi bi-eye me-1"></i> Lihat
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('novels/' . $novel['id']); ?>" class="btn btn-primary-gradient btn-sm rounded-pill px-3">
                                                    <i class="bi bi-folder2-open me-1"></i> Buka Naskah
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Aktivitas Terakhir (Bab Terbaru & AI Logs) -->
                <div class="row g-4" id="ai-history">
                    <!-- Bab Terbaru yang Dikerjakan -->
                    <div class="col-md-6">
                        <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle h-100">
                            <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history text-info"></i> Bab Terbaru
                            </h5>

                            <?php if (empty($recentChapters)): ?>
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-file-earmark-x fs-2 mb-2 d-block opacity-50"></i>
                                    Belum ada bab yang ditulis.
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentChapters as $rc): ?>
                                        <div class="list-group-item bg-transparent text-white px-0 py-2 border-0 border-bottom border-dark-subtle">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <span class="badge bg-dark text-purple-light border border-secondary-subtle small me-1">Bab <?= $rc['chapter_order']; ?></span>
                                                    <span class="fw-semibold small"><?= sanitize($rc['title']); ?></span>
                                                    <div class="text-muted small mt-1"><?= sanitize($rc['novel_title']); ?></div>
                                                </div>
                                                <span class="text-muted small" style="font-size: 0.75rem;"><?= format_date($rc['created_at']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Riwayat Interaksi AI Writing Assistant -->
                    <div class="col-md-6">
                        <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle h-100">
                            <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-stars text-purple-light"></i> Riwayat AI Writing
                            </h5>

                            <?php if (empty($recentAiLogs)): ?>
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-chat-left-dots fs-2 mb-2 d-block opacity-50"></i>
                                    Belum ada riwayat konsultasi AI.
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($recentAiLogs as $aiLog): ?>
                                        <div class="p-2 rounded-3 bg-dark border border-secondary-subtle small">
                                            <div class="d-flex justify-content-between align-items-center text-muted mb-1" style="font-size: 0.75rem;">
                                                <span class="badge bg-purple-subtle text-purple-light"><i class="bi bi-sparkles"></i> Prompt</span>
                                                <span><?= format_date($aiLog['created_at']); ?></span>
                                            </div>
                                            <div class="text-light fw-medium mb-1 line-clamp-1">
                                                "<?= sanitize($aiLog['prompt']); ?>"
                                            </div>
                                            <div class="text-muted fst-italic line-clamp-2" style="font-size: 0.8rem;">
                                                <?= sanitize($aiLog['response']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    function refreshAuthorStats() {
        fetch('<?= base_url("dashboard/stats_api"); ?>', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && data.stats) {
                const s = data.stats;
                const elTotal = document.getElementById('stat-author-total-novels');
                const elPub = document.getElementById('stat-author-published-novels');
                const elDraft = document.getElementById('stat-author-draft-novels');
                const elChap = document.getElementById('stat-author-total-chapters');
                const elSide = document.getElementById('stat-sidebar-novels');

                if (elTotal) elTotal.textContent = s.total_novels;
                if (elPub) elPub.textContent = s.published_novels;
                if (elDraft) elDraft.textContent = s.draft_novels;
                if (elChap) elChap.textContent = s.total_chapters;
                if (elSide) elSide.textContent = s.total_novels;
            }
        })
        .catch(err => console.debug('Live sync stats skipped:', err));
    }

    // Panggil saat navigasi pageshow (mencegah cache browser back/forward) dan window visibility change
    window.addEventListener('pageshow', refreshAuthorStats);
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            refreshAuthorStats();
        }
    });
})();
</script>
