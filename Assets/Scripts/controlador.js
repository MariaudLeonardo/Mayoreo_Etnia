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

let tallas = [21, 21.5, 22, 22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26, 26.5, 27, 27.5, 28, 28.5, 29, 29.5, 30]

// Funciones

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
 * Establece el precio en el elemento HTML especificado según el paquete.
 * @param {string} id - El ID del elemento <p> del precio (ej: 'precio_item_01').
 * @param {string} paquete - La opción seleccionada: 'seis' o 'doce'.
 */
function cambiarPrecio(id, paquete) {
    const elementoPrecio = document.getElementById(id);
    let precio = 0;

    if (!elementoPrecio) {
        console.error(`Error: Elemento de precio con ID "${id}" no encontrado.`);
        return;
    }

    if (paquete === 'seis') {
        precio = precioBotinPaqueteSeis;
    } else if (paquete === 'doce') {
        precio = precioBotinPaqueteDoce;
    }

    // Formatear y asignar el nuevo precio
    elementoPrecio.textContent = "$" + precio.toFixed(2); // .toFixed(2) añade decimales para moneda
}

/**
 * Función que inicializa los precios al cargar la página (Paquete 6 por defecto).
 * @param {string} id - El ID del elemento <p> del precio.
 */
function establecerPrecioInicial(id) {
    cambiarPrecio(id, 'seis'); // 🔑 Establece el precio inicial al paquete 6
}

// LLamar funciones inciales
document.addEventListener('DOMContentLoaded', (event) => {
    // Inicializa el precio del Producto 1 (Paquete 6 por defecto)
    establecerPrecioInicial('precio_item_01'); 
    
});



