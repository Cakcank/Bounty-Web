<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../db.php";

$category = $_GET['category'] ?? null;

if ($category) {
    $stmt = $conn->prepare("
        SELECT a.*, u.username AS publisher_name
        FROM advertisement_posts a
        LEFT JOIN users u ON a.publisher = u.id
        WHERE a.category = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->bind_param("s", $category);
} else {
    $stmt = $conn->prepare("
        SELECT a.*, u.username AS publisher_name
        FROM advertisement_posts a
        LEFT JOIN users u ON a.publisher = u.id
        ORDER BY a.created_at DESC
    ");
}

$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_all(MYSQLI_ASSOC);

echo json_encode($data);  // ⬅️ FIX UTAMA

$stmt->close();

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Untuk OPTIONS (preflight) biar tidak error
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../db.php";
$id = $_GET['id'] ?? null;
if (!$id) { http_response_code(400); echo json_encode(["error"=>"id required"]); exit; }
$stmt = $conn->prepare("SELECT * FROM advertisement_posts WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
echo json_encode($res->fetch_assoc() ?: []);
$stmt->close();
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Untuk OPTIONS (preflight) biar tidak error
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../db.php";
$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;
if (!$id) { http_response_code(400); echo json_encode(["error"=>"id required"]); exit; }

$fields = ['title','category','location','description','reward','duration','image','phone','address'];
$set = [];
$types = "";
$params = [];

foreach ($fields as $f) {
    if (isset($input[$f])) {
        $set[] = "$f = ?";
        $types .= is_numeric($input[$f]) ? (strpos($f,'reward')!==false ? "d" : "s") : "s";
        $params[] = $input[$f];
    }
}
if (count($set) === 0) {
    echo json_encode(["status"=>"nothing changed"]); exit;
}
$types .= "i";
$params[] = $id;

$sql = "UPDATE missing_posts SET " . implode(",", $set) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();
echo json_encode(["status"=>"updated"]);
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../db.php";
header("Content-Type: application/json");

// ===========================
// Ambil data dari FormData()
// ===========================
$title     = $_POST['title'] ?? null;
$category  = $_POST['category'] ?? null;
$content   = $_POST['content'] ?? null;
$publisher = $_POST['publisher'] ?? null; // <-- INI ID (INTEGER)
$duration  = $_POST['duration'] ?? null;

$image = null;

// ===========================
// Upload gambar (file)
// ===========================
if (!empty($_FILES['image'])) {
    $tmp  = $_FILES['image']['tmp_name'];
    $name = time() . "_" . preg_replace("/[^A-Za-z0-9\.\-]/", "", $_FILES['image']['name']);

    if (!is_dir("../uploads")) {
        mkdir("../uploads", 0777, true);
    }

    move_uploaded_file($tmp, "../uploads/" . $name);
    $image = $name;
}
// ===========================
// Base64 fallback
// ===========================
else if (!empty($_POST['image'])) {
    $image = $_POST['image'];
}

// ===========================
// VALIDASI
// ===========================
if (!$title || !$category || !$publisher) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "missing fields"]);
    exit;
}

// ===========================
// INSERT data
// ===========================
$stmt = $conn->prepare("
    INSERT INTO advertisement_posts 
    (title, category, content, image, publisher, duration, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param("ssssii",
    $title,
    $category,
    $content,
    $image,
    $publisher,
    $duration
);


$ok = $stmt->execute();
$id = $stmt->insert_id;
$stmt->close();

if ($ok) {
    echo json_encode([
        "success" => true,
        "message" => "created",
        "id" => $id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "database error"
    ]);
}
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Untuk OPTIONS (preflight) biar tidak error
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../db.php";
$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;
if (!$id) { http_response_code(400); echo json_encode(["error"=>"id required"]); exit; }
$stmt = $conn->prepare("DELETE FROM missing_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
echo json_encode(["status"=>"deleted"]);
