<?php
require_once 'config.php';

/*
|--------------------------------------------------------------------------
| Allowed Tables (Security)
|--------------------------------------------------------------------------
*/
$tables = ['categories', 'menu_items'];

/*
|--------------------------------------------------------------------------
| Delete Row
|--------------------------------------------------------------------------
*/
if (isset($_POST['delete_row'])) {

    $table = $_POST['table'] ?? '';
    $id    = $_POST['id'] ?? '';

    if (!in_array($table, $tables)) {
        die('Invalid table');
    }

    if (!is_numeric($id)) {
        die('Invalid ID');
    }

    $stmt = $conn->prepare("DELETE FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: test.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DB Data Manager</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 p-6">

<h1 class="text-2xl font-bold mb-6">Database Data Manager</h1>

<div class="space-y-8">

<?php foreach ($tables as $table): ?>
    <div class="bg-white shadow rounded-lg p-4">

        <h2 class="text-lg font-semibold mb-4">
            Table: <span class="text-blue-600"><?= htmlspecialchars($table) ?></span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-2/10 border border-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <?php
                        // Get columns
                        $cols = $conn->query("SHOW COLUMNS FROM `$table`");
                        $columns = $cols->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($columns as $col):
                        ?>
                            <th class="p-2 border"><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                        <th class="p-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Get data
                $data = $conn->query("SELECT * FROM `$table` ORDER BY id DESC");
                while ($row = $data->fetch(PDO::FETCH_ASSOC)):
                ?>
                    <tr class="hover:bg-gray-50">
                        <?php foreach ($columns as $col): ?>
                            <td class="p-2 border">
                                <?= htmlspecialchars((string)$row[$col]) ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="p-2 border text-center">
                            <form method="POST"
                                  onsubmit="return confirm('Are you sure? This row will be permanently deleted!')">
                                <input type="hidden" name="table" value="<?= $table ?>">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button
                                    name="delete_row"
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>

                <?php if ($data->rowCount() === 0): ?>
                    <tr>
                        <td colspan="<?= count($columns) + 1 ?>"
                            class="p-4 text-center text-gray-500">
                            No data found
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>
<?php endforeach; ?>

</div>

</body>
</html>
