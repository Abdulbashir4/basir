<?php
require_once 'config.php';

$id = intval($_GET['id']);
$row = $conn->query(
    "SELECT * FROM menu_items WHERE id=$id"
)->fetch(PDO::FETCH_ASSOC);

$content = json_decode($row['description'], true);
?>

<div class="space-y-4">

<h1 class="text-2xl font-bold"><?= htmlspecialchars($row['title']) ?></h1>

<?php foreach ($content as $block): ?>

    <?php if ($block['type'] === 'text'): ?>
        <p class="text-gray-700 leading-relaxed">
            <?= nl2br(htmlspecialchars($block['value'])) ?>
        </p>
    <?php endif; ?>

    <?php if ($block['type'] === 'image'): ?>
        <img src="uploads/<?= htmlspecialchars($block['value']) ?>"
             class="w-full rounded shadow border">
    <?php endif; ?>

<?php endforeach; ?>

</div>
