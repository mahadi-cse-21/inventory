<?php
class Item {
    private $conn;
    private $table_name = "items";

    public $id;
    public $item_code;
    public $name;
    public $description;
    public $category_id;
    public $manufacturer;
    public $purchase_date;
    public $cost;
    public $quantity;
    public $borrowable;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, item_code, name, description, category_id, manufacturer, purchase_date, cost, quantity, borrowable, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET item_code=:item_code, name=:name, description=:description, category_id=:category_id, manufacturer=:manufacturer, purchase_date=:purchase_date, cost=:cost, quantity=:quantity, borrowable=:borrowable";
        $stmt = $this->conn->prepare($query);
        $this->item_code = htmlspecialchars(strip_tags($this->item_code));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->manufacturer = htmlspecialchars(strip_tags($this->manufacturer));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->borrowable = htmlspecialchars(strip_tags($this->borrowable));
        $stmt->bindParam(":item_code", $this->item_code);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":manufacturer", $this->manufacturer);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":cost", $this->cost);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":borrowable", $this->borrowable);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET item_code = :item_code, name = :name, description = :description, category_id = :category_id, manufacturer = :manufacturer, purchase_date = :purchase_date, cost = :cost, quantity = :quantity, borrowable = :borrowable WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->item_code = htmlspecialchars(strip_tags($this->item_code));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->manufacturer = htmlspecialchars(strip_tags($this->manufacturer));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->borrowable = htmlspecialchars(strip_tags($this->borrowable));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":item_code", $this->item_code);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":manufacturer", $this->manufacturer);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":cost", $this->cost);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":borrowable", $this->borrowable);
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
