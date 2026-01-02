<?php
require_once 'config.php';

// Handle form submit
if (!empty($_POST['name'])) {
    $stmt = $conn->prepare(
        "INSERT INTO categories (name) VALUES (?)"
    );
    $stmt->execute([ $_POST['name'] ]);
    header("Location: category.php");
    exit;
}

// Fetch categories
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded shadow">

    <h1 class="text-xl font-bold mb-4">Add Category</h1>

    <form method="POST" class="flex gap-2 mb-6">
        <input
            name="name"
            placeholder="Category name (HTML, CSS...)"
            class="border p-2 flex-1"
            required
        >
        <button class="bg-blue-600 text-white px-4">
            Add
        </button>
    </form>

    <h2 class="font-semibold mb-2">Existing Categories</h2>

    <ul class="space-y-2">
        <?php foreach ($categories as $cat): ?>
            <li class="flex justify-between bg-gray-50 p-2 rounded">
                <span><?= htmlspecialchars($cat['name']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <ul class="space-y-2">
<?php foreach ($categories as $cat): ?>
    <li class="flex justify-between items-center bg-gray-50 p-2 rounded">
        <span><?= htmlspecialchars($cat['name']) ?></span>

        <a href="delete_category.php?id=<?= $cat['id'] ?>"
           onclick="return confirm('এই category delete করতে চান?')"
           class="text-red-600 text-sm">
           Delete
        </a>
    </li>
<?php endforeach; ?>
</ul>


</div>

</body>
</html>
