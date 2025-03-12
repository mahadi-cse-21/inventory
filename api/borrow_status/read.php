<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/borrow_status.php';

$database = new Database();
$db = $database->getConnection();

$borrow_status = new BorrowStatus($db);

$stmt = $borrow_status->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $borrow_statuses_arr = array();
    $borrow_statuses_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $borrow_status_item = array(
            "id" => $id,
            "status_name" => $status_name
        );
        array_push($borrow_statuses_arr["records"], $borrow_status_item);
    }

    http_response_code(200);
    echo json_encode($borrow_statuses_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No borrow statuses found."));
}
?>
