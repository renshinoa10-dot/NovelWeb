<section class="py-5">
    <div class="container py-lg-3">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url(); ?>" class="text-muted text-decoration-none"><i class="bi bi-house-door"></i> Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('novel'); ?>" class="text-muted text-decoration-none">Jelajah Novel</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= sanitize($novel['title']); ?></li>
            </ol>
        </nav>

        <!-- Novel Hero Detail Card -->
        <div class="card bg-card-dark rounded-4 p-4 p-lg-5 border border-secondary-subtle mb-5">
            <div class="row g-4 align-items-center">
                <?php $coverUrl = novel_cover_url($novel['cover'] ?? null); ?>
                <div class="col-md-4 col-lg-3 text-center">
                    <?php if ($coverUrl): ?>
                        <div class="novel-cover-detail rounded-4 overflow-hidden border border-secondary-subtle p-2 d-flex flex-column align-items-center justify-content-center bg-dark" style="min-height: 280px; max-height: 380px;">
                            <img src="<?= $coverUrl; ?>" alt="<?= sanitize($novel['title']); ?>" class="w-100 h-100 object-fit-cover rounded-3 shadow">
                        </div>
                    <?php else: ?>
                        <div class="novel-cover-detail rounded-4 overflow-hidden border border-secondary-subtle p-5 d-flex flex-column align-items-center justify-content-center bg-dark" style="min-height: 280px;">
                            <i class="bi bi-book-half text-primary display-2 mb-3"></i>
                            <span class="badge bg-purple-glow text-white px-3 py-1 rounded-pill"><?= sanitize($novel['genre']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                            <i class="bi bi-check-circle me-1"></i> <?= ucfirst($novel['status']); ?>
                        </span>
                        <span class="badge bg-dark text-muted border border-secondary-subtle px-3 py-1 rounded-pill">
                            <i class="bi bi-journal-text me-1"></i> <?= $novel['total_chapters']; ?> Bab
                        </span>
                        <span class="badge bg-dark border border-secondary-subtle text-purple-light px-3 py-1 rounded-pill">
                            <i class="bi bi-tag-fill me-1"></i> <?= sanitize($novel['genre']); ?>
                        </span>
                    </div>

                    <h1 class="display-6 fw-bold text-white mb-3"><?= sanitize($novel['title']); ?></h1>

                    <div class="d-flex align-items-center gap-3 mb-4 text-muted small">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-circle text-primary fs-5"></i>
                            <span class="text-white fw-medium"><?= sanitize($novel['author_name']); ?></span>
                        </div>
                        <span>•</span>
                        <span>Dipublikasikan: <?= format_date($novel['created_at']); ?></span>
                    </div>

                    <h6 class="text-white fw-bold mb-2">Sinopsis Cerita:</h6>
                    <p class="text-muted mb-4" style="line-height: 1.8;">
                        <?= nl2br(sanitize($novel['synopsis'])); ?>
                    </p>

                    <?php if (!empty($chapters)): ?>
                        <a href="<?= base_url('novels/' . $novel['id'] . '/read/' . $chapters[0]['id']); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow">
                            <i class="bi bi-play-circle-fill"></i> Mulai Baca (Bab 1)
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Daftar Bab (Table of Contents) -->
        <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-dark-subtle">
                <div>
                    <h4 class="fw-bold text-white mb-1"><i class="bi bi-list-ol text-primary me-2"></i> Daftar Bab Naskah</h4>
                    <span class="text-muted small">Total <?= count($chapters); ?> bab tersedia untuk dibaca</span>
                </div>
            </div>

            <?php if (empty($chapters)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-text fs-1 mb-2 d-block"></i>
                    <p class="mb-0">Belum ada bab yang dipublikasikan untuk novel ini.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($chapters as $chapter): ?>
                        <a href="<?= base_url('novels/' . $novel['id'] . '/read/' . $chapter['id']); ?>" class="list-group-item list-group-item-action bg-transparent text-white d-flex justify-content-between align-items-center py-3 px-3 rounded-3 border-0 border-bottom border-dark-subtle hover-bg-dark">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-dark border border-secondary-subtle text-purple-light rounded-pill px-3 py-2">
                                    Bab <?= $chapter['chapter_order']; ?>
                                </span>
                                <span class="fw-semibold"><?= sanitize($chapter['title']); ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span><?= format_date($chapter['created_at']); ?></span>
                                <i class="bi bi-chevron-right text-primary"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
