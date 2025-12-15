/* -------------------------------------------- VARIABLES -------------------------------------------- */
let carritoDeCompras = [];

// Variables para el modal de producto
let modalProductoActual = null;
let modalTallaSeleccionada = null;
let modalColorSeleccionado = 0;
let modalPaqueteSeleccionado = 'seis';
let modalPrecioBase = 0;

/* -------------------------------------------- FUNCIONES PARA PRODUCTOS DINÁMICOS -------------------------------------------- */

/**
 * Cambia la imagen del producto cuando se hace clic en un color
 */
function establecerImagen(idElemento, nuevaRuta, nuevoAlt) {
    const imagenElemento = document.getElementById(idElemento);
    if (imagenElemento) {
        imagenElemento.src = nuevaRuta;
        imagenElemento.alt = nuevoAlt;
    }
}

/**
/**
 * Cambia el precio mostrado según el paquete seleccionado
 * @param {string} idPrecio - ID del elemento de precio
 * @param {string} paquete - 'seis' o 'doce'
 * @param {number} idProducto - ID del producto
 */
function cambiarPrecio(idPrecio, paquete, idProducto = null) {
    console.log('cambiarPrecio llamado:', idPrecio, paquete, idProducto);
    
    const elementoPrecio = document.getElementById(idPrecio);
    if (!elementoPrecio) {
        console.error('Elemento no encontrado:', idPrecio);
        return;
    }
    
    // DEPURACIÓN: Ver qué valores tenemos
    console.log('textContent:', elementoPrecio.textContent);
    console.log('data-precio-base:', elementoPrecio.getAttribute('data-precio-base'));
    console.log('data-paquete-actual:', elementoPrecio.getAttribute('data-paquete-actual'));
    console.log('data-precio-paquete:', elementoPrecio.getAttribute('data-precio-paquete'));
    
    // ESTRATEGIA CORREGIDA: Siempre usar el precio base individual
    // Primero intentar obtener el precio individual directamente
    let precioIndividual = parseFloat(elementoPrecio.getAttribute('data-precio-individual'));
    
    // Si no existe, usar el data-precio-base que DEBERÍA ser individual
    if (isNaN(precioIndividual) || precioIndividual === 0) {
        precioIndividual = parseFloat(elementoPrecio.getAttribute('data-precio-base'));
        console.log('Usando data-precio-base como individual:', precioIndividual);
    }
    
    // Si aún no hay precio individual válido, calcularlo del precio actual
    if (isNaN(precioIndividual) || precioIndividual === 0) {
        console.warn('No se encontró precio individual, calculando desde precio actual...');
        const precioActual = parseFloat(elementoPrecio.textContent.replace('$', '').replace(',', ''));
        const paqueteActual = elementoPrecio.getAttribute('data-paquete-actual') || 'seis';
        
        if (paqueteActual === 'seis') {
            precioIndividual = precioActual / (6 * 0.9); // Revertir cálculo de 6 pares
        } else if (paqueteActual === 'doce') {
            precioIndividual = precioActual / (12 * 0.8); // Revertir cálculo de 12 pares
        } else {
            precioIndividual = precioActual; // Asumir que es individual
        }
        console.warn('Precio individual calculado:', precioIndividual);
        
        // Guardarlo para futuros usos
        elementoPrecio.setAttribute('data-precio-individual', precioIndividual);
    }
    
    // Validar que el precio individual sea razonable
    if (precioIndividual > 1000) { // Si parece alto, probablemente ya incluye paquete
        console.error('Precio individual parece incorrecto (demasiado alto):', precioIndividual);
        // Intentar corregir asumiendo que el precio mostrado es para 6 pares
        const precioMostrado = parseFloat(elementoPrecio.textContent.replace('$', '').replace(',', ''));
        precioIndividual = precioMostrado / (6 * 0.9);
        console.error('Precio individual corregido a:', precioIndividual);
        elementoPrecio.setAttribute('data-precio-individual', precioIndividual);
    }
    
    console.log('Precio individual final para cálculo:', precioIndividual);
    
    // Calcular el nuevo precio basado en el precio individual CORRECTO
    let precioFinal;
    if (paquete === 'seis') {
        precioFinal = precioIndividual * 6 * 0.9; // 10% descuento
        console.log('Cálculo para 6 pares:', precioIndividual, '× 6 × 0.9 =', precioFinal);
    } else if (paquete === 'doce') {
        precioFinal = precioIndividual * 12 * 0.8; // 20% descuento
        console.log('Cálculo para 12 pares:', precioIndividual, '× 12 × 0.8 =', precioFinal);
    } else {
        precioFinal = precioIndividual;
        console.log('Cálculo individual:', precioIndividual);
    }
    
    // Formatear y mostrar
    elementoPrecio.textContent = '$' + precioFinal.toFixed(2);
    elementoPrecio.setAttribute('data-paquete-actual', paquete);
    elementoPrecio.setAttribute('data-precio-paquete', precioFinal);
    
    // Actualizar botones visualmente
    const contenedor = document.querySelector(`.container__item_01 #precio_item_${idProducto}`)?.closest('.container__item_01');
    if (contenedor) {
        // Remover clase activa de todos
        contenedor.querySelectorAll('.btn__paquete_seis, .btn__paquete_doce').forEach(btn => {
            btn.classList.remove('activo');
        });
        
        // Agregar clase activa al seleccionado
        const botonSeleccionado = contenedor.querySelector(`.btn__paquete_${paquete}`);
        if (botonSeleccionado) {
            botonSeleccionado.classList.add('activo');
        }
    }
    
    console.log('Nuevo precio calculado:', precioFinal);
    console.log('---');
}

