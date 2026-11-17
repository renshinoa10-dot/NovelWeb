<?php
/**
 * karyaNovel - User Model
 * Mengelola otentikasi dan profil pengguna
 */

class UserModel extends Model {
    /**
     * Mencari user berdasarkan email
     */
    public function findByEmail(string $email): ?array {
        if (!$this->isDbConnected()) {
            return null;
        }

        try {
            $this->db->query("SELECT * FROM users WHERE email = :email LIMIT 1");
            $this->db->bind(':email', $email);
            $user = $this->db->single();
            return $user ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Mencari user berdasarkan ID
     */
    public function findById(int $id): ?array {
        if (!$this->isDbConnected()) {
            return null;
        }

        try {
            $this->db->query("SELECT id, name, email, role, avatar, bio, created_at FROM users WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            $user = $this->db->single();
            return $user ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
}
