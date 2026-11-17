<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden pt-5 pb-5">
    <!-- Background Glow Effects -->
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>

    <div class="container position-relative py-lg-5">
        <div class="row align-items-center g-5">
            <!-- Left Hero Content -->
            <div class="col-lg-6">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-purple-subtle border border-purple-subtle mb-4">
                    <span class="badge bg-purple-glow text-white rounded-pill"><i class="bi bi-stars"></i> New</span>
                    <span class="text-purple-light small fw-medium">AI Writing Assistant v1.0 Siap Digunakan</span>
                </div>

                <h1 class="display-4 fw-extrabold text-white hero-headline mb-3">
                    Ubah Imajinasimu Jadi <span class="text-gradient">Mahakarya Novel</span>
                </h1>

                <p class="lead text-muted hero-subtext mb-4">
                    karyaNovel adalah sistem penulisan & penerbitan novel terpadu yang didukung <strong>AI Writing Assistant</strong>. Kembangkan karakter, susun alur bab, dan terbitkan ceritamu ke ribuan pembaca tanpa hambatan <em>writer's block</em>.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?= base_url('auth/register'); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-4 shadow-lg d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square"></i> Mulai Menulis Gratis
                    </a>
                    <a href="#explore" class="btn btn-glass-secondary btn-lg rounded-pill px-4 d-flex align-items-center gap-2">
                        <i class="bi bi-compass"></i> Jelajah Cerita
                    </a>
                </div>

                <!-- Trust badges -->
                <div class="d-flex align-items-center gap-4 pt-3 text-muted small border-top border-dark-subtle">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Editor Responsif</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>AI Assistant Bahasa Indonesia</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Draft & Publikasi Bebas</span>
                    </div>
                </div>
            </div>

            <!-- Right Hero Visual: AI Assistant Interactive Mockup -->
            <div class="col-lg-6">
                <div class="card-hero-mockup shadow-2xl rounded-4 p-4 border border-secondary-subtle position-relative">
                    <!-- Window Controls -->
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom border-secondary-subtle">
                        <div class="d-flex gap-2">
                            <span class="mockup-dot bg-danger"></span>
                            <span class="mockup-dot bg-warning"></span>
                            <span class="mockup-dot bg-success"></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                            <span class="fw-medium text-white-50">Bab 12 - Menembus Gerbang Langit.txt</span>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Tersimpan</span>
                    </div>

                    <!-- Editor Content Preview -->
                    <div class="mockup-editor-body mb-3">
                        <p class="text-white-50 mb-2 font-monospace small" style="line-height: 1.7;">
                            Kabut tebal perlahan membelah saat Bayu melangkah mendekati bibir tebing. Kilatan petir merah menyambar di kejauhan, memantulkan bayangan pedang pusaka yang terhunus di tangan kanannya...
                        </p>
                    </div>

                    <!-- AI Assistant floating prompt response card -->
                    <div class="ai-suggestion-box rounded-3 p-3 border border-purple-glow">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ai-sparkle-icon"><i class="bi bi-stars"></i></span>
                                <span class="fw-bold text-white small">AI Writing Assistant</span>
                                <span class="badge bg-purple-subtle text-purple-light small">Lanjutkan Adegan</span>
                            </div>
                            <button class="btn btn-sm btn-outline-purple py-0 px-2 rounded-pill small" id="demoAiBtn" onclick="simulateAiTyping()">
                                <i class="bi bi-arrow-repeat"></i> Regenerasi
                            </button>
                        </div>
                        <div class="ai-typing-text small text-light" id="aiDemoText">
                            "Ia tahu pertarungan ini bukan lagi tentang membalaskan dendam perguruan, melainkan mencegah gerbang dimensi langit runtuh sebelum fajar menyingsing."
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top border-purple-subtle">
                            <button class="btn btn-xs btn-outline-light rounded-pill px-2 py-1 small"><i class="bi bi-x"></i> Tolak</button>
                            <button class="btn btn-xs btn-primary-gradient rounded-pill px-3 py-1 small"><i class="bi bi-check2"></i> Sisipkan ke Bab</button>
                        </div>
                    </div>

                    <!-- Editor Footer Tools -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 text-muted small">
                        <div class="d-flex gap-3">
                            <span><i class="bi bi-fonts me-1"></i> 1.420 Kata</span>
                            <span><i class="bi bi-clock me-1"></i> ~5 mnt baca</span>
                        </div>
                        <span class="text-purple-light"><i class="bi bi-cpu me-1"></i> Model: karyaNovel AI-Gen</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar Section -->
