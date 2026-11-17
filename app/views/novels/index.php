<section class="py-5">
    <div class="container py-lg-3">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard'); ?>" class="text-muted text-decoration-none"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Manajemen Novel</li>
            </ol>
        </nav>

        <!-- Header & Action Row -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="badge bg-purple-subtle text-purple-light px-3 py-1 rounded-pill mb-2">
                    <i class="bi bi-journal-bookmark me-1"></i> Ruang Kerja Penulis
                </span>
                <h1 class="display-6 fw-bold text-white mb-1">Daftar Novel Saya</h1>
                <p class="text-muted mb-0">Kelola seluruh proyek naskah novel, status draft, dan publikasi karyamu.</p>
            </div>
            <div>
                <a href="<?= base_url('novels/create'); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-4 shadow d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Buat Novel Baru
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="card bg-card-dark rounded-4 p-3 border border-secondary-subtle mb-4">
            <div class="row g-3 align-items-center justify-content-between">
                <!-- Status Filter Pills -->
                <div class="col-md-6">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('novels'); ?>" class="btn btn-sm <?= empty($currentStatus) ? 'btn-primary-gradient' : 'btn-glass-secondary'; ?> rounded-pill px-3">
                            Semua (<?= $stats['total_novels']; ?>)
                        </a>
                        <a href="<?= base_url('novels?status=published'); ?>" class="btn btn-sm <?= ($currentStatus === 'published') ? 'btn-primary-gradient' : 'btn-glass-secondary'; ?> rounded-pill px-3">
                            <i class="bi bi-check-circle me-1 text-success"></i> Published (<?= $stats['published_novels']; ?>)
                        </a>
                        <a href="<?= base_url('novels?status=draft'); ?>" class="btn btn-sm <?= ($currentStatus === 'draft') ? 'btn-primary-gradient' : 'btn-glass-secondary'; ?> rounded-pill px-3">
                            <i class="bi bi-pencil me-1 text-warning"></i> Draft (<?= $stats['draft_novels']; ?>)
                        </a>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="col-md-5 col-lg-4">
                    <form action="<?= base_url('novels'); ?>" method="GET">
                        <?php if (!empty($currentStatus)): ?>
                            <input type="hidden" name="status" value="<?= sanitize($currentStatus); ?>">
                        <?php endif; ?>
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" class="form-control bg-dark border-secondary-subtle text-white" placeholder="Cari judul novel..." value="<?= sanitize($searchQuery ?? ''); ?>">
                            <button class="btn btn-primary-gradient px-3" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Novel List Content -->
        <?php if (empty($novels)): ?>
            <!-- EMPTY STATE -->
            <div class="card bg-card-dark rounded-4 p-5 text-center border border-secondary-subtle my-4">
                <div class="empty-state-icon mx-auto mb-3 text-muted">
                    <i class="bi bi-journal-plus display-1 opacity-50"></i>
                </div>
                <?php if (!empty($searchQuery) || !empty($currentStatus)): ?>
                    <h4 class="text-white fw-bold mb-2">Tidak Ada Novel yang Ditemukan</h4>
                    <p class="text-muted max-w-700 mx-auto mb-4" style="max-width: 520px;">
                        Tidak ada novel yang cocok dengan filter atau kata kunci pencarian Anda. Coba reset filter pencarian.
                    </p>
                    <div>
                        <a href="<?= base_url('novels'); ?>" class="btn btn-outline-light rounded-pill px-4 btn-sm">Reset Filter</a>
                    </div>
                <?php else: ?>
                    <h4 class="text-white fw-bold mb-2">Belum ada novel. Yuk mulai membuat karya pertamamu!</h4>
                    <p class="text-muted max-w-700 mx-auto mb-4" style="max-width: 520px;">
                        Tuangkan ide imajinatifmu sekarang dan biarkan AI Writing Assistant membantumu menyusun naskah bab demi bab!
                    </p>
                    <div>
                        <a href="<?= base_url('novels/create'); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-5 shadow">
                            <i class="bi bi-plus-circle-fill me-2"></i> + Buat Novel Baru
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($novels as $novel): ?>
                    <?php $coverUrl = novel_cover_url($novel['cover'] ?? null); ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-novel h-100 rounded-4 overflow-hidden border border-secondary-subtle d-flex flex-column">
                            <!-- Cover Thumbnail -->
                            <div class="novel-cover-placeholder d-flex flex-column align-items-center justify-content-center p-3 text-center position-relative" style="height: 160px;">
                                <?php if ($coverUrl): ?>
                                    <img src="<?= $coverUrl; ?>" alt="<?= sanitize($novel['title']); ?>" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0 opacity-75">
                                <?php else: ?>
                                    <i class="bi bi-book-half text-primary opacity-50 display-4 mb-2"></i>
                                <?php endif; ?>
                                <span class="badge bg-dark bg-opacity-75 text-white border border-secondary position-relative mb-1">
                                    <?= sanitize($novel['genre']); ?>
                                </span>
                                <h5 class="fw-bold text-white px-2 mb-0 line-clamp-1 position-relative"><?= sanitize($novel['title']); ?></h5>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <?php if ($novel['status'] === 'published'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i> Published
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                            <i class="bi bi-pencil me-1"></i> Draft
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-muted small"><i class="bi bi-file-earmark-text text-info me-1"></i> <?= $novel['total_chapters']; ?> Bab</span>
                                </div>

                                <h5 class="card-title fw-bold text-white mb-2 line-clamp-1">
                                    <a href="<?= base_url('novels/' . $novel['id']); ?>" class="text-white text-decoration-none hover-primary">
                                        <?= sanitize($novel['title']); ?>
                                    </a>
                                </h5>

                                <p class="card-text text-muted small flex-grow-1 line-clamp-3 mb-3">
                                    <?= sanitize($novel['synopsis']); ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top border-dark-subtle mt-auto">
                                    <span><i class="bi bi-calendar3 me-1"></i> Dibuat: <?= format_date($novel['created_at']); ?></span>
                                    <span><i class="bi bi-clock-history me-1"></i> <?= format_date($novel['updated_at']); ?></span>
                                </div>
                            </div>

                            <!-- Card Action Footer -->
                            <div class="card-footer bg-dark-deep p-3 border-top border-dark-subtle d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <a href="<?= base_url('novels/' . $novel['id']); ?>" class="btn btn-sm btn-outline-light rounded-pill px-3" title="Buka Naskah & Bab">
                                        <i class="bi bi-folder2-open me-1"></i> Detail
                                    </a>
                                    <a href="<?= base_url('novels/' . $novel['id'] . '/edit'); ?>" class="btn btn-sm btn-outline-warning rounded-pill px-2" title="Sunting Data Novel">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <!-- Quick Toggle Status -->
                                    <form action="<?= base_url('novels/' . $novel['id'] . '/toggle_status'); ?>" method="POST" class="d-inline" onsubmit="return confirm('Ubah status publikasi novel \'<?= addslashes(sanitize($novel['title'])); ?>\'?');">
                                        <?= csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm <?= ($novel['status'] === 'published') ? 'btn-outline-success' : 'btn-outline-secondary'; ?> rounded-pill px-2" title="<?= ($novel['status'] === 'published') ? 'Klik untuk kembalikan ke Draft' : 'Klik untuk Publikasikan'; ?>">
                                            <i class="bi <?= ($novel['status'] === 'published') ? 'bi-globe-americas' : 'bi-eye-slash'; ?>"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Delete Form with Confirmation Dialog -->
                                <form action="<?= base_url('novels/' . $novel['id'] . '/delete'); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus novel ini? Semua bab yang terkait juga dapat terhapus.');">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Hapus Novel">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
