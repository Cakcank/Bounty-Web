<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db.php";

$userId = $_GET['userId'] ?? '';

if (!$userId) {
    echo json_encode(["error" => "userId is required"]);
    exit;
}

/* --------------------------------------------------
   GET MISSING POSTS
-------------------------------------------------- */
$stmt1 = $conn->prepare("
    SELECT 
        m.id,
        m.title,
        m.category,
        m.location,
        m.description,
        m.reward,
        m.duration,
        m.image,
        m.publisher,
        m.created_at,
        u.username AS publisherName
    FROM missing_posts m
    LEFT JOIN users u ON m.publisher = u.id
    WHERE m.publisher = ?
");
$stmt1->bind_param("i", $userId);
$stmt1->execute();
$result1 = $stmt1->get_result();

$missing = [];
while ($row = $result1->fetch_assoc()) {
    $missing[] = [
        "id"            => $row["id"],
        "title"         => $row["title"],
        "subtitle"      => $row["location"] ?? "Lokasi tidak tersedia", // Menambahkan fallback
        "description"   => $row["description"],
        "reward"        => $row["reward"],
        "duration"      => $row["duration"],
        "image"         => $row["image"],
        "publisher"     => $row["publisher"],
        "publisherName"=> $row["publisherName"],
        "created_at"    => $row["created_at"],
        "type"          => "missing"
    ];
}

/* --------------------------------------------------
   GET ADVERTISEMENT POSTS
-------------------------------------------------- */
$stmt2 = $conn->prepare("
    SELECT 
        a.id,
        a.title,
        a.category,
        a.content,
        a.duration,
        a.image,
        a.publisher,
        a.created_at,
        u.username AS publisherName
    FROM advertisement_posts a
    LEFT JOIN users u ON a.publisher = u.id
    WHERE a.publisher = ?
");
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$result2 = $stmt2->get_result();

$ads = [];
while ($row = $result2->fetch_assoc()) {
    $ads[] = [
        "id"            => $row["id"],
        "title"         => $row["title"],
        "subtitle"      => $row["category"],
        "description"   => $row["content"],
        "reward"        => 0,
        "duration"      => $row["duration"],
        "image"         => $row["image"],
        "publisher"     => $row["publisher"],
        "publisherName"=> $row["publisherName"],
        "created_at"    => $row["created_at"],
        "type"          => "advertisement"
    ];
}

/* --------------------------------------------------
   MERGE & RETURN
-------------------------------------------------- */

$response = array_merge($missing, $ads);

echo json_encode($response);
