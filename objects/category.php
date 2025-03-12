<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $category_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, category_name FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET category_name=:category_name";
        $stmt = $this->conn->prepare($query);
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $stmt->bindParam(":category_name", $this->category_name);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET category_name = :category_name WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':category_name', $this->category_name);
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