<section class="py-4 bg-card-dark border-top border-bottom border-dark-subtle">
    <div class="container">
        <div class="row g-4 text-center">
            <?php foreach ($stats as $stat): ?>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <div class="stat-icon-wrapper text-primary">
                            <i class="bi <?= $stat['icon']; ?> fs-3"></i>
                        </div>
                        <div class="text-start">
                            <h3 class="fw-extrabold text-white mb-0"><?= $stat['value']; ?></h3>
                            <span class="text-muted small"><?= $stat['label']; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- AI Features Highlight Section -->
<section class="py-5 position-relative" id="ai-features">
    <div class="container py-lg-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-purple-glow text-white px-3 py-2 rounded-pill mb-2">
                <i class="bi bi-stars me-1"></i> Fitur AI Unggulan
            </span>
            <h2 class="display-6 fw-bold text-white mb-3">
                Kreativitas Anda, Diperkuat Kecerdasan Buatan
            </h2>
            <p class="text-muted">
                AI di karyaNovel dirancang bukan untuk menggantikan penulis, melainkan menjadi asisten setia yang siap memantik inspirasi kapan pun Anda membutuhkannya.
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($aiFeatures as $feature): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-feature h-100 p-4 rounded-4 border border-secondary-subtle">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="feature-icon-box text-primary">
                                <i class="bi <?= $feature['icon']; ?>"></i>
                            </div>
                            <span class="badge bg-dark border border-secondary-subtle text-purple-light small"><?= $feature['badge']; ?></span>
                        </div>
                        <h5 class="fw-bold text-white mb-2"><?= $feature['title']; ?></h5>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            <?= $feature['description']; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured / Popular Novels Section -->
