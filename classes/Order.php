<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Product.php';

class Order {
    private PDO $db;
    private Product $productModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->productModel = new Product();
    }

    public function create(int $userId, string $shippingAddress, string $shippingPhone, array $cartItems): int {
        if (empty($cartItems)) {
            throw new InvalidArgumentException("Cannot place an order with an empty cart.");
        }

        try {
            $this->db->beginTransaction();

            $totalAmount = 0.0;
            foreach ($cartItems as $item) {
                $product = $this->productModel->getById($item['product_id']);
                if (!$product) {
                    throw new RuntimeException("Product ID {$item['product_id']} not found.");
                }
                if ($product['stock'] < $item['quantity']) {
                    throw new RuntimeException("Insufficient stock for product: {$product['name']}.");
                }
                $totalAmount += (float)$product['price'] * $item['quantity'];
            }

            $orderStmt = $this->db->prepare("
                INSERT INTO orders (user_id, shipping_address, shipping_phone, total_amount, payment_method, payment_status, order_status)
                VALUES (:user_id, :shipping_address, :shipping_phone, :total_amount, 'cod', 'unpaid', 'pending')
            ");
            $orderStmt->execute([
                ':user_id'          => $userId,
                ':shipping_address' => $shippingAddress,
                ':shipping_phone'   => $shippingPhone,
                ':total_amount'     => $totalAmount
            ]);

            $orderId = (int)$this->db->lastInsertId();

            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)
            ");

            foreach ($cartItems as $item) {
                $product = $this->productModel->getById($item['product_id']);
                $itemStmt->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => $item['product_id'],
                    ':quantity'   => $item['quantity'],
                    ':price'      => $product['price']
                ]);

                if (!$this->productModel->updateStock($item['product_id'], $item['quantity'])) {
                    throw new RuntimeException("Failed to decrement stock for product ID: {$item['product_id']}.");
                }
            }

            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT o.*, u.name AS customer_name, u.email AS customer_email 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public function getItems(int $orderId): array {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name AS product_name, pi.image_path
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function getByUserId(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            WHERE user_id = :user_id 
            ORDER BY id DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT o.*, u.name AS customer_name 
            FROM orders o
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $orderStatus, ?string $paymentStatus = null): bool {
        $allowedOrderStatus = ['pending', 'delivered'];
        $allowedPaymentStatus = ['unpaid', 'paid'];

        if (!in_array($orderStatus, $allowedOrderStatus, true)) {
            return false;
        }

        if ($paymentStatus !== null) {
            if (!in_array($paymentStatus, $allowedPaymentStatus, true)) {
                return false;
            }
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET order_status = :order_status, payment_status = :payment_status 
                WHERE id = :id
            ");
            return $stmt->execute([
                ':order_status'   => $orderStatus,
                ':payment_status' => $paymentStatus,
                ':id'             => $id
            ]);
        }

        $stmt = $this->db->prepare("
            UPDATE orders 
            SET order_status = :order_status 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':order_status' => $orderStatus,
            ':id'           => $id
        ]);
    }

    public function count(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
        return (int)$stmt->fetchColumn();
    }

    public function getTotalRevenue(): float {
        $stmt = $this->db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'");
        return (float)$stmt->fetchColumn();
    }
}