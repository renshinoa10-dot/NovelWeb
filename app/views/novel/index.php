<section class="py-5">
    <div class="container py-lg-3">
        <!-- Page Header & Search Bar -->
        <div class="row align-items-center justify-content-between mb-5 g-4">
            <div class="col-lg-6">
                <span class="badge bg-purple-subtle text-purple-light px-3 py-1 rounded-pill mb-2">
                    <i class="bi bi-compass me-1"></i> Eksplorasi Cerita
                </span>
                <h1 class="display-6 fw-bold text-white mb-2">
                    <?= !empty($currentGenre) ? 'Genre: ' . sanitize($currentGenre) : 'Jelajah Seluruh Novel'; ?>
                </h1>
                <p class="text-muted mb-0">Temukan ribuan naskah fiksi orisinal dari para penulis berbakat karyaNovel.</p>
            </div>
            <div class="col-lg-5">
                <form action="<?= base_url('novel'); ?>" method="GET" class="d-flex gap-2">
                    <?php if (!empty($currentGenre)): ?>
                        <input type="hidden" name="genre" value="<?= sanitize($currentGenre); ?>">
                    <?php endif; ?>
                    <div class="input-group">
                        <span class="input-group-text bg-card-dark border-secondary-subtle text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control bg-card-dark border-secondary-subtle text-white" placeholder="Cari judul novel atau kata kunci sinopsis..." value="<?= sanitize($searchQuery ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary-gradient px-4 rounded-3">Cari</button>
                </form>
            </div>
        </div>

        <!-- Genre Filter Pills -->
        <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom border-dark-subtle">
            <a href="<?= base_url('novel'); ?>" class="btn btn-sm <?= empty($currentGenre) ? 'btn-primary-gradient' : 'btn-glass-secondary'; ?> rounded-pill px-3">
                Semua Genre
            </a>
            <?php foreach ($genresSummary as $g): ?>
                <a href="<?= base_url('novel/genre/' . urlencode($g['genre'])); ?>" class="btn btn-sm <?= ($currentGenre && strtolower($currentGenre) === strtolower($g['genre'])) ? 'btn-primary-gradient' : 'btn-glass-secondary'; ?> rounded-pill px-3">
                    <?= sanitize($g['genre']); ?> <span class="badge bg-dark ms-1"><?= $g['count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Novels Grid -->
        <?php if (empty($novels)): ?>
            <div class="text-center py-5 bg-card-dark rounded-4 border border-secondary-subtle my-4">
                <i class="bi bi-journal-x text-muted display-3 mb-3 d-block"></i>
                <h4 class="text-white fw-bold">Tidak ada novel yang ditemukan</h4>
                <p class="text-muted small">Coba ubah kata kunci pencarian atau pilih genre novel yang lain.</p>
                <a href="<?= base_url('novel'); ?>" class="btn btn-outline-light rounded-pill px-4 btn-sm mt-2">Reset Filter</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($novels as $novel): ?>
                    <?php $coverUrl = novel_cover_url($novel['cover'] ?? null); ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-novel h-100 rounded-4 overflow-hidden border border-secondary-subtle shadow-sm transition-all">
                            <!-- Cover -->
                            <div class="card-novel-cover-wrap position-relative" style="height: 200px;">
                                <?php if ($coverUrl): ?>
                                    <img src="<?= $coverUrl; ?>" alt="<?= sanitize($novel['title']); ?>" class="w-100 h-100 object-fit-cover">
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-center">
                                        <i class="bi bi-book-half text-primary opacity-50 display-4 mb-2"></i>
                                        <h6 class="fw-bold text-white px-2 mb-0 line-clamp-1"><?= sanitize($novel['title']); ?></h6>
                                    </div>
                                <?php endif; ?>
                                <span class="badge bg-dark bg-opacity-75 text-purple-light border border-secondary position-absolute top-0 end-0 m-2 rounded-pill px-3 py-1">
                                    <?= sanitize($novel['genre']); ?>
                                </span>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="card-title fw-bold text-white mb-2 line-clamp-1">
                                    <a href="<?= base_url('novel/detail/' . $novel['id']); ?>" class="text-white text-decoration-none hover-primary">
                                        <?= sanitize($novel['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted small flex-grow-1 line-clamp-3 mb-3">
                                    <?= sanitize($novel['synopsis']); ?>
                                </p>

                                <div class="d-flex align-items-center justify-content-between pt-3 border-top border-dark-subtle mt-auto">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle text-primary"></i>
                                        <span class="text-white-50 small fw-medium"><?= sanitize($novel['author_name']); ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-journal-text text-primary me-1"></i> <?= $novel['total_chapters']; ?> Bab
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-dark-deep p-3 border-top border-dark-subtle d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> <?= format_date($novel['created_at']); ?></span>
                                <a href="<?= base_url('novel/detail/' . $novel['id']); ?>" class="btn btn-sm btn-primary-gradient rounded-pill px-3">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination Links -->
            <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="Navigasi Halaman Novel" class="mt-5">
                    <ul class="pagination justify-content-center flex-wrap gap-1">
                        <?php
                        $currentPage = (int)$pagination['page'];
                        $totalPages = (int)$pagination['total_pages'];
                        $queryParams = [];
                        if (!empty($currentGenre)) $queryParams['genre'] = $currentGenre;
                        if (!empty($searchQuery)) $queryParams['q'] = $searchQuery;

                        $buildPageUrl = function($p) use ($queryParams) {
                            $params = array_merge($queryParams, ['page' => $p]);
                            return base_url('novel?' . http_build_query($params));
                        };
                        ?>
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-pill px-3 bg-card-dark text-white border-secondary-subtle" href="<?= ($currentPage > 1) ? $buildPageUrl($currentPage - 1) : '#'; ?>">
                                <i class="bi bi-chevron-left me-1"></i> Sebelumnya
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link rounded-circle px-3 bg-card-dark border-secondary-subtle <?= ($i === $currentPage) ? 'btn-primary-gradient text-white border-0' : 'text-white'; ?>" href="<?= $buildPageUrl($i); ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link rounded-pill px-3 bg-card-dark text-white border-secondary-subtle" href="<?= ($currentPage < $totalPages) ? $buildPageUrl($currentPage + 1) : '#'; ?>">
                                Berikutnya <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
