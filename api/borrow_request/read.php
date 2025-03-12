<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/borrow_request.php';

$database = new Database();
$db = $database->getConnection();

$borrow_request = new BorrowRequest($db);

$stmt = $borrow_request->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $borrow_requests_arr = array();
    $borrow_requests_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $borrow_request_item = array(
            "id" => $id,
            "user_id" => $user_id,
            "item_id" => $item_id,
            "purpose" => $purpose,
            "quantity" => $quantity,
            "duration" => $duration,
            "status_id" => $status_id,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($borrow_requests_arr["records"], $borrow_request_item);
    }

    http_response_code(200);
    echo json_encode($borrow_requests_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No borrow requests found."));
}
?>
