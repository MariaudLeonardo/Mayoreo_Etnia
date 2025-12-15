/* -------------------------------------------- VARIABELS -------------------------------------------- */
// Precios de productos
let precioBotinPaqueteSeis = 510*6;
let precioBotinPaqueteDoce = 432*12;

let precioBalerinaPaqueteSeis = 365*6;
let precioBalerinaPaqueteDoce = 260*12;

let precioConfortPaqueteSeis = 453*6;
let precioConfortPaqueteDoce = 380*12;

let precioCasualPaqueteSeis = 315*6;
let precioCasualPaqueteDoce = 275*12;

let precioSandaliaConfortPaqueteSeis = 390*6;
let precioSandaliaConfortPaqueteDoce = 310*12;

// Pues las tallas 
let tallas = [21, 21.5, 22, 22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26, 26.5, 27, 27.5, 28, 28.5, 29, 29.5, 30]

let carritoDeCompras = []; // Array para guardar los productos

// Variables de estado del Producto 01 (Valores por defecto)
let infoProductoActual = {
    id: 'item_01',
    nombre: 'Botín con Hebilla',
    imagen: 'Assets/Imagenes/Items/Botines/botin_con_hebilla_cafe.jpg', // Ruta inicial
    color: 'Botín Café',
    paquete: 'seis' // 'seis' o 'doce'
}

// --- ESTADO DEL PRODUCTO 01 ---
// Esta variable recordará cuál es el precio del paquete seleccionado actualmente.
// Por defecto inicia con el paquete de seis.
let precioActualProducto_01 = precioBotinPaqueteSeis;

/* -------------------------------------------- FUNCIONES -------------------------------------------- */

/**
 * 
 * @param {*} id Id del componente a modificar
 * @param {*} nuevaRuta Ruta de la imagen a mostrar
 * @param {*} nuevoAlt Nombre
 */
function establecerImagen(id, nuevaRuta, nuevoAlt) {
    const imagenElemento = document.getElementById(id);
    if (imagenElemento) {
        imagenElemento.src = nuevaRuta;
        imagenElemento.alt = nuevoAlt;
        
        // Guardamos la selección actual para usarla en el carrito
        infoProductoActual.imagen = nuevaRuta;
        infoProductoActual.color = nuevoAlt;
    }
}

/**
 * 1. Selecciona el precio del paquete (6 o 12).
 * 2. Guarda ese precio en la variable de estado.
 * 3. Llama a la función que calcula el total.
 */
function cambiarPrecio(idPrecio, paquete) {
    // Actualizamos la variable de estado según el botón presionado
    if (paquete === 'seis') {
        precioActualProducto_01 = precioBotinPaqueteSeis;
    } else if (paquete === 'doce') {
        precioActualProducto_01 = precioBotinPaqueteDoce;
    }

    // Guardamos el paquete actual
    infoProductoActual.paquete = paquete;
    
    // Actualizamos variable global de precio (tu lógica anterior)
    if (paquete === 'seis') precioActualProducto_01 = precioBotinPaqueteSeis;
    else if (paquete === 'doce') precioActualProducto_01 = precioBotinPaqueteDoce;
    
    actualizarSubtotal();
}

/**
 * Controla los botones de + y -
 * @param {string} idInput - El ID del input de cantidad
 * @param {number} cambio - (+1 o -1)
 */
function controlarCantidad(idInput, cambio) {
    const input = document.getElementById(idInput);
    
    // Convertimos el valor actual a número entero
    let valorActual = parseInt(input.value);

    // Si no es un número (por error), asumimos 1
    if (isNaN(valorActual)) valorActual = 1;

    // Calculamos el nuevo valor
    let nuevoValor = valorActual + cambio;

    // Validación: No permitir menos de 1
    if (nuevoValor < 1) {
        nuevoValor = 1;
    }

    // Actualizamos el input visualmente
    input.value = nuevoValor;

    // Recalculamos el precio total
    actualizarSubtotal();
}

/**
 * Valida cuando el usuario escribe directamente en el input
 */
