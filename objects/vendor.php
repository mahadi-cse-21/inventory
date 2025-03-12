<?php
class Vendor {
    private $conn;
    private $table_name = "vendors";

    public $id;
    public $vendor_name;
    public $contact_info;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, vendor_name, contact_info, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET vendor_name=:vendor_name, contact_info=:contact_info";
        $stmt = $this->conn->prepare($query);
        $this->vendor_name = htmlspecialchars(strip_tags($this->vendor_name));
        $this->contact_info = htmlspecialchars(strip_tags($this->contact_info));
        $stmt->bindParam(":vendor_name", $this->vendor_name);
        $stmt->bindParam(":contact_info", $this->contact_info);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET vendor_name = :vendor_name, contact_info = :contact_info WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->vendor_name = htmlspecialchars(strip_tags($this->vendor_name));
        $this->contact_info = htmlspecialchars(strip_tags($this->contact_info));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':vendor_name', $this->vendor_name);
        $stmt->bindParam(':contact_info', $this->contact_info);
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
