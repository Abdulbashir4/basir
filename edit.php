<?php
require_once 'config.php';

$id = intval($_GET['id']);
$row = $conn->query(
    "SELECT * FROM menu_items WHERE id=$id"
)->fetch(PDO::FETCH_ASSOC);

$content = json_decode($row['description'], true) ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Content</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<form class="max-w-xl mx-auto mt-10 bg-white p-4 space-y-4"
      id="editForm"
      enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= $id ?>">

    <input
        value="<?= htmlspecialchars($row['title']) ?>"
        class="border p-2 w-full"
        disabled
    >

    <div id="blocks" class="space-y-3"></div>

    <div class="flex gap-2">
        <button type="button" onclick="addText()"
                class="bg-gray-200 px-3 py-1 rounded">
            ➕ Add Text
        </button>

        <button type="button" onclick="addImage()"
                class="bg-gray-200 px-3 py-1 rounded">
            🖼️ Add Image
        </button>
    </div>

    <button class="bg-blue-600 text-white px-4 py-2 w-full">
        Update
    </button>
</form>

<script>
let blocks = <?= json_encode($content) ?>;

function addText() {
    blocks.push({ type: 'text', value: '' });
    render();
}

function addImage() {
    blocks.push({ type: 'image', value: null });
    render();
}

function render() {
    const box = document.getElementById('blocks');
    box.innerHTML = '';

    blocks.forEach((b, i) => {

        if (b.type === 'text') {
            box.innerHTML += `
            <textarea class="border p-2 w-full"
              oninput="blocks[${i}].value=this.value">${b.value ?? ''}</textarea>
            `;
        }

        if (b.type === 'image') {
            box.innerHTML += `
            <div class="space-y-1">
                ${b.value ? `<img src="../uploads/${b.value}" class="w-full rounded">` : ''}
                <input type="file"
                  onchange="blocks[${i}].file=this.files[0]"
                  class="border p-2 w-full">
            </div>
            `;
        }
    });
}

render();

document.getElementById('editForm').addEventListener('submit', e => {
    e.preventDefault();

    const fd = new FormData();
    fd.append('id', <?= $id ?>);

    const content = [];

    blocks.forEach(b => {
        if (b.type === 'text') {
            content.push({ type: 'text', value: b.value });
        }

        if (b.type === 'image') {
            if (b.file) {
                const name = Date.now() + '_' + b.file.name;
                fd.append('images[]', b.file, name);
                content.push({ type: 'image', value: name });
            } else {
                content.push({ type: 'image', value: b.value });
            }
        }
    });

    fd.append('content_json', JSON.stringify(content));

    fetch('update.php', { method: 'POST', body: fd })
        .then(() => location.href = '../index.php');
});
</script>

</body>
</html>
