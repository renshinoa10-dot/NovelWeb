<?php
/**
 * karyaNovel - Footer Component
 */
?>
<footer class="footer-modern mt-auto">
    <div class="footer-top py-5 border-top border-dark-subtle">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-logo-icon sm">
                            <i class="bi bi-feather"></i>
                        </div>
                        <span class="brand-title fs-4">karya<span class="text-gradient">Novel</span></span>
                    </div>
                    <p class="text-muted small pe-lg-4">
                        Sistem platform penerbitan novel online cerdas yang dirancang untuk mendukung para penulis menuangkan imajinasi dengan bantuan AI Writing Assistant terintegrasi.
                    </p>
                    <div class="d-flex gap-3 text-muted">
                        <a href="#" class="social-icon" aria-label="Github"><i class="bi bi-github"></i></a>
                        <a href="#" class="social-icon" aria-label="Twitter/X"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-icon" aria-label="Discord"><i class="bi bi-discord"></i></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>

                <!-- Link Penulis -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Untuk Penulis</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= base_url('auth/register'); ?>">Daftar Menjadi Penulis</a></li>
                        <li><a href="<?= base_url('#ai-features'); ?>">Fitur AI Assistant</a></li>
                        <li><a href="<?= base_url('dashboard'); ?>">Dashboard Penulis</a></li>
                        <li><a href="<?= base_url('#editor-preview'); ?>">Editor Bab Cerdas</a></li>
                    </ul>
                </div>

                <!-- Link Pembaca -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Untuk Pembaca</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= base_url('#explore'); ?>">Jelajah Novel</a></li>
                        <li><a href="<?= base_url('#genres'); ?>">Kategori Populer</a></li>
                        <li><a href="<?= base_url('#latest'); ?>">Rilis Bab Terbaru</a></li>
                        <li><a href="<?= base_url('#featured'); ?>">Rekomendasi Editor</a></li>
                    </ul>
                </div>

                <!-- Fitur AI & Teknologi -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Keunggulan AI Writing</h6>
                    <div class="p-3 rounded-3 bg-card-dark border border-secondary-subtle">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-purple-glow text-white"><i class="bi bi-stars me-1"></i> AI Powered</span>
                            <span class="text-white-50 small">Model Bahasa Terkini</span>
                        </div>
                        <p class="text-muted small mb-0">
                            Didukung generator alur otomatis, analisis dinamika dialog, dan koreksi tata bahasa sastra yang menjaga orisinalitas karya Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom py-3 bg-dark-deep border-top border-dark text-center text-muted small">
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span>&copy; <?= date('Y'); ?> <strong>karyaNovel</strong>. Seluruh hak cipta dilindungi.</span>
            <div class="d-flex gap-3">
                <a href="#" class="text-muted text-decoration-none">Syarat & Ketentuan</a>
                <span>•</span>
                <a href="#" class="text-muted text-decoration-none">Kebijakan Privasi</a>
                <span>•</span>
                <a href="#" class="text-muted text-decoration-none">Bantuan</a>
            </div>
        </div>
    </div>
</footer>
