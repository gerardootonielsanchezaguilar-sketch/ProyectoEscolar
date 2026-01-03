// Variables globales
let contadorProductos = 0;

// Agregar primer producto automáticamente al cargar
window.addEventListener('DOMContentLoaded', function() {
    agregarProducto();
});

/**
 * Buscar cliente en la base de datos
 */
function buscarCliente() {
    const busqueda = document.getElementById('buscar_cliente').value.toLowerCase();
    const resultados = document.getElementById('resultados_clientes');
    
    if (busqueda.length < 3) {
        resultados.style.display = 'none';
        return;
    }
    
    const clientesFiltrados = clientesDisponibles.filter(cliente => 
        cliente.nombre.toLowerCase().includes(busqueda) || 
        cliente.dui.includes(busqueda)
    );
    
    if (clientesFiltrados.length > 0) {
        resultados.innerHTML = clientesFiltrados.map(cliente => `
            <div class="resultado-item" onclick="seleccionarCliente(${cliente.id})">
                <strong>${cliente.nombre}</strong><br>
                <small>DUI: ${cliente.dui} | Tel: ${cliente.telefono}</small>
            </div>
        `).join('');
        resultados.style.display = 'block';
    } else {
        resultados.innerHTML = '<div class="resultado-item">No se encontraron clientes</div>';
        resultados.style.display = 'block';
    }
}

/**
 * Seleccionar cliente de la búsqueda
 */
function seleccionarCliente(idCliente) {
    const cliente = clientesDisponibles.find(c => c.id === idCliente);
    
    if (cliente) {
        document.getElementById('id_cliente').value = cliente.id;
        document.getElementById('nombre_cliente').value = cliente.nombre;
        document.getElementById('dui_cliente').value = cliente.dui;
        document.getElementById('telefono_cliente').value = cliente.telefono;
        document.getElementById('edad_cliente').value = cliente.edad;
        document.getElementById('buscar_cliente').value = '';
        document.getElementById('resultados_clientes').style.display = 'none';
    }
}

/**
 * Limpiar campos para nuevo cliente
 */
function nuevoCliente() {
    document.getElementById('id_cliente').value = '';
    document.getElementById('nombre_cliente').value = '';
    document.getElementById('dui_cliente').value = '';
    document.getElementById('telefono_cliente').value = '';
    document.getElementById('edad_cliente').value = '';
    document.getElementById('buscar_cliente').value = '';
    document.getElementById('resultados_clientes').style.display = 'none';
    document.getElementById('nombre_cliente').focus();
}

/**
 * Agregar un nuevo producto al formulario
 */
