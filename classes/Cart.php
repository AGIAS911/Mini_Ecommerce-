<?php
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/Product.php';

class Cart {
    private const SESSION_KEY = 'cart';
    private Product $productModel;

    public function __construct() {
        $this->productModel = new Product();
        Session::start();
    }

    public function add(int $productId, int $quantity = 1): bool {
        if ($quantity <= 0) {
            return false;
        }

        $product = $this->productModel->getById($productId);
        if (!$product) {
            return false;
        }

        $cart = Session::get(self::SESSION_KEY, []);
        $currentQty = $cart[$productId] ?? 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $product['stock']) {
            return false;
        }

        $cart[$productId] = $newQty;
        Session::set(self::SESSION_KEY, $cart);
        return true;
    }

    public function update(int $productId, int $quantity): bool {
        $cart = Session::get(self::SESSION_KEY, []);

        if (!isset($cart[$productId])) {
            return false;
        }

        if ($quantity <= 0) {
            $this->remove($productId);
            return true;
        }

        $product = $this->productModel->getById($productId);
        if (!$product || $quantity > $product['stock']) {
            return false;
        }

        $cart[$productId] = $quantity;
        Session::set(self::SESSION_KEY, $cart);
        return true;
    }

    public function remove(int $productId): void {
        $cart = Session::get(self::SESSION_KEY, []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::set(self::SESSION_KEY, $cart);
        }
    }

    public function clear(): void {
        Session::remove(self::SESSION_KEY);
    }

    public function getItems(): array {
        $cart = Session::get(self::SESSION_KEY, []);
        if (empty($cart)) {
            return [];
        }

        $items = [];
        foreach ($cart as $productId => $quantity) {
            $product = $this->productModel->getById((int)$productId);
            if ($product) {
                $subtotal = (float)$product['price'] * $quantity;
                $items[] = [
                    'product_id' => (int)$product['id'],
                    'name'       => $product['name'],
                    'price'      => (float)$product['price'],
                    'stock'      => (int)$product['stock'],
                    'image_path' => $product['image_path'],
                    'quantity'   => $quantity,
                    'subtotal'   => $subtotal
                ];
            } else {
                $this->remove((int)$productId);
            }
        }
        return $items;
    }

    public function getTotal(): float {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    public function getCount(): int {
        $cart = Session::get(self::SESSION_KEY, []);
        return array_sum($cart);
    }

    public function isEmpty(): bool {
        $cart = Session::get(self::SESSION_KEY, []);
        return empty($cart);
    }
}