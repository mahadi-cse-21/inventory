<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/purchase.php';

$database = new Database();
$db = $database->getConnection();

$purchase = new Purchase($db);

$stmt = $purchase->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $purchases_arr = array();
    $purchases_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $purchase_item = array(
            "id" => $id,
            "item_id" => $item_id,
            "vendor_id" => $vendor_id,
            "purchase_date" => $purchase_date,
            "quantity" => $quantity,
            "cost" => $cost,
            "invoice" => $invoice,
            "warranty_details" => $warranty_details,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($purchases_arr["records"], $purchase_item);
    }

    http_response_code(200);
    echo json_encode($purchases_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No purchases found."));
}
?>
