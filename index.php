<?php
require_once 'config.php';
include 'functions.php';

/**
 * Get selected category
 * If not set, default to HTML
 */
if (isset($_GET['cat'])) {
    $cat_id = (int) $_GET['cat'];
} else {
    // Default category = HTML
    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute(['HTML']);
    $defaultCat = $stmt->fetch(PDO::FETCH_ASSOC);

    $cat_id = $defaultCat ? $defaultCat['id'] : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Basir Docs</title>
     <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="script.js"></script>
    <style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

</head>

<body class="bg-gray-100 h-screen flex flex-col">

<!-- ================= HEADER ================= -->
<!-- <header class="bg-blue-600 text-white px-6 py-4 flex gap-6">
    <?php
    $cats = $conn->query("SELECT * FROM categories");
    foreach ($cats as $cat):
    ?>
        <a href="?cat=<?= $cat['id'] ?>"
           class="<?= $cat_id == $cat['id'] ? 'underline font-bold' : '' ?>">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
    <a href="add.php">Input</a>
</header> -->
<header class="bg-blue-600 text-white px-4 py-3">
    <div
        id="categoryBar"
        class="flex gap-6 overflow-x-auto whitespace-nowrap scrollbar-hide cursor-grab active:cursor-grabbing"
    >
        <?php
    $cats = $conn->query("SELECT * FROM categories");
    foreach ($cats as $cat):
    ?>
        <a href="?cat=<?= $cat['id'] ?>"
           class="<?= $cat_id == $cat['id'] ? 'underline font-bold' : '' ?>">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
    <a href="add.php">Input</a>
    </div>
</header>


<!-- ================= MAIN AREA ================= -->
<div class="flex flex-1 overflow-hidden">

    <!-- ===== SIDEBAR ===== -->
    <aside class="w-72 bg-white p-4 ">
    <?php
    if ($cat_id) {
        renderMenu(null, $cat_id);
    } else {
        echo "<p>Select a category</p>";
    }
    ?>
</aside>


    <!-- ===== CONTENT AREA ===== -->
    <main class="flex-1 overflow-y-auto p-8">
        <div id="mainContent"
             class="bg-white rounded-lg shadow p-6 min-h-[300px]">

            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                Welcome 👋
            </h1>

            <p class="text-gray-600">
                Please select a menu item from the left sidebar to see details.
            </p>

        </div>
    </main>

</div>

<!-- ================= FOOTER (Optional) ================= -->
<footer class="bg-gray-200 text-center text-sm py-2">
    © <?= date('Y') ?> Basir Docs. All rights reserved.
</footer>
<script>
const slider = document.getElementById('categoryBar');

let isDown = false;
let startX;
let scrollLeft;

slider.addEventListener('mousedown', (e) => {
    isDown = true;
    slider.classList.add('active');
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
});

slider.addEventListener('mouseleave', () => {
    isDown = false;
    slider.classList.remove('active');
});

slider.addEventListener('mouseup', () => {
    isDown = false;
    slider.classList.remove('active');
});

slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 1.5; // scroll speed
    slider.scrollLeft = scrollLeft - walk;
});
</script>

</body>
</html>
