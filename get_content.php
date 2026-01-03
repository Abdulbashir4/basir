<?php
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);

$row = $conn->query(
    "SELECT title, description FROM menu_items WHERE id=$id"
)->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "<p>Content not found</p>";
    exit;
}

$content = json_decode($row['description'], true) ?? [];
?>

<h1 class="text-2xl font-bold mb-4">
    <?= htmlspecialchars($row['title']) ?>
</h1>

<!-- CANVAS -->
<div style="
    position: relative;
    min-height: 600px;
    width: 100%;
    background: #fff;
">

<?php foreach ($content as $block): ?>

    <?php
        $style  = $block['style'] ?? [];
        $left   = (int)($style['left'] ?? 0);
        $top    = (int)($style['top'] ?? 0);
        $width  = (int)($style['width'] ?? 300);
        $height = (int)($style['height'] ?? 150);
    ?>

    <!-- TEXT BLOCK -->
    <?php if ($block['type'] === 'text'): ?>
        <div style="
            position:absolute;
            left:<?= $left ?>px;
            top:<?= $top ?>px;
            width:<?= $width ?>px;
            height:<?= $height ?>px;
            overflow:auto;
        ">
            <?= nl2br(htmlspecialchars($block['value'])) ?>
        </div>
    <?php endif; ?>

    <!-- IMAGE BLOCK -->
    <?php if ($block['type'] === 'image'): ?>
        <div style="
            position:absolute;
            left:<?= $left ?>px;
            top:<?= $top ?>px;
            width:<?= $width ?>px;
            height:<?= $height ?>px;
        ">
            <img
                src="uploads/<?= htmlspecialchars($block['value']) ?>"
                style="
                    width:100%;
                    height:100%;
                    object-fit:contain;
                "
            >
        </div>
    <?php endif; ?>

<?php endforeach; ?>

</div>
