<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $user_id;
    public $full_name;
    public $email;
    public $password;
    public $role_id;
    public $status;
    public $profile_photo;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, user_id, full_name, email, role_id, status, profile_photo, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET user_id=:user_id, full_name=:full_name, email=:email, password=:password, role_id=:role_id, status=:status, profile_photo=:profile_photo";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->role_id = htmlspecialchars(strip_tags($this->role_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->profile_photo = htmlspecialchars(strip_tags($this->profile_photo));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role_id", $this->role_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":profile_photo", $this->profile_photo);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id, full_name = :full_name, email = :email, password = :password, role_id = :role_id, status = :status, profile_photo = :profile_photo WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->role_id = htmlspecialchars(strip_tags($this->role_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->profile_photo = htmlspecialchars(strip_tags($this->profile_photo));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role_id", $this->role_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":profile_photo", $this->profile_photo);
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
