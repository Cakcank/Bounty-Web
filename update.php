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

if (!isset($_POST['id']) || !isset($_POST['table'])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit();
}

$id = intval($_POST['id']);
$table = $_POST['table'];

$title       = $_POST['title'] ?? "";
$category    = $_POST['category'] ?? "";
$location    = $_POST['location'] ?? "";
$description = $_POST['description'] ?? "";
$content     = $_POST['content'] ?? "";
$reward      = floatval($_POST['reward'] ?? 0);
$duration    = intval($_POST['duration'] ?? 0);

// Old image = filename (bukan URL)
$old_image = $_POST['old_image'] ?? "";
$image = $old_image;

$uploadDir = __DIR__ . "/uploads/";
$baseURL   = "uploads/";

// pastikan folder ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// upload file baru
if (!empty($_FILES['image']['name'])) {

    $newFileName = time() . "_" . basename($_FILES['image']['name']);
    $targetPath  = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {

        // hapus file lama
        if (!empty($old_image)) {
            $oldPath = $uploadDir . $old_image;
            if (file_exists($oldPath)) unlink($oldPath);
        }

        // simpan hanya filename
        $image = $newFileName;
    }
}

// UPDATE QUERY
if ($table === "missing") {

    $sql = "UPDATE missing_posts 
            SET title=?, category=?, location=?, description=?, reward=?, image=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisi",
        $title, $category, $location, $description, $reward, $image, $id
    );

} elseif ($table === "ads") {

    $sql = "UPDATE advertisement_posts 
            SET title=?, category=?, content=?, image=?, duration=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii",
        $title, $category, $content, $image, $duration, $id
    );

} else {
    echo json_encode(["error" => "Invalid table"]);
    exit();
}

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "image"  => $image // now filename only
    ]);
} else {
    echo json_encode(["error" => $stmt->error]);
}
?>
