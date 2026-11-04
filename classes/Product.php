<?php
require_once __DIR__ . '/../config/Database.php';

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(?int $categoryId = null, ?string $search = null): array {
        $sql = "SELECT p.*, c.name AS category_name, pi.image_path 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE 1=1";
        $params = [];

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if ($search !== null && trim($search) !== '') {
            $sql .= " AND p.name LIKE :search";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $sql = "SELECT p.*, c.name AS category_name, pi.image_path 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                WHERE p.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    public function getImages(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC, id ASC");
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll();
    }

    public function create(int $categoryId, string $name, ?string $description, float $price, int $stock, ?string $imagePath = null): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO products (category_id, name, description, price, stock) VALUES (:category_id, :name, :description, :price, :stock)");
            $stmt->execute([
                ':category_id' => $categoryId,
                ':name'        => $name,
                ':description' => $description,
                ':price'       => $price,
                ':stock'       => $stock
            ]);

            $productId = (int)$this->db->lastInsertId();

            if ($imagePath !== null) {
                $imgStmt = $this->db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (:product_id, :image_path, 1)");
                $imgStmt->execute([
                    ':product_id' => $productId,
                    ':image_path' => $imagePath
                ]);
            }

            $this->db->commit();
            return $productId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function update(int $id, int $categoryId, string $name, ?string $description, float $price, int $stock, ?string $newImagePath = null): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE products SET category_id = :category_id, name = :name, description = :description, price = :price, stock = :stock WHERE id = :id");
            $stmt->execute([
                ':category_id' => $categoryId,
                ':name'        => $name,
                ':description' => $description,
                ':price'       => $price,
                ':stock'       => $stock,
                ':id'          => $id
            ]);

            if ($newImagePath !== null) {
                $resetStmt = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id");
                $resetStmt->execute([':product_id' => $id]);

                $imgStmt = $this->db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (:product_id, :image_path, 1)");
                $imgStmt->execute([
                    ':product_id' => $id,
                    ':image_path' => $newImagePath
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM products");
        return (int)$stmt->fetchColumn();
    }

  public function updateStock(int $id, int $quantity): bool {
    $stmt = $this->db->prepare("UPDATE products SET stock = stock - :deduct_qty WHERE id = :id AND stock >= :check_qty");
    return $stmt->execute([
        ':deduct_qty' => $quantity,
        ':check_qty'  => $quantity,
        ':id'         => $id
    ]);
}
}