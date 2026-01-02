<?php
require_once 'config.php';

$id = intval($_GET['id']);

/**
 * STEP 1: check sub button আছে কিনা
 */
$check = $conn->prepare(
    "SELECT COUNT(*) FROM menu_items WHERE parent_id=?"
);
$check->execute([$id]);

if ($check->fetchColumn() > 0) {
    die("❌ এই বাটনের অধীনে সাব বাটন আছে। আগে সেগুলো ডিলিট করুন।");
}

/**
 * STEP 2: এই বাটনের description (JSON) আনুন
 */
$stmt = $conn->prepare(
    "SELECT description FROM menu_items WHERE id=?"
);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && !empty($row['description'])) {

    $content = json_decode($row['description'], true);

    if (is_array($content)) {
        foreach ($content as $block) {
            if (
                isset($block['type']) &&
                $block['type'] === 'image' &&
                !empty($block['value'])
            ) {
                $file = 'uploads/' . $block['value'];

                if (file_exists($file)) {
                    unlink($file); // 🔥 image delete
                }
            }
        }
    }
}

/**
 * STEP 3: এখন DB থেকে menu item ডিলিট করুন
 */
$del = $conn->prepare("DELETE FROM menu_items WHERE id=?");
$del->execute([$id]);

header("Location: add.php");
exit;
