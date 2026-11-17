<section class="py-5">
    <div class="container py-lg-3">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard'); ?>" class="text-muted text-decoration-none"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('novels'); ?>" class="text-muted text-decoration-none">Novel Saya</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('novels/' . $novel['id']); ?>" class="text-muted text-decoration-none"><?= sanitize($novel['title']); ?></a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Manajemen Bab</li>
            </ol>
        </nav>

        <!-- Header Row -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="badge bg-purple-subtle text-purple-light px-3 py-1 rounded-pill mb-2">
                    <i class="bi bi-book me-1"></i> <?= sanitize($novel['title']); ?>
                </span>
                <h1 class="display-6 fw-bold text-white mb-1">Daftar Bab & Pengurutan Naskah</h1>
                <p class="text-muted mb-0">Kelola urutan bab, status naskah, dan lanjutkan penulisan setiap episode cerita.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= base_url('novels/' . $novel['id']); ?>" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Detail Naskah
                </a>
                <a href="<?= base_url('chapter/create/' . $novel['id']); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-4 shadow d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Bab
                </a>
            </div>
        </div>

        <!-- Chapters List Card -->
        <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle shadow-xl">
            <?php if (empty($chapters)): ?>
                <!-- EMPTY STATE -->
                <div class="text-center py-5">
                    <div class="empty-state-icon mx-auto mb-3 text-muted">
                        <i class="bi bi-journal-plus display-1 opacity-50"></i>
                    </div>
                    <h4 class="text-white fw-bold mb-2">Belum ada bab. Yuk mulai tulis bab pertamamu!</h4>
                    <p class="text-muted max-w-700 mx-auto mb-4" style="max-width: 520px;">
                        Novel ini belum memiliki bab cerita. Tuangkan ide pertamamu dan manfaatkan asisten AI kami untuk mengembangkan alur adegan!
                    </p>
                    <a href="<?= base_url('chapter/create/' . $novel['id']); ?>" class="btn btn-primary-gradient btn-lg rounded-pill px-5 shadow">
                        <i class="bi bi-plus-circle-fill me-2"></i> Tambah Bab
                    </a>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-dark-subtle">
                    <span class="text-muted small">Menampilkan <strong><?= count($chapters); ?></strong> bab secara berurutan</span>
                    <span class="badge bg-dark border border-secondary text-info"><i class="bi bi-arrow-down-up me-1"></i> Urutan Berdasarkan Nomor Bab</span>
                </div>

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
                                    <!-- Order Controls -->
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

                                    <!-- Title -->
                                    <td>
                                        <a href="<?= base_url('chapter/edit/' . $ch['id']); ?>" class="text-white fw-semibold text-decoration-none hover-primary">
                                            <?= sanitize($ch['title']); ?>
                                        </a>
                                    </td>

                                    <!-- Status with Quick Toggle -->
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

                                    <!-- Created Date -->
                                    <td class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i> <?= format_date($ch['created_at']); ?>
                                    </td>

                                    <!-- Actions: Tulis, Edit, Delete, Baca -->
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- Tombol Tulis -->
                                            <a href="<?= base_url('chapter/edit/' . $ch['id']); ?>" class="btn btn-xs btn-primary-gradient rounded-pill px-3 py-1 small" title="Buka Editor Naskah">
                                                <i class="bi bi-pencil-fill me-1"></i> Tulis
                                            </a>

                                            <!-- Tombol Edit -->
                                            <a href="<?= base_url('chapter/edit/' . $ch['id']); ?>" class="btn btn-xs btn-outline-warning rounded-pill px-2 py-1 small" title="Sunting Bab">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>

                                            <!-- Tombol Baca -->
                                            <a href="<?= base_url('chapter/read/' . $ch['id']); ?>" class="btn btn-xs btn-outline-info rounded-pill px-2 py-1 small" target="_blank" title="Baca Tampilan Pembaca">
                                                <i class="bi bi-eye"></i> Baca
                                            </a>

                                            <!-- Tombol Delete with Confirmation -->
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
