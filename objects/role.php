<?php
class Role {
    private $conn;
    private $table_name = "roles";

    public $id;
    public $role_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, role_name FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET role_name=:role_name";
        $stmt = $this->conn->prepare($query);
        $this->role_name = htmlspecialchars(strip_tags($this->role_name));
        $stmt->bindParam(":role_name", $this->role_name);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET role_name = :role_name WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->role_name = htmlspecialchars(strip_tags($this->role_name));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':role_name', $this->role_name);
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
