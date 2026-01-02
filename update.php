<?php
require_once 'config.php';

$id = intval($_POST['id']);
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
    "UPDATE menu_items SET description=? WHERE id=?"
);
$stmt->execute([$content_json, $id]);
