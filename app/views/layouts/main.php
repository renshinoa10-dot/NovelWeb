<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) : APP_NAME . ' - ' . APP_TAGLINE; ?></title>
    <meta name="description" content="<?= isset($metaDescription) ? sanitize($metaDescription) : 'Platform Penulisan Novel Terdepan dengan Bantuan AI Writing Assistant.'; ?>">
    <meta name="keywords" content="novel, karyaNovel, ai writing assistant, menulis novel, baca novel online, penulis novel indonesia">
    <meta name="author" content="karyaNovel Team">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= isset($pageTitle) ? sanitize($pageTitle) : APP_NAME; ?>">
    <meta property="og:description" content="<?= isset($metaDescription) ? sanitize($metaDescription) : APP_TAGLINE; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= base_url(); ?>">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS with cache buster -->
    <link rel="stylesheet" href="<?= asset('css/style.css?v=' . filemtime(APPROOT . '/../public/css/style.css')); ?>">
</head>
<body class="d-flex flex-column min-vh-100 bg-dark-main text-body-primary">

    <!-- Global Reading Scroll Progress Bar -->
    <div class="reading-progress-container">
        <div class="reading-progress-bar" id="readingProgressBar"></div>
    </div>

    <!-- Global Header / Navbar -->
    <?php require_once APPROOT . '/views/layouts/navbar.php'; ?>

    <!-- Flash Message Container -->
    <div class="container mt-3">
        <?= render_flash(); ?>
    </div>

    <!-- Main Content Slot -->
    <main class="flex-shrink-0" id="mainAppContent">
        <?= $content ?? ''; ?>
    </main>

    <!-- Global Footer -->
    <?php require_once APPROOT . '/views/layouts/footer.php'; ?>

    <!-- Global Floating Chatbot Assistant Component -->
    <?php require_once APPROOT . '/views/home/chatbot.php'; ?>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom App JS -->
    <script src="<?= asset('js/main.js'); ?>"></script>

    <!-- Chatbot Client Script with cache buster -->
    <script src="<?= asset('js/chatbot.js?v=' . filemtime(APPROOT . '/../public/js/chatbot.js')); ?>"></script>
</body>
</html>
