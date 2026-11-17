<?php
/**
 * karyaNovel - Base Model Class
 * Menyediakan generic CRUD & Active Query methods yang konsisten
 */

abstract class Model {
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Memeriksa ketersediaan koneksi database
     */
    public function isDbConnected(): bool {
        return $this->db->isConnected();
    }

    /**
     * Mendapatkan semua record dari tabel
     *
     * @param string $orderBy
     * @param int|null $limit
     * @return array
     */
    public function all(string $orderBy = 'id DESC', ?int $limit = null): array {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }
        $this->db->query($sql);
        if ($limit !== null) {
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        }
        return $this->db->resultSet();
    }

    /**
     * Mencari satu record berdasarkan Primary Key
     *
     * @param int|string $id
     * @return array|null
     */
    public function find($id): ?array {
        $this->db->query("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        return $result ?: null;
    }

    /**
     * Mencari record berdasarkan kolom tertentu
     *
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @param string $orderBy
     * @return array
     */
    public function where(string $column, $value, string $operator = '=', string $orderBy = 'id DESC'): array {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException("Nama kolom database tidak valid.");
        }
        $allowedOperators = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'];
        if (!in_array(strtoupper(trim($operator)), $allowedOperators, true)) {
            throw new InvalidArgumentException("Operator query database tidak diizinkan.");
        }

        $this->db->query("SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val ORDER BY {$orderBy}");
        $this->db->bind(':val', $value);
        return $this->db->resultSet();
    }

    /**
     * Mengambil satu record pertama berdasarkan kolom tertentu
     *
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @return array|null
     */
    public function whereFirst(string $column, $value, string $operator = '='): ?array {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException("Nama kolom database tidak valid.");
        }
        $allowedOperators = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'];
        if (!in_array(strtoupper(trim($operator)), $allowedOperators, true)) {
            throw new InvalidArgumentException("Operator query database tidak diizinkan.");
        }

        $this->db->query("SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val LIMIT 1");
        $this->db->bind(':val', $value);
        $result = $this->db->single();
        return $result ?: null;
    }

    /**
     * Menyisipkan record baru ke database
     *
     * @param array $data Kolom => Nilai
     * @return string ID dari data yang baru dibuat
     */
    public function create(array $data): string {
        $columns = array_keys($data);
        $fields = '`' . implode('`, `', $columns) . '`';
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO `{$this->table}` ({$fields}) VALUES ({$placeholders})";
        $this->db->query($sql);

        foreach ($data as $column => $value) {
            $this->db->bind(':' . $column, $value);
        }

        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Memperbarui record berdasarkan Primary Key
     *
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool {
        $setClauses = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "`{$column}` = :{$column}";
        }
        $setString = implode(', ', $setClauses);

        $sql = "UPDATE `{$this->table}` SET {$setString} WHERE `{$this->primaryKey}` = :pk_id";
        $this->db->query($sql);

        foreach ($data as $column => $value) {
            $this->db->bind(':' . $column, $value);
        }
        $this->db->bind(':pk_id', $id);

        return $this->db->execute();
    }

    /**
     * Menghapus record berdasarkan Primary Key
     *
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool {
        $this->db->query("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Menghitung total record (dengan optional WHERE)
     *
     * @param string $whereClause contoh: "WHERE status = :status"
     * @param array $params
     * @return int
     */
    public function count(string $whereClause = '', array $params = []): int {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}` {$whereClause}";
        $this->db->query($sql);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $result = $this->db->single();
        return (int)($result['total'] ?? 0);
    }
}
