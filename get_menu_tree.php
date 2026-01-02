<?php
require_once 'config.php';

$category_id = intval($_GET['category_id']);

function buildTree($parent_id, $category_id, $conn) {

    $sql = is_null($parent_id)
        ? "SELECT * FROM menu_items 
           WHERE parent_id IS NULL AND category_id=$category_id"
        : "SELECT * FROM menu_items 
           WHERE parent_id=$parent_id AND category_id=$category_id";

    $items = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) return '';

    $html = '<ul class="ml-4 space-y-1">';

    foreach ($items as $item) {

        // check child
        $hasChild = $conn->query(
            "SELECT id FROM menu_items 
             WHERE parent_id={$item['id']} LIMIT 1"
        )->rowCount() > 0;

        $html .= '<li>';
        $html .= '<div class="flex justify-between items-center bg-gray-50 px-2 py-1 rounded">';

        $html .= '<span>'.$item['title'].'</span>';

        $html .= '<a 
            href="delete_menu.php?id='.$item['id'].'"
            onclick="return confirm(\'এই বাটনটি ডিলিট করতে চান?\')"
            class="text-red-600 text-xs"
        >Delete</a>';

        $html .= '</div>';

        if ($hasChild) {
            $html .= buildTree($item['id'], $category_id, $conn);
        }

        $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
}

echo buildTree(null, $category_id, $conn);
