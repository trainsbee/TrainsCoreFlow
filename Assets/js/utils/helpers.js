function removeRow(userId) {
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (row) {
        row.remove(); // 👈 elimina la fila del DOM
    }
}
