<section class="py-5">
    <div class="container py-lg-3">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard'); ?>" class="text-muted text-decoration-none"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('novels'); ?>" class="text-muted text-decoration-none">Novel Saya</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= sanitize($novel['title']); ?></li>
            </ol>
        </nav>

        <!-- Novel Overview Banner -->
        <div class="card bg-card-dark rounded-4 p-4 p-lg-5 border border-secondary-subtle mb-4 shadow-xl">
            <div class="row g-4 align-items-center">
                <?php $coverUrl = novel_cover_url($novel['cover'] ?? null); ?>
                <div class="col-md-4 col-lg-3 text-center">
                    <div class="novel-cover-detail rounded-4 overflow-hidden border border-secondary-subtle p-3 d-flex flex-column align-items-center justify-content-center bg-dark position-relative" style="min-height: 240px;">
                        <?php if ($coverUrl): ?>
                            <img src="<?= $coverUrl; ?>" alt="<?= sanitize($novel['title']); ?>" class="w-100 h-100 object-fit-cover rounded-3">
                        <?php else: ?>
                            <i class="bi bi-book-half text-primary display-2 mb-2"></i>
                            <span class="badge bg-purple-glow text-white px-3 py-1 rounded-pill"><?= sanitize($novel['genre']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <?php if ($novel['status'] === 'published'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Status: Published
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-pencil-fill me-1"></i> Status: Draft (Rahasia)
                            </span>
                        <?php endif; ?>
                        <span class="badge bg-dark border border-secondary-subtle text-purple-light px-3 py-1 rounded-pill">
                            <i class="bi bi-tag-fill me-1"></i> <?= sanitize($novel['genre']); ?>
                        </span>
                        <span class="badge bg-dark border border-secondary-subtle text-info px-3 py-1 rounded-pill">
                            <i class="bi bi-file-earmark-text me-1"></i> <?= count($chapters); ?> Bab Naskah
                        </span>
                    </div>

                    <h1 class="display-6 fw-extrabold text-white mb-2"><?= sanitize($novel['title']); ?></h1>
                    
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3 text-muted small">
                        <span><i class="bi bi-person-fill text-primary me-1"></i> Penulis: <strong class="text-white"><?= sanitize($novel['author_name'] ?? $user['name']); ?></strong></span>
                        <span>•</span>
                        <span><i class="bi bi-calendar3 me-1"></i> Dibuat: <?= format_date($novel['created_at']); ?></span>
                        <span>•</span>
                        <span><i class="bi bi-clock-history me-1"></i> Diperbarui: <?= format_date($novel['updated_at']); ?></span>
                    </div>

                    <h6 class="text-white fw-bold mb-2">Sinopsis Naskah:</h6>
                    <p class="text-muted mb-4" style="line-height: 1.8;">
                        <?= nl2br(sanitize($novel['synopsis'])); ?>
                    </p>

                    <!-- Management Actions Toolbar -->
                    <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top border-dark-subtle">
                        <a href="<?= base_url('novels/' . $novel['id'] . '/edit'); ?>" class="btn btn-outline-warning rounded-pill px-3 btn-sm">
                            <i class="bi bi-pencil-square me-1"></i> Edit Data Novel
                        </a>

                        <a href="<?= base_url('chapter/index/' . $novel['id']); ?>" class="btn btn-primary-gradient rounded-pill px-3 btn-sm">
                            <i class="bi bi-list-nested me-1"></i> Kelola Bab
                        </a>

                        <!-- Tombol Publikasi / Kembalikan ke Draft -->
                        <?php if ($novel['status'] === 'published'): ?>
                            <form action="<?= base_url('novels/' . $novel['id'] . '/toggle_status'); ?>" method="POST" class="d-inline" onsubmit="return confirm('Kembalikan status novel ke Draft? Naskah novel ini beserta babnya akan disembunyikan dari katalog publik.');">
                                <?= csrf_field(); ?>
                                <button type="submit" class="btn btn-outline-secondary rounded-pill px-3 btn-sm" title="Kembalikan ke Draft">
                                    <i class="bi bi-eye-slash me-1"></i> Kembalikan ke Draft
                                </button>
                            </form>
                            <a href="<?= base_url('novel/detail/' . $novel['id']); ?>" class="btn btn-outline-light rounded-pill px-3 btn-sm" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Publik
                            </a>
                        <?php else: ?>
                            <form action="<?= base_url('novels/' . $novel['id'] . '/toggle_status'); ?>" method="POST" class="d-inline" onsubmit="return confirm('Publikasikan novel ini sekarang? Seluruh pengunjung publik akan dapat melihat novel ini dan membaca bab berstatus Published.');">
                                <?= csrf_field(); ?>
                                <button type="submit" class="btn btn-success rounded-pill px-3 btn-sm shadow-sm" title="Publikasikan Novel">
                                    <i class="bi bi-globe-americas me-1"></i> Publikasikan Novel
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Delete Button Trigger with Confirmation Dialog -->
                        <form action="<?= base_url('novels/' . $novel['id'] . '/delete'); ?>" method="POST" class="d-inline ms-auto" onsubmit="return confirm('Apakah Anda yakin ingin menghapus novel ini? Semua bab yang terkait juga dapat terhapus.');">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-3 btn-sm">
                                <i class="bi bi-trash me-1"></i> Hapus Novel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chapters Management Card -->
        <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4 pb-3 border-bottom border-dark-subtle">
                <div>
                    <h4 class="fw-bold text-white mb-0">
                        <i class="bi bi-list-nested text-primary me-2"></i> Daftar Bab Naskah
                    </h4>
                    <span class="text-muted small">Total <?= count($chapters); ?> bab tersimpan dalam proyek novel ini</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('chapter/create/' . $novel['id']); ?>" class="btn btn-primary-gradient btn-sm rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Bab
                    </a>
                </div>
            </div>

            <?php if (empty($chapters)): ?>
                <!-- EMPTY STATE -->
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-plus display-4 mb-2 d-block opacity-50"></i>
                    <h5 class="text-white fw-bold">Belum Ada Bab Naskah</h5>
                    <p class="small text-muted mb-3">Novel ini belum memiliki bab cerita. Mulai tuangkan kisahmu sekarang!</p>
                    <a href="<?= base_url('chapter/create/' . $novel['id']); ?>" class="btn btn-primary-gradient btn-sm rounded-pill px-4 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Tambah Bab Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom border-dark-subtle">
                                <th style="width: 140px;">Urutan Bab</th>
                                <th>Judul Bab</th>
                                <th style="width: 130px;">Status</th>
                                <th style="width: 140px;">Tanggal Dibuat</th>
                                <th class="text-end" style="min-width: 250px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chapters as $index => $ch): ?>
                                <tr>
                                    <td>
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <span class="badge bg-dark border border-secondary-subtle text-purple-light px-3 py-2 rounded-pill fw-bold">
                                                Bab <?= (int)$ch['chapter_order']; ?>
                                            </span>
                                            <div class="btn-group-vertical btn-group-sm">
                                                <a href="<?= base_url('chapter/move_up/' . $ch['id']); ?>" class="btn btn-xs btn-outline-secondary py-0 px-1 <?= ($index === 0) ? 'disabled' : ''; ?>" title="Naikkan Urutan">
                                                    <i class="bi bi-chevron-up" style="font-size: 0.75rem;"></i>
                                                </a>
                                                <a href="<?= base_url('chapter/move_down/' . $ch['id']); ?>" class="btn btn-xs btn-outline-secondary py-0 px-1 <?= ($index === count($chapters) - 1) ? 'disabled' : ''; ?>" title="Turunkan Urutan">
                                                    <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('chapter/edit/' . $ch['id']); ?>" class="text-white fw-semibold text-decoration-none hover-primary">
                                            <?= sanitize($ch['title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('chapter/toggle_status/' . $ch['id']); ?>" class="text-decoration-none" title="Klik untuk mengubah status draft/published">
                                            <?php if ($ch['status'] === 'published'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                    <i class="bi bi-check-circle me-1"></i> Published
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">
                                                    <i class="bi bi-pencil me-1"></i> Draft
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="text-muted small"><?= format_date($ch['created_at']); ?></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- Tombol Tulis -->
                                            <a href="<?= base_url('chapter/edit/' . $ch['id']); ?>" class="btn btn-xs btn-primary-gradient rounded-pill px-3 py-1 small" title="Tulis & Kembangkan Naskah Bab">
                                                <i class="bi bi-pencil-fill me-1"></i> Tulis
                                            </a>

                                            <!-- Tombol Edit -->
                                            <a href="<?= base_url('chapter/edit/' . $ch['id']); ?>" class="btn btn-xs btn-outline-warning rounded-pill px-2 py-1 small" title="Sunting Bab">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>

                                            <!-- Tombol Baca -->
                                            <a href="<?= base_url('chapter/read/' . $ch['id']); ?>" class="btn btn-xs btn-outline-info rounded-pill px-2 py-1 small" target="_blank" title="Tampilan Baca">
                                                <i class="bi bi-eye"></i> Baca
                                            </a>

                                            <!-- Tombol Delete -->
                                            <form action="<?= base_url('chapter/delete/' . $ch['id']); ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Bab #<?= (int)$ch['chapter_order']; ?> \'<?= addslashes(sanitize($ch['title'])); ?>\'? Urutan bab lain akan disesuaikan secara otomatis.');">
                                                <?= csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small" title="Hapus Bab">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
