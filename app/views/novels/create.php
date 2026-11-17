<section class="py-5">
    <div class="container py-lg-3" style="max-width: 900px;">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard'); ?>" class="text-muted text-decoration-none"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('novels'); ?>" class="text-muted text-decoration-none">Novel Saya</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Buat Novel Baru</li>
            </ol>
        </nav>

        <div class="card bg-card-dark rounded-4 p-4 p-md-5 border border-secondary-subtle shadow-2xl">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-dark-subtle">
                <div class="brand-logo-icon">
                    <i class="bi bi-journal-plus"></i>
                </div>
                <div>
                    <h2 class="h3 fw-bold text-white mb-1">Buat Proyek Novel Baru</h2>
                    <p class="text-muted small mb-0">Isi konsep dasar ceritamu. Bab-bab naskah dapat ditambahkan setelah novel dibuat.</p>
                </div>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 small rounded-3 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= sanitize($errors['general']); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('novels/create'); ?>" method="POST" enctype="multipart/form-data" novalidate>
                <?= csrf_field(); ?>
                <div class="row g-4 mb-4">
                    <!-- Judul Novel -->
                    <div class="col-12">
                        <label class="form-label text-light fw-medium">Judul Novel <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg bg-dark border-secondary-subtle text-white <?= !empty($errors['title']) ? 'is-invalid' : ''; ?>" placeholder="Contoh: Lembah Bintang: Menembus Horizon" value="<?= sanitize($oldInput['title'] ?? ''); ?>" required autofocus maxlength="200">
                        <?php if (!empty($errors['title'])): ?>
                            <div class="invalid-feedback"><?= sanitize($errors['title']); ?></div>
                        <?php else: ?>
                            <div class="form-text text-muted">Judul yang menarik dan mudah diingat (3 - 200 karakter).</div>
                        <?php endif; ?>
                    </div>

                    <!-- Genre Selection -->
                    <div class="col-md-6">
                        <label class="form-label text-light fw-medium">Genre / Kategori Utama <span class="text-danger">*</span></label>
                        <select name="genre" class="form-select bg-dark border-secondary-subtle text-white <?= !empty($errors['genre']) ? 'is-invalid' : ''; ?>" required>
                            <option value="">-- Pilih Genre --</option>
                            <?php foreach ($availableGenres as $genreOption): ?>
                                <option value="<?= $genreOption; ?>" <?= ($oldInput['genre'] === $genreOption) ? 'selected' : ''; ?>>
                                    <?= $genreOption; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['genre'])): ?>
                            <div class="invalid-feedback"><?= sanitize($errors['genre']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Status Publikasi -->
                    <div class="col-md-6">
                        <label class="form-label text-light fw-medium">Status Awal Proyek</label>
                        <select name="status" class="form-select bg-dark border-secondary-subtle text-white">
                            <option value="draft" <?= ($oldInput['status'] === 'draft') ? 'selected' : ''; ?>>
                                📝 Draft (Rahasia / Hanya Anda yang dapat melihat)
                            </option>
                            <option value="published" <?= ($oldInput['status'] === 'published') ? 'selected' : ''; ?>>
                                🌐 Published (Langsung rilis ke katalog publik)
                            </option>
                        </select>
                    </div>

                    <!-- Sinopsis Cerita -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-light fw-medium mb-0">Sinopsis / Ringkasan Cerita <span class="text-danger">*</span></label>
                            <span class="text-muted small" id="charCount">0 / 5000 karakter</span>
                        </div>
                        <textarea name="synopsis" id="synopsisText" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['synopsis']) ? 'is-invalid' : ''; ?>" rows="6" placeholder="Tuliskan latar belakang dunia, perkenalan tokoh utama, dan konflik utama yang memikat pembaca..." required minlength="20" maxlength="5000"><?= sanitize($oldInput['synopsis'] ?? ''); ?></textarea>
                        <?php if (!empty($errors['synopsis'])): ?>
                            <div class="invalid-feedback"><?= sanitize($errors['synopsis']); ?></div>
                        <?php else: ?>
                            <div class="form-text text-muted">Minimal 20 karakter. Sinopsis akan tampil pada kartu novel dan pencarian publik.</div>
                        <?php endif; ?>
                    </div>

                    <!-- Upload Cover Image -->
                    <div class="col-12">
                        <label class="form-label text-light fw-medium">Gambar Sampul / Cover (Opsional)</label>
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4 text-center">
                                <div id="coverPreviewContainer" class="rounded-4 border border-secondary-subtle p-3 bg-dark d-flex flex-column align-items-center justify-content-center" style="height: 180px;">
                                    <img id="coverPreviewImg" src="" alt="Preview Cover" class="w-100 h-100 object-fit-cover rounded-3 d-none">
                                    <div id="coverPlaceholder" class="text-muted">
                                        <i class="bi bi-image fs-1 opacity-50 d-block mb-1"></i>
                                        <span class="small">Preview Sampul</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <input type="file" name="cover" id="coverInput" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['cover']) ? 'is-invalid' : ''; ?>" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewCoverImage(this)">
                                <?php if (!empty($errors['cover'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['cover']); ?></div>
                                <?php else: ?>
                                    <div class="form-text text-muted">Format yang didukung: JPG, PNG, WEBP. Ukuran maksimal: 2 MB. Jika tidak diunggah, cover standar akan digunakan.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-dark-subtle">
                    <a href="<?= base_url('novels'); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary-gradient btn-lg rounded-pill px-5 shadow">
                        <i class="bi bi-check2-circle me-2"></i> Simpan & Buat Novel
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Character Counter for Synopsis
document.addEventListener('DOMContentLoaded', function () {
    const synopsisArea = document.getElementById('synopsisText');
    const charCounter = document.getElementById('charCount');

    if (synopsisArea && charCounter) {
        function updateCount() {
            const count = synopsisArea.value.length;
            charCounter.textContent = count + ' / 5000 karakter';
        }
        synopsisArea.addEventListener('input', updateCount);
        updateCount();
    }
});

// Instant Image Preview
function previewCoverImage(input) {
    const previewImg = document.getElementById('coverPreviewImg');
    const placeholder = document.getElementById('coverPlaceholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (previewImg && placeholder) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