<section class="py-5 bg-dark-deep" id="explore">
    <div class="container py-lg-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
            <div>
                <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-1 mb-2">Pilihan Editor</span>
                <h2 class="h2 fw-bold text-white mb-0">Novel Unggulan & Terpopuler</h2>
            </div>
            <a href="<?= base_url('novel'); ?>" class="btn btn-outline-light rounded-pill px-4 btn-sm">
                Lihat Semua Novel <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredNovels as $novel): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-novel h-100 rounded-4 overflow-hidden border border-secondary-subtle">
                        <!-- Cover Container -->
                        <div class="novel-cover-wrapper position-relative">
                            <div class="novel-cover-placeholder d-flex flex-column align-items-center justify-content-center p-4 text-center">
                                <i class="bi bi-book text-primary opacity-50 display-3 mb-2"></i>
                                <span class="badge bg-dark bg-opacity-75 text-white border border-secondary mb-1"><?= $novel['genre'] ?? 'Fiksi'; ?></span>
                                <h5 class="fw-bold text-white px-2 mb-0"><?= sanitize($novel['title']); ?></h5>
                            </div>
                            <div class="novel-status-badge">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> <?= ucfirst($novel['status']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold text-white mb-2 line-clamp-1">
                                <?= sanitize($novel['title']); ?>
                            </h5>
                            <p class="card-text text-muted small flex-grow-1 line-clamp-3 mb-3">
                                <?= sanitize($novel['synopsis']); ?>
                            </p>

                            <!-- Author & Stats -->
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top border-dark-subtle mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="author-avatar-sm">
                                        <i class="bi bi-person-circle text-primary fs-5"></i>
                                    </div>
                                    <span class="text-white-50 small fw-medium"><?= sanitize($novel['author_name'] ?? 'Penulis karyaNovel'); ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <span><i class="bi bi-journal-text text-primary me-1"></i> <?= $novel['total_chapters'] ?? 0; ?> Bab</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Action -->
                        <div class="card-footer bg-dark-deep p-3 border-top border-dark-subtle d-flex justify-content-between align-items-center">
                            <span class="text-warning small"><i class="bi bi-star-fill me-1"></i> <?= $novel['rating'] ?? '4.8'; ?></span>
                            <a href="<?= base_url('novel/detail/' . ($novel['id'] ?? 1)); ?>" class="btn btn-sm btn-primary-gradient rounded-pill px-3">
                                <i class="bi bi-book-half me-1"></i> Baca Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Genre & Categories Section -->
<section class="py-5" id="genres">
    <div class="container py-lg-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <h2 class="h2 fw-bold text-white mb-2">Eksplorasi Berdasarkan Kategori</h2>
            <p class="text-muted">Temukan cerita favoritmu dari berbagai genre novel yang menarik.</p>
        </div>

        <div class="row g-3">
            <?php foreach ($genres as $genre): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= base_url('novel/genre/' . urlencode(strtolower($genre['name']))); ?>" class="genre-card text-decoration-none d-block p-3 rounded-4 border border-secondary-subtle text-center h-100">
                        <div class="genre-icon-circle mx-auto mb-2" style="background-color: <?= $genre['color']; ?>22; color: <?= $genre['color']; ?>;">
                            <i class="bi <?= $genre['icon']; ?> fs-4"></i>
                        </div>
                        <h6 class="fw-bold text-white mb-1"><?= $genre['name']; ?></h6>
                        <span class="text-muted small"><?= $genre['count']; ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How it Works for Writers -->
<section class="py-5 bg-card-dark border-top border-bottom border-dark-subtle">
    <div class="container py-lg-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-purple-subtle text-purple-light rounded-pill px-3 py-1 mb-2">Alur Penulisan</span>
            <h2 class="h2 fw-bold text-white mb-2">Bagaimana Cara Menulis di karyaNovel?</h2>
            <p class="text-muted">Empat langkah mudah untuk mengubah ide dalam pikiran Anda menjadi novel lengkap.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="step-card p-4 rounded-4 text-center h-100 border border-secondary-subtle">
                    <div class="step-number text-gradient mb-3">01</div>
                    <h5 class="fw-bold text-white mb-2">Buat Judul & Sinopsis</h5>
                    <p class="text-muted small mb-0">Tentukan konsep novel, pilih genre, dan unggah cover untuk proyek barumu.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="step-card p-4 rounded-4 text-center h-100 border border-secondary-subtle">
                    <div class="step-number text-gradient mb-3">02</div>
                    <h5 class="fw-bold text-white mb-2">Tulis Bab dengan AI</h5>
                    <p class="text-muted small mb-0">Tulis isi cerita di editor modern. Gunakan AI Assistant saat butuh ide kelanjutan adegan.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="step-card p-4 rounded-4 text-center h-100 border border-secondary-subtle">
                    <div class="step-number text-gradient mb-3">03</div>
                    <h5 class="fw-bold text-white mb-2">Simpan Draft / Atur Urutan</h5>
                    <p class="text-muted small mb-0">Simpan bab sebagai draft rahasia atau rapikan nomor urut bab sesuka hati.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="step-card p-4 rounded-4 text-center h-100 border border-secondary-subtle">
                    <div class="step-number text-gradient mb-3">04</div>
                    <h5 class="fw-bold text-white mb-2">Publikasikan ke Pembaca</h5>
                    <p class="text-muted small mb-0">Rilis karyamu ke halaman publik dan dapatkan apresiasi dari ribuan pembaca.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 position-relative">
    <div class="container py-lg-4">
        <div class="cta-banner rounded-5 p-5 position-relative overflow-hidden text-center text-lg-start border border-purple-glow">
            <div class="cta-glow"></div>
            <div class="row align-items-center position-relative">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2 class="display-6 fw-extrabold text-white mb-3">
                        Siap Menjadi Penulis Novel Sukses Berikutnya?
                    </h2>
                    <p class="lead text-light opacity-75 mb-0">
                        Daftar akun gratis sekarang dan rasakan pengalaman menulis novel tercepat dan termudah bersama AI Writing Assistant.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= base_url('auth/register'); ?>" class="btn btn-light btn-lg rounded-pill px-5 fw-bold shadow">
                        Daftar Sekarang <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
