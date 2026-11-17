<?php
$aiConfig = $aiConfig ?? [
    'provider' => 'gemini',
    'apiKey' => '',
    'model' => 'gemini-3.5-flash-lite',
    'timeout' => 30,
    'maxPromptLength' => 2000,
    'curatedModels' => [],
    'isConfigured' => false
];
?>
<section class="py-5">
    <div class="container py-lg-3">
        <!-- Admin Header Banner -->
        <div class="card bg-card-dark rounded-4 p-4 p-md-5 border border-warning-subtle mb-4 shadow">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                            <i class="bi bi-shield-lock-fill me-1"></i> Panel Administrator
                        </span>
                        <span class="text-muted small">Sistem Kendali karyaNovel</span>
                    </div>
                    <h1 class="display-6 fw-bold text-white mb-1">
                        Pusat Kontrol & <span class="text-gradient">Manajemen Sistem</span>
                    </h1>
                    <p class="text-muted mb-0">Kelola pengguna, pantau karya novel, moderasi konten naskah bab, dan evaluasi metrik ekosistem karyaNovel.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('dashboard'); ?>" class="btn btn-outline-light rounded-pill px-3 py-2 btn-sm">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard Penulis
                    </a>
                    <a href="<?= base_url('novels'); ?>" class="btn btn-primary rounded-pill px-3 py-2 btn-sm" target="_blank">
                        <i class="bi bi-globe me-1"></i> Katalog Publik
                    </a>
                </div>
            </div>
        </div>

        <!-- 5 Pilar Statistik Dashboard Utama -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg">
                <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Total User</span>
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-0" id="stat-total-users"><?= $stats['totalUsers']; ?></h3>
                    <span class="text-muted small mt-1 d-block">Penulis & Admin</span>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Total Novel</span>
                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-circle">
                            <i class="bi bi-journal-bookmark-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-0" id="stat-total-novels"><?= $stats['totalNovels']; ?></h3>
                    <span class="text-muted small mt-1 d-block">Semua Naskah</span>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Novel Published</span>
                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-circle">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-0" id="stat-published-novels"><?= $stats['publishedNovels']; ?></h3>
                    <span class="text-success small mt-1 d-block">Telah Terbit Publik</span>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Novel Draft</span>
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-0" id="stat-draft-novels"><?= $stats['draftNovels']; ?></h3>
                    <span class="text-warning small mt-1 d-block">Konsep / Belum Terbit</span>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="card bg-card-dark rounded-4 p-3 p-md-4 border border-secondary-subtle h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold text-uppercase">Total Bab</span>
                        <div class="bg-purple bg-opacity-10 text-purple-light p-2 rounded-circle">
                            <i class="bi bi-file-earmark-text-fill fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-0" id="stat-total-chapters"><?= $stats['totalChapters']; ?></h3>
                    <span class="text-muted small mt-1 d-block">Bab Tersimpan</span>
                </div>
            </div>
        </div>

        <!-- 5 Navigasi Fitur Admin -->
        <ul class="nav nav-pills mb-4 gap-2 flex-wrap" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="<?= base_url('admin'); ?>" class="nav-link <?= ($activeTab === 'dashboard') ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-speedometer2 me-2"></i> 1. Admin Dashboard
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= base_url('admin/users'); ?>" class="nav-link <?= ($activeTab === 'users') ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-people-fill me-2"></i> 2. User Management (<span class="badge-tab-users"><?= $stats['totalUsers']; ?></span>)
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= base_url('admin/novels'); ?>" class="nav-link <?= ($activeTab === 'novels') ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-journal-text me-2"></i> 3. Novel Management (<span class="badge-tab-novels"><?= $stats['totalNovels']; ?></span>)
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= base_url('admin/content'); ?>" class="nav-link <?= ($activeTab === 'content') ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-file-earmark-diff-fill me-2"></i> 4. Content Management (<span class="badge-tab-chapters"><?= $stats['totalChapters']; ?></span>)
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= base_url('admin/settings'); ?>" class="nav-link <?= ($activeTab === 'settings') ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-cpu-fill me-2"></i> 5. Konfigurasi AI & Chatbot
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= base_url('admin/notifications'); ?>" class="nav-link <?= ($activeTab === 'notifications') ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-whatsapp me-2 text-success"></i> 6. Notifikasi WhatsApp (Fonnte)
                </a>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <!-- ========================================== -->
            <!-- 1. ADMIN DASHBOARD OVERVIEW               -->
            <!-- ========================================== -->
            <?php if ($activeTab === 'dashboard'): ?>
                <div class="tab-pane fade show active" id="dashboard-panel" role="tabpanel">
                    <div class="row g-4 mb-4">
                        <!-- Ringkasan Ekosistem -->
                        <div class="col-lg-8">
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="fw-bold text-white mb-0">
                                        <i class="bi bi-bar-chart-fill text-warning me-2"></i>Ringkasan Ekosistem karyaNovel
                                    </h4>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Sistem Aktif & Normal
                                    </span>
                                </div>
                                <p class="text-muted">
                                    Platform karyaNovel saat ini menampung <strong><?= $stats['totalUsers']; ?> pengguna</strong> dengan total 
                                    <strong><?= $stats['totalNovels']; ?> novel</strong> dan <strong><?= $stats['totalChapters']; ?> bab</strong> naskah.
                                </p>

                                <div class="row g-3 mt-2">
                                    <div class="col-sm-4">
                                        <div class="p-3 rounded-3 bg-dark border border-secondary-subtle">
                                            <span class="text-muted small d-block">Rasio Publikasi</span>
                                            <h4 class="fw-bold text-info mb-1">
                                                <?= $stats['totalNovels'] > 0 ? round(($stats['publishedNovels'] / $stats['totalNovels']) * 100, 1) : 0; ?>%
                                            </h4>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-info" style="width: <?= $stats['totalNovels'] > 0 ? round(($stats['publishedNovels'] / $stats['totalNovels']) * 100) : 0; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 rounded-3 bg-dark border border-secondary-subtle">
                                            <span class="text-muted small d-block">Rata-rata Bab/Novel</span>
                                            <h4 class="fw-bold text-purple-light mb-1">
                                                <?= $stats['totalNovels'] > 0 ? round($stats['totalChapters'] / $stats['totalNovels'], 1) : 0; ?> Bab
                                            </h4>
                                            <span class="text-muted small">Per naskah novel</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 rounded-3 bg-dark border border-secondary-subtle">
                                            <span class="text-muted small d-block">Log Generasi AI</span>
                                            <h4 class="fw-bold text-warning mb-1"><?= $stats['totalAiLogs']; ?></h4>
                                            <span class="text-muted small">Interaksi prompt AI</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top border-secondary-subtle">
                                    <a href="<?= base_url('admin/users'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                        <i class="bi bi-people me-1"></i> Kelola Pengguna
                                    </a>
                                    <a href="<?= base_url('admin/novels'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                        <i class="bi bi-journal-text me-1"></i> Moderasi Novel
                                    </a>
                                    <a href="<?= base_url('admin/content'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                        <i class="bi bi-file-earmark-diff me-1"></i> Moderasi Bab
                                    </a>
                                    <a href="<?= base_url('admin/settings'); ?>" class="btn btn-outline-warning btn-sm rounded-pill px-3">
                                        <i class="bi bi-cpu me-1"></i> Konfigurasi AI & Chatbot
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Info Akses Admin Saat Ini -->
                        <div class="col-lg-4">
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle h-100">
                                <h5 class="fw-bold text-white mb-3">
                                    <i class="bi bi-shield-check text-warning me-2"></i>Status Otoritas Admin
                                </h5>
                                <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-dark rounded-3 border border-secondary-subtle">
                                    <div class="bg-warning text-dark rounded-circle p-2 fw-bold fs-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)); ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold text-white mb-0 text-truncate"><?= sanitize($admin['name'] ?? 'Administrator'); ?></h6>
                                        <span class="text-muted small text-truncate d-block"><?= sanitize($admin['email'] ?? ''); ?></span>
                                        <span class="badge bg-warning text-dark fw-bold mt-1">Super Administrator</span>
                                    </div>
                                </div>

                                <!-- Ringkasan Status AI Chatbot -->
                                <div class="p-3 bg-dark rounded-3 border border-secondary-subtle mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Mesin Chatbot AI</span>
                                        <span class="badge bg-<?= !empty($aiConfig['isConfigured']) ? 'success' : 'warning'; ?>-subtle text-<?= !empty($aiConfig['isConfigured']) ? 'success' : 'warning'; ?> border border-<?= !empty($aiConfig['isConfigured']) ? 'success' : 'warning'; ?>-subtle rounded-pill" style="font-size: 0.7rem;">
                                            <?= !empty($aiConfig['isConfigured']) ? 'Aktif' : 'Offline'; ?>
                                        </span>
                                    </div>
                                    <div class="text-white small fw-bold text-truncate" title="<?= sanitize($aiConfig['model']); ?>">
                                        <i class="bi bi-cpu text-warning me-1"></i> <?= sanitize($aiConfig['model']); ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary-subtle">
                                        <small class="text-muted"><?= strtoupper($aiConfig['provider']); ?></small>
                                        <a href="<?= base_url('admin/settings'); ?>" class="text-warning text-decoration-none small fw-semibold">
                                            Edit API & Model <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Ringkasan Status WhatsApp Gateway (Fonnte) -->
                                <div class="p-3 bg-dark rounded-3 border border-secondary-subtle mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">WhatsApp Gateway</span>
                                        <span class="badge bg-<?= !empty($fonnteConfig['enabled']) ? 'success' : 'secondary'; ?>-subtle text-<?= !empty($fonnteConfig['enabled']) ? 'success' : 'secondary'; ?> border border-<?= !empty($fonnteConfig['enabled']) ? 'success' : 'secondary'; ?>-subtle rounded-pill" style="font-size: 0.7rem;">
                                            <?= !empty($fonnteConfig['enabled']) ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </div>
                                    <div class="text-white small fw-bold text-truncate">
                                        <i class="bi bi-whatsapp text-success me-1"></i> Fonnte Gateway
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary-subtle">
                                        <small class="text-muted">Target: <?= sanitize($fonnteConfig['targetPhone']); ?></small>
                                        <a href="<?= base_url('admin/notifications'); ?>" class="text-success text-decoration-none small fw-semibold">
                                            Kelola WhatsApp <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="small text-muted">
                                    <p class="mb-2"><i class="bi bi-lock-fill text-success me-2"></i>Semua rute admin terproteksi di tingkat backend.</p>
                                    <p class="mb-0"><i class="bi bi-shield-slash text-danger me-2"></i>User biasa atau tamu otomatis dialihkan keluar.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cuplikan Aktivitas Naskah & Pengguna Terbaru -->
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold text-white mb-0">Naskah Novel Terbaru</h5>
                                    <a href="<?= base_url('admin/novels'); ?>" class="btn btn-sm btn-link text-warning text-decoration-none">Lihat Semua</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover align-middle mb-0 small">
                                        <thead>
                                            <tr class="text-muted border-bottom border-dark-subtle">
                                                <th>Judul</th>
                                                <th>Penulis</th>
                                                <th>Status</th>
                                                <th class="text-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($novels, 0, 5) as $nov): ?>
                                                <tr>
                                                    <td>
                                                        <span class="fw-semibold text-white"><?= sanitize($nov['title']); ?></span>
                                                    </td>
                                                    <td class="text-light-50"><?= sanitize($nov['author_name']); ?></td>
                                                    <td>
                                                        <?php if ($nov['status'] === 'published'): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Published</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Draft</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="<?= base_url('novel/detail/' . $nov['id']); ?>" target="_blank" class="btn btn-xs btn-outline-light rounded-pill px-2 py-1">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold text-white mb-0">Pengguna Terdaftar Terbaru</h5>
                                    <a href="<?= base_url('admin/users'); ?>" class="btn btn-sm btn-link text-warning text-decoration-none">Lihat Semua</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover align-middle mb-0 small">
                                        <thead>
                                            <tr class="text-muted border-bottom border-dark-subtle">
                                                <th>Pengguna</th>
                                                <th>Role</th>
                                                <th>Karya</th>
                                                <th>Terdaftar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($users, 0, 5) as $u): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold text-white"><?= sanitize($u['name']); ?></div>
                                                        <div class="text-muted" style="font-size: 0.75rem;"><?= sanitize($u['email']); ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if ($u['role'] === 'admin'): ?>
                                                            <span class="badge bg-warning text-dark fw-bold">Admin</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Penulis</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $u['total_novels']; ?> Novel</td>
                                                    <td class="text-muted"><?= format_date($u['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- 2. USER MANAGEMENT                        -->
            <!-- ========================================== -->
            <?php if ($activeTab === 'users'): ?>
                <div class="tab-pane fade show active" id="users-panel" role="tabpanel">
                    <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle shadow">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                            <div>
                                <h4 class="fw-bold text-white mb-1">
                                    <i class="bi bi-people text-primary me-2"></i>Manajemen Pengguna & Otoritas Role
                                </h4>
                                <p class="text-muted small mb-0">Lihat profil pengguna, verifikasi peran, ubah hak akses (User / Admin), atau hapus akun bermasalah.</p>
                            </div>
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                                Total <?= count($users); ?> Pengguna
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small border-bottom border-dark-subtle">
                                        <th>ID</th>
                                        <th>Pengguna</th>
                                        <th>Email</th>
                                        <th>Role Akses</th>
                                        <th>Total Karya</th>
                                        <th>Interaksi AI</th>
                                        <th>Terdaftar</th>
                                        <th class="text-end">Aksi Moderasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td><span class="text-muted">#<?= $u['id']; ?></span></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold text-white d-block"><?= sanitize($u['name']); ?></span>
                                                        <?php if ($u['id'] == $admin['id']): ?>
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill" style="font-size: 0.65rem;">Akun Anda</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-light-50"><?= sanitize($u['email']); ?></td>
                                            <td>
                                                <?php if ($u['role'] === 'admin'): ?>
                                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                                                        <i class="bi bi-shield-fill me-1"></i> Admin
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-light border border-secondary px-2 py-1">
                                                        <i class="bi bi-pen me-1"></i> Penulis
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark border border-secondary text-info"><?= $u['total_novels']; ?> Novel</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark border border-secondary text-purple-light"><?= $u['total_ai_prompts']; ?> Prompt</span>
                                            </td>
                                            <td class="text-muted small"><?= format_date($u['created_at']); ?></td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2">
                                                    <!-- Form Ubah Role -->
                                                    <?php if ($u['id'] == $admin['id']): ?>
                                                        <button class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 small" disabled title="Tidak dapat mengubah role akun aktif Anda sendiri">
                                                            <i class="bi bi-lock-fill me-1"></i> Akun Anda
                                                        </button>
                                                    <?php else: ?>
                                                        <form action="<?= base_url('admin/change_role/' . $u['id']); ?>" method="POST" class="d-inline">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="role" value="<?= ($u['role'] === 'admin') ? 'user' : 'admin'; ?>">
                                                            <button type="submit" class="btn btn-xs <?= ($u['role'] === 'admin') ? 'btn-outline-secondary' : 'btn-outline-warning'; ?> rounded-pill px-2 py-1 small" title="Ubah Role">
                                                                <i class="bi bi-arrow-repeat me-1"></i>
                                                                <?= ($u['role'] === 'admin') ? 'Jadikan Penulis' : 'Jadikan Admin'; ?>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <!-- Form Hapus User -->
                                                    <?php if ($u['id'] != $admin['id']): ?>
                                                        <form action="<?= base_url('admin/delete_user/' . $u['id']); ?>" method="POST" class="d-inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus pengguna <?= htmlspecialchars(addslashes($u['name']), ENT_QUOTES, 'UTF-8'); ?>? Seluruh karya novel dan bab miliknya akan ikut terhapus permanen!');">
                                                            <?= csrf_field(); ?>
                                                            <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small" title="Hapus Pengguna Beserta Karyanya">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- 3. NOVEL MANAGEMENT                       -->
            <!-- ========================================== -->
            <?php if ($activeTab === 'novels'): ?>
                <div class="tab-pane fade show active" id="novels-panel" role="tabpanel">
                    <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle shadow">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                            <div>
                                <h4 class="fw-bold text-white mb-1">
                                    <i class="bi bi-journal-text text-info me-2"></i>Manajemen & Moderasi Seluruh Novel
                                </h4>
                                <p class="text-muted small mb-0">Pantau seluruh novel karya penulis, pemilik naskah, status publikasi, dan hapus konten yang melanggar ketentuan.</p>
                            </div>
                            <span class="badge bg-info bg-opacity-25 text-info border border-info-subtle px-3 py-2 rounded-pill">
                                Total <?= count($novels); ?> Naskah Novel
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small border-bottom border-dark-subtle">
                                        <th>ID</th>
                                        <th>Judul Novel</th>
                                        <th>Pemilik (Penulis)</th>
                                        <th>Genre</th>
                                        <th>Total Bab</th>
                                        <th>Status Publikasi</th>
                                        <th>Tanggal Dibuat</th>
                                        <th class="text-end">Aksi Moderasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($novels as $nov): ?>
                                        <tr>
                                            <td><span class="text-muted">#<?= $nov['id']; ?></span></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($nov['cover'])): ?>
                                                        <img src="<?= base_url($nov['cover']); ?>" alt="Cover" class="rounded object-fit-cover shadow-sm" style="width: 36px; height: 50px;">
                                                    <?php else: ?>
                                                        <div class="rounded bg-secondary d-flex align-items-center justify-content-center text-light" style="width: 36px; height: 50px;">
                                                            <i class="bi bi-book"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <span class="fw-semibold text-white d-block"><?= sanitize($nov['title']); ?></span>
                                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                            <?= sanitize($nov['synopsis'] ?? '-'); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-medium text-white d-block"><?= sanitize($nov['author_name']); ?></span>
                                                <span class="text-muted small"><?= sanitize($nov['author_email']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark border border-secondary text-purple-light"><?= sanitize($nov['genre']); ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('admin/content?novel_id=' . $nov['id']); ?>" class="badge bg-dark border border-secondary text-info text-decoration-none" title="Filter bab novel ini di Content Management">
                                                    <?= $nov['total_chapters']; ?> Bab <i class="bi bi-arrow-right-short"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($nov['status'] === 'published'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                        <i class="bi bi-check-circle me-1"></i> Published
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">
                                                        <i class="bi bi-clock-history me-1"></i> Draft
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small"><?= format_date($nov['created_at']); ?></td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-2">
                                                    <!-- Pratinjau Publik -->
                                                    <a href="<?= base_url('novel/detail/' . $nov['id']); ?>" class="btn btn-xs btn-outline-light rounded-pill px-2 py-1 small" target="_blank" title="Lihat Halaman Publik">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <!-- Toggle Status Publikasi -->
                                                    <form action="<?= base_url('admin/toggle_novel_status/' . $nov['id']); ?>" method="POST" class="d-inline">
                                                        <?= csrf_field(); ?>
                                                        <button type="submit" class="btn btn-xs <?= ($nov['status'] === 'published') ? 'btn-outline-warning' : 'btn-outline-success'; ?> rounded-pill px-2 py-1 small" title="Ubah Status Publikasi">
                                                            <?= ($nov['status'] === 'published') ? 'Set Draft' : 'Publikasikan'; ?>
                                                        </button>
                                                    </form>

                                                    <!-- Hapus Konten Novel Melanggar Aturan -->
                                                    <form action="<?= base_url('admin/delete_novel/' . $nov['id']); ?>" method="POST" class="d-inline" onsubmit="return confirm('PERINGATAN MODERATOR: Apakah Anda yakin ingin menghapus novel \'<?= htmlspecialchars(addslashes($nov['title']), ENT_QUOTES, 'UTF-8'); ?>\' secara permanen karena melanggar aturan? Seluruh bab naskah di dalamnya akan ikut terhapus!');">
                                                        <?= csrf_field(); ?>
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small" title="Hapus Novel Melanggar Aturan">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- 4. CONTENT MANAGEMENT                     -->
            <!-- ========================================== -->
            <?php if ($activeTab === 'content'): ?>
                <div class="tab-pane fade show active" id="content-panel" role="tabpanel">
                    <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle shadow">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div>
                                <h4 class="fw-bold text-white mb-1">
                                    <i class="bi bi-file-earmark-diff-fill text-purple-light me-2"></i>Moderasi Konten & Manajemen Bab
                                </h4>
                                <p class="text-muted small mb-0">Melihat novel dan bab, periksa pratinjau teks naskah, moderasi status publikasi, dan hapus bab yang melanggar aturan.</p>
                            </div>

                            <!-- Filter Naskah Novel -->
                            <div class="d-flex align-items-center gap-2">
                                <label for="novelFilterSelect" class="text-muted small text-nowrap">Filter Naskah:</label>
                                <select id="novelFilterSelect" class="form-select form-select-sm bg-dark text-white border-secondary rounded-pill" style="min-width: 220px;" onchange="window.location.href='<?= base_url('admin/content'); ?>' + (this.value ? '?novel_id=' + this.value : '');">
                                    <option value="">Semua Novel (<?= count($novels); ?> Naskah)</option>
                                    <?php foreach ($novels as $n): ?>
                                        <option value="<?= $n['id']; ?>" <?= ($filterNovelId === (int)$n['id']) ? 'selected' : ''; ?>>
                                            #<?= $n['id']; ?> <?= sanitize($n['title']); ?> (<?= sanitize($n['author_name']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($filterNovelId !== null): ?>
                                    <a href="<?= base_url('admin/content'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill" title="Reset Filter">
                                        <i class="bi bi-x-circle"></i> Reset
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (empty($chapters)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-journal-x fs-1 text-muted mb-3 d-block"></i>
                                <h5 class="text-white fw-bold">Tidak ada bab naskah ditemukan</h5>
                                <p class="text-muted small">Belum ada bab yang dibuat untuk filter novel ini, atau naskah masih kosong.</p>
                                <?php if ($filterNovelId !== null): ?>
                                    <a href="<?= base_url('admin/content'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                        Tampilkan Semua Bab
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small border-bottom border-dark-subtle">
                                            <th>ID</th>
                                            <th>Bab Naskah</th>
                                            <th>Judul Novel</th>
                                            <th>Penulis</th>
                                            <th>Status Bab</th>
                                            <th>Dibuat</th>
                                            <th class="text-end">Aksi Moderasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($chapters as $ch): ?>
                                            <tr>
                                                <td><span class="text-muted">#<?= $ch['id']; ?></span></td>
                                                <td>
                                                    <span class="fw-semibold text-white d-block">
                                                        Bab <?= $ch['chapter_order']; ?>: <?= sanitize($ch['title']); ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <?= mb_strlen(strip_tags($ch['content'] ?? '')); ?> karakter
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="text-info fw-medium"><?= sanitize($ch['novel_title']); ?></span>
                                                    <span class="text-muted small d-block">
                                                        Status Novel: <?= ($ch['novel_status'] === 'published') ? '<span class="text-success">Published</span>' : '<span class="text-warning">Draft</span>'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-white d-block"><?= sanitize($ch['author_name']); ?></span>
                                                    <span class="text-muted small"><?= sanitize($ch['author_email']); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($ch['status'] === 'published'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                            <i class="bi bi-check-circle me-1"></i> Published
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">
                                                            <i class="bi bi-clock me-1"></i> Draft
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted small"><?= format_date($ch['created_at']); ?></td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-2">
                                                        <!-- Pratinjau Modal Naskah -->
                                                        <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2 py-1 small" data-bs-toggle="modal" data-bs-target="#previewChapterModal<?= $ch['id']; ?>" title="Pratinjau Isi Bab Naskah">
                                                            <i class="bi bi-eye"></i> Baca
                                                        </button>

                                                        <!-- Toggle Status Bab -->
                                                        <form action="<?= base_url('admin/toggle_chapter_status/' . $ch['id']); ?>" method="POST" class="d-inline">
                                                            <?= csrf_field(); ?>
                                                            <button type="submit" class="btn btn-xs <?= ($ch['status'] === 'published') ? 'btn-outline-warning' : 'btn-outline-success'; ?> rounded-pill px-2 py-1 small" title="Ubah Status Bab">
                                                                <?= ($ch['status'] === 'published') ? 'Set Draft' : 'Publikasikan'; ?>
                                                            </button>
                                                        </form>

                                                        <!-- Hapus Bab Melanggar Aturan -->
                                                        <form action="<?= base_url('admin/delete_chapter/' . $ch['id']); ?>" method="POST" class="d-inline" onsubmit="return confirm('PERINGATAN MODERATOR: Apakah Anda yakin ingin menghapus bab \'<?= htmlspecialchars(addslashes($ch['title']), ENT_QUOTES, 'UTF-8'); ?>\' secara permanen? Urutan nomor bab naskah tersisa akan disusun ulang otomatis.');">
                                                            <?= csrf_field(); ?>
                                                            <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1 small" title="Hapus Bab Melanggar Aturan">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <!-- Modal Pratinjau Naskah Bab -->
                                                    <div class="modal fade text-start" id="previewChapterModal<?= $ch['id']; ?>" tabindex="-1" aria-labelledby="previewModalLabel<?= $ch['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                            <div class="modal-content bg-card-dark text-white border border-secondary-subtle rounded-4">
                                                                <div class="modal-header border-secondary-subtle">
                                                                    <div>
                                                                        <span class="badge bg-purple bg-opacity-25 text-purple-light border border-purple-subtle rounded-pill mb-1">
                                                                            <?= sanitize($ch['novel_title']); ?> &bull; Bab <?= $ch['chapter_order']; ?>
                                                                        </span>
                                                                        <h5 class="modal-title fw-bold text-white" id="previewModalLabel<?= $ch['id']; ?>">
                                                                            <?= sanitize($ch['title']); ?>
                                                                        </h5>
                                                                        <small class="text-muted">Ditulis oleh: <?= sanitize($ch['author_name']); ?> (<?= sanitize($ch['author_email']); ?>)</small>
                                                                    </div>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body py-4 px-4 text-light-50 lh-lg" style="font-size: 1.05rem;">
                                                                    <div class="p-3 rounded-3 bg-dark border border-secondary-subtle mb-3">
                                                                        <span class="small text-muted d-block">Status Saat Ini: <strong><?= ucfirst($ch['status']); ?></strong> | Dibuat: <strong><?= format_date($ch['created_at']); ?></strong></span>
                                                                    </div>
                                                                    <div class="chapter-content-body font-serif">
                                                                        <?= nl2br(sanitize($ch['content'])); ?>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-secondary-subtle justify-content-between">
                                                                    <div>
                                                                        <span class="text-muted small">ID Bab: #<?= $ch['id']; ?></span>
                                                                    </div>
                                                                    <div class="d-flex gap-2">
                                                                        <form action="<?= base_url('admin/toggle_chapter_status/' . $ch['id']); ?>" method="POST" class="d-inline">
                                                                            <?= csrf_field(); ?>
                                                                            <button type="submit" class="btn btn-sm <?= ($ch['status'] === 'published') ? 'btn-outline-warning' : 'btn-outline-success'; ?> rounded-pill px-3">
                                                                                <?= ($ch['status'] === 'published') ? 'Ubah Menjadi Draft' : 'Publikasikan Bab'; ?>
                                                                            </button>
                                                                        </form>
                                                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- 5. KONFIGURASI AI ASSISTANT & CHATBOT     -->
            <!-- ========================================== -->
            <?php if ($activeTab === 'settings'): ?>
                <div class="tab-pane fade show active" id="settings-panel" role="tabpanel">
                    <!-- Header Banner Tab 5 -->
                    <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4 shadow-sm">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning-subtle px-3 py-1 rounded-pill fw-semibold">
                                        <i class="bi bi-cpu-fill me-1"></i> Konfigurasi AI Engine
                                    </span>
                                    <span class="text-muted small">Chatbot & Writing Assistant</span>
                                </div>
                                <h3 class="fw-bold text-white mb-1">
                                    Pengaturan <span class="text-gradient">API Key & Modeling AI</span>
                                </h3>
                                <p class="text-muted mb-0">
                                    Ganti API Key AI dengan mudah, deteksi seluruh model yang tersedia pada kunci tersebut secara real-time, dan pilih model terbaik untuk chatbot dan asisten novel Anda.
                                </p>
                            </div>
                            <div>
                                <?php if (!empty($aiConfig['isConfigured'])): ?>
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success-subtle rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 0.6rem; height: 0.6rem;" role="status"></span>
                                        <strong>API Key Aktif:</strong> <?= strtoupper($aiConfig['provider']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-15 text-warning border border-warning-subtle rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <strong>API Key Belum Diisi:</strong> Menggunakan Mode Fallback
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 4 Kartu Status Konfigurasi Saat Ini -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card bg-card-dark rounded-4 p-3 border border-secondary-subtle h-100 shadow-sm">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Penyedia AI Aktif</span>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-cloud-check-fill text-info fs-4"></i>
                                    <div>
                                        <h5 class="fw-bold text-white mb-0"><?= (strtolower($aiConfig['provider']) === 'openai') ? 'OpenAI / Custom' : 'Google Gemini'; ?></h5>
                                        <small class="text-muted"><?= strtoupper($aiConfig['provider']); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card bg-card-dark rounded-4 p-3 border border-secondary-subtle h-100 shadow-sm">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Model Aktif Saat Ini</span>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-cpu-fill text-warning fs-4"></i>
                                    <div>
                                        <h5 class="fw-bold text-white mb-0 text-truncate" style="max-width: 170px;" title="<?= sanitize($aiConfig['model']); ?>">
                                            <?= sanitize($aiConfig['model']); ?>
                                        </h5>
                                        <small class="text-warning">Mesin Default Chatbot</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card bg-card-dark rounded-4 p-3 border border-secondary-subtle h-100 shadow-sm">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Status Kunci API</span>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-key-fill text-success fs-4"></i>
                                    <div>
                                        <h5 class="fw-bold text-white mb-0">
                                            <?php if (!empty($aiConfig['apiKey'])): ?>
                                                <?= substr($aiConfig['apiKey'], 0, 6) . '...' . substr($aiConfig['apiKey'], -4); ?>
                                            <?php else: ?>
                                                <span class="text-danger small">Belum Diatur</span>
                                            <?php endif; ?>
                                        </h5>
                                        <small class="text-muted"><?= !empty($aiConfig['apiKey']) ? 'Tersimpan Aman' : 'Mode Offline'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="card bg-card-dark rounded-4 p-3 border border-secondary-subtle h-100 shadow-sm">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Batas Timeout & Karakter</span>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-sliders text-purple-light fs-4"></i>
                                    <div>
                                        <h5 class="fw-bold text-white mb-0"><?= $aiConfig['timeout']; ?>s / <?= $aiConfig['maxPromptLength']; ?> Karakter</h5>
                                        <small class="text-muted">Batas Maksimal Request</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Layout 2 Kolom: Form Pengaturan Utama + Tester & Panduan -->
                    <div class="row g-4">
                        <!-- Kolom Kiri: Form Konfigurasi & Pemilihan Model -->
                        <div class="col-lg-8">
                            <form action="<?= base_url('admin/update_settings'); ?>" method="POST" id="formAiConfig">
                                <?= csrf_field(); ?>

                                <!-- Bagian 1: Pengaturan API Key -->
                                <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4 shadow-sm">
                                    <h4 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-circle p-2 fs-6">1</span>
                                        Kunci API (API Key) & Provider
                                    </h4>

                                    <!-- Pilihan Provider -->
                                    <div class="mb-4">
                                        <label class="form-label text-muted small text-uppercase fw-bold">Pilih Provider AI</label>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="card bg-dark border <?= ($aiConfig['provider'] === 'gemini') ? 'border-primary' : 'border-secondary-subtle'; ?> rounded-3 p-3 text-start w-100 cursor-pointer h-100 provider-card" for="providerGemini">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <input type="radio" class="form-check-input mt-0" name="ai_provider" id="providerGemini" value="gemini" <?= ($aiConfig['provider'] === 'gemini') ? 'checked' : ''; ?>>
                                                            <strong class="text-white">Google Gemini</strong>
                                                        </div>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Sangat Direkomendasikan</span>
                                                    </div>
                                                    <small class="text-muted d-block ps-4">Respon cepat, kuota melimpah, dan mendukung model multimodal terbaru.</small>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="card bg-dark border <?= ($aiConfig['provider'] === 'openai') ? 'border-primary' : 'border-secondary-subtle'; ?> rounded-3 p-3 text-start w-100 cursor-pointer h-100 provider-card" for="providerOpenAi">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <input type="radio" class="form-check-input mt-0" name="ai_provider" id="providerOpenAi" value="openai" <?= ($aiConfig['provider'] === 'openai') ? 'checked' : ''; ?>>
                                                            <strong class="text-white">OpenAI / Custom</strong>
                                                        </div>
                                                        <span class="badge bg-secondary-subtle text-light rounded-pill">Standar</span>
                                                    </div>
                                                    <small class="text-muted d-block ps-4">Mendukung GPT-4o, GPT-4o Mini, atau endpoint kustom kompatibel OpenAI.</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Input API Key -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="aiApiKeyInput" class="form-label text-muted small text-uppercase fw-bold mb-0">API Key AI</label>
                                            <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-info text-decoration-none small">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Dapatkan API Key Gratis di Google AI Studio
                                            </a>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary-subtle text-muted">
                                                <i class="bi bi-key-fill"></i>
                                            </span>
                                            <input type="password" 
                                                   class="form-control bg-dark text-white border-secondary-subtle py-2 font-monospace" 
                                                   id="aiApiKeyInput" 
                                                   name="ai_api_key" 
                                                   value="<?= htmlspecialchars($aiConfig['apiKey'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                   placeholder="Masukkan API Key (contoh: AQ.Ab8RN6Ji... atau sk-...)" 
                                                   autocomplete="off">
                                            <button type="button" class="btn btn-outline-secondary border-secondary-subtle text-light-50" id="btnToggleApiKey" title="Lihat / Sembunyikan API Key">
                                                <i class="bi bi-eye" id="eyeIcon"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary border-secondary-subtle text-light-50" id="btnClearApiKey" title="Kosongkan Kolom">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div class="form-text text-muted small mt-2">
                                            <i class="bi bi-shield-check text-success me-1"></i> Kunci API disimpan aman di database server dan disinkronkan ke file lingkungan <code>.env</code>.
                                        </div>
                                    </div>

                                    <!-- Tombol Paling Utama: Ambil & Deteksi Model dari API Key -->
                                    <div class="p-3 rounded-3 bg-dark border border-primary border-opacity-25 mt-3">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                            <div>
                                                <h6 class="fw-bold text-white mb-1">
                                                    <i class="bi bi-stars text-warning me-1"></i> Deteksi Otomatis Model dari API Key
                                                </h6>
                                                <small class="text-muted">Klik tombol ini untuk mengecek model apa saja yang diizinkan dan aktif pada API Key yang Anda ketik.</small>
                                            </div>
                                            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold text-nowrap shadow-sm" id="btnFetchModels">
                                                <span class="spinner-border spinner-border-sm me-2 d-none" id="fetchSpinner" role="status"></span>
                                                <i class="bi bi-search me-1" id="fetchIcon"></i> Cek & Ambil Model
                                            </button>
                                        </div>

                                        <!-- Wadah Pesan Status Hasil Deteksi -->
                                        <div id="fetchStatusMessage" class="mt-3 d-none"></div>
                                    </div>
                                </div>

                                <!-- Bagian 2: Pemilihan Model AI yang Tersedia -->
                                <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4 shadow-sm" id="sectionModelSelection">
                                    <h4 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-circle p-2 fs-6">2</span>
                                        Pilih Modeling yang Tersedia
                                    </h4>
                                    <p class="text-muted small">
                                        Pilih model kecerdasan buatan yang ingin digunakan oleh Chatbot dan Writing Assistant. Model yang direkomendasikan memiliki tanda bintang (<i class="bi bi-star-fill text-warning"></i>).
                                    </p>

                                    <!-- Select Dropdown Model -->
                                    <div class="mb-4">
                                        <label for="aiModelSelect" class="form-label text-muted small text-uppercase fw-bold">Daftar Pilihan Model</label>
                                        <select class="form-select bg-dark text-white border-secondary-subtle py-2 fw-semibold fs-6" id="aiModelSelect" name="ai_model">
                                            <!-- Option Direkomendasikan -->
                                            <optgroup label="⭐ Model Direkomendasikan (Terbaru & Populer)" id="optgroupRecommended">
                                                <?php foreach ($aiConfig['curatedModels'] as $cm): ?>
                                                    <?php if (!empty($cm['is_recommended'])): ?>
                                                        <option value="<?= $cm['id']; ?>" <?= ($aiConfig['model'] === $cm['id']) ? 'selected' : ''; ?>>
                                                            <?= $cm['name']; ?> (<?= $cm['id']; ?>) <?= !empty($cm['badge']) ? '— ' . $cm['badge'] : ''; ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </optgroup>

                                            <!-- Option Model Lainnya -->
                                            <optgroup label="📦 Model Lainnya" id="optgroupOther">
                                                <?php foreach ($aiConfig['curatedModels'] as $cm): ?>
                                                    <?php if (empty($cm['is_recommended'])): ?>
                                                        <option value="<?= $cm['id']; ?>" <?= ($aiConfig['model'] === $cm['id']) ? 'selected' : ''; ?>>
                                                            <?= $cm['name']; ?> (<?= $cm['id']; ?>) <?= !empty($cm['badge']) ? '— ' . $cm['badge'] : ''; ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                <?php if (!in_array($aiConfig['model'], array_column($aiConfig['curatedModels'], 'id'), true)): ?>
                                                    <option value="<?= sanitize($aiConfig['model']); ?>" selected>
                                                        <?= sanitize($aiConfig['model']); ?> (Model Aktif Saat Ini)
                                                    </option>
                                                <?php endif; ?>
                                            </optgroup>

                                            <!-- Opsi Kustom -->
                                            <optgroup label="⚙️ Model Kustom">
                                                <option value="custom">-- Ketik Nama Model Lain Secara Manual --</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <!-- Field Input Model Kustom (Muncul jika pilih 'custom') -->
                                    <div class="mb-4 d-none" id="customModelGroup">
                                        <label for="aiModelCustomInput" class="form-label text-muted small text-uppercase fw-bold">Nama Model Kustom</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary-subtle text-muted">
                                                <i class="bi bi-pencil-square"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control bg-dark text-white border-secondary-subtle py-2 font-monospace" 
                                                   id="aiModelCustomInput" 
                                                   name="ai_model_custom" 
                                                   placeholder="Contoh: gemini-2.5-pro, gpt-4o, atau fine-tuned model ID">
                                        </div>
                                        <small class="text-muted">Masukkan ID model persis seperti yang terdaftar di dokumentasi penyedia AI Anda.</small>
                                    </div>

                                    <!-- Kartu Model Visual Cepat (Quick Select Cards) -->
                                    <div>
                                        <label class="form-label text-muted small text-uppercase fw-bold d-block mb-2">Preset Model Favorit (Klik untuk Memilih Cepat)</label>
                                        <div class="row g-2" id="quickModelCards">
                                            <?php foreach ($aiConfig['curatedModels'] as $cm): ?>
                                                <div class="col-sm-6 col-md-4">
                                                    <div class="p-3 rounded-3 bg-dark border <?= ($aiConfig['model'] === $cm['id']) ? 'border-warning shadow' : 'border-secondary-subtle'; ?> h-100 cursor-pointer quick-model-pill transition-all" 
                                                         data-model-id="<?= $cm['id']; ?>">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <strong class="text-white small fw-bold text-truncate" style="max-width: 140px;"><?= $cm['name']; ?></strong>
                                                            <?php if (!empty($cm['badge'])): ?>
                                                                <span class="badge bg-warning bg-opacity-25 text-warning rounded-pill px-2 py-0" style="font-size: 0.7rem;"><?= $cm['badge']; ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="text-muted d-block font-monospace" style="font-size: 0.75rem;"><?= $cm['id']; ?></span>
                                                        <p class="text-muted small mt-2 mb-0 lh-sm" style="font-size: 0.8rem;"><?= $cm['description']; ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bagian 3: Pengaturan Lanjutan (Collapsible Accordion) -->
                                <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#advancedSettingsCollapse" aria-expanded="false" aria-controls="advancedSettingsCollapse">
                                        <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                                            <i class="bi bi-gear-wide-connected text-muted"></i> Pengaturan Lanjutan (Timeout & Karakter)
                                        </h5>
                                        <i class="bi bi-chevron-down text-muted"></i>
                                    </div>

                                    <div class="collapse mt-3" id="advancedSettingsCollapse">
                                        <div class="row g-3 pt-2">
                                            <div class="col-md-6">
                                                <label for="aiTimeoutInput" class="form-label text-muted small text-uppercase fw-bold">Batas Waktu Timeout Request (Detik)</label>
                                                <div class="input-group">
                                                    <input type="number" 
                                                           class="form-control bg-dark text-white border-secondary-subtle" 
                                                           id="aiTimeoutInput" 
                                                           name="ai_timeout" 
                                                           value="<?= $aiConfig['timeout']; ?>" 
                                                           min="5" 
                                                           max="120">
                                                    <span class="input-group-text bg-dark border-secondary-subtle text-muted">Detik</span>
                                                </div>
                                                <small class="text-muted">Batas maksimal menunggu respon server AI sebelum beralih ke mode offline (default 30 detik).</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="aiMaxPromptInput" class="form-label text-muted small text-uppercase fw-bold">Batas Maksimal Karakter Prompt</label>
                                                <div class="input-group">
                                                    <input type="number" 
                                                           class="form-control bg-dark text-white border-secondary-subtle" 
                                                           id="aiMaxPromptInput" 
                                                           name="ai_max_prompt_length" 
                                                           value="<?= $aiConfig['maxPromptLength']; ?>" 
                                                           min="500" 
                                                           max="10000" 
                                                           step="500">
                                                    <span class="input-group-text bg-dark border-secondary-subtle text-muted">Karakter</span>
                                                </div>
                                                <small class="text-muted">Mencegah konsumsi token berlebih saat prompt diajukan penulis (default 2000 karakter).</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tombol Aksi Simpan Form -->
                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                    <button type="submit" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow">
                                        <i class="bi bi-check2-circle me-2"></i> Simpan Konfigurasi AI
                                    </button>
                                    <a href="<?= base_url('admin/settings'); ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Batal / Muat Ulang
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Kolom Kanan: Uji Coba Koneksi AI Langsung (Live Interactive Tester) & Panduan -->
                        <div class="col-lg-4">
                            <!-- Widget Tester Koneksi AI -->
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="fw-bold text-white mb-0">
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Uji Koneksi AI Langsung
                                    </h5>
                                    <span class="badge bg-warning bg-opacity-15 text-warning border border-warning-subtle rounded-pill">Tester</span>
                                </div>
                                <p class="text-muted small mb-3">
                                    Kirim pesan tes ke API Key & Model yang sedang Anda atur untuk memastikan respon berjalan lancar sebelum disimpan.
                                </p>

                                <div class="mb-3">
                                    <label for="testerPrompt" class="form-label text-muted small text-uppercase fw-bold">Pesan Uji Coba</label>
                                    <textarea class="form-control bg-dark text-white border-secondary-subtle" 
                                              id="testerPrompt" 
                                              rows="3" 
                                              placeholder="Tulis pesan uji coba...">Halo AI Assistant karyaNovel! Berikan satu kalimat salam ramah dan konfirmasi kesiapan Anda membantu penulisan novel.</textarea>
                                </div>

                                <button type="button" class="btn btn-warning rounded-pill w-100 py-2 fw-bold text-dark shadow-sm" id="btnTestConnection">
                                    <span class="spinner-border spinner-border-sm me-2 d-none" id="testerSpinner" role="status"></span>
                                    <i class="bi bi-play-circle-fill me-1" id="testerIcon"></i> Jalankan Tes Koneksi
                                </button>

                                <!-- Hasil Uji Coba -->
                                <div id="testerResultContainer" class="mt-3 d-none">
                                    <div class="p-3 rounded-3 bg-dark border border-secondary-subtle">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle rounded-pill" id="testerResultBadge">
                                                <i class="bi bi-check-circle me-1"></i> Berhasil Terhubung
                                            </span>
                                            <span class="text-muted small font-monospace" id="testerLatency">⚡ 0 ms</span>
                                        </div>
                                        <div class="small text-muted mb-1">
                                            Model: <strong class="text-white" id="testerModelUsed">-</strong>
                                        </div>
                                        <div class="p-2 rounded bg-black bg-opacity-50 text-light small font-serif" id="testerReplyText" style="white-space: pre-wrap; font-size: 0.85rem;">
                                            ...
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Widget Panduan Singkat -->
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle shadow-sm">
                                <h5 class="fw-bold text-white mb-3">
                                    <i class="bi bi-info-circle-fill text-info me-2"></i>Panduan Cepat API Key
                                </h5>
                                <ol class="text-muted small ps-3 mb-3 lh-lg">
                                    <li>Buka <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-info text-decoration-none fw-semibold">Google AI Studio</a>.</li>
                                    <li>Login dengan Akun Google Anda dan klik <strong>Create API Key</strong>.</li>
                                    <li>Salin kunci yang diawali dengan <code>AQ...</code> atau format Google AI.</li>
                                    <li>Tempelkan ke kolom <strong>API Key AI</strong> di formulir sebelah kiri.</li>
                                    <li>Klik tombol <strong>"Cek & Ambil Model"</strong> untuk memuat daftar model yang diaktifkan di akun Anda.</li>
                                    <li>Pilih model (disarankan <strong>Gemini 2.5 Flash</strong>) lalu klik <strong>Simpan Konfigurasi</strong>.</li>
                                </ol>

                                <div class="p-3 rounded-3 bg-dark border border-secondary-subtle">
                                    <strong class="text-warning small d-block mb-1">
                                        <i class="bi bi-lightbulb-fill me-1"></i> Keunggulan Gemini 2.5 Flash:
                                    </strong>
                                    <p class="text-muted small mb-0">
                                        Model ini memiliki latensi paling rendah, pemahaman Bahasa Indonesia yang sangat natural, dan kuota gratis (free-tier) yang sangat tinggi untuk kebutuhan Chatbot.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JavaScript Khusus Pengelolaan Konfigurasi AI & Chatbot -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const apiKeyInput = document.getElementById('aiApiKeyInput');
                    const toggleApiKeyBtn = document.getElementById('btnToggleApiKey');
                    const eyeIcon = document.getElementById('eyeIcon');
                    const clearApiKeyBtn = document.getElementById('btnClearApiKey');
                    const btnFetchModels = document.getElementById('btnFetchModels');
                    const fetchSpinner = document.getElementById('fetchSpinner');
                    const fetchIcon = document.getElementById('fetchIcon');
                    const fetchStatusMsg = document.getElementById('fetchStatusMessage');
                    const aiModelSelect = document.getElementById('aiModelSelect');
                    const customModelGroup = document.getElementById('customModelGroup');
                    const customModelInput = document.getElementById('aiModelCustomInput');
                    const quickModelPills = document.querySelectorAll('.quick-model-pill');
                    const btnTestConnection = document.getElementById('btnTestConnection');
                    const testerSpinner = document.getElementById('testerSpinner');
                    const testerIcon = document.getElementById('testerIcon');
                    const testerResultContainer = document.getElementById('testerResultContainer');
                    const testerResultBadge = document.getElementById('testerResultBadge');
                    const testerLatency = document.getElementById('testerLatency');
                    const testerModelUsed = document.getElementById('testerModelUsed');
                    const testerReplyText = document.getElementById('testerReplyText');
                    const providerRadios = document.querySelectorAll('input[name="ai_provider"]');

                    // 1. Toggle Show / Hide API Key
                    if (toggleApiKeyBtn && apiKeyInput) {
                        toggleApiKeyBtn.addEventListener('click', function() {
                            if (apiKeyInput.type === 'password') {
                                apiKeyInput.type = 'text';
                                eyeIcon.className = 'bi bi-eye-slash';
                            } else {
                                apiKeyInput.type = 'password';
                                eyeIcon.className = 'bi bi-eye';
                            }
                        });
                    }

                    // 2. Clear API Key
                    if (clearApiKeyBtn && apiKeyInput) {
                        clearApiKeyBtn.addEventListener('click', function() {
                            apiKeyInput.value = '';
                            apiKeyInput.focus();
                        });
                    }

                    // 3. Toggle Custom Model input ketika pilihan 'custom' dipilih
                    if (aiModelSelect && customModelGroup) {
                        aiModelSelect.addEventListener('change', function() {
                            if (this.value === 'custom') {
                                customModelGroup.classList.remove('d-none');
                                customModelInput.focus();
                            } else {
                                customModelGroup.classList.add('d-none');
                            }
                            highlightActiveQuickCard(this.value);
                        });
                    }

                    // 4. Quick Select Cards Click Handler
                    quickModelPills.forEach(pill => {
                        pill.addEventListener('click', function() {
                            const modelId = this.getAttribute('data-model-id');
                            if (!modelId) return;

                            // Cek apakah opsi sudah ada di select
                            let optionExists = false;
                            for (let i = 0; i < aiModelSelect.options.length; i++) {
                                if (aiModelSelect.options[i].value === modelId) {
                                    aiModelSelect.selectedIndex = i;
                                    optionExists = true;
                                    break;
                                }
                            }

                            if (!optionExists) {
                                const newOpt = new Option(modelId + ' (Terpilih)', modelId, true, true);
                                aiModelSelect.add(newOpt, 0);
                                aiModelSelect.value = modelId;
                            }

                            if (customModelGroup) {
                                customModelGroup.classList.add('d-none');
                            }

                            highlightActiveQuickCard(modelId);
                        });
                    });

                    function highlightActiveQuickCard(selectedModel) {
                        quickModelPills.forEach(p => {
                            if (p.getAttribute('data-model-id') === selectedModel) {
                                p.classList.add('border-warning', 'shadow');
                                p.classList.remove('border-secondary-subtle');
                            } else {
                                p.classList.remove('border-warning', 'shadow');
                                p.classList.add('border-secondary-subtle');
                            }
                        });
                    }

                    // 5. AJAX: Cek & Ambil Model dari API Key
                    if (btnFetchModels) {
                        btnFetchModels.addEventListener('click', function() {
                            const apiKey = apiKeyInput ? apiKeyInput.value.trim() : '';
                            const selectedProvider = document.querySelector('input[name="ai_provider"]:checked') ? document.querySelector('input[name="ai_provider"]:checked').value : 'gemini';

                            if (!apiKey) {
                                showFetchStatus('danger', '<i class="bi bi-exclamation-triangle-fill me-1"></i> Harap masukkan API Key terlebih dahulu sebelum mendeteksi model.');
                                if (apiKeyInput) apiKeyInput.focus();
                                return;
                            }

                            // Tampilkan loading spinner
                            btnFetchModels.disabled = true;
                            fetchSpinner.classList.remove('d-none');
                            fetchIcon.classList.add('d-none');
                            showFetchStatus('info', '<i class="bi bi-arrow-repeat me-1 spinner-border spinner-border-sm"></i> Menghubungi server ' + selectedProvider.toUpperCase() + ' untuk membaca model yang aktif...');

                            fetch('<?= base_url("admin/fetch_models"); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    api_key: apiKey,
                                    provider: selectedProvider
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                btnFetchModels.disabled = false;
                                fetchSpinner.classList.add('d-none');
                                fetchIcon.classList.remove('d-none');

                                if (data.success && Array.isArray(data.models) && data.models.length > 0) {
                                    populateModelSelect(data.models, data.current_model);
                                    showFetchStatus('success', '<i class="bi bi-check-circle-fill me-1"></i> Berhasil! Ditemukan <strong>' + data.models.length + ' model</strong> aktif pada API Key Anda. Silakan pilih salah satu model di bawah.');
                                } else {
                                    const errMsg = data.error || 'Gagal mengambil daftar model dari API Key.';
                                    showFetchStatus('warning', '<i class="bi bi-exclamation-circle-fill me-1"></i> ' + errMsg + '<br><small>Menampilkan daftar model kurasi default karyaNovel yang siap dipilih.</small>');
                                    if (data.fallback_models) {
                                        populateModelSelect(data.fallback_models, data.current_model);
                                    }
                                }
                            })
                            .catch(err => {
                                btnFetchModels.disabled = false;
                                fetchSpinner.classList.add('d-none');
                                fetchIcon.classList.remove('d-none');
                                showFetchStatus('danger', '<i class="bi bi-x-octagon-fill me-1"></i> Terjadi kesalahan koneksi saat memproses request (' + err.message + '). Pastikan server terhubung ke internet.');
                            });
                        });
                    }

                    function showFetchStatus(type, htmlContent) {
                        if (!fetchStatusMsg) return;
                        fetchStatusMsg.className = 'alert alert-' + type + ' rounded-3 p-3 mt-3 d-block';
                        fetchStatusMsg.innerHTML = htmlContent;
                    }

                    function populateModelSelect(models, currentModel) {
                        if (!aiModelSelect) return;

                        // Bersihkan opsi lama kecuali optgroup custom
                        aiModelSelect.innerHTML = '';

                        const optgroupRec = document.createElement('optgroup');
                        optgroupRec.label = '⭐ Model Direkomendasikan (Generasi Flash & Cepat)';

                        const optgroupOther = document.createElement('optgroup');
                        optgroupOther.label = '📦 Seluruh Model Tersedia';

                        let hasRec = false;
                        let hasOther = false;

                        models.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.id;
                            let label = m.name ? m.name + ' (' + m.id + ')' : m.id;
                            if (m.description) {
                                label += ' — ' + m.description.substring(0, 45) + '...';
                            }
                            opt.textContent = label;

                            if (m.id === currentModel) {
                                opt.selected = true;
                            }

                            if (m.is_recommended) {
                                optgroupRec.appendChild(opt);
                                hasRec = true;
                            } else {
                                optgroupOther.appendChild(opt);
                                hasOther = true;
                            }
                        });

                        if (hasRec) aiModelSelect.appendChild(optgroupRec);
                        if (hasOther) aiModelSelect.appendChild(optgroupOther);

                        const optgroupCustom = document.createElement('optgroup');
                        optgroupCustom.label = '⚙️ Opsi Manual';
                        const customOpt = document.createElement('option');
                        customOpt.value = 'custom';
                        customOpt.textContent = '-- Ketik Nama Model Lain Secara Manual --';
                        optgroupCustom.appendChild(customOpt);
                        aiModelSelect.appendChild(optgroupCustom);

                        highlightActiveQuickCard(aiModelSelect.value);
                    }

                    // 6. AJAX: Uji Koneksi AI Langsung
                    if (btnTestConnection) {
                        btnTestConnection.addEventListener('click', function() {
                            const apiKey = apiKeyInput ? apiKeyInput.value.trim() : '';
                            const selectedProvider = document.querySelector('input[name="ai_provider"]:checked') ? document.querySelector('input[name="ai_provider"]:checked').value : 'gemini';
                            let selectedModel = aiModelSelect ? aiModelSelect.value : '';
                            if (selectedModel === 'custom' && customModelInput) {
                                selectedModel = customModelInput.value.trim();
                            }
                            const promptText = document.getElementById('testerPrompt') ? document.getElementById('testerPrompt').value.trim() : '';

                            if (!apiKey) {
                                alert('Harap isi API Key terlebih dahulu untuk menguji koneksi.');
                                if (apiKeyInput) apiKeyInput.focus();
                                return;
                            }

                            btnTestConnection.disabled = true;
                            testerSpinner.classList.remove('d-none');
                            testerIcon.classList.add('d-none');
                            testerResultContainer.classList.add('d-none');

                            fetch('<?= base_url("admin/test_ai_connection"); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    api_key: apiKey,
                                    provider: selectedProvider,
                                    model: selectedModel,
                                    prompt: promptText
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                btnTestConnection.disabled = false;
                                testerSpinner.classList.add('d-none');
                                testerIcon.classList.remove('d-none');
                                testerResultContainer.classList.remove('d-none');

                                if (data.success) {
                                    testerResultBadge.className = 'badge bg-success bg-opacity-25 text-success border border-success-subtle rounded-pill';
                                    testerResultBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Koneksi Berhasil & Aktif';
                                    testerLatency.textContent = '⚡ ' + (data.latency_ms || 0) + ' ms';
                                    testerModelUsed.textContent = data.model || selectedModel;
                                    testerReplyText.textContent = data.reply || '(Respon kosong)';
                                } else {
                                    testerResultBadge.className = 'badge bg-danger bg-opacity-25 text-danger border border-danger-subtle rounded-pill';
                                    testerResultBadge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Koneksi Gagal';
                                    testerLatency.textContent = '⚡ ' + (data.latency_ms || 0) + ' ms';
                                    testerModelUsed.textContent = selectedModel;
                                    testerReplyText.textContent = data.error || 'Terjadi kesalahan saat menghubungi server AI.';
                                }
                            })
                            .catch(err => {
                                btnTestConnection.disabled = false;
                                testerSpinner.classList.add('d-none');
                                testerIcon.classList.remove('d-none');
                                testerResultContainer.classList.remove('d-none');

                                testerResultBadge.className = 'badge bg-danger bg-opacity-25 text-danger border border-danger-subtle rounded-pill';
                                testerResultBadge.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Error Request';
                                testerLatency.textContent = 'Error';
                                testerModelUsed.textContent = selectedModel;
                                testerReplyText.textContent = 'Koneksi gagal: ' + err.message;
                            });
                        });
                    }
                });
                </script>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- 6. NOTIFIKASI WHATSAPP (FONNTE GATEWAY)    -->
            <!-- ========================================== -->
            <?php if ($activeTab === 'notifications'): ?>
                <div class="tab-pane fade show active" id="notifications-panel" role="tabpanel">
                    <!-- Header Banner -->
                    <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                            <div>
                                <h3 class="fw-bold text-white mb-1">
                                    <i class="bi bi-whatsapp text-success me-2"></i>Gateway Notifikasi WhatsApp (Fonnte)
                                </h3>
                                <p class="text-muted mb-0">
                                    Layanan forwarding notifikasi otomatis untuk update cerita baru, rilis bab baru, dan pengumuman sistem via WhatsApp API Fonnte.
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-<?= !empty($fonnteConfig['enabled']) ? 'success' : 'secondary'; ?>-subtle text-<?= !empty($fonnteConfig['enabled']) ? 'success' : 'secondary'; ?> border border-<?= !empty($fonnteConfig['enabled']) ? 'success' : 'secondary'; ?>-subtle rounded-pill px-3 py-2 fw-semibold">
                                    <i class="bi bi-<?= !empty($fonnteConfig['enabled']) ? 'check-circle-fill' : 'pause-circle-fill'; ?> me-1"></i>
                                    <?= !empty($fonnteConfig['enabled']) ? 'Gateway Aktif' : 'Gateway Nonaktif'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Row 1: Status Device & Statistik -->
                    <div class="row g-4 mb-4">
                        <!-- Perangkat WhatsApp Terhubung -->
                        <div class="col-lg-5">
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold text-white mb-0">
                                        <i class="bi bi-phone-fill text-success me-2"></i>Perangkat Fonnte
                                    </h5>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnRefreshDevice">
                                        <i class="bi bi-arrow-clockwise me-1" id="iconRefreshDevice"></i>
                                        <span id="textRefreshDevice">Cek Status</span>
                                    </button>
                                </div>

                                <div class="p-3 bg-dark rounded-3 border border-secondary-subtle mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted small">Status Koneksi</span>
                                        <span class="badge bg-<?= ($fonnteDevice['device_status'] ?? '') === 'connect' ? 'success' : 'danger'; ?>-subtle text-<?= ($fonnteDevice['device_status'] ?? '') === 'connect' ? 'success' : 'danger'; ?> border border-<?= ($fonnteDevice['device_status'] ?? '') === 'connect' ? 'success' : 'danger'; ?>-subtle rounded-pill px-3 py-1" id="deviceStatusBadge">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> <?= strtoupper($fonnteDevice['device_status'] ?? 'Unknown'); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted small">Nomor WhatsApp</span>
                                        <strong class="text-white" id="deviceNumber"><?= sanitize($fonnteDevice['device'] ?? '-'); ?></strong>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted small">Nama Perangkat</span>
                                        <span class="text-white fw-semibold" id="deviceName"><?= sanitize($fonnteDevice['name'] ?? '-'); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted small">Paket Layanan</span>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill" id="devicePackage"><?= sanitize($fonnteDevice['package'] ?? 'Free'); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted small">Sisa Kuota Pesan</span>
                                        <span class="badge bg-warning text-dark fw-bold rounded-pill" id="deviceQuota"><?= (int)($fonnteDevice['quota'] ?? 0); ?> Pesan</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted small">Masa Berlaku</span>
                                        <span class="text-muted small" id="deviceExpired"><?= sanitize($fonnteDevice['expired'] ?? '-'); ?></span>
                                    </div>
                                </div>

                                <div class="small text-muted">
                                    <i class="bi bi-shield-check text-success me-1"></i>
                                    Forward otomatis bekerja di background saat novel atau bab baru dipublikasikan.
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Statistik -->
                        <div class="col-lg-7">
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle h-100">
                                <h5 class="fw-bold text-white mb-3">
                                    <i class="bi bi-bar-chart-fill text-warning me-2"></i>Statistik Forwarding Notifikasi
                                </h5>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-dark rounded-3 border border-secondary-subtle text-center">
                                            <span class="text-muted small d-block mb-1">Total Forward</span>
                                            <h3 class="fw-bold text-white mb-0"><?= $notificationStats['total']; ?></h3>
                                            <small class="text-muted">Pesan terproses</small>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-dark rounded-3 border border-success-subtle text-center">
                                            <span class="text-success small d-block mb-1">Berhasil</span>
                                            <h3 class="fw-bold text-success mb-0"><?= $notificationStats['success_count']; ?></h3>
                                            <small class="text-muted">
                                                <?= $notificationStats['total'] > 0 ? round(($notificationStats['success_count'] / $notificationStats['total']) * 100) : 0; ?>% rasio sukses
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="p-3 bg-dark rounded-3 border border-danger-subtle text-center">
                                            <span class="text-danger small d-block mb-1">Gagal</span>
                                            <h3 class="fw-bold text-danger mb-0"><?= $notificationStats['failed_count']; ?></h3>
                                            <small class="text-muted">Perlu evaluasi</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 bg-dark rounded-3 border border-secondary-subtle">
                                    <span class="text-muted small fw-bold text-uppercase d-block mb-2" style="font-size: 0.75rem;">Distribusi Berdasarkan Jenis Pesan:</span>
                                    <div class="row g-2 text-center">
                                        <div class="col-4">
                                            <div class="p-2 rounded bg-black bg-opacity-25 border border-secondary-subtle">
                                                <span class="badge bg-primary mb-1">Cerita Baru</span>
                                                <div class="fw-bold text-white"><?= $notificationStats['novel_count']; ?></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded bg-black bg-opacity-25 border border-secondary-subtle">
                                                <span class="badge bg-info mb-1">Bab Baru</span>
                                                <div class="fw-bold text-white"><?= $notificationStats['chapter_count']; ?></div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2 rounded bg-black bg-opacity-25 border border-secondary-subtle">
                                                <span class="badge bg-warning text-dark mb-1">Sistem / Siaran</span>
                                                <div class="fw-bold text-white"><?= $notificationStats['system_count']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Pengaturan & Aksi Forward -->
                    <div class="row g-4 mb-4">
                        <!-- Form Pengaturan Fonnte -->
                        <div class="col-lg-6">
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle h-100">
                                <h5 class="fw-bold text-white mb-3">
                                    <i class="bi bi-gear-fill text-warning me-2"></i>Konfigurasi Fonnte Gateway
                                </h5>

                                <form action="<?= base_url('admin/update_fonnte_settings'); ?>" method="POST">
                                    <?= csrf_field(); ?>

                                    <!-- API Key Fonnte -->
                                    <div class="mb-3">
                                        <label for="fonnte_api_key" class="form-label text-white small fw-semibold">
                                            API Key Fonnte <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary-subtle text-muted">
                                                <i class="bi bi-key-fill"></i>
                                            </span>
                                            <input type="password" 
                                                   class="form-control bg-dark border-secondary-subtle text-white" 
                                                   id="fonnte_api_key" 
                                                   name="fonnte_api_key" 
                                                   value="<?= sanitize($fonnteConfig['apiKey']); ?>" 
                                                   required 
                                                   placeholder="Masukkan API Key dari Fonnte Dashboard">
                                            <button class="btn btn-outline-secondary" type="button" id="btnToggleApiKey">
                                                <i class="bi bi-eye" id="iconEyeApiKey"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">API Key diperoleh dari halaman Device di <a href="https://fonnte.com" target="_blank" class="text-warning">fonnte.com</a>.</small>
                                    </div>

                                    <!-- Nomor Target Default WhatsApp -->
                                    <div class="mb-3">
                                        <label for="fonnte_target_phone" class="form-label text-white small fw-semibold">
                                            Nomor Tujuan Forward Default (Target WhatsApp) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary-subtle text-muted">
                                                <i class="bi bi-telephone-fill"></i>
                                            </span>
                                            <input type="text" 
                                                   class="form-control bg-dark border-secondary-subtle text-white" 
                                                   id="fonnte_target_phone" 
                                                   name="fonnte_target_phone" 
                                                   value="<?= sanitize($fonnteConfig['targetPhone']); ?>" 
                                                   required 
                                                   placeholder="Contoh: 089634700208 atau 0812...,0898...">
                                        </div>
                                        <small class="text-muted">
                                            Dapat berupa nomor tunggal (format 08xxx / 62xxx), beberapa nomor dipisah koma, atau WhatsApp Group ID.
                                        </small>
                                    </div>

                                    <!-- Trigger Switches -->
                                    <div class="mb-3 p-3 bg-dark rounded-3 border border-secondary-subtle">
                                        <h6 class="text-white small fw-bold mb-3">Trigger Otomatis Forward Notifikasi:</h6>
                                        
                                        <!-- Master Switch -->
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="fonnte_enabled" name="fonnte_enabled" value="1" <?= !empty($fonnteConfig['enabled']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label text-white small fw-semibold" for="fonnte_enabled">
                                                Aktifkan Layanan WhatsApp Gateway Secara Global
                                            </label>
                                        </div>

                                        <!-- Forward Cerita Baru -->
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="fonnte_notify_new_novel" name="fonnte_notify_new_novel" value="1" <?= !empty($fonnteConfig['notifyNewNovel']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label text-white small" for="fonnte_notify_new_novel">
                                                Forward Notifikasi Saat Cerita / Novel Baru Dirilis
                                            </label>
                                        </div>

                                        <!-- Forward Bab Baru -->
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="fonnte_notify_new_chapter" name="fonnte_notify_new_chapter" value="1" <?= !empty($fonnteConfig['notifyNewChapter']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label text-white small" for="fonnte_notify_new_chapter">
                                                Forward Notifikasi Saat Bab / Chapter Baru Dipublikasikan
                                            </label>
                                        </div>

                                        <!-- Notifikasi Sistem -->
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="fonnte_notify_system" name="fonnte_notify_system" value="1" <?= !empty($fonnteConfig['notifySystem']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label text-white small" for="fonnte_notify_system">
                                                Izinkan Pengiriman Notifikasi & Pengumuman Sistem
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">
                                        <i class="bi bi-save me-1"></i> Simpan Pengaturan WhatsApp
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Kolom Aksi Manual (Kirim Notifikasi Sistem & Uji Coba) -->
                        <div class="col-lg-6">
                            <!-- Card Kirim Notifikasi Sistem -->
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle mb-4">
                                <h5 class="fw-bold text-white mb-2">
                                    <i class="bi bi-megaphone-fill text-info me-2"></i>Kirim Pengumuman Sistem
                                </h5>
                                <p class="text-muted small mb-3">
                                    Broadcast pesan resmi administrator ke nomor target atau grup WhatsApp pembaca.
                                </p>

                                <form action="<?= base_url('admin/send_system_notification'); ?>" method="POST">
                                    <?= csrf_field(); ?>

                                    <div class="mb-3">
                                        <label for="system_title" class="form-label text-white small fw-semibold">Judul Pengumuman</label>
                                        <input type="text" class="form-control bg-dark border-secondary-subtle text-white" id="system_title" name="title" required placeholder="Contoh: Pemeliharaan Sistem / Update Fitur Baru">
                                    </div>

                                    <div class="mb-3">
                                        <label for="system_message" class="form-label text-white small fw-semibold">Isi Pesan</label>
                                        <textarea class="form-control bg-dark border-secondary-subtle text-white" id="system_message" name="message" rows="3" required placeholder="Tuliskan isi pesan pengumuman..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="system_target" class="form-label text-white small fw-semibold">Target Nomor (Opsional)</label>
                                        <input type="text" class="form-control bg-dark border-secondary-subtle text-white" id="system_target" name="target" placeholder="Kosongkan untuk menggunakan target default (<?= sanitize($fonnteConfig['targetPhone']); ?>)">
                                    </div>

                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                                        <i class="bi bi-whatsapp me-1"></i> Kirim Pengumuman ke WhatsApp
                                    </button>
                                </form>
                            </div>

                            <!-- Card Uji Coba (Test Message) -->
                            <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle">
                                <h5 class="fw-bold text-white mb-2">
                                    <i class="bi bi-send-check-fill text-success me-2"></i>Uji Coba Pengiriman Pesan
                                </h5>
                                <p class="text-muted small mb-3">
                                    Kirim pesan uji coba singkat untuk memverifikasi bahwa Fonnte terhubung dan dapat mengirim pesan ke nomor WhatsApp Anda.
                                </p>

                                <form action="<?= base_url('admin/test_fonnte'); ?>" method="POST">
                                    <?= csrf_field(); ?>

                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-dark border-secondary-subtle text-muted">
                                            <i class="bi bi-phone"></i>
                                        </span>
                                        <input type="text" class="form-control bg-dark border-secondary-subtle text-white" name="target_phone" value="<?= sanitize($fonnteConfig['targetPhone']); ?>" placeholder="Nomor tujuan tes (misal 089634700208)" required>
                                        <button type="submit" class="btn btn-outline-success fw-semibold px-4">
                                            <i class="bi bi-send-fill me-1"></i> Kirim Pesan Tes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Riwayat Log Forwarding Notifikasi -->
                    <div class="card bg-card-dark rounded-4 p-4 border border-secondary-subtle">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-white mb-1">
                                    <i class="bi bi-clock-history text-warning me-2"></i>Riwayat Log Notifikasi WhatsApp
                                </h5>
                                <span class="text-muted small">Menampilkan maksimal 50 aktivitas forwarding terakhir.</span>
                            </div>
                            <?php if (!empty($notificationLogs)): ?>
                                <form action="<?= base_url('admin/clear_notification_logs'); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua riwayat log notifikasi?');">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                        <i class="bi bi-trash me-1"></i> Bersihkan Log
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($notificationLogs)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-chat-left-dots fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-0">Belum ada riwayat forwarding notifikasi WhatsApp.</p>
                                <small>Notifikasi akan otomatis tercatat ketika ada novel, chapter baru, atau pengumuman yang dikirim.</small>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th style="width: 50px;">#</th>
                                            <th style="width: 140px;">Waktu</th>
                                            <th style="width: 120px;">Jenis</th>
                                            <th style="width: 150px;">Nomor Tujuan</th>
                                            <th>Judul / Subjek</th>
                                            <th style="width: 100px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notificationLogs as $idx => $log): ?>
                                            <tr>
                                                <td class="text-muted small"><?= $idx + 1; ?></td>
                                                <td class="small text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['type'] === 'new_novel'): ?>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                                            <i class="bi bi-journal-plus me-1"></i> Cerita Baru
                                                        </span>
                                                    <?php elseif ($log['type'] === 'new_chapter'): ?>
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                                                            <i class="bi bi-file-earmark-plus me-1"></i> Bab Baru
                                                        </span>
                                                    <?php elseif ($log['type'] === 'system'): ?>
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                                            <i class="bi bi-megaphone me-1"></i> Sistem
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-light border border-secondary-subtle rounded-pill">
                                                            <i class="bi bi-flask me-1"></i> Uji Coba
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-white small fw-semibold font-monospace">
                                                    <?= sanitize($log['target']); ?>
                                                </td>
                                                <td>
                                                    <div class="text-white small fw-bold"><?= sanitize($log['title'] ?? '-'); ?></div>
                                                    <div class="text-muted small text-truncate" style="max-width: 400px;" title="<?= sanitize($log['message']); ?>">
                                                        <?= sanitize(mb_substr($log['message'], 0, 100)) . (mb_strlen($log['message']) > 100 ? '...' : ''); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($log['status'] === 'success'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Berhasil
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                            <i class="bi bi-x-circle-fill me-1"></i> Gagal
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // 1. Toggle Show / Hide API Key
                    const btnToggleApiKey = document.getElementById('btnToggleApiKey');
                    const apiKeyInput = document.getElementById('fonnte_api_key');
                    const iconEyeApiKey = document.getElementById('iconEyeApiKey');

                    if (btnToggleApiKey && apiKeyInput && iconEyeApiKey) {
                        btnToggleApiKey.addEventListener('click', function() {
                            if (apiKeyInput.type === 'password') {
                                apiKeyInput.type = 'text';
                                iconEyeApiKey.className = 'bi bi-eye-slash';
                            } else {
                                apiKeyInput.type = 'password';
                                iconEyeApiKey.className = 'bi bi-eye';
                            }
                        });
                    }

                    // 2. Refresh Device Status via AJAX
                    const btnRefreshDevice = document.getElementById('btnRefreshDevice');
                    const iconRefreshDevice = document.getElementById('iconRefreshDevice');
                    const textRefreshDevice = document.getElementById('textRefreshDevice');

                    if (btnRefreshDevice) {
                        btnRefreshDevice.addEventListener('click', function() {
                            btnRefreshDevice.disabled = true;
                            if (iconRefreshDevice) iconRefreshDevice.classList.add('spin-animation');
                            if (textRefreshDevice) textRefreshDevice.textContent = 'Memeriksa...';

                            fetch('<?= base_url("admin/fetch_fonnte_device"); ?>', {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                btnRefreshDevice.disabled = false;
                                if (iconRefreshDevice) iconRefreshDevice.classList.remove('spin-animation');
                                if (textRefreshDevice) textRefreshDevice.textContent = 'Cek Status';

                                if (data && data.status) {
                                    const badge = document.getElementById('deviceStatusBadge');
                                    if (badge) {
                                        const isConnected = data.device_status === 'connect';
                                        badge.className = isConnected 
                                            ? 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1' 
                                            : 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1';
                                        badge.innerHTML = '<i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> ' + (data.device_status || 'UNKNOWN').toUpperCase();
                                    }
                                    if (document.getElementById('deviceNumber')) document.getElementById('deviceNumber').textContent = data.device || '-';
                                    if (document.getElementById('deviceName')) document.getElementById('deviceName').textContent = data.name || '-';
                                    if (document.getElementById('devicePackage')) document.getElementById('devicePackage').textContent = data.package || 'Free';
                                    if (document.getElementById('deviceQuota')) document.getElementById('deviceQuota').textContent = (data.quota || 0) + ' Pesan';
                                    if (document.getElementById('deviceExpired')) document.getElementById('deviceExpired').textContent = data.expired || '-';
                                } else {
                                    alert(data.message || 'Gagal memeriksa status perangkat Fonnte.');
                                }
                            })
                            .catch(err => {
                                btnRefreshDevice.disabled = false;
                                if (iconRefreshDevice) iconRefreshDevice.classList.remove('spin-animation');
                                if (textRefreshDevice) textRefreshDevice.textContent = 'Cek Status';
                                alert('Error koneksi: ' + err.message);
                            });
                        });
                    }
                });
                </script>
                <style>
                .spin-animation {
                    display: inline-block;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
                </style>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function() {
    function refreshAdminStats() {
        fetch('<?= base_url("admin/stats_api"); ?>', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success && data.stats) {
                const s = data.stats;
                const elUsers = document.getElementById('stat-total-users');
                const elNovels = document.getElementById('stat-total-novels');
                const elPub = document.getElementById('stat-published-novels');
                const elDraft = document.getElementById('stat-draft-novels');
                const elChap = document.getElementById('stat-total-chapters');

                if (elUsers) elUsers.textContent = s.totalUsers;
                if (elNovels) elNovels.textContent = s.totalNovels;
                if (elPub) elPub.textContent = s.publishedNovels;
                if (elDraft) elDraft.textContent = s.draftNovels;
                if (elChap) elChap.textContent = s.totalChapters;

                document.querySelectorAll('.badge-tab-users').forEach(el => el.textContent = s.totalUsers);
                document.querySelectorAll('.badge-tab-novels').forEach(el => el.textContent = s.totalNovels);
                document.querySelectorAll('.badge-tab-chapters').forEach(el => el.textContent = s.totalChapters);
            }
        })
        .catch(err => console.debug('Live sync admin stats skipped:', err));
    }

    window.addEventListener('pageshow', refreshAdminStats);
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            refreshAdminStats();
        }
    });
})();
</script>