function validarEntrada(idInput) {
    const input = document.getElementById(idInput);
    let valor = parseInt(input.value);

    // Si no es número o es menor a 1, forzamos a 1
    if (isNaN(valor) || valor < 1) {
        valor = 1;
    }

    input.value = valor;
    actualizarSubtotal();
}

/**
 * Función auxiliar para evitar que escriban letras en el input HTML
 */
function soloNumeros(e) {
    var key = window.Event ? e.which : e.keyCode;
    return (key >= 48 && key <= 57); // Solo teclas de números
}

/**
 * FUNCIÓN CENTRAL DE CÁLCULO
 * Toma el precio del paquete seleccionado * la cantidad del input
 * y actualiza el texto en pantalla.
 */
function actualizarSubtotal() {
    // 1. Obtener la cantidad actual del input
    const input = document.getElementById('input_01');
    let cantidad = parseInt(input.value);
    if (isNaN(cantidad)) cantidad = 1;

    // 2. Calcular el total (Precio del paquete guardado * cantidad)
    const total = precioActualProducto_01 * cantidad;

    // 3. Mostrar el resultado en el párrafo de precio
    const elementoPrecio = document.getElementById('precio_item_01');
    if (elementoPrecio) {
        // Usa toLocaleString para formato de miles (ej: $3,060.00) o toFixed(2)
        elementoPrecio.textContent = "$" + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

/**
 * Inicialización al cargar la página
 */
function establecerPrecioInicial() {
    // Nos aseguramos que inicie con el precio del paquete de 6
    precioActualProducto_01 = precioBotinPaqueteSeis;
    actualizarSubtotal();
}

// Evento de carga
document.addEventListener('DOMContentLoaded', (event) => {
    establecerPrecioInicial(); 
});

/* ---------------- SELECCION DE TALLAS ---------------- */

function abrirModalTallas() {
    const modal = document.getElementById('modal-tallas');
    const grid = document.getElementById('grid-tallas');
    
    // Limpiar opciones anteriores
    grid.innerHTML = '';

    // Generar botones de tallas dinámicamente
    tallas.forEach(talla => {
        const btn = document.createElement('button');
        btn.textContent = talla;
        btn.classList.add('btn-talla');
        // Al hacer clic en una talla, se agrega al carrito
        btn.onclick = () => agregarItemAlCarrito(talla);
        grid.appendChild(btn);
    });

    modal.style.display = 'flex'; // Mostrar modal
}

function cerrarModalTallas() {
    document.getElementById('modal-tallas').style.display = 'none';
}

/**
 * LÓGICA PRINCIPAL: Agrega el producto al array y actualiza el HTML
 */
function agregarItemAlCarrito(tallaSeleccionada) {
    // 1. Obtener cantidad y calcular precio final de esta línea
    const inputCantidad = document.getElementById('input_01');
    const cantidad = parseInt(inputCantidad.value);
    const precioUnitario = infoProductoActual.paquete === 'seis' ? precioBotinPaqueteSeis : precioBotinPaqueteDoce;
    const precioTotalLinea = precioUnitario * cantidad;

    // 2. Crear objeto del producto
    const nuevoProducto = {
        nombre: infoProductoActual.nombre,
        imagen: infoProductoActual.imagen,
        color: infoProductoActual.color,
        paquete: infoProductoActual.paquete,
        cantidad: cantidad,
        talla: tallaSeleccionada,
        precioTotal: precioTotalLinea
    };

    // 3. Agregar al array del carrito
    carritoDeCompras.push(nuevoProducto);

    // 4. Renderizar (pintar) el carrito en el HTML
    renderizarCarritoHTML();

    // 5. Cerrar modal y resetear
    cerrarModalTallas();
    resetearControlesProducto();
    
    // Opcional: Abrir el carrito lateral automáticamente para confirmar
    document.getElementById('carrito-lateral').classList.add('activo');
    document.getElementById('carrito-overlay').classList.add('activo');
}

// Actualiza la función renderizarCarritoHTML en tu controlador.js

function renderizarCarritoHTML() {
    const contenedor = document.getElementById('carrito-contenido');
    const elementoTotal = document.getElementById('carrito-precio-final');
    
    contenedor.innerHTML = ''; // Limpiar carrito visual
    let totalGlobal = 0;

    if (carritoDeCompras.length === 0) {
        contenedor.innerHTML = '<p style="text-align: center; margin-top: 20px;">Tu carrito está vacío.</p>';
    } else {
        carritoDeCompras.forEach((producto, index) => { // 🔑 Usamos 'index' para saber qué elemento eliminar
            totalGlobal += producto.precioTotal;

            // Crear HTML para cada item
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('item-carrito');
            
            // 🔑 Agregamos el botón de eliminar, llamando a la nueva función con el índice
            itemDiv.innerHTML = `
                <img src="${producto.imagen}" alt="${producto.color}">
                <div class="item-info">
                    <h4>${producto.nombre}</h4>
                    <p>Color: ${producto.color}</p>
                    <p>Talla: <strong>${producto.talla}</strong> | Paq: ${producto.paquete === 'seis' ? '6' : '12'}</p>
                    <p>Cant: ${producto.cantidad} x $${(producto.precioTotal / producto.cantidad).toFixed(2)}</p>
                    <p style="color: var(--color_uno); font-weight: bold;">$${producto.precioTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                </div>
                <button class="btn-eliminar-item" onclick="eliminarItemDelCarrito(${index})">
                    &times; </button> 
            `;
            contenedor.appendChild(itemDiv);
        });
    }

    // Actualizar el precio total del carrito abajo
    elementoTotal.textContent = "$" + totalGlobal.toLocaleString('en-US', {minimumFractionDigits: 2});
}

/**
 * Elimina un producto del array carritoDeCompras por su índice
 * y actualiza la vista del carrito.
 * @param {number} index - El índice (posición) del elemento a eliminar.
 */
function eliminarItemDelCarrito(index) {
    // 1. Eliminar el producto del array
    // splice(índice, cuántos_eliminar)
    carritoDeCompras.splice(index, 1); 

    // 2. Volver a renderizar el carrito para actualizar la lista y el subtotal
    renderizarCarritoHTML();
}

function resetearControlesProducto() {
    // 1. Resetear cantidad a 1
    const input = document.getElementById('input_01');
    input.value = 1;

    // 2. Resetear paquete a 'seis' (Opcional, depende de tu gusto)
    // Para hacer esto visualmente, simulamos click en el botón de paquete 6
    const btnSeis = document.querySelector('.btn__paquete_seis');
    if(btnSeis) btnSeis.click(); // Esto dispara cambiarPrecio y resetea estilos y lógica

    // 3. Recalcular subtotal de la vista del producto
    actualizarSubtotal();
}

/* ---------------- LOGICA DEL CARRITO LATERAL ---------------- */

function inicializarCarritoLateral() {
    const btnAbrir = document.getElementById('btn-abrir-carrito');
    const btnCerrar = document.getElementById('btn-cerrar-carrito');
    const overlay = document.getElementById('carrito-overlay');
    const carritoLateral = document.getElementById('carrito-lateral');

    // Función para abrir
    if(btnAbrir) {
        btnAbrir.addEventListener('click', (e) => {
            e.preventDefault(); // Evita que la página salte al inicio
            carritoLateral.classList.add('activo');
            overlay.classList.add('activo');
        });
    }

    // Funciones para cerrar (Botón X y clic afuera)
    function cerrarCarrito() {
        carritoLateral.classList.remove('activo');
        overlay.classList.remove('activo');
    }

    if(btnCerrar) btnCerrar.addEventListener('click', cerrarCarrito);
    if(overlay) overlay.addEventListener('click', cerrarCarrito);
}

// Actualizamos el listener principal para incluir la inicialización del carrito
document.addEventListener('DOMContentLoaded', (event) => {
    // Tus funciones anteriores
    establecerPrecioInicial();
    
    // Nueva función del carrito
    inicializarCarritoLateral();
});