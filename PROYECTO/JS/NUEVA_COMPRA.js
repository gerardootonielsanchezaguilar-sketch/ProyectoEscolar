// Variables globales
let contadorProductos = 0;

// Agregar primer producto automáticamente al cargar
window.addEventListener('DOMContentLoaded', function() {
    agregarProducto();
});

/**
 * Agregar un nuevo producto al formulario
 */
function agregarProducto() {
    contadorProductos++;
    const container = document.getElementById('productosContainer');
    
    const productoDiv = document.createElement('div');
    productoDiv.className = 'producto-item';
    // usar un id de contenedor distinto al id del select para evitar duplicados
    productoDiv.id = `producto-item-${contadorProductos}`;
    productoDiv.innerHTML = `
        <div class="item-number">Producto #${contadorProductos}</div>
        <div class="producto-grid-extended">
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

            <!-- Nombre Personalizado (solo para Especial) -->
            <div class="form-group" id="nombre-especial-${contadorProductos}" style="display:none;">
                <label>Nombre Producto <span class="required">*</span></label>
                <input type="text" id="nombre-personalizado-${contadorProductos}" 
                       placeholder="Escribe el nombre del producto">
            </div>

            <!-- Código (solo para Especial) -->
            <div class="form-group" id="codigo-especial-${contadorProductos}" style="display:none;">
                <label>Código</label>
                <input type="text" id="codigo-personalizado-${contadorProductos}" 
                       placeholder="Ej: ESP-001">
            </div>

            <!-- Mostrar código del producto -->
            <div class="form-group">
                <label>Código</label>
                <input type="text" id="codigo-display-${contadorProductos}" 
                       class="readonly" readonly placeholder="Se llenará automáticamente">
            </div>

            <!-- Cantidad -->
            <div class="form-group">
                <label>Cantidad <span class="required">*</span></label>
                <input type="number" name="productos[${contadorProductos}][cantidad]" 
                       id="cantidad-${contadorProductos}" min="1" value="1" required 
                       onchange="calcularSubtotalItem(${contadorProductos})">
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
    const nombreEspecialDiv = document.getElementById(`nombre-especial-${index}`);
    const codigoEspecialDiv = document.getElementById(`codigo-especial-${index}`);
    const lineaSeleccionada = lineaSelect.value;
    
    // Limpiar selector de productos
    productoSelect.innerHTML = '<option value="">-- Seleccionar Producto --</option>';
    productoSelect.disabled = false;
    
    // Ocultar campos especiales por defecto
    nombreEspecialDiv.style.display = 'none';
    codigoEspecialDiv.style.display = 'none';
    
    if (lineaSeleccionada === 'Especial') {
        // Mostrar campos para producto especial
        nombreEspecialDiv.style.display = 'block';
        codigoEspecialDiv.style.display = 'block';
        productoSelect.innerHTML = '<option value="nuevo">➕ Producto Nuevo (Especial)</option>';
        productoSelect.value = 'nuevo';
        
        // Requerir nombre personalizado
        document.getElementById(`nombre-personalizado-${index}`).required = true;
    } else if (lineaSeleccionada && productosPorLinea[lineaSeleccionada]) {
        // Cargar productos de la línea seleccionada
        const productos = productosPorLinea[lineaSeleccionada];
        productos.forEach(producto => {
            const option = document.createElement('option');
            option.value = producto.ID;
            option.textContent = `${producto.NOMBRE_PRODUCTO} [${producto.CODIGO}] (Stock: ${producto.STOCK})`;
            option.dataset.codigo = producto.CODIGO;
            option.dataset.nombre = producto.NOMBRE_PRODUCTO;
            option.dataset.categoria = producto.CATEGORIA;
            option.dataset.precio = producto.PRECIO;
            productoSelect.appendChild(option);
        });
    }
}

/**
 * Actualizar información del producto seleccionado
 */
function actualizarInfoProducto(index) {
    const productoSelect = document.getElementById(`producto-${index}`);
    const lineaSelect = document.getElementById(`linea-${index}`);
    const selectedOption = productoSelect.options[productoSelect.selectedIndex];
    
    if (productoSelect.value === 'nuevo') {
        // Producto especial - usar valores personalizados
        const nombrePersonalizado = document.getElementById(`nombre-personalizado-${index}`).value;
        const codigoPersonalizado = document.getElementById(`codigo-personalizado-${index}`).value || 'ESP-AUTO';
        
        document.getElementById(`nombre-${index}`).value = nombrePersonalizado || 'Producto Especial';
        document.getElementById(`categoria-${index}`).value = 'Especial';
        document.getElementById(`codigo-${index}`).value = codigoPersonalizado;
        document.getElementById(`codigo-display-${index}`).value = codigoPersonalizado;
        document.getElementById(`precio-${index}`).value = 0;
        
        // Reemplazar listeners por asignación a oninput para evitar múltiples listeners acumulados
        const nombrePersonalizadoEl = document.getElementById(`nombre-personalizado-${index}`);
        const codigoPersonalizadoEl = document.getElementById(`codigo-personalizado-${index}`);
        if (nombrePersonalizadoEl) {
            nombrePersonalizadoEl.oninput = function() {
                document.getElementById(`nombre-${index}`).value = this.value;
            };
        }
        if (codigoPersonalizadoEl) {
            codigoPersonalizadoEl.oninput = function() {
                document.getElementById(`codigo-${index}`).value = this.value;
                document.getElementById(`codigo-display-${index}`).value = this.value;
            };
        }
        
    } else if (selectedOption && selectedOption.dataset.nombre) {
        // Producto existente
        document.getElementById(`nombre-${index}`).value = selectedOption.dataset.nombre;
        document.getElementById(`categoria-${index}`).value = selectedOption.dataset.categoria;
        document.getElementById(`codigo-${index}`).value = selectedOption.dataset.codigo;
        document.getElementById(`codigo-display-${index}`).value = selectedOption.dataset.codigo;
        document.getElementById(`precio-${index}`).value = selectedOption.dataset.precio;
        
        calcularSubtotalItem(index);
    }
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
 * Calcular totales generales de la compra
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
        // el id del contenedor ahora es producto-item-<index>
        const elemento = document.getElementById(`producto-item-${index}`);
        if (elemento) elemento.remove();
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
document.getElementById('formNuevaCompra').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const productosItems = document.querySelectorAll('.producto-item');
    if (productosItems.length === 0) {
        mostrarAlerta('Debes agregar al menos un producto', 'danger');
        return;
    }
    
    // Validar productos especiales
    let valido = true;
    productosItems.forEach((item, i) => {
        // extraer el índice tomando la última parte del id (resistente a formatos como producto-item-1)
        const index = item.id.split('-').pop();
        const lineaSelect = document.getElementById(`linea-${index}`);
        if (lineaSelect.value === 'Especial') {
            const nombrePersonalizado = document.getElementById(`nombre-personalizado-${index}`).value;
            if (!nombrePersonalizado) {
                mostrarAlerta('Debes especificar el nombre para productos especiales', 'danger');
                valido = false;
            }
        }
    });
    
    if (!valido) return;
    
    if (!confirm('¿Confirmar el registro de esta compra?')) {
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
    
    fetch('procesar_nueva_compra.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('✅ Compra registrada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = 'compras.php';
            }, 2000);
        } else {
            mostrarAlerta('❌ Error: ' + data.mensaje, 'danger');
            btnSubmit.disabled = false;
            btnSubmit.textContent = '💾 Guardar Compra';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('❌ Error al procesar la solicitud', 'danger');
        btnSubmit.disabled = false;
        btnSubmit.textContent = '💾 Guardar Compra';
    });
});