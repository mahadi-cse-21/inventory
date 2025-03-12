<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/item.php';

$database = new Database();
$db = $database->getConnection();

$item = new Item($db);

$stmt = $item->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $items_arr = array();
    $items_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $item_item = array(
            "id" => $id,
            "item_code" => $item_code,
            "name" => $name,
            "description" => $description,
            "category_id" => $category_id,
            "manufacturer" => $manufacturer,
            "purchase_date" => $purchase_date,
            "cost" => $cost,
            "quantity" => $quantity,
            "borrowable" => $borrowable,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($items_arr["records"], $item_item);
    }

    http_response_code(200);
    echo json_encode($items_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No items found."));
}
?>
