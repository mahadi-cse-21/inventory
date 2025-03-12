<?php
class BorrowStatus {
    private $conn;
    private $table_name = "borrow_status";

    public $id;
    public $status_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, status_name FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET status_name=:status_name";
        $stmt = $this->conn->prepare($query);
        $this->status_name = htmlspecialchars(strip_tags($this->status_name));
        $stmt->bindParam(":status_name", $this->status_name);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET status_name = :status_name WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->status_name = htmlspecialchars(strip_tags($this->status_name));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':status_name', $this->status_name);
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
