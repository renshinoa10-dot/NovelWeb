<?php
/**
 * karyaNovel - Sistem Novel Berbasis Website dengan AI Writing Assistant
 * Front Controller & Application Bootstrap
 */

// 1. Muat Konfigurasi Dasar
require_once __DIR__ . '/app/config/config.php';

// 2. Muat Helper Global
require_once APPROOT . '/helpers/helpers.php';

// 3. Muat Core Framework Classes
require_once APPROOT . '/core/Session.php';
require_once APPROOT . '/core/Database.php';
require_once APPROOT . '/core/Model.php';
require_once APPROOT . '/models/Setting.php';
require_once APPROOT . '/models/NotificationLog.php';
require_once APPROOT . '/services/FonnteService.php';
require_once APPROOT . '/core/Controller.php';
require_once APPROOT . '/core/App.php';

// 4. Inisialisasi Session
Session::start();

// 5. Jalankan Router Aplikasi
$app = new App();
