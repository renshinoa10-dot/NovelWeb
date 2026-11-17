<section class="py-5 my-auto">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-6">
                <div class="card bg-card-dark rounded-4 p-4 p-md-5 border border-secondary-subtle shadow-2xl">
                    <div class="text-center mb-4">
                        <div class="brand-logo-icon mx-auto mb-3">
                            <i class="bi bi-feather"></i>
                        </div>
                        <h2 class="h3 fw-bold text-white mb-1">Daftar Akun <span class="text-gradient">karyaNovel</span></h2>
                        <p class="text-muted small">Pilih peran akun Anda dan verifikasi nomor WhatsApp via OTP resmi</p>
                    </div>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 small rounded-3 mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div><?= sanitize($errors['general']); ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('auth/register'); ?>" method="POST" novalidate id="knRegisterForm">
                        <?= csrf_field(); ?>

                        <!-- Pilihan Aktor / Role -->
                        <div class="mb-4">
                            <label class="form-label text-light small fw-medium mb-2 d-block">Pilih Peran Akun Anda</label>
                            <div class="row g-2">
                                <!-- Pembaca -->
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="role" id="rolePembaca" value="pembaca" <?= (($oldInput['role'] ?? 'pembaca') === 'pembaca') ? 'checked' : ''; ?> onchange="handleRoleChange()">
                                    <label class="btn btn-outline-info w-100 p-2 text-center rounded-3 h-100 d-flex flex-column align-items-center justify-content-center gap-1" for="rolePembaca">
                                        <i class="bi bi-book-half fs-4"></i>
                                        <span class="small fw-semibold">Pembaca</span>
                                    </label>
                                </div>
                                <!-- Penulis -->
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="role" id="rolePenulis" value="penulis" <?= (($oldInput['role'] ?? '') === 'penulis') ? 'checked' : ''; ?> onchange="handleRoleChange()">
                                    <label class="btn btn-outline-purple w-100 p-2 text-center rounded-3 h-100 d-flex flex-column align-items-center justify-content-center gap-1" for="rolePenulis">
                                        <i class="bi bi-feather fs-4"></i>
                                        <span class="small fw-semibold">Penulis</span>
                                    </label>
                                </div>
                                <!-- Administrator -->
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="role" id="roleAdmin" value="admin" <?= (($oldInput['role'] ?? '') === 'admin') ? 'checked' : ''; ?> onchange="handleRoleChange()">
                                    <label class="btn btn-outline-danger w-100 p-2 text-center rounded-3 h-100 d-flex flex-column align-items-center justify-content-center gap-1" for="roleAdmin">
                                        <i class="bi bi-shield-lock fs-4"></i>
                                        <span class="small fw-semibold">Admin</span>
                                    </label>
                                </div>
                            </div>
                            <div id="roleDescription" class="form-text text-white-50 x-small mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                <span id="roleDescText">Jelajahi dan baca ribuan novel karya penulis tanah air.</span>
                            </div>
                        </div>

                        <!-- Kolom Passcode Admin (Muncul hanya jika role Admin dipilih) -->
                        <div class="mb-3 <?= (($oldInput['role'] ?? '') === 'admin') ? '' : 'd-none'; ?>" id="adminPasscodeGroup">
                            <label class="form-label text-warning small fw-medium">
                                <i class="bi bi-key-fill me-1"></i> Passcode Otorisasi Administrator
                            </label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-warning-subtle text-warning"><i class="bi bi-shield-lock-fill"></i></span>
                                <input type="password" name="admin_passcode" id="adminPasscode" class="form-control bg-dark border-warning-subtle text-white <?= !empty($errors['admin_passcode']) ? 'is-invalid' : ''; ?>" placeholder="Masukkan kode otorisasi Admin">
                                <?php if (!empty($errors['admin_passcode'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['admin_passcode']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text text-white-50 x-small">Hanya untuk staf & pengelola sistem resmi karyaNovel.</div>
                        </div>

                        <!-- Nama Lengkap / Pena -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium">Nama Lengkap / Nama Pena</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-secondary-subtle text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['name']) ? 'is-invalid' : ''; ?>" placeholder="Contoh: Ahmad Rian" value="<?= sanitize($oldInput['name'] ?? ''); ?>" required autofocus>
                                <?php if (!empty($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['name']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium">Alamat Email</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-secondary-subtle text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['email']) ? 'is-invalid' : ''; ?>" placeholder="nama@email.com" value="<?= sanitize($oldInput['email'] ?? ''); ?>" required>
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Nomor Telepon (WhatsApp OTP via Fonnte) -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium">
                                Nomor WhatsApp <span class="text-success fw-bold">(Wajib untuk Verifikasi OTP)</span>
                            </label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-secondary-subtle text-success"><i class="bi bi-whatsapp"></i></span>
                                <input type="tel" name="phone" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['phone']) ? 'is-invalid' : ''; ?>" placeholder="Contoh: 08123456789 atau 628123456789" value="<?= sanitize($oldInput['phone'] ?? ''); ?>" required>
                                <?php if (!empty($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['phone']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text text-white-50 x-small">
                                <i class="bi bi-shield-check text-success me-1"></i>Kode OTP acak akan dikirimkan otomatis ke nomor WhatsApp ini via service Fonnte.
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label text-light small fw-medium">Kata Sandi</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-secondary-subtle text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" id="regPassword" name="password" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['password']) ? 'is-invalid' : ''; ?>" placeholder="Minimal 6 karakter" required minlength="6">
                                <button class="btn btn-outline-secondary border-secondary-subtle" type="button" onclick="togglePasswordVisibility('regPassword', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (!empty($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['password']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-4">
                            <label class="form-label text-light small fw-medium">Konfirmasi Kata Sandi</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-dark border-secondary-subtle text-muted"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" id="regConfirmPassword" name="confirm_password" class="form-control bg-dark border-secondary-subtle text-white <?= !empty($errors['confirm_password']) ? 'is-invalid' : ''; ?>" placeholder="Ulangi kata sandi" required minlength="6">
                                <button class="btn btn-outline-secondary border-secondary-subtle" type="button" onclick="togglePasswordVisibility('regConfirmPassword', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if (!empty($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= sanitize($errors['confirm_password']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-gradient w-100 rounded-pill py-2 fw-semibold mb-3">
                            <i class="bi bi-send-check-fill me-1"></i> Daftar & Dapatkan Kode OTP WhatsApp
                        </button>
                    </form>

                    <div class="text-center text-muted small pt-3 border-top border-dark-subtle">
                        Sudah punya akun? 
                        <a href="<?= base_url('auth/login'); ?>" class="text-purple-light fw-medium text-decoration-none">Masuk di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function handleRoleChange() {
    const rolePembaca = document.getElementById('rolePembaca');
    const rolePenulis = document.getElementById('rolePenulis');
    const roleAdmin = document.getElementById('roleAdmin');
    const adminPasscodeGroup = document.getElementById('adminPasscodeGroup');
    const roleDescText = document.getElementById('roleDescText');

    if (roleAdmin && roleAdmin.checked) {
        if (adminPasscodeGroup) adminPasscodeGroup.classList.remove('d-none');
        if (roleDescText) roleDescText.textContent = 'Akses kendali Administrator sistem, analitik pengguna, dan kendali operasional.';
    } else if (rolePenulis && rolePenulis.checked) {
        if (adminPasscodeGroup) adminPasscodeGroup.classList.add('d-none');
        if (roleDescText) roleDescText.textContent = 'Tulis cerita novel, kelola bab, publikasikan naskah, dan manfaatkan AI Writing Assistant.';
    } else {
        if (adminPasscodeGroup) adminPasscodeGroup.classList.add('d-none');
        if (roleDescText) roleDescText.textContent = 'Jelajahi dan nikmati ribuan novel dari berbagai genre karya penulis favoritmu.';
    }
}
document.addEventListener('DOMContentLoaded', handleRoleChange);
</script>
