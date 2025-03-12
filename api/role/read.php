<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/role.php';

$database = new Database();
$db = $database->getConnection();

$role = new Role($db);

$stmt = $role->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $roles_arr = array();
    $roles_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $role_item = array(
            "id" => $id,
            "role_name" => $role_name
        );
        array_push($roles_arr["records"], $role_item);
    }

    http_response_code(200);
    echo json_encode($roles_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No roles found."));
}
?>
