<?php
require_once 'config.php';

$mode = $_GET['mode'] ?? 'new'; // new | edit
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
</head>
<body class="h-screen flex flex-col bg-gray-100">

<!-- HEADER -->
<header class="bg-blue-600 text-white px-6 py-3 font-bold flex justify-between">
    <span onclick="window.location.href='index.php'">Admin Page Builder</span>
    <div class="space-x-3 text-sm">
        <a href="add.php?mode=new" class="underline">➕ New Button</a>
        <a href="add.php?mode=edit" class="underline">✏️ Edit Button</a>
        <a href="category.php" class="underline">Add Category</a>
    </div>
</header>

<div class="flex flex-1 overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r p-4 space-y-3">
        <h2 class="font-semibold">Blocks</h2>

        <div draggable="true" data-type="title"
             ondragstart="drag(event)"
             class="p-2 bg-gray-100 cursor-move rounded">Title</div>

        <div draggable="true" data-type="text"
             ondragstart="drag(event)"
             class="p-2 bg-gray-100 cursor-move rounded">Text</div>

        <div draggable="true" data-type="image"
             ondragstart="drag(event)"
             class="p-2 bg-gray-100 cursor-move rounded">Image</div>
    </aside>

    <!-- CONTENT -->
    <main class="flex-1 p-6 overflow-y-auto">

        <?php if ($mode === 'edit'): ?>
            <!-- SELECT BUTTON -->
            <select class="border p-2 mb-4"
                    onchange="location.href='add.php?mode=edit&button_id='+this.value">
                <option value="">— Select Button —</option>
                <?php
                $items = $conn->query("SELECT id,title FROM menu_items");
                foreach ($items as $item) {
                    $sel = ($item['id'] == $button_id) ? 'selected' : '';
                    echo "<option value='{$item['id']}' $sel>{$item['title']}</option>";
                }
                ?>
            </select>
        <?php endif; ?>

        <form id="builderForm" enctype="multipart/form-data">

            <?php if ($mode === 'edit'): ?>
                <input type="hidden" name="button_id" value="<?= $button_id ?>">
            <?php endif; ?>
<select name="category_id" id="categorySelect" class="border p-2 w-full mb-3" required>
    <option value="">Select Category</option>
    <?php
    foreach ($conn->query("SELECT * FROM categories") as $c) {
        echo "<option value='{$c['id']}'>{$c['name']}</option>";
    }
    ?>
</select>
<div class="mt-4">

    <!-- Toggle Button -->
    <button
        type="button"
        onclick="toggleTree()"
        class="text-sm text-blue-600 underline mb-2"
    >
        Show Existing Buttons & Sub Buttons
    </button>

    <!-- Tree Container (Hidden by default) -->
    <div
        id="menuTreeWrapper"
        class="hidden border rounded p-2 bg-white text-sm"
    >
        <h3 class="font-semibold mb-2">
            Existing Buttons & Sub Buttons
        </h3>

        <div id="menuTree">
            Select category to see buttons
        </div>
    </div>

</div>


            <!-- TITLE -->
            <input name="title"
                   value="<?= htmlspecialchars($title) ?>"
                   placeholder="Button Title"
                   class="border p-2 w-full mb-3"
                   required>

            <!-- PARENT -->
            <select name="parent_id" id="parentSelect" class="border p-2 w-full mb-4">
                <option value="">Main Button</option>
                <?php
                foreach ($conn->query("SELECT id,title FROM menu_items") as $it) {
                    $sel = ($it['id'] == $parent_id) ? 'selected' : '';
                    echo "<option value='{$it['id']}' $sel>{$it['title']}</option>";
                }
                ?>
            </select>

            <!-- DROP ZONE -->
            <div id="dropZone"
                 ondrop="drop(event)"
                 ondragover="allowDrop(event)"
                 class="min-h-[350px] border-2 border-dashed bg-white p-4 space-y-4">
            </div>

            <button class="mt-6 bg-blue-600 text-white px-4 py-2 rounded">
                <?= $mode === 'edit' ? 'Update Button' : 'Save New Button' ?>
            </button>

            <input type="hidden" name="content_json" id="content_json">
        </form>

    </main>
</div>

<script>
let content = <?= json_encode($content) ?>;
render();

function allowDrop(e){e.preventDefault();}
function drag(e){e.dataTransfer.setData("type", e.target.dataset.type);}

function drop(e){
    e.preventDefault();
    const type=e.dataTransfer.getData("type");

    if(type==='title') content.push({type:'title',value:'Title'});
    if(type==='text') content.push({type:'text',value:'Text...'});
    if(type==='image') content.push({type:'image',value:null});

    render();
}

function render(){
    const z=document.getElementById('dropZone');
    z.innerHTML='';
    content.forEach((b,i)=>{
        if(b.type==='title'){
            z.innerHTML+=`<input class="border p-2 w-full text-xl"
             value="${b.value}"
             oninput="content[${i}].value=this.value">`;
        }
        if(b.type==='text'){
            z.innerHTML+=`<textarea class="border p-2 w-full"
             oninput="content[${i}].value=this.value">${b.value}</textarea>`;
        }
        if(b.type==='image'){
            z.innerHTML+=`<input type="file"
             onchange="content[${i}].file=this.files[0]">`;
        }
    });
}

document.getElementById('builderForm').addEventListener('submit',e=>{
    e.preventDefault();
    const fd=new FormData(e.target);

    const final=[];
    content.forEach(b=>{
        if(b.type==='image' && b.file){
            const name=Date.now()+'_'+b.file.name;
            fd.append('images[]',b.file,name);
            final.push({type:'image',value:name});
        }else{
            final.push(b);
        }
    });

    fd.set('content_json',JSON.stringify(final));

    fetch('save_update.php',{method:'POST',body:fd})
        .then(()=>alert('Saved Successfully'));
});


const categorySelect = document.getElementById('categorySelect');
const parentSelect   = document.getElementById('parentSelect');

categorySelect.addEventListener('change', function () {

    const categoryId = this.value;

    // Reset parent dropdown
    parentSelect.innerHTML = '<option value="">Main Button</option>';

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

const menuTree = document.getElementById('menuTree');

categorySelect.addEventListener('change', function () {

    const categoryId = this.value;
    menuTree.innerHTML = 'Loading...';

    if (!categoryId) {
        menuTree.innerHTML = 'Select category to see buttons';
        return;
    }

    fetch('get_menu_tree.php?category_id=' + categoryId)
        .then(res => res.text())
        .then(html => {
            menuTree.innerHTML = html || 'No buttons found';
        });
});

function toggleTree() {
    const wrapper = document.getElementById('menuTreeWrapper');
    const btn = event.target;

    wrapper.classList.toggle('hidden');

    btn.textContent = wrapper.classList.contains('hidden')
        ? 'Show Existing Buttons & Sub Buttons'
        : 'Hide Existing Buttons & Sub Buttons';
}
</script>

</body>
</html>