function agregarProducto() {
    contadorProductos++;
    const container = document.getElementById('productosContainer');
    
    const productoDiv = document.createElement('div');
    productoDiv.className = 'producto-item';
    // Usar id distinto para el contenedor para NO colisionar con el select (producto-<n>)
    productoDiv.id = `producto-item-${contadorProductos}`;
    productoDiv.innerHTML = `
        <div class="item-number">Producto #${contadorProductos}</div>
        <div class="producto-grid-venta">
            <!-- Línea -->
            <div class="form-group">
                <label>Línea <span class="required">*</span></label>
                <select name="productos[${contadorProductos}][linea]" id="linea-${contadorProductos}" 
                        required onchange="cambiarLinea(${contadorProductos})">
                    <option value="">-- Seleccionar Línea --</option>
                    ${lineasDisponibles.map(linea => `
                        <option value="${linea}">${linea}</option>
                    `).join('')}
                </select>
            </div>

            <!-- Producto -->
            <div class="form-group">
                <label>Producto <span class="required">*</span></label>
                <select name="productos[${contadorProductos}][id]" id="producto-${contadorProductos}" 
                        required onchange="actualizarInfoProducto(${contadorProductos})" disabled>
                    <option value="">-- Primero selecciona una línea --</option>
                </select>
                <input type="hidden" name="productos[${contadorProductos}][nombre]" id="nombre-${contadorProductos}">
                <input type="hidden" name="productos[${contadorProductos}][categoria]" id="categoria-${contadorProductos}">
                <input type="hidden" name="productos[${contadorProductos}][codigo]" id="codigo-${contadorProductos}">
            </div>

            <!-- Stock Disponible -->
            <div class="form-group">
                <label>Stock Disponible</label>
                <input type="text" id="stock-display-${contadorProductos}" 
                       class="readonly" readonly placeholder="0" style="text-align: center;">
                <input type="hidden" id="stock-${contadorProductos}">
            </div>

            <!-- Cantidad -->
            <div class="form-group">
                <label>Cantidad <span class="required">*</span></label>
                <input type="number" name="productos[${contadorProductos}][cantidad]" 
                       id="cantidad-${contadorProductos}" min="1" value="1" required 
                       onchange="validarStock(${contadorProductos})">
            </div>

            <!-- Precio Unitario -->
            <div class="form-group">
                <label>Precio Unit. <span class="required">*</span></label>
                <input type="number" name="productos[${contadorProductos}][precio_unitario]" 
                       id="precio-${contadorProductos}" min="0" step="0.01" required 
                       onchange="calcularSubtotalItem(${contadorProductos})">
            </div>

            <!-- Descuento -->
            <div class="form-group">
                <label>Descuento</label>
                <input type="number" name="productos[${contadorProductos}][descuento]" 
                       id="descuento-${contadorProductos}" min="0" step="0.01" value="0" 
                       onchange="calcularSubtotalItem(${contadorProductos})">
            </div>

            <!-- Total -->
            <div class="form-group">
                <label>Total</label>
                <input type="number" id="total-${contadorProductos}" class="readonly" readonly value="0.00">
            </div>

            <!-- Botón eliminar -->
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn-remove" onclick="eliminarProducto(${contadorProductos})">
                    🗑️
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(productoDiv);
}

/**
 * Cambiar línea y cargar productos correspondientes
 */
function cambiarLinea(index) {
    const lineaSelect = document.getElementById(`linea-${index}`);
    const productoSelect = document.getElementById(`producto-${index}`);
    const lineaSeleccionada = lineaSelect.value;
    
    productoSelect.innerHTML = '<option value="">-- Seleccionar Producto --</option>';
    productoSelect.disabled = false;
    
    if (lineaSeleccionada && productosPorLinea[lineaSeleccionada]) {
        const productos = productosPorLinea[lineaSeleccionada];
        let productosDisponibles = 0;
        
        productos.forEach(producto => {
            const stock = parseInt(producto.STOCK);
            const option = document.createElement('option');
            option.value = producto.ID;
            
            // Mostrar TODOS los productos, pero deshabilitar los sin stock
            if (stock > 0) {
                option.textContent = `${producto.NOMBRE_PRODUCTO} [${producto.CODIGO}] - Stock: ${stock} ✓`;
                option.style.color = '#28a745';
                productosDisponibles++;
            } else {
                option.textContent = `${producto.NOMBRE_PRODUCTO} [${producto.CODIGO}] - SIN STOCK ✗`;
                option.disabled = true;
                option.style.color = '#dc3545';
                option.style.fontStyle = 'italic';
            }
            
            option.dataset.codigo = producto.CODIGO;
            option.dataset.nombre = producto.NOMBRE_PRODUCTO;
            option.dataset.categoria = producto.CATEGORIA;
            option.dataset.precio = producto.PRECIO;
            option.dataset.stock = stock;
            option.dataset.estado = producto.ESTADO;
            
            productoSelect.appendChild(option);
        });
        
        // Mensaje si no hay productos disponibles en esta línea
        if (productosDisponibles === 0) {
            const optionInfo = document.createElement('option');
            optionInfo.value = '';
            optionInfo.textContent = '⚠️ No hay productos disponibles en esta línea';
            optionInfo.disabled = true;
            optionInfo.style.color = '#856404';
            optionInfo.style.fontWeight = 'bold';
            productoSelect.insertBefore(optionInfo, productoSelect.firstChild.nextSibling);
        }
    }
    
    // Limpiar campos
    document.getElementById(`stock-display-${index}`).value = '0';
    document.getElementById(`stock-${index}`).value = '0';
    document.getElementById(`precio-${index}`).value = '0';
    calcularSubtotalItem(index);
}

/**
 * Actualizar información del producto seleccionado
 */
function actualizarInfoProducto(index) {
    const productoSelect = document.getElementById(`producto-${index}`);
    const selectedOption = productoSelect.options[productoSelect.selectedIndex];
    
    if (selectedOption && selectedOption.dataset.nombre) {
        const stock = parseInt(selectedOption.dataset.stock);
        
        document.getElementById(`nombre-${index}`).value = selectedOption.dataset.nombre;
        document.getElementById(`categoria-${index}`).value = selectedOption.dataset.categoria;
        document.getElementById(`codigo-${index}`).value = selectedOption.dataset.codigo;
        document.getElementById(`precio-${index}`).value = selectedOption.dataset.precio;
        document.getElementById(`stock-${index}`).value = stock;
        document.getElementById(`stock-display-${index}`).value = `${stock} unidades`;
        
        // Validar cantidad inicial
        validarStock(index);
        calcularSubtotalItem(index);
    }
}

/**
 * Validar que la cantidad no exceda el stock
 */
function validarStock(index) {
    const cantidad = parseInt(document.getElementById(`cantidad-${index}`).value) || 0;
    const stock = parseInt(document.getElementById(`stock-${index}`).value) || 0;
    const cantidadInput = document.getElementById(`cantidad-${index}`);
    
    if (cantidad > stock) {
        mostrarAlerta(`⚠️ La cantidad no puede exceder el stock disponible (${stock} unidades)`, 'warning');
        cantidadInput.value = stock;
        cantidadInput.style.borderColor = '#FFA500';
        setTimeout(() => {
            cantidadInput.style.borderColor = '';
        }, 2000);
    }
    
    calcularSubtotalItem(index);
}

/**
 * Calcular subtotal de un item específico
 */
function calcularSubtotalItem(index) {
    const cantidad = parseFloat(document.getElementById(`cantidad-${index}`).value) || 0;
    const precio = parseFloat(document.getElementById(`precio-${index}`).value) || 0;
    const descuento = parseFloat(document.getElementById(`descuento-${index}`).value) || 0;
    
    const subtotal = cantidad * precio;
    const total = subtotal - descuento;
    
    document.getElementById(`total-${index}`).value = total.toFixed(2);
    
    calcularTotales();
}

/**
 * Calcular totales generales de la venta
 */
function calcularTotales() {
    let subtotal = 0;
    
    document.querySelectorAll('[id^="total-"]').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const impuesto = subtotal * 0.13;
    const descuentoGeneral = parseFloat(document.getElementById('descuento_general').value) || 0;
    const total = subtotal + impuesto - descuentoGeneral;
    
    document.getElementById('subtotalDisplay').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('impuestoDisplay').textContent = `$${impuesto.toFixed(2)}`;
    document.getElementById('totalDisplay').textContent = `$${total.toFixed(2)}`;
}

/**
 * Eliminar un producto del formulario
 */
function eliminarProducto(index) {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        // El contenedor ahora tiene id "producto-item-<n>". Intentamos removerlo.
        const contenedor = document.getElementById(`producto-item-${index}`);
        if (contenedor) {
            contenedor.remove();
        } else {
            // Fallback por si hay marcaje antiguo
            const elementoAntiguo = document.getElementById(`producto-${index}`);
            if (elementoAntiguo) elementoAntiguo.remove();
        }
        calcularTotales();
    }
}

/**
 * Restablecer formulario
 */
function restablecerFormulario() {
    if (confirm('¿Estás seguro de restablecer el formulario? Se perderán todos los datos.')) {
        document.getElementById('formVenta').reset();
        document.getElementById('productosContainer').innerHTML = '';
        contadorProductos = 0;
        agregarProducto();
        calcularTotales();
    }
}

/**
 * Mostrar alerta en pantalla
 */
function mostrarAlerta(mensaje, tipo) {
    const alert = document.getElementById('alertMessage');
    alert.className = `alert alert-${tipo}`;
    alert.textContent = mensaje;
    alert.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    setTimeout(() => {
        alert.style.display = 'none';
    }, 5000);
}

/**
 * Manejar el envío del formulario
 */
document.getElementById('formVenta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const productosItems = document.querySelectorAll('.producto-item');
    if (productosItems.length === 0) {
        mostrarAlerta('Debes agregar al menos un producto', 'danger');
        return;
    }
    
    // Validar que todos los productos tengan selección
    let todosValidos = true;
    productosItems.forEach((item) => {
        // El id del contenedor es "producto-item-<n>"; tomar la última porción para obtener el índice
        const index = item.id.split('-').pop();
        const productoSelect = document.getElementById(`producto-${index}`);
        if (!productoSelect || !productoSelect.value) {
            todosValidos = false;
        }
    });
    
    if (!todosValidos) {
        mostrarAlerta('Debes seleccionar un producto para cada item', 'danger');
        return;
    }
    
    if (!confirm('¿Confirmar el registro de esta venta?')) {
        return;
    }
    
    const formData = new FormData(this);
    
    const subtotal = parseFloat(document.getElementById('subtotalDisplay').textContent.replace('$', ''));
    const impuesto = parseFloat(document.getElementById('impuestoDisplay').textContent.replace('$', ''));
    const total = parseFloat(document.getElementById('totalDisplay').textContent.replace('$', ''));
    
    formData.append('subtotal_general', subtotal);
    formData.append('impuesto_general', impuesto);
    formData.append('total_general', total);
    
    const btnSubmit = this.querySelector('button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.textContent = '⏳ Guardando...';

    // DEBUG: volcar contenido del FormData a la consola (útil para ver qué se envía)
    try {
        for (const pair of formData.entries()) {
            console.log('FormData:', pair[0], '=', pair[1]);
        }
    } catch (e) {
        console.log('No se pudo enumerar FormData:', e);
    }

    // Enviar al endpoint correcto que existe en el proyecto: PROCESO_VENTA.php
    fetch('PROCESO_VENTA.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Intentar parsear JSON; si falla, devolver el texto para ayudar a depurar (warnings/errores PHP)
        return response.text().then(text => {
            try {
                const json = JSON.parse(text);
                return { ok: true, json };
            } catch (err) {
                return { ok: false, text };
            }
        });
    })
    .then(result => {
        if (!result.ok) {
            console.error('Respuesta no JSON del servidor:', result.text);
            mostrarAlerta('❌ Respuesta inesperada del servidor. Revisa la consola (Network/Response).', 'danger');
            btnSubmit.disabled = false;
            btnSubmit.textContent = '💾 Guardar Venta';
            return;
        }

        const data = result.json;
        if (data.success) {
            mostrarAlerta('✅ Venta registrada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = 'gestion_venta.php';
            }, 2000);
        } else {
            mostrarAlerta('❌ Error: ' + data.mensaje, 'danger');
            btnSubmit.disabled = false;
            btnSubmit.textContent = '💾 Guardar Venta';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        mostrarAlerta('❌ Error al procesar la solicitud', 'danger');
        btnSubmit.disabled = false;
        btnSubmit.textContent = '💾 Guardar Venta';
    });
});

// Cerrar resultados de búsqueda al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.cliente-busqueda')) {
        document.getElementById('resultados_clientes').style.display = 'none';
    }
});