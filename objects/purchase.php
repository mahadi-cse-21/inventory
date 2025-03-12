<?php
class Purchase {
    private $conn;
    private $table_name = "purchases";

    public $id;
    public $item_id;
    public $vendor_id;
    public $purchase_date;
    public $quantity;
    public $cost;
    public $invoice;
    public $warranty_details;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT id, item_id, vendor_id, purchase_date, quantity, cost, invoice, warranty_details, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET item_id=:item_id, vendor_id=:vendor_id, purchase_date=:purchase_date, quantity=:quantity, cost=:cost, invoice=:invoice, warranty_details=:warranty_details";
        $stmt = $this->conn->prepare($query);
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->vendor_id = htmlspecialchars(strip_tags($this->vendor_id));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->invoice = htmlspecialchars(strip_tags($this->invoice));
        $this->warranty_details = htmlspecialchars(strip_tags($this->warranty_details));
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":vendor_id", $this->vendor_id);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":cost", $this->cost);
        $stmt->bindParam(":invoice", $this->invoice);
        $stmt->bindParam(":warranty_details", $this->warranty_details);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " SET item_id = :item_id, vendor_id = :vendor_id, purchase_date = :purchase_date, quantity = :quantity, cost = :cost, invoice = :invoice, warranty_details = :warranty_details WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->vendor_id = htmlspecialchars(strip_tags($this->vendor_id));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->cost = htmlspecialchars(strip_tags($this->cost));
        $this->invoice = htmlspecialchars(strip_tags($this->invoice));
        $this->warranty_details = htmlspecialchars(strip_tags($this->warranty_details));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":vendor_id", $this->vendor_id);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":cost", $this->cost);
        $stmt->bindParam(":invoice", $this->invoice);
        $stmt->bindParam(":warranty_details", $this->warranty_details);
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
