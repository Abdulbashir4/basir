<?php
require_once 'config.php';

$id = intval($_GET['id']);

// Check if category has menu items
$check = $conn->prepare(
    "SELECT COUNT(*) FROM menu_items WHERE category_id=?"
);
$check->execute([$id]);

if ($check->fetchColumn() > 0) {
    die("এই category-র অধীনে menu আছে, আগে সেগুলো delete করুন");
}

// Safe delete
$stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
$stmt->execute([$id]);

header("Location: category.php");
