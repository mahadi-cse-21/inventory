<?php
class BorrowRequest {
    private $conn;
    private $table_name = "borrow_requests";

    public $id;
    public $user_id;
    public $item_id;
    public $purpose;
    public $quantity;
    public $duration;
    public $status_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, user_id, item_id, purpose, quantity, duration, status_id, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, item_id=:item_id, purpose=:purpose, quantity=:quantity, duration=:duration, status_id=:status_id";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->purpose = htmlspecialchars(strip_tags($this->purpose));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->duration = htmlspecialchars(strip_tags($this->duration));
        $this->status_id = htmlspecialchars(strip_tags($this->status_id));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":purpose", $this->purpose);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":duration", $this->duration);
        $stmt->bindParam(":status_id", $this->status_id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id, item_id = :item_id, purpose = :purpose, quantity = :quantity, duration = :duration, status_id = :status_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->purpose = htmlspecialchars(strip_tags($this->purpose));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->duration = htmlspecialchars(strip_tags($this->duration));
        $this->status_id = htmlspecialchars(strip_tags($this->status_id));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":purpose", $this->purpose);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":duration", $this->duration);
        $stmt->bindParam(":status_id", $this->status_id);
        $stmt->bindParam(':id', $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
