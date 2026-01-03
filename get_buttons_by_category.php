<?php
require_once 'config.php';

$cat_id = (int)($_GET['category_id'] ?? 0);

function buildTree($parent_id, $cat_id, $level = 0) {
    global $conn;

    $sql = "SELECT id, title FROM menu_items
            WHERE category_id = $cat_id
            AND parent_id " . ($parent_id ? "= $parent_id" : "IS NULL") . "
            ORDER BY title";

    $items = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($items as $item) {
        $result[] = [
            'id'    => $item['id'],
            'title' => str_repeat('— ', $level) . $item['title']
        ];

        // 🔁 recursive call (sub-button)
        $children = buildTree($item['id'], $cat_id, $level + 1);
        $result = array_merge($result, $children);
    }

    return $result;
}

echo json_encode(buildTree(null, $cat_id));
