<?php
if (!headers_sent()) {
    http_response_code(404);
}
?>
<section class="py-5 my-auto text-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="error-code text-gradient fw-bolder mb-3" style="font-size: 6rem; line-height: 1;">404</div>
                <h2 class="h3 fw-bold text-white mb-3"><?= sanitize($message ?? 'Halaman Tidak Ditemukan'); ?></h2>
                <p class="text-muted mb-4">
                    Maaf, halaman atau bab novel yang Anda tuju mungkin telah dipindahkan, dihapus, atau belum tersedia.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= base_url(); ?>" class="btn btn-primary-gradient px-4 rounded-pill">
                        <i class="bi bi-house-door me-2"></i> Ke Beranda
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
