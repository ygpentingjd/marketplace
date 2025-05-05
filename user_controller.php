<?php
class UserController {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function viewCart($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addToCart($user_id, $product_id, $quantity) {
        $stmt = $this->conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        return $stmt->execute();
    }

    public function removeFromCart($user_id, $product_id) {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        return $stmt->execute();
    }

    public function checkout($user_id) {
        $this->conn->begin_transaction();
        try {
            $cart_items = $this->viewCart($user_id);
            if (empty($cart_items)) {
                throw new Exception("Keranjang belanja kosong");
            }

            $total = 0;
            foreach ($cart_items as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
            $stmt->bind_param("id", $user_id, $total);
            $stmt->execute();
            $order_id = $this->conn->insert_id;

            foreach ($cart_items as $item) {
                $stmt = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }

            $this->removeFromCart($user_id, $item['product_id']);

            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error checkout: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderHistory($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}