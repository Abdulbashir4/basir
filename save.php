<?php
require_once 'config.php';

$title = $_POST['title'];
$parent_id = $_POST['parent_id'] ?: NULL;
$content_json = $_POST['content_json'];

if (!empty($_FILES['images'])) {
    foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
        move_uploaded_file(
            $tmp,
            "../uploads/" . $_FILES['images']['name'][$i]
        );
    }
}

$stmt = $conn->prepare(
    "INSERT INTO menu_items (title, parent_id, description)
     VALUES (?, ?, ?)"
);
$stmt->execute([$title, $parent_id, $content_json]);

header("Location: add.php");
