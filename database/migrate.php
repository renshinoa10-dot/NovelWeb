<?php
/**
 * karyaNovel - Database Migration Runner CLI
 * Jalankan: php database/migrate.php
 */

require_once dirname(__DIR__) . '/app/config/config.php';
require_once APPROOT . '/core/Database.php';

echo "=======================================================\n";
echo " karyaNovel - Database Migration\n";
echo " Host: " . DB_HOST . ":" . DB_PORT . " | DB: " . DB_NAME . "\n";
echo "=======================================================\n\n";

try {
    // 1. Buat database jika belum ada
    $dsnWithoutDb = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsnWithoutDb, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "[1/3] Memeriksa / Membuat database `" . DB_NAME . "`...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "      Database siap.\n\n";

    // 2. Baca file schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("File schema.sql tidak ditemukan di {$schemaFile}");
    }

    echo "[2/3] Membaca skema SQL dari database/schema.sql...\n";
    $sql = file_get_contents($schemaFile);

    // 3. Eksekusi skema
    echo "[3/3] Menjalankan migrasi tabel dan seeder data...\n";
    $pdo->exec("USE `" . DB_NAME . "`;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS `api_keys`, `illustrations`;");
    $pdo->exec($sql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n>>> MIGRASI SUKSES! Seluruh tabel dan data seeder telah berhasil dibuat. <<<\n\n";

    // Tampilkan daftar tabel yang terbentuk
    $stmt = $pdo->query("SHOW TABLES;");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tabel yang aktif:\n";
    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
        $count = $countStmt->fetchColumn();
        echo "  - {$table} ({$count} records)\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "\n[ERROR] Migrasi gagal: " . $e->getMessage() . "\n";
    exit(1);
}
