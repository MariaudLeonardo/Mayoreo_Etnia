let precioBotinPaqueteSeis = 510;
let precioBotinPaqueteDoce = 432;

let precioBalerinaPaqueteSeis = 365;
let precioBalerinaPaqueteDoce = 260;

let precioConfortPaqueteSeis = 453;
let precioConfortPaqueteDoce = 380;

let precioCasualPaqueteSeis = 315;
let precioCasualPaqueteDoce = 275;

let precioSandaliaConfortPaqueteSeis = 390;
let precioSandaliaConfortPaqueteDoce = 310;

// Funciones

/**
 * Establece el precio inicial (por defecto del paquete de 6) en el elemento HTML especificado.
 * @param {string} id - El ID del elemento <p> del precio.
 */
function establecerPrecioInicial(id) {
    // 1. Seleccionar el elemento <p> usando su ID
    const elementoPrecio = document.getElementById(id);
    
    if (elementoPrecio) {
        // 2. Corrección de sintaxis: Usamos el signo $
        const textoFinal = "$" + precioBotinPaqueteSeis + ".00"; 
        
        // 3. Asignar el valor
        elementoPrecio.textContent = textoFinal;
    } else {
        console.error(`Error: Elemento de precio con ID "${id}" no encontrado.`);
    }
}

// Añadir aqui todos los precios de cada producto
document.addEventListener('DOMContentLoaded', (event) => {
    // Llamar la función con el ID correcto de tu HTML
    establecerPrecioInicial('precio_item_01'); 
    
    // Aquí puedes llamar a otras funciones de inicialización si las tienes
});

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