/**
 * Controla los botones de + y - para cualquier producto
 */
function controlarCantidad(idInput, cambio) {
    const input = document.getElementById(idInput);
    let valorActual = parseInt(input.value) || 0;
    let nuevoValor = valorActual + cambio;
    
    if (nuevoValor < 1) nuevoValor = 1;
    input.value = nuevoValor;
}

/**
 * Valida entrada manual en el input
 */
function validarEntrada(idInput) {
    const input = document.getElementById(idInput);
    let valor = parseInt(input.value) || 1;
    if (valor < 1) valor = 1;
    input.value = valor;
}

/**
 * Solo permite números en el input
 */
function soloNumeros(event) {
    const charCode = (event.which) ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

/* ---------------- SELECCIÓN DE TALLAS DINÁMICA ---------------- */

let productoSeleccionadoActual = null;

function abrirModalTallas(idZapato) {
    productoSeleccionadoActual = idZapato;
    const modal = document.getElementById('modal-tallas');
    const grid = document.getElementById('grid-tallas');
    
    grid.innerHTML = '';
    
    // Obtener el contenedor del zapato para buscar las tallas
    const contenedorZapato = document.querySelector(`.container__item_01 #input_${idZapato}`)?.closest('.container__item_01');
    
    if (contenedorZapato) {
        // Obtener las tallas desde el data attribute (si las guardamos)
        const tallasJSON = contenedorZapato.getAttribute('data-tallas');
        let tallasParaEsteZapato = [];
        
        if (tallasJSON) {
            try {
                tallasParaEsteZapato = JSON.parse(tallasJSON);
            } catch (e) {
                console.error('Error parsing tallas:', e);
                // Si hay error, usar tallas predeterminadas
                tallasParaEsteZapato = [22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26];
            }
        } else {
            // Si no hay data-tallas, usar predeterminadas
            tallasParaEsteZapato = [22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26];
        }
        
        // Crear botones de tallas
        tallasParaEsteZapato.forEach(talla => {
            const btn = document.createElement('button');
            btn.textContent = talla;
            btn.classList.add('btn-talla');
            btn.onclick = () => agregarItemAlCarrito(idZapato, talla);
            grid.appendChild(btn);
        });
    } else {
        // Fallback: usar tallas predeterminadas
        const tallasPredeterminadas = [22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26];
        tallasPredeterminadas.forEach(talla => {
            const btn = document.createElement('button');
            btn.textContent = talla;
            btn.classList.add('btn-talla');
            btn.onclick = () => agregarItemAlCarrito(idZapato, talla);
            grid.appendChild(btn);
        });
    }
    
    modal.style.display = 'flex';
}

function cerrarModalTallas() {
    document.getElementById('modal-tallas').style.display = 'none';
}

function agregarItemAlCarrito(idZapato, tallaSeleccionada) {
    // Obtener información del producto desde el DOM
    const inputCantidad = document.getElementById('input_' + idZapato);
    const elementoPrecio = document.getElementById('precio_item_' + idZapato);
    const imagen = document.getElementById('item_' + idZapato);
    
    if (!inputCantidad || !elementoPrecio || !imagen) {
        console.error('No se pudo encontrar la información del producto');
        return;
    }
    
    const cantidad = parseInt(inputCantidad.value) || 1;
    const paqueteActual = elementoPrecio.getAttribute('data-paquete-actual') || 'seis';
    
    // Obtener precio CORRECTO (ya incluye descuento por paquete)
    const precioTexto = elementoPrecio.textContent;
    const precioTotal = parseFloat(precioTexto.replace('$', '').replace(',', ''));
    
    // Obtener nombre del producto
    const nombreElemento = inputCantidad.closest('.container__item_01')?.querySelector('.info__item_01 h2');
    const nombre = nombreElemento ? nombreElemento.textContent : 'Producto ' + idZapato;
    
    const nuevoProducto = {
        id: idZapato,
        nombre: nombre,
        imagen: imagen.src,
        color: imagen.alt,
        paquete: paqueteActual, // ← USA EL PAQUETE CORRECTO
        cantidad: cantidad,
        talla: tallaSeleccionada,
        precioTotal: precioTotal
    };
    
    carritoDeCompras.push(nuevoProducto);
    renderizarCarritoHTML();
    cerrarModalTallas();
    
    // Resetear cantidad a 1
    inputCantidad.value = 1;
    
    // Abrir carrito automáticamente
    document.getElementById('carrito-lateral').classList.add('activo');
    document.getElementById('carrito-overlay').classList.add('activo');
}

/* ---------------- RENDERIZAR CARRITO ---------------- */

function renderizarCarritoHTML() {
    const contenedor = document.getElementById('carrito-contenido');
    const elementoTotal = document.getElementById('carrito-precio-final');
    
    contenedor.innerHTML = '';
    let totalGlobal = 0;
    
    if (carritoDeCompras.length === 0) {
        contenedor.innerHTML = '<p style="text-align: center; margin-top: 20px;">Tu carrito está vacío.</p>';
    } else {
        carritoDeCompras.forEach((producto, index) => {
            totalGlobal += producto.precioTotal;
            
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('item-carrito');
            itemDiv.innerHTML = `
                <img src="${producto.imagen}" alt="${producto.color}">
                <div class="item-info">
                    <h4>${producto.nombre}</h4>
                    <p>Color: ${producto.color}</p>
                    <p>Talla: <strong>${producto.talla}</strong> | Paq: ${producto.paquete === 'seis' ? '6' : '12'}</p>
                    <p>Cant: ${producto.cantidad} x $${(producto.precioTotal / producto.cantidad).toFixed(2)}</p>
                    <p style="color: var(--color_uno); font-weight: bold;">$${producto.precioTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                </div>
                <button class="btn-eliminar-item" onclick="eliminarItemDelCarrito(${index})">&times;</button>
            `;
            contenedor.appendChild(itemDiv);
        });
    }
    
    elementoTotal.textContent = "$" + totalGlobal.toLocaleString('en-US', {minimumFractionDigits: 2});
}

function eliminarItemDelCarrito(index) {
    carritoDeCompras.splice(index, 1);
    renderizarCarritoHTML();
}

/* ---------------- CARRITO LATERAL ---------------- */

function inicializarCarritoLateral() {
    const btnAbrir = document.getElementById('btn-abrir-carrito');
    const btnCerrar = document.getElementById('btn-cerrar-carrito');
    const overlay = document.getElementById('carrito-overlay');
    const carritoLateral = document.getElementById('carrito-lateral');
    
    function cerrarCarrito() {
        carritoLateral.classList.remove('activo');
        overlay.classList.remove('activo');
    }
    
    if(btnAbrir) {
        btnAbrir.addEventListener('click', (e) => {
            e.preventDefault();
            carritoLateral.classList.add('activo');
            overlay.classList.add('activo');
        });
    }
    
    if(btnCerrar) btnCerrar.addEventListener('click', cerrarCarrito);
    if(overlay) overlay.addEventListener('click', cerrarCarrito);
}

/* ================= MODAL DE PRODUCTO ================= */

/**
 * Abre el modal de producto con la información del zapato
 */
function abrirModalProducto(producto) {
    console.log('Modal abierto con datos:', producto);
    console.log('Tallas disponibles:', producto.tallas);
    
    // Validar que lleguen los datos
    if (!producto || !producto.imagenes || producto.imagenes.length === 0) {
        console.error('Datos del producto incompletos:', producto);
        return;
    }
    
    modalProductoActual = producto;
    modalTallaSeleccionada = null;
    modalColorSeleccionado = 0;
    modalPaqueteSeleccionado = 'seis';
    modalPrecioBase = producto.precio;
    
    // Actualizar información básica
    document.getElementById('modalNombre').textContent = producto.nombre;
    document.getElementById('modalCategoria').textContent = producto.categoria;
    
    // Actualizar imagen principal
    const imagenPrincipal = document.getElementById('modalImagenPrincipal');
    imagenPrincipal.src = producto.imagenes[0] || producto.imagen;
    imagenPrincipal.alt = producto.nombre;
    
    // Crear miniaturas
    const miniaturasContainer = document.getElementById('modalMiniaturas');
    miniaturasContainer.innerHTML = '';
    
    producto.imagenes.forEach((imagen, index) => {
        const img = document.createElement('img');
        img.src = imagen;
        img.alt = `${producto.nombre} - vista ${index + 1}`;
        img.classList.add('miniatura');
        if (index === 0) img.classList.add('miniatura__activa');
        
        img.onclick = () => {
            // Cambiar imagen principal
            imagenPrincipal.src = imagen;
            // Remover clase activa de todas
            miniaturasContainer.querySelectorAll('.miniatura').forEach(m => {
                m.classList.remove('miniatura__activa');
            });
            // Agregar clase activa a la miniatura clickeada
            img.classList.add('miniatura__activa');
        };
        
        miniaturasContainer.appendChild(img);
    });
    
    // Crear botones de colores
    const coloresContainer = document.getElementById('modalColores');
    coloresContainer.innerHTML = '';
    
    // Si hay colores, mostrar título
    if (producto.colores && producto.colores.length > 0) {
        const tituloColores = document.createElement('h3');
        tituloColores.textContent = 'Color:';
        coloresContainer.appendChild(tituloColores);
    }
    
    producto.colores.forEach((color, index) => {
        const btn = document.createElement('button');
        btn.classList.add('modal__color');
        btn.style.backgroundColor = color;
        if (index === 0) btn.classList.add('activo');
        
        btn.onclick = () => {
            // Cambiar imagen al color seleccionado
            if (producto.imagenes[index]) {
                imagenPrincipal.src = producto.imagenes[index];
                
                // Actualizar miniaturas
                miniaturasContainer.querySelectorAll('.miniatura').forEach((m, i) => {
                    m.classList.remove('miniatura__activa');
                    if (i === index) m.classList.add('miniatura__activa');
                });
            }
            
            // Remover clase activa de todos los colores
            coloresContainer.querySelectorAll('.modal__color').forEach(c => {
                c.classList.remove('activo');
            });
            
            // Agregar clase activa al color seleccionado
            btn.classList.add('activo');
            modalColorSeleccionado = index;
        };
        
        coloresContainer.appendChild(btn);
    });
    
    // MODIFICADO: Crear botones de tallas DESDE BD
    const tallasContainer = document.getElementById('modalTallas');
    // Limpiar todo
    tallasContainer.innerHTML = '<h3>Selecciona tu talla:</h3>';
    
    // Verificar si hay tallas del producto
    if (producto.tallas && producto.tallas.length > 0) {
        // Usar las tallas específicas del producto
        producto.tallas.forEach(talla => {
            const btn = document.createElement('button');
            btn.textContent = talla;
            btn.onclick = () => {
                // Remover clase activa de todas las tallas
                tallasContainer.querySelectorAll('button').forEach(b => {
                    b.classList.remove('talla__activa');
                });
                
                // Agregar clase activa a la talla seleccionada
                btn.classList.add('talla__activa');
                modalTallaSeleccionada = talla;
            };
            
            tallasContainer.appendChild(btn);
        });
    } else {
        // Si no hay tallas específicas, mostrar mensaje
        tallasContainer.innerHTML += '<p>No hay tallas disponibles para este producto.</p>';
    }
    
    // Resetear cantidad
    document.getElementById('modalCantidad').value = 1;
    
    // Actualizar botones de paquete
    document.querySelectorAll('.modal__paquetes button').forEach(btn => {
        btn.classList.remove('activo');
        if (btn.textContent.includes('6')) btn.classList.add('activo');
    });
    
    // Calcular y mostrar precio inicial
    modalCalcularPrecio();
    
    // Mostrar modal
    document.getElementById('modalProducto').classList.add('activo');

    // IMPORTANTE: Calcular y mostrar precio INICIAL con paquete de 6
    modalCalcularPrecio();
    
    // Activar visualmente el botón de paquete 6
    document.querySelectorAll('.modal__paquetes button').forEach(btn => {
        btn.classList.remove('activo');
        if (btn.textContent.includes('6')) {
            btn.classList.add('activo');
        }
    });
    
    // Mostrar modal
    document.getElementById('modalProducto').classList.add('activo');
}

/**
 * Cierra el modal de producto
 */
function cerrarModalProducto() {
    document.getElementById('modalProducto').classList.remove('activo');
}

/**
 * Cambia el paquete en el modal
 */
function modalCambiarPaquete(paquete) {
    modalPaqueteSeleccionado = paquete;
    
    // Actualizar botones visualmente
    document.querySelectorAll('.modal__paquetes button').forEach(btn => {
        btn.classList.remove('activo');
        if ((paquete === 'seis' && btn.textContent.includes('6')) || 
            (paquete === 'doce' && btn.textContent.includes('12'))) {
            btn.classList.add('activo');
        }
    });
    
    // Recalcular precio
    modalCalcularPrecio();
}

/**
 * Calcula y actualiza el precio en el modal
 */
function modalCalcularPrecio() {
    const cantidad = parseInt(document.getElementById('modalCantidad').value) || 1;
    let precioUnitario = modalPrecioBase;
    
    // Aplicar descuento por paquete CORREGIDO
    if (modalPaqueteSeleccionado === 'seis') {
        // Precio para 6 pares: precio individual * 6 * 0.9
        precioUnitario = modalPrecioBase * 6 * 0.9;
    } else if (modalPaqueteSeleccionado === 'doce') {
        // Precio para 12 pares: precio individual * 12 * 0.8
        precioUnitario = modalPrecioBase * 12 * 0.8;
    }
    
    const precioTotal = precioUnitario * cantidad;
    
    // Mostrar precio
    const elementoPrecio = document.getElementById('modalPrecio');
    elementoPrecio.textContent = `$${precioTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
}

/**
 * Controla la cantidad en el modal
 */
function modalControlarCantidad(cambio) {
    const input = document.getElementById('modalCantidad');
    let valor = parseInt(input.value) || 1;
    valor += cambio;
    
    if (valor < 1) valor = 1;
    input.value = valor;
    
    modalCalcularPrecio();
}

/**
 * Valida la entrada manual en el modal
 */
function modalValidarEntrada() {
    const input = document.getElementById('modalCantidad');
    let valor = parseInt(input.value) || 1;
    if (valor < 1) valor = 1;
    input.value = valor;
    
    modalCalcularPrecio();
}

/**
 * Agrega el producto del modal al carrito
 */
function modalAgregarAlCarrito() {
    if (!modalTallaSeleccionada) {
        alert('Por favor selecciona una talla');
        return;
    }
    
    if (!modalProductoActual) {
        alert('Error: Información del producto no disponible');
        return;
    }
    
    const cantidad = parseInt(document.getElementById('modalCantidad').value) || 1;
    const precioModal = parseFloat(document.getElementById('modalPrecio').textContent.replace('$', '').replace(',', ''));
    
    const nuevoProducto = {
        id: modalProductoActual.id,
        nombre: modalProductoActual.nombre,
        imagen: modalProductoActual.imagenes[modalColorSeleccionado || 0] || modalProductoActual.imagen,
        color: `Color ${modalColorSeleccionado + 1 || 1}`,
        paquete: modalPaqueteSeleccionado,
        cantidad: cantidad,
        talla: modalTallaSeleccionada,
        precioTotal: precioModal
    };
    
    carritoDeCompras.push(nuevoProducto);
    renderizarCarritoHTML();
    cerrarModalProducto();
    
    // Mostrar confirmación
    alert('Producto agregado al carrito!');
    
    // Abrir carrito automáticamente
    document.getElementById('carrito-lateral').classList.add('activo');
    document.getElementById('carrito-overlay').classList.add('activo');
}

/* ---------------- INICIALIZACIÓN ---------------- */

document.addEventListener('DOMContentLoaded', (event) => {

    inicializarPreciosPorDefecto();

    inicializarCarritoLateral();
    
    // Inicializar precios base en todos los productos
    document.querySelectorAll('[id^="precio_item_"]').forEach(elemento => {
        const precioBase = parseFloat(elemento.textContent.replace('$', '').replace(',', ''));
        elemento.setAttribute('data-precio-base', precioBase);
        elemento.setAttribute('data-paquete-actual', 'individual');
    });
    
    // Cerrar modal al hacer clic fuera
    const modalProducto = document.getElementById('modalProducto');
    if (modalProducto) {
        modalProducto.addEventListener('click', (e) => {
            if (e.target === modalProducto) {
                cerrarModalProducto();
            }
        });
    }
    
    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (document.getElementById('modalProducto').classList.contains('activo')) {
                cerrarModalProducto();
            }
            if (document.getElementById('modal-tallas').style.display === 'flex') {
                cerrarModalTallas();
            }
        }
    });
});

/**
 * Inicializa todos los precios con paquete de 6 por defecto
 */
function inicializarPreciosPorDefecto() {
    document.querySelectorAll('[id^="precio_item_"]').forEach(elemento => {
        // Obtener precio individual
        let precioIndividual = parseFloat(elemento.getAttribute('data-precio-individual'));
        
        // Si no existe, usar data-precio-base
        if (isNaN(precioIndividual) || precioIndividual === 0) {
            precioIndividual = parseFloat(elemento.getAttribute('data-precio-base'));
        }
        
        if (isNaN(precioIndividual) || precioIndividual === 0) {
            console.error('No se pudo obtener precio individual para:', elemento.id);
            return;
        }
        
        const idPrecio = elemento.id;
        const idProducto = idPrecio.replace('precio_item_', '');
        
        // Calcular precio para paquete de 6 por defecto
        const precioSeis = precioIndividual * 6 * 0.9;
        
        // Actualizar todo
        elemento.textContent = '$' + precioSeis.toFixed(2);
        elemento.setAttribute('data-paquete-actual', 'seis');
        elemento.setAttribute('data-precio-paquete', precioSeis);
        
        // Si no tiene data-precio-individual, agregarlo
        if (!elemento.getAttribute('data-precio-individual')) {
            elemento.setAttribute('data-precio-individual', precioIndividual);
        }
        
        // Activar visualmente el botón de paquete 6
        const contenedor = elemento.closest('.container__item_01');
        if (contenedor) {
            const btnSeis = contenedor.querySelector('.btn__paquete_seis');
            const btnDoce = contenedor.querySelector('.btn__paquete_doce');
            
            if (btnSeis) btnSeis.classList.add('activo');
            if (btnDoce) btnDoce.classList.remove('activo');
        }
    });
}