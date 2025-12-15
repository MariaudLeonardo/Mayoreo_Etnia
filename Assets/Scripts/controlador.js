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
        
        imagenElemento.src = nuevaRuta;
        imagenElemento.alt = nuevoAlt;
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

    // Recalculamos el total mostrando el cambio
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