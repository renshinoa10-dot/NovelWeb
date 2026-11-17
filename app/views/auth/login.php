<section class="py-5 my-auto">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card bg-card-dark rounded-4 p-4 p-md-5 border border-secondary-subtle shadow-2xl">
                    <div class="text-center mb-4">
                        <div class="brand-logo-icon mx-auto mb-3">
                            <i class="bi bi-feather"></i>
                        </div>
                        <h2 class="h3 fw-bold text-white mb-1">Masuk ke <span class="text-gradient">karyaNovel</span></h2>
                        <p class="text-muted small">Pintu masuk resmi untuk Pembaca, Penulis, dan Administrator</p>
                        
                        <!-- Role Actor Badges -->
                        <div class="d-flex justify-content-center gap-2 mt-2">
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="bi bi-book-half me-1"></i>Pembaca</span>
                            <span class="badge bg-purple-subtle text-purple-light border border-purple-subtle px-2 py-1"><i class="bi bi-feather me-1"></i>Penulis</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-shield-lock me-1"></i>Admin</span>
                        </div>
                    </div>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 small rounded-3 mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div><?= sanitize($errors['general']); ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('auth/login'); ?>" method="POST" novalidate id="knLoginForm">
                        <?= csrf_field(); ?>
                        
                        <!-- Email / WhatsApp Input -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium">Alamat Email atau Nomor WhatsApp</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-secondary-subtle text-muted">
                                    <i class="bi bi-person-badge"></i>
                                </span>
                                <input type="text" 
                                       name="identifier" 
                                       class="form-control bg-dark border-secondary-subtle text-white <?= (!empty($errors['identifier']) || !empty($errors['email'])) ? 'is-invalid' : ''; ?>" 
                                       placeholder="nama@email.com atau 08123456789" 
                                       value="<?= sanitize($oldIdentifier ?? ($oldEmail ?? '')); ?>" 
                                       required 
                                       autofocus>
                                <?php if (!empty($errors['identifier'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['identifier']); ?></div>
                                <?php elseif (!empty($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text text-white-50 x-small mt-1">
                                Anda dapat masuk menggunakan alamat email atau nomor WhatsApp yang terdaftar.
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-light small fw-medium mb-0">Kata Sandi</label>
                            </div>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-secondary-subtle text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" id="loginPassword" name="password" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['password']) ? 'is-invalid' : ''; ?>" placeholder="••••••••" required>
                                <button class="btn btn-outline-secondary border-secondary-subtle" type="button" onclick="togglePasswordVisibility('loginPassword', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (!empty($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['password']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-gradient w-100 rounded-pill py-2 fw-semibold mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                        </button>
                    </form>

                    <div class="text-center text-muted small pt-3 border-top border-dark-subtle">
                        Belum memiliki akun? 
                        <a href="<?= base_url('auth/register'); ?>" class="text-purple-light fw-medium text-decoration-none">Daftar Akun Baru</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
