<?php
/**
 * karyaNovel - Database Wrapper (PDO)
 * Menggunakan Singleton Pattern untuk koneksi efisien
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $dbh = null;
    private $stmt;
    private string $error = '';

    private function __construct() {
        $port = defined('DB_PORT') ? DB_PORT : '3306';
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . $port . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Jika database belum dibuat, simpan info error agar tidak crash fatal saat inisialisasi awal
            if (ENVIRONMENT === 'development') {
                error_log("Database Connection Error: " . $this->error);
            }
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): ?PDO {
        return $this->dbh;
    }

    public function isConnected(): bool {
        return $this->dbh !== null;
    }

    public function getError(): string {
        return $this->error;
    }

    /**
     * Persiapkan query SQL
     */
    public function query(string $sql): self {
        if (!$this->dbh) {
            throw new Exception("Koneksi database belum terbentuk: " . $this->error);
        }
        $this->stmt = $this->dbh->prepare($sql);
        return $this;
    }

    /**
     * Binding parameter query dengan tipe data otomatis
     */
    public function bind($param, $value, $type = null): self {
        if ($this->stmt === null) {
            throw new Exception("Tidak ada statement query yang aktif.");
        }

        if (is_null($type)) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Eksekusi prepared statement
     */
    public function execute(): bool {
        if ($this->stmt === null) {
            return false;
        }
        return $this->stmt->execute();
    }

    /**
     * Mengambil banyak baris hasil (Array of Associative Arrays)
     */
    public function resultSet(): array {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Mengambil satu baris hasil
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Mendapatkan jumlah baris yang terpengaruh
     */
    public function rowCount(): int {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    /**
     * Mendapatkan ID baris terakhir yang di-insert
     */
    public function lastInsertId(): string {
        return $this->dbh ? $this->dbh->lastInsertId() : '0';
    }
}
