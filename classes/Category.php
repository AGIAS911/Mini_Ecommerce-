<?php
require_once __DIR__ . '/../config/Database.php';

class Category {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function create(string $name, ?string $description = null): bool {
        $stmt = $this->db->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description
        ]);
    }

    public function update(int $id, string $name, ?string $description = null): bool {
        $stmt = $this->db->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function nameExists(string $name, ?int $excludeId = null): bool {
        $sql = "SELECT id FROM categories WHERE name = :name";
        $params = [':name' => $name];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM categories");
        return (int) $stmt->fetchColumn();
    }
}