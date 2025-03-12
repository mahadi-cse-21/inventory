<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/vendor.php';

$database = new Database();
$db = $database->getConnection();

$vendor = new Vendor($db);

$stmt = $vendor->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $vendors_arr = array();
    $vendors_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $vendor_item = array(
            "id" => $id,
            "vendor_name" => $vendor_name,
            "contact_info" => $contact_info,
            "created_at" => $created_at,
            "updated_at" => $updated_at
        );
        array_push($vendors_arr["records"], $vendor_item);
    }

    http_response_code(200);
    echo json_encode($vendors_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No vendors found."));
}
?>
