var resetted = false;
function resetBar() {
    if (!resetted) {
        document.getElementById('keyword').value = '';
        resetted = true;
    }
}
