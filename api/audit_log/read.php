<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/audit_log.php';

$database = new Database();
$db = $database->getConnection();

$audit_log = new AuditLog($db);

$stmt = $audit_log->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $audit_logs_arr = array();
    $audit_logs_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $audit_log_item = array(
            "id" => $id,
            "user_id" => $user_id,
            "action" => $action,
            "created_at" => $created_at
        );
        array_push($audit_logs_arr["records"], $audit_log_item);
    }

    http_response_code(200);
    echo json_encode($audit_logs_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No audit logs found."));
}
?>
