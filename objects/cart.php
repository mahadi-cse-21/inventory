<?php
class Cart {
    private $conn;
    private $table_name = "carts";

    public $cart_id;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT cart_id, user_id, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $stmt->bindParam(":user_id", $this->user_id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->cart_id = htmlspecialchars(strip_tags($this->cart_id));
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':cart_id', $this->cart_id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE cart_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->cart_id = htmlspecialchars(strip_tags($this->cart_id));
        $stmt->bindParam(1, $this->cart_id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
