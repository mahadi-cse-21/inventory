<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/cart.php';

$database = new Database();
$db = $database->getConnection();

$cart = new Cart($db);

$stmt = $cart->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $carts_arr = array();
    $carts_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $cart_item = array(
            "cart_id" => $cart_id,
            "user_id" => $user_id,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($carts_arr["records"], $cart_item);
    }

    http_response_code(200);
    echo json_encode($carts_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No carts found."));
}
?>
