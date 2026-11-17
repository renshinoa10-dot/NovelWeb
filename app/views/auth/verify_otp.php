<section class="py-5 my-auto">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card bg-card-dark rounded-4 p-4 p-md-5 border border-secondary-subtle shadow-2xl text-center">
                    
                    <!-- WhatsApp Icon with Glowing Badge -->
                    <div class="position-relative d-inline-block mx-auto mb-3">
                        <div class="brand-logo-icon bg-success-subtle border border-success text-success shadow-lg" style="width: 64px; height: 64px; font-size: 2rem;">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                    </div>

                    <h2 class="h4 fw-bold text-white mb-2">Verifikasi Akun WhatsApp</h2>
                    <p class="text-muted small mb-3">
                        Kami telah mengirimkan 6 digit kode OTP verifikasi melalui pesan WhatsApp ke nomor:
                    </p>
                    
                    <div class="d-inline-flex align-items-center justify-content-center gap-2 bg-dark border border-secondary-subtle rounded-pill px-3 py-1 mb-4 text-light fw-semibold small">
                        <i class="bi bi-shield-check text-success"></i>
                        <span><?= sanitize($maskedPhone); ?></span>
                        <span class="badge bg-purple-subtle text-purple-light border border-purple-subtle ms-1"><?= ucfirst(sanitize($role)); ?></span>
                    </div>

                    <?php if (!empty($errors['otp'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 small rounded-3 mb-4 text-start" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div><?= sanitize($errors['otp']); ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Input Kode OTP -->
                    <form action="<?= base_url('auth/verify_otp'); ?>" method="POST" autocomplete="off" class="mb-4">
                        <?= csrf_field(); ?>
                        
                        <div class="mb-4">
                            <label for="otpCodeInput" class="form-label text-light small fw-medium mb-2">
                                Masukkan 6 Digit Kode OTP
                            </label>
                            <input type="text" 
                                   id="otpCodeInput"
                                   name="otp" 
                                   class="form-control bg-dark border-secondary-subtle text-white text-center fw-bold fs-3 <?= !empty($errors['otp']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="••••••" 
                                   maxlength="6" 
                                   pattern="[0-9]{6}" 
                                   inputmode="numeric" 
                                   style="letter-spacing: 0.6rem; font-family: monospace;"
                                   required 
                                   autofocus>
                            <div class="form-text text-white-50 x-small mt-2">
                                <i class="bi bi-info-circle me-1"></i>Kode hanya berlaku selama 10 menit. Jangan bagikan kepada siapa pun.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-gradient w-100 rounded-pill py-2 fw-semibold shadow-sm">
                            <i class="bi bi-check-circle-fill me-1"></i> Verifikasi Sekarang
                        </button>
                    </form>

                    <!-- Form Kirim Ulang OTP -->
                    <div class="pt-3 border-top border-dark-subtle">
                        <p class="text-muted small mb-2">Tidak menerima pesan kode OTP di WhatsApp?</p>
                        
                        <form action="<?= base_url('auth/resend_otp'); ?>" method="POST" id="knResendOtpForm">
                            <?= csrf_field(); ?>
                            <button type="submit" id="knResendOtpBtn" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fw-medium" disabled>
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                <span id="resendText">Kirim Ulang Kode (<span id="resendTimer">60</span>s)</span>
                            </button>
                        </form>
                    </div>

                    <div class="text-center text-muted small mt-4">
                        Salah nomor telepon? 
                        <a href="<?= base_url('auth/register'); ?>" class="text-purple-light fw-medium text-decoration-none">Daftar ulang di sini</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('otpCodeInput');
    const resendBtn = document.getElementById('knResendOtpBtn');
    const resendText = document.getElementById('resendText');
    const resendTimer = document.getElementById('resendTimer');

    // Filter input: hanya angka
    if (input) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                // Auto submit jika 6 digit telah terisi
                this.form.submit();
            }
        });
    }

    // Countdown 60 detik sebelum bisa kirim ulang
    let countdown = 60;
    const interval = setInterval(() => {
        countdown--;
        if (countdown > 0) {
            if (resendTimer) resendTimer.textContent = countdown;
        } else {
            clearInterval(interval);
            if (resendBtn) {
                resendBtn.disabled = false;
                resendBtn.classList.remove('btn-outline-success');
                resendBtn.classList.add('btn-success');
            }
            if (resendText) {
                resendText.innerHTML = 'Kirim Ulang Kode OTP via WhatsApp';
            }
        }
    }, 1000);
});
</script>
