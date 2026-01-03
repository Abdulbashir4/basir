<?php function renderMenu($parent_id = null, $cat_id) {
    global $conn;

    $sql = "SELECT * FROM menu_items 
            WHERE parent_id ".($parent_id ? "= $parent_id" : "IS NULL")."
            AND category_id = $cat_id
            ORDER BY id";

    $items = $conn->query($sql);

    if ($items->rowCount() > 0) {
        echo "<ul class='space-y-1 pl-2'>";

        foreach ($items as $item) {
            echo "<li>";

            echo "<a href='#'
                    class='block px-2 py-1 rounded hover:bg-gray-100 border border-black px-2 py-1 mb-2'
                    onclick='loadContent({$item['id']}); return false;'>
                    ".htmlspecialchars($item['title'])."
                  </a>";

            // recursive call for sub menu
            renderMenu($item['id'], $cat_id);

            echo "</li>";
        }

        echo "</ul>";
    }
}

?>