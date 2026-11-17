<section class="py-4 py-lg-5">
    <div class="container" style="max-width: 820px;">
        <!-- Breadcrumbs Navigation -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= base_url(); ?>" class="text-muted text-decoration-none"><i class="bi bi-house-door"></i> Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('novels'); ?>" class="text-muted text-decoration-none">Jelajah Novel</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('novels/' . $chapter['novel_id']); ?>" class="text-muted text-decoration-none"><?= sanitize($chapter['novel_title'] ?? ($novel['title'] ?? 'Novel')); ?></a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Bab <?= $chapter['chapter_order']; ?></li>
            </ol>
        </nav>

        <?php 
        $currentUser = Session::user(); 
        $isAuthor = ($currentUser && (int)$currentUser['id'] === (int)($chapter['author_id'] ?? ($novel['user_id'] ?? 0)));
        ?>
        <?php if ($isAuthor && ($chapter['status'] === 'draft' || ($novel['status'] ?? '') === 'draft' || ($chapter['novel_status'] ?? '') === 'draft')): ?>
            <div class="alert alert-warning d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 rounded-4 mb-4 shadow-sm border border-warning-subtle">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-eye-slash-fill fs-5 text-warning"></i>
                    <div>
                        <strong>Mode Pratinjau Penulis:</strong> Naskah atau bab ini berstatus <em>Draft</em> dan tidak terlihat oleh publik.
                    </div>
                </div>
                <a href="<?= base_url('chapter/edit/' . $chapter['id']); ?>" class="btn btn-warning btn-sm rounded-pill px-3 text-nowrap fw-semibold">
                    <i class="bi bi-pencil-square me-1"></i> Edit Bab
                </a>
            </div>
        <?php endif; ?>

        <!-- Chapter Header -->
        <div class="text-center py-4 border-bottom border-secondary-subtle mb-4">
            <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                <span class="badge bg-purple-subtle text-purple-light px-3 py-1 rounded-pill fw-semibold">
                    Bab <?= $chapter['chapter_order']; ?>
                </span>
                <span class="badge bg-dark border border-secondary text-muted small">
                    ~<?= ceil(str_word_count(strip_tags($chapter['content'])) / 180); ?> Menit Baca
                </span>
            </div>
            
            <h1 class="display-6 fw-bold text-white mb-2"><?= sanitize($chapter['title']); ?></h1>
            
            <div class="text-muted small d-flex flex-wrap justify-content-center align-items-center gap-2 gap-md-3">
                <span><i class="bi bi-person text-primary me-1"></i> <?= sanitize($chapter['author_name'] ?? ($novel['author_name'] ?? 'Penulis')); ?></span>
                <span>•</span>
                <span><i class="bi bi-book text-info me-1"></i> <?= sanitize($chapter['novel_title'] ?? ($novel['title'] ?? 'Novel')); ?></span>
                <span>•</span>
                <span><i class="bi bi-calendar3 me-1"></i> <?= format_date($chapter['created_at']); ?></span>
            </div>
        </div>

        <!-- Reading Customization Toolbar (Kenyamanan Membaca Teks Panjang) -->
        <div class="card bg-card-dark rounded-4 p-2 p-md-3 border border-secondary-subtle mb-4 shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                
                <!-- Font Family Switcher -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted small d-none d-sm-inline me-1">Gaya:</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-reader-font active rounded-pill px-3" data-font="sans" onclick="setReaderFont('sans')">
                        Sans Modern
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-reader-font rounded-pill px-3" data-font="serif" onclick="setReaderFont('serif')">
                        Serif Sastra
                    </button>
                </div>

                <!-- Font Size Controls -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted small d-none d-sm-inline me-1">Ukuran:</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2" onclick="changeReaderFontSize(-1)" title="Perkecil Ukuran Huruf">
                        <i class="bi bi-dash"></i> A-
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2" onclick="changeReaderFontSize(1)" title="Perbesar Ukuran Huruf">
                        <i class="bi bi-plus"></i> A+
                    </button>
                </div>

                <!-- Reading Themes -->
                <div class="d-flex align-items-center gap-1">
                    <span class="text-muted small d-none d-sm-inline me-1">Warna:</span>
                    <button type="button" class="btn btn-xs btn-reader-theme rounded-circle p-0 active border border-secondary" data-theme="dark" style="width: 26px; height: 26px; background-color: #111726;" onclick="setReaderTheme('dark')" title="Mode Gelap"></button>
                    <button type="button" class="btn btn-xs btn-reader-theme rounded-circle p-0 border border-secondary ms-1" data-theme="sepia" style="width: 26px; height: 26px; background-color: #1a1714;" onclick="setReaderTheme('sepia')" title="Mode Sepia Hangat"></button>
                    <button type="button" class="btn btn-xs btn-reader-theme rounded-circle p-0 border border-secondary ms-1" data-theme="oled" style="width: 26px; height: 26px; background-color: #000000;" onclick="setReaderTheme('oled')" title="Mode OLED Hitam"></button>
                </div>

            </div>
        </div>

        <!-- Chapter Top Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary-subtle">
            <?php if (!empty($prevChapter)): ?>
                <a href="<?= base_url('novels/' . $chapter['novel_id'] . '/read/' . $prevChapter['id']); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-chevron-left me-1"></i> Bab Sebelumnya
                </a>
            <?php else: ?>
                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 opacity-50" disabled>
                    <i class="bi bi-chevron-left me-1"></i> Bab Sebelumnya
                </button>
            <?php endif; ?>

            <!-- Dropdown Daftar Bab -->
            <div class="dropdown">
                <button class="btn btn-glass-secondary btn-sm rounded-pill px-3 dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-list-ol me-1 text-primary"></i> Daftar Bab
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow-lg border border-secondary-subtle rounded-3" style="max-height: 320px; overflow-y: auto;">
                    <?php foreach ($allChapters as $ch): ?>
                        <li>
                            <a class="dropdown-item py-2 <?= ($ch['id'] == $chapter['id']) ? 'active bg-primary' : ''; ?>" href="<?= base_url('novels/' . $chapter['novel_id'] . '/read/' . $ch['id']); ?>">
                                Bab <?= $ch['chapter_order']; ?>: <?= sanitize($ch['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if (!empty($nextChapter)): ?>
                <a href="<?= base_url('novels/' . $chapter['novel_id'] . '/read/' . $nextChapter['id']); ?>" class="btn btn-primary-gradient btn-sm rounded-pill px-3 fw-semibold shadow-sm">
                    Bab Selanjutnya <i class="bi bi-chevron-right ms-1"></i>
                </a>
            <?php else: ?>
                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 opacity-50" disabled>
                    Bab Selanjutnya <i class="bi bi-chevron-right ms-1"></i>
                </button>
            <?php endif; ?>
        </div>

        <!-- Chapter Content Body -->
        <article class="chapter-content-card rounded-4 p-4 p-md-5 border border-secondary-subtle mb-5 shadow-lg reader-theme-dark transition-all">
            <div class="chapter-body reader-font-sans" style="font-size: 1.18rem; line-height: 2.2;">
                <?= nl2br(sanitize($chapter['content'])); ?>
            </div>
        </article>

        <!-- Chapter Bottom Navigation -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 pt-3 border-top border-secondary-subtle">
            <?php if (!empty($prevChapter)): ?>
                <a href="<?= base_url('novels/' . $chapter['novel_id'] . '/read/' . $prevChapter['id']); ?>" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold w-100 w-sm-auto">
                    <i class="bi bi-chevron-left me-2"></i> Bab Sebelumnya
                </a>
            <?php else: ?>
                <div class="d-none d-sm-block"></div>
            <?php endif; ?>

            <a href="<?= base_url('novels/' . $chapter['novel_id']); ?>" class="btn btn-glass-secondary rounded-pill px-4 fw-semibold w-100 w-sm-auto">
                <i class="bi bi-journal-text me-2 text-info"></i> Daftar Isi Novel
            </a>

            <?php if (!empty($nextChapter)): ?>
                <a href="<?= base_url('novels/' . $chapter['novel_id'] . '/read/' . $nextChapter['id']); ?>" class="btn btn-primary-gradient rounded-pill px-4 fw-semibold shadow-sm w-100 w-sm-auto">
                    Bab Selanjutnya <i class="bi bi-chevron-right ms-2"></i>
                </a>
            <?php else: ?>
                <div class="d-none d-sm-block"></div>
            <?php endif; ?>
        </div>
    </div>
</section>
