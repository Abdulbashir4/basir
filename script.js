function loadContent(id) {
    fetch('get_content.php?id=' + id)
        .then(res => res.text())
        .then(data => {
            document.getElementById('mainContent').innerHTML = data;
        });
}

function toggle(el) {
    const child = el.parentElement.nextElementSibling;
    if (child) {
        child.classList.toggle('hidden');
        el.innerText = el.innerText === '▶' ? '▼' : '▶';
    }
}
