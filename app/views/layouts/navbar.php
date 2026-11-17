<?php
/**
 * karyaNovel - Navigation Bar Component (Improved UI/UX)
 */
$isLoggedIn = Session::isLoggedIn();
$currentUser = Session::user();
$isAdmin = Session::isAdmin();

// Deteksi menu aktif secara otomatis
$requestUrl = $_GET['url'] ?? $_SERVER['REQUEST_URI'] ?? '';
$isHome = empty($requestUrl) || $requestUrl === '/' || $requestUrl === 'home';
$isExplore = str_contains($requestUrl, 'novel');
$isDashboard = str_contains($requestUrl, 'dashboard');
$isAdminPage = str_contains($requestUrl, 'admin');
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top glass-navbar py-2 py-lg-3" id="mainNavbar">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 py-1" href="<?= base_url(); ?>">
            <div class="brand-logo-icon shadow-sm">
                <i class="bi bi-feather"></i>
            </div>
            <div class="d-flex flex-column">
                <span class="brand-title">karya<span class="text-gradient">Novel</span></span>
                <span class="brand-subtitle d-none d-sm-inline">AI Writing Studio</span>
            </div>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 p-2 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links & Actions -->
        <div class="collapse navbar-collapse mt-3 mt-lg-0" id="navbarContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?= $isHome ? 'active fw-semibold' : ''; ?> px-3 py-2" href="<?= base_url(); ?>">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($isExplore && !$isDashboard && !$isAdminPage) ? 'active fw-semibold' : ''; ?> px-3 py-2" href="<?= base_url('novel'); ?>">
                        <i class="bi bi-compass me-1"></i> Jelajah Novel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2" href="<?= base_url('#genres'); ?>">
                        <i class="bi bi-grid me-1"></i> Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2" href="<?= base_url('#ai-features'); ?>">
                        <span class="badge bg-purple-glow text-white me-1"><i class="bi bi-stars"></i> AI</span> Fitur Penulis
                    </a>
                </li>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 pt-2 pt-lg-0 border-top border-secondary-subtle border-lg-0">
                <?php if ($isLoggedIn && $currentUser): ?>
                    <!-- Logged in State (Multi-Aktor: Pembaca, Penulis, Admin) -->
                    <?php 
                        $userRole = $currentUser['role'] ?? 'pembaca';
                        $isPenulis = ($userRole === 'penulis');
                        $isPembaca = ($userRole === 'pembaca' || $userRole === 'user');
                    ?>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($isAdmin): ?>
                            <a href="<?= base_url('admin'); ?>" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-2 fw-semibold <?= $isAdminPage ? 'active bg-warning text-dark' : ''; ?>">
                                <i class="bi bi-shield-lock-fill me-1"></i> Admin Panel
                            </a>
                            <a href="<?= base_url('dashboard'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 fw-semibold <?= $isDashboard ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        <?php elseif ($isPenulis): ?>
                            <a href="<?= base_url('dashboard'); ?>" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 fw-semibold <?= $isDashboard ? 'active bg-white text-dark' : ''; ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard Penulis
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('novels?public=1'); ?>" class="btn btn-outline-info btn-sm rounded-pill px-3 py-2 fw-semibold">
                                <i class="bi bi-compass me-1"></i> Jelajah Novel
                            </a>
                        <?php endif; ?>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-1 rounded-pill hover-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($currentUser['name']); ?>&background=6366f1&color=fff" 
                                     alt="<?= sanitize($currentUser['name']); ?>" 
                                     class="rounded-circle border border-primary shadow-sm" 
                                     width="36" height="36">
                                <span class="d-none d-xl-inline fw-semibold small text-truncate" style="max-width: 120px;">
                                    <?= sanitize($currentUser['name']); ?>
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border border-secondary-subtle rounded-4 mt-2 py-2">
                                <li class="px-3 py-2 border-bottom border-dark-subtle">
                                    <div class="fw-bold text-white small"><?= sanitize($currentUser['name']); ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= sanitize($currentUser['email']); ?></div>
                                    <div class="mt-1">
                                        <?php if ($isAdmin): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-shield-lock-fill me-1"></i>Admin</span>
                                        <?php elseif ($isPenulis): ?>
                                            <span class="badge bg-purple-subtle text-purple-light border border-purple-subtle"><i class="bi bi-feather me-1"></i>Penulis</span>
                                        <?php else: ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-book-half me-1"></i>Pembaca</span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php if ($isAdmin): ?>
                                    <li><a class="dropdown-item text-warning fw-semibold py-2" href="<?= base_url('admin'); ?>"><i class="bi bi-shield-lock-fill me-2"></i> Admin Panel</a></li>
                                    <li><a class="dropdown-item text-success py-2" href="<?= base_url('admin/notifications'); ?>"><i class="bi bi-whatsapp me-2"></i> Notifikasi WhatsApp</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= base_url('dashboard'); ?>"><i class="bi bi-grid-fill me-2 text-primary"></i> Dashboard Penulis</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= base_url('novels'); ?>"><i class="bi bi-journal-bookmark-fill me-2 text-info"></i> Novel Saya</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= base_url('novels/create'); ?>"><i class="bi bi-plus-circle me-2 text-success"></i> Tulis Novel Baru</a></li>
                                <?php elseif ($isPenulis): ?>
                                    <li><a class="dropdown-item py-2" href="<?= base_url('dashboard'); ?>"><i class="bi bi-grid-fill me-2 text-primary"></i> Dashboard Penulis</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= base_url('novels'); ?>"><i class="bi bi-journal-bookmark-fill me-2 text-info"></i> Novel Saya</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= base_url('novels/create'); ?>"><i class="bi bi-plus-circle me-2 text-success"></i> Tulis Novel Baru</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item py-2" href="<?= base_url('novels?public=1'); ?>"><i class="bi bi-compass me-2 text-info"></i> Jelajah Novel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider border-dark-subtle"></li>
                                <li><a class="dropdown-item text-danger py-2" href="<?= base_url('auth/logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Guest State -->
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('auth/login'); ?>" class="btn btn-ghost-light btn-sm px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </a>
                        <a href="<?= base_url('auth/register'); ?>" class="btn btn-primary-gradient btn-sm px-4 py-2 rounded-pill shadow-sm fw-semibold">
                            <i class="bi bi-pencil-square me-1"></i> Mulai Menulis
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
