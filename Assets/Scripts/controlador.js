function establecerImagen(id, nuevaRuta, nuevoAlt) {
        const imagenElemento = document.getElementById(id);
        
        imagenElemento.src = nuevaRuta;
        imagenElemento.alt = nuevoAlt;
    }

function reducirCantidad(id, cantidadActual){

}

// Precios reales del producto (Botin con Hebilla)
const PRECIO_PAQUETE_6 = 510; 
const PRECIO_PAQUETE_12 = 432; // Precio de ejemplo, más bajo para el de 12

function cambiarPrecio(toggleElement) {
    // 1. Obtener las referencias de los elementos
    const itemContainer = toggleElement.closest('.container__item_01');
    
    // Asumiendo que el precio tiene la clase 'precio' dentro de 'info__item_01'
    const precioElemento = itemContainer.querySelector('.info__item_01 .precio'); 
    const labelElemento = itemContainer.querySelector('#paqueteLabel_01');
    
    let nuevoPrecio;
    let nuevaEtiqueta;

    // 2. Determinar el nuevo precio basado en el estado del toggle
    if (toggleElement.checked) {
        // Toggle ACTIVADO: Paquete de 12 (Precio más bajo)
        nuevoPrecio = PRECIO_PAQUETE_12;
        //nuevaEtiqueta = 'Paquete de 12';
    } else {
        // Toggle DESACTIVADO: Paquete de 6 (Precio normal)
        nuevoPrecio = PRECIO_PAQUETE_6;
        //nuevaEtiqueta = 'Paquete de 6';
    }

    // 3. Actualizar los elementos en la página
    precioElemento.textContent = `$${nuevoPrecio}`;
    labelElemento.textContent = nuevaEtiqueta;
    
    // Opcional: Llamar a una función de actualización del carrito si existe
    // actualizarDatosCarrito(itemContainer.id, nuevoPrecio); 
}