<?php
require_once 'config.php';

$category_id = $_POST['category_id'] ?? null;
$title       = $_POST['title'];
$parent_id   = $_POST['parent_id'] ?: NULL;
$content     = $_POST['content_json'];

// ---------- Image Upload ----------
if (!empty($_FILES['images'])) {
    foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
        move_uploaded_file(
            $tmp,
            "uploads/" . $_FILES['images']['name'][$i]
        );
    }
}

// ---------- UPDATE ----------
if (!empty($_POST['button_id'])) {

    $stmt = $conn->prepare(
        "UPDATE menu_items 
         SET category_id=?, title=?, parent_id=?, description=? 
         WHERE id=?"
    );

    $stmt->execute([
        $category_id,
        $title,
        $parent_id,
        $content,
        $_POST['button_id']
    ]);

// ---------- INSERT NEW ----------
} else {

    $stmt = $conn->prepare(
        "INSERT INTO menu_items (category_id, title, parent_id, description)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $category_id,
        $title,
        $parent_id,
        $content
    ]);
}
