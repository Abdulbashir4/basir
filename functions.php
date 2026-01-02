<?php function renderMenu($parent_id = NULL, $category_id = NULL) {
    global $conn;

    if (!$category_id) return;

    $sql = is_null($parent_id)
        ? "SELECT * FROM menu_items
           WHERE parent_id IS NULL AND category_id=$category_id"
        : "SELECT * FROM menu_items
           WHERE parent_id=$parent_id AND category_id=$category_id";

    $items = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if ($items) {
        echo "<ul class='ml-4 space-y-1'>";
        foreach ($items as $item) {

            $hasChild = $conn->query(
                "SELECT id FROM menu_items
                 WHERE parent_id={$item['id']} AND category_id=$category_id
                 LIMIT 1"
            )->rowCount() > 0;

            echo "<li>";
            echo "<div onclick='loadContent({$item['id']})' class='flex justify-between px-2 py-1 hover:bg-gray-100 border border-black rounded'>";

            echo "<span 
                  class='cursor-pointer'>
                  {$item['title']}
                  </span>";

            if ($hasChild) {
                echo "<span onclick='toggle(this)'>▶</span>";
            }

            echo "</div>";

            if ($hasChild) {
                echo "<div class='hidden mt-2'>";
                renderMenu($item['id'], $category_id);
                echo "</div>";
            }

            echo "</li>";
        }
        echo "</ul>";
    }
}
?>