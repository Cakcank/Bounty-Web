<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id']) || !isset($data['table'])) {
    echo json_encode(["error" => "id and table required"]);
    exit();
}

$id = intval($data['id']);
$table = $data['table'];

if ($table === "ads") {
    $sql = "DELETE FROM advertisement_posts WHERE id=?";
}

else if ($table === "missing") {
    $sql = "DELETE FROM missing_posts WHERE id=?";
}

else {
    echo json_encode(["error" => "Invalid table"]);
    exit();
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["error" => $conn->error]);
}
?>
