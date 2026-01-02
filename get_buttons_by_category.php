<?php
require_once 'config.php';

$category_id = intval($_GET['category_id']);

$stmt = $conn->prepare(
    "SELECT id, title 
     FROM menu_items 
     WHERE category_id=? AND parent_id IS NULL
     ORDER BY title"
);
$stmt->execute([$category_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
