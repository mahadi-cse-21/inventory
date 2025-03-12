<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/cart_item.php';

$database = new Database();
$db = $database->getConnection();

$cart_item = new CartItem($db);

$stmt = $cart_item->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $cart_items_arr = array();
    $cart_items_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $cart_item_item = array(
            "id" => $id,
            "cart_id" => $cart_id,
            "product_id" => $product_id,
            "quantity" => $quantity,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($cart_items_arr["records"], $cart_item_item);
    }

    http_response_code(200);
    echo json_encode($cart_items_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No cart items found."));
}
?>
