function establecerImagen(id, nuevaRuta, nuevoAlt) {
    const imagenElemento = document.getElementById(id);

    imagenElemento.src = nuevaRuta;
    imagenElemento.alt = nuevoAlt;
}

function reducirCantidad(id, cantidadActual) {

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
    // actualizarDatosCarrito(itemContainer.id, snuevoPrecio); 
}

/* ================= MODAL PRODUCTO ================================================================================================================================================================================ */

function abrirModalProducto({ nombre, precio, imagen }) {
    document.getElementById("modalNombre").textContent = nombre;
    document.getElementById("modalPrecio").textContent = `$${precio}`;

    const imagenPrincipal = document.getElementById("modalImagen");
    imagenPrincipal.src = imagen;

    document.querySelectorAll(".miniatura").forEach((img, index) => {
        img.classList.toggle("miniatura__activa", index === 0);
    });

    document.getElementById("modalProducto").classList.add("activo");
}


function cerrarModalProducto() {
    document.getElementById("modalProducto").classList.remove("activo");
}

/* Cerrar al hacer clic fuera */
document.addEventListener("click", function (e) {
    const modal = document.getElementById("modalProducto");
    if (e.target === modal) {
        cerrarModalProducto();
    }
});

/* ================= TALLAS ================= */
let tallaSeleccionada = null;

function seleccionarTalla(btn) {
    const botones = btn.parentElement.querySelectorAll("button");

    botones.forEach(b => b.classList.remove("talla__activa"));
    btn.classList.add("talla__activa");

    tallaSeleccionada = btn.textContent;
}

/* ================= GALERÍA ================= */

function cambiarImagenPrincipal(imgPequena) {
    const contenedor = imgPequena.closest(".modal__imagen");
    const imagenPrincipal = contenedor.querySelector(".imagen__principal");
    const miniaturas = contenedor.querySelectorAll(".miniatura");

    miniaturas.forEach(img => img.classList.remove("miniatura__activa"));

    imagenPrincipal.src = imgPequena.src;
    imgPequena.classList.add("miniatura__activa");
}