<?php
class ReturnItem {
    private $conn;
    private $table_name = "returns";

    public $id;
    public $borrow_request_id;
    public $return_date;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, borrow_request_id, return_date, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET borrow_request_id=:borrow_request_id, return_date=:return_date";
        $stmt = $this->conn->prepare($query);
        $this->borrow_request_id = htmlspecialchars(strip_tags($this->borrow_request_id));
        $this->return_date = htmlspecialchars(strip_tags($this->return_date));
        $stmt->bindParam(":borrow_request_id", $this->borrow_request_id);
        $stmt->bindParam(":return_date", $this->return_date);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET borrow_request_id = :borrow_request_id, return_date = :return_date WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->borrow_request_id = htmlspecialchars(strip_tags($this->borrow_request_id));
        $this->return_date = htmlspecialchars(strip_tags($this->return_date));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":borrow_request_id", $this->borrow_request_id);
        $stmt->bindParam(":return_date", $this->return_date);
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
