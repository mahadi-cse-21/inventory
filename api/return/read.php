<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/return.php';

$database = new Database();
$db = $database->getConnection();

$return_item = new ReturnItem($db);

$stmt = $return_item->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $returns_arr = array();
    $returns_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $return_item_item = array(
            "id" => $id,
            "borrow_request_id" => $borrow_request_id,
            "return_date" => $return_date,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($returns_arr["records"], $return_item_item);
    }

    http_response_code(200);
    echo json_encode($returns_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No returns found."));
}
?>
