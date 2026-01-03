<?php
require_once 'config.php';

$mode = $_GET['mode'] ?? 'new';
$button_id = $_GET['button_id'] ?? null;

$title = '';
$parent_id = '';
$content = [];

if ($mode === 'edit' && $button_id) {
    $row = $conn->query(
        "SELECT * FROM menu_items WHERE id=".(int)$button_id
    )->fetch(PDO::FETCH_ASSOC);

    $title = $row['title'];
    $parent_id = $row['parent_id'];
    $content = json_decode($row['description'], true) ?? [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Page Builder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
.resize-handle:hover {
    background: #1d4ed8;
}
</style>

</head>

<body class="h-screen flex flex-col bg-gray-100">

<!-- HEADER -->
<header class="bg-blue-600 text-white px-6 py-3 font-bold flex justify-between">
    <span onclick="location.href='index.php'" class="cursor-pointer">
        Admin Page Builder
    </span>
    <div class="space-x-3 text-sm">
        <a href="add.php?mode=new" class="underline">➕ New Button</a>
        <a href="add.php?mode=edit" class="underline">✏️ Edit Button</a>
        <a href="category.php" class="underline">Add Category</a>
    </div>
</header>

<div class="flex flex-1 overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r p-4 space-y-3">
        <h2 class="font-semibold mb-2">Blocks</h2>

        <div draggable="true" data-type="text"
             ondragstart="drag(event)"
             class="p-2 bg-gray-100 cursor-move rounded">
             Text
        </div>

        <div draggable="true" data-type="image"
             ondragstart="drag(event)"
             class="p-2 bg-gray-100 cursor-move rounded">
             Image
        </div>
    </aside>

    <!-- CONTENT -->
    <main class="flex-1 p-6 overflow-auto">

        <?php if ($mode === 'edit'): ?>
        <select class="border p-2 mb-3"
                onchange="location.href='add.php?mode=edit&button_id='+this.value">
            <option value="">— Select Button —</option>
            <?php
            foreach ($conn->query("SELECT id,title FROM menu_items") as $it) {
                $sel = ($it['id'] == $button_id) ? 'selected' : '';
                echo "<option value='{$it['id']}' $sel>{$it['title']}</option>";
            }
            ?>
        </select>
        <?php endif; ?>

        <form id="builderForm" enctype="multipart/form-data">

            <?php if ($mode === 'edit'): ?>
                <input type="hidden" name="button_id" value="<?= $button_id ?>">
            <?php endif; ?>

                <!-- CATEGORY -->
<select name="category_id"  id="categorySelect"
        class="border p-2 w-full mb-3"
        required>
    <option value="">Select Category</option>
    <?php
    $editCategory = $row['category_id'] ?? '';
    foreach ($conn->query("SELECT * FROM categories") as $c) {
        $sel = ($c['id'] == $editCategory) ? 'selected' : '';
        echo "<option value='{$c['id']}' $sel>{$c['name']}</option>";
    }
    ?>
</select>


            <input name="title"
                   value="<?= htmlspecialchars($title) ?>"
                   placeholder="Button Title"
                   class="border p-2 w-full mb-3"
                   required>

            <select name="parent_id" id="parentSelect" class="border p-2 w-full mb-4">
                <option value="">Main Button</option>
                <?php
                foreach ($conn->query("SELECT id,title FROM menu_items") as $it) {
                    $sel = ($it['id'] == $parent_id) ? 'selected' : '';
                    echo "<option value='{$it['id']}' $sel>{$it['title']}</option>";
                }
                ?>
            </select>
            


            <!-- FREE POSITION CANVAS -->
            <div id="dropZone"
                 class="relative min-h-[500px] border-2 border-dashed bg-gray-50 mb-6">
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                <?= $mode === 'edit' ? 'Update Button' : 'Save Button' ?>
            </button>

            <input type="hidden" name="content_json">
        </form>
    </main>
</div>

<script>
/* ================= GLOBAL ================= */
let content = <?= json_encode($content) ?> || [];
let activeIndex = null;
let offsetX = 0, offsetY = 0;

/* ================= DRAG FROM SIDEBAR ================= */
function drag(e){
    e.dataTransfer.setData("type", e.target.dataset.type);
}

/* ================= DROPZONE ================= */
const dropZone = document.getElementById('dropZone');

dropZone.addEventListener('dragover', e => e.preventDefault());

dropZone.addEventListener('drop', e => {
    e.preventDefault();

    const type = e.dataTransfer.getData("type");

    const base = {
        left: 20,
        top: 20,
        width: 300,
        height: 150
    };

    if(type === 'text'){
        content.push({
            type: 'text',
            value: 'Text...',
            style: { ...base }
        });
    }

    if(type === 'image'){
        content.push({
            type: 'image',
            value: null,
            style: { ...base }
        });
    }

    render();
});

/* ================= IMAGE HANDLER ================= */
function handleImageSelect(e, index){
    const file = e.target.files[0];
    if(!file) return;

    content[index].file = file;
    render();
}

/* ================= RENDER ================= */
function render(){
    dropZone.innerHTML = '';

    content.forEach((b, i) => {

        const box = document.createElement('div');
        box.className =
          'absolute bg-white border rounded p-3 shadow cursor-move';

        box.style.left   = b.style.left + 'px';
        box.style.top    = b.style.top + 'px';
        box.style.width  = b.style.width + 'px';
        box.style.height = b.style.height + 'px';

        let html = '';

        /* TEXT BLOCK */
        if(b.type === 'text'){
            html = `
<textarea class="border p-2 w-full h-full resize-none"
 oninput="content[${i}].value=this.value">${b.value}</textarea>

<div class="mt-2 text-xs">
  Width
  <input type="number" value="${b.style.width}"
   oninput="content[${i}].style.width=this.value; render()">
  Height
  <input type="number" value="${b.style.height}"
   oninput="content[${i}].style.height=this.value; render()">
</div>
`;
        }

        /* IMAGE BLOCK */
        if(b.type === 'image'){
            let preview = '';

            if(b.file){
                preview = URL.createObjectURL(b.file);
            } else if(b.value){
                preview = 'uploads/' + b.value;
            }

            html = `
${preview ? `
<img src="${preview}"
 class="w-full h-full object-contain mb-2 rounded border">
` : `
<div class="w-full h-full flex items-center justify-center
 border border-dashed text-xs text-gray-400 mb-2">
 No image selected
</div>
`}

<input type="file"
 onchange="handleImageSelect(event, ${i})">

<div class="mt-2 text-xs">
  Width
  <input type="number" value="${b.style.width}"
   oninput="content[${i}].style.width=this.value; render()">
  Height
  <input type="number" value="${b.style.height}"
   oninput="content[${i}].style.height=this.value; render()">
</div>
`;
        }

        box.innerHTML = `
<button onclick="removeBlock(${i})"
 class="absolute top-1 right-1 text-red-500 text-xs z-10">✖</button>

<div class="resize-handle absolute bottom-0 right-0 w-3 h-3
 bg-blue-500 cursor-se-resize"></div>

${html}
`;

        /* MOVE ELEMENT */
        box.onmousedown = e => {
            if(e.target.classList.contains('resize-handle')) return;

            offsetX = e.clientX - b.style.left;
            offsetY = e.clientY - b.style.top;

            document.onmousemove = e => {
                b.style.left = e.clientX - offsetX;
                b.style.top  = e.clientY - offsetY;
                render();
            };

            document.onmouseup = () => {
                document.onmousemove = null;
            };
        };

        /* RESIZE ELEMENT */
        const handle = box.querySelector('.resize-handle');
        handle.onmousedown = e => {
            e.stopPropagation();

            const startX = e.clientX;
            const startY = e.clientY;
            const startW = b.style.width;
            const startH = b.style.height;

            document.onmousemove = e => {
                b.style.width  = Math.max(50, startW + (e.clientX - startX));
                b.style.height = Math.max(50, startH + (e.clientY - startY));
                render();
            };

            document.onmouseup = () => {
                document.onmousemove = null;
            };
        };

        dropZone.appendChild(box);
    });
}

/* ================= REMOVE ================= */
function removeBlock(i){
    if(confirm('Remove this element?')){
        content.splice(i,1);
        render();
    }
}

/* ================= SAVE ================= */
document.getElementById('builderForm')
  .addEventListener('submit', e => {
    e.preventDefault();
    const fd = new FormData(e.target);

    content.forEach(b => {
        if(b.type === 'image' && b.file){
            const name = Date.now() + '_' + b.file.name;
            fd.append('images[]', b.file, name);
            b.value = name;
            delete b.file;
        }
    });

    fd.set('content_json', JSON.stringify(content));

    fetch('save_update.php',{
        method:'POST',
        body:fd
    }).then(()=>alert('Saved Successfully'));
});

/* ================= INIT ================= */
render();

const categorySelect = document.getElementById('categorySelect');
const parentSelect   = document.getElementById('parentSelect');
const menuTree       = document.getElementById('menuTree');

/* ===== LOAD PARENT BUTTONS ===== */
categorySelect.addEventListener('change', function () {

    const categoryId = this.value;

    parentSelect.innerHTML =
        '<option value="">Main Button</option>';

    if (!categoryId) return;

    fetch('get_buttons_by_category.php?category_id=' + categoryId)
        .then(res => res.json())
        .then(data => {
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.title;
                parentSelect.appendChild(opt);
            });
        });
});

</script>



</body>
</html>
