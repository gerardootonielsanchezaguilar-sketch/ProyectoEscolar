// Búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#comprasTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Ver detalle de compra
function verDetalle(idCompra) {
    document.getElementById('modalDetalle').style.display = 'block';
    document.getElementById('modalDetalleBody').innerHTML = '<p style="text-align:center; padding: 40px;">Cargando...</p>';
    fetch(`obtener_detalle_compra.php?id=${idCompra}`)
        .then(r => r.text())
        .then(data => document.getElementById('modalDetalleBody').innerHTML = data)
        .catch(() => document.getElementById('modalDetalleBody').innerHTML = '<p style="color:red;text-align:center;">Error al cargar los datos</p>');
}

// Abrir modal de cambio de estado
function abrirModalEstado(id, factura, estadoActual) {
    document.getElementById('modalEstado').style.display = 'block';
    document.getElementById('idCompraEstado').value = id;
    document.getElementById('modalFactura').textContent = factura;
    document.getElementById('modalEstadoActual').innerHTML = `<span class="badge badge-${estadoActual.toLowerCase()}">${estadoActual}</span>`;
    const select = document.getElementById('selectNuevoEstado');
    Array.from(select.options).forEach(o => o.disabled = (o.value === estadoActual));
}

// Cerrar modal
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Cerrar al hacer clic fuera
window.onclick = e => {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
};

// Enviar formulario de estado
document.getElementById('formEstado').addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const nuevoEstado = formData.get('nuevo_estado');
    if (confirm(`¿Cambiar el estado a "${nuevoEstado}"?\n⚠️ Puede afectar el inventario.`)) {
        fetch('cambiar_estado_compra.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Estado actualizado correctamente');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.mensaje);
                }
            })
            .catch(() => alert('❌ Error al procesar la solicitud'));
    }
});
