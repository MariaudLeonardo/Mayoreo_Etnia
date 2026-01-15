/* -------------------------------------------- VARIABLES -------------------------------------------- */

// 1. Definimos la carpeta raíz del proyecto (Ajusta si cambias el nombre de la carpeta en htdocs)
const ROOT_PATH = '/Mayoreo_Etnia/';

// 2. Definimos las rutas EXACTAS a las APIs (Rutas Absolutas)
const API_CARRITO = ROOT_PATH + 'Assets/Scripts/api_carrito.php';
const API_FAVORITOS = ROOT_PATH + 'Assets/Scripts/api_favoritos.php';

// Variables para el modal de producto
var idZapatoActual = 0;
var nombreZapatoActual = "";
var precioZapatoActual = 0;
var imagenZapatoActual = "";
var idColorActual = null;
var idCategoriaActualGlobal = 1;

let modalProductoActual = null;
let modalTallaSeleccionada = null; // (Ya no se usa mucho, pero se deja por compatibilidad)
let modalColorSeleccionado = 0;
let modalPaqueteSeleccionado = 'seis';
let modalPrecioBase = 0;
let idZapatoActualEnModal = 0;

var carritoDeCompras = carritoDeCompras || [];
// CONFIGURACIÓN CORREGIDA: Solo de 22.5 a 26
const tallasDisponibles = [22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26];
let estadoDistribucion = {
    idProducto: null,
    metaTotal: 6,
    conteoActual: 0,
    tallas: {},
    tallaEditando: null,
    basePaquete: 6,
    // NUEVO: Para guardar info visual si venimos del modal de detalle
    infoOverride: null
};

/* -------------------------------------------- FUNCIONES GENERALES -------------------------------------------- */
// ... (Tus funciones generales establecerImagen, cambiarPrecio, etc. se quedan igual) ...
// ... Copia aquí las funciones: establecerImagen, cambiarPrecio, controlarCantidad, validarEntrada, soloNumeros ...

function establecerImagen(idElemento, nuevaRuta, nuevoAlt) {
    const imagenElemento = document.getElementById(idElemento);
    if (imagenElemento) {
        imagenElemento.src = nuevaRuta;
        imagenElemento.alt = nuevoAlt;
    }
}

function cambiarPrecio(idPrecio, paquete, idProducto) {
    const elementoPrecio = document.getElementById(idPrecio);
    if (!elementoPrecio) return;

    // 1. OBTENER PRECIO INDIVIDUAL (Tu lógica original intacta)
    let precioIndividual = parseFloat(elementoPrecio.getAttribute('data-precio-individual'));
    if (isNaN(precioIndividual) || precioIndividual === 0) {
        precioIndividual = parseFloat(elementoPrecio.getAttribute('data-precio-base'));
    }

    if (isNaN(precioIndividual) || precioIndividual === 0) {
        const precioActual = parseFloat(elementoPrecio.textContent.replace('$', '').replace(',', ''));
        const paqueteActual = elementoPrecio.getAttribute('data-paquete-actual') || 'seis';
        precioIndividual = (paqueteActual === 'seis') ? precioActual / (6 * 0.9) : precioActual / (12 * 0.8);
        if (isNaN(precioIndividual)) precioIndividual = precioActual;
        elementoPrecio.setAttribute('data-precio-individual', precioIndividual);
    }

    // 2. OBTENER LA CANTIDAD DE PAQUETES ACTUAL (La corrección)
    // Buscamos el input de cantidad para saber si hay 1, 5 o 10 paquetes seleccionados
    const inputCantidad = document.getElementById(`input_${idProducto}`);
    const cantPaquetes = inputCantidad ? (parseInt(inputCantidad.value) || 1) : 1;

    // 3. CÁLCULO DE PRECIO POR PAQUETE
    let precioUnPaquete = (paquete === 'seis') ? precioIndividual * 6 * 0.9 : (paquete === 'doce' ? precioIndividual * 12 * 0.8 : precioIndividual);

    // 4. CÁLCULO DEL TOTAL REAL (Precio paquete x Cantidad seleccionada)
    let totalFinal = precioUnPaquete * cantPaquetes;

    // 5. ACTUALIZAR UI (Usamos toLocaleString para que se vea profesional con comas)
    elementoPrecio.textContent = '$' + totalFinal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // 6. ACTUALIZAR ATRIBUTOS (Para que el carrito lea los valores correctos)
    elementoPrecio.setAttribute('data-paquete-actual', paquete);
    elementoPrecio.setAttribute('data-precio-paquete', precioUnPaquete); // Guardamos lo que vale 1 paquete solo

    // 7. GESTIÓN DE BOTONES ACTIVOS (Tu lógica original)
    const contenedor = document.querySelector(`.container__item_01 #precio_item_${idProducto}`)?.closest('.container__item_01');
    if (contenedor) {
        contenedor.querySelectorAll('.btn__paquete_seis, .btn__paquete_doce').forEach(btn => btn.classList.remove('activo'));
        contenedor.querySelector(`.btn__paquete_${paquete}`)?.classList.add('activo');
    }
}

function controlarCantidad(idInput, cambio) {
    const input = document.getElementById(idInput);
    let valor = parseInt(input.value) || 0;
    valor += cambio; if (valor < 1) valor = 1; input.value = valor;
    const idProd = idInput.replace('input_', '');
    // Importante: Si existe la función, úsala. Si no la has copiado, asegúrate de tener actualizarPrecioTarjeta
    if (typeof actualizarPrecioTarjeta === 'function') actualizarPrecioTarjeta(idProd);
}

function validarEntrada(idInput) {
    const input = document.getElementById(idInput);
    let valor = parseInt(input.value) || 1; if (valor < 1) valor = 1; input.value = valor;
    const idProd = idInput.replace('input_', '');
    if (typeof actualizarPrecioTarjeta === 'function') actualizarPrecioTarjeta(idProd);
}

function soloNumeros(event) {
    const k = event.which || event.keyCode;
    return (k <= 31 || (k >= 48 && k <= 57));
}

// Necesitas esta función de la respuesta anterior para que funcionen los + / - de las tarjetas
function actualizarPrecioTarjeta(idProducto) {
    const elementoPrecio = document.getElementById(`precio_item_${idProducto}`);
    const inputCantidad = document.getElementById(`input_${idProducto}`);
    const contenedor = elementoPrecio?.closest('.container__item_01');

    if (!elementoPrecio || !inputCantidad || !contenedor) return;

    // 1. Obtener el precio base por par
    let precioIndividual = parseFloat(elementoPrecio.getAttribute('data-precio-individual'));
    if (isNaN(precioIndividual) || precioIndividual === 0) {
        precioIndividual = parseFloat(elementoPrecio.getAttribute('data-precio-base'));
    }

    // 2. Determinar el tipo de paquete activo (6 o 12)
    const esDoce = contenedor.querySelector('.btn__paquete_doce').classList.contains('activo');
    const pares = esDoce ? 12 : 6;
    const factor = esDoce ? 0.8 : 0.9; // 20% desc para 12 pares, 10% desc para 6 pares

    // 3. Obtener la cantidad de paquetes desde el input
    const cantPaquetes = parseInt(inputCantidad.value) || 1;

    // 4. Calcular el precio de UN SOLO PAQUETE
    const precioUnPaquete = precioIndividual * pares * factor;

    // 5. Calcular el TOTAL (Precio paquete x Cantidad de paquetes)
    const totalFinal = precioUnPaquete * cantPaquetes;

    // 6. ACTUALIZAR UI: Mostramos el total con formato de moneda (comas y puntos)
    elementoPrecio.textContent = '$' + totalFinal.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // 7. ACTUALIZAR ATRIBUTOS: 
    // Guardamos el precio de UN paquete para que el carrito haga su propia multiplicación después
    elementoPrecio.setAttribute('data-precio-paquete', precioUnPaquete);
}


/* ==========================================================================
   LOGICA DE DISTRIBUCIÓN DE TALLAS (MODIFICADA PARA ACEPTAR DATOS DEL MODAL)
   ========================================================================== */

/**
 * Ahora acepta un segundo parámetro opcional 'configOverride'
 * Si viene del Modal Detalle, usaremos esos datos en lugar de leer el DOM de la tarjeta
 */
/* =======================================================
   MODAL DE DISTRIBUCIÓN DE TALLAS (LÓGICA COMPLETA)
   ======================================================= */

function abrirModalTallas(idZapato, configOverride = null) {
    // 1. Sincronizar ID global
    idZapatoActual = idZapato;

    let totalParesMeta = 6;     // Valor por defecto base
    let paresPorPaquete = 6;    // Valor por defecto base
    let categoriaId = 1;        // Valor por defecto

    // ---------------------------------------------------------
    // CASO A: Venimos del Modal de Producto Detallado (Override)
    // ---------------------------------------------------------
    if (configOverride) {
        paresPorPaquete = configOverride.basePaquete;
        totalParesMeta = configOverride.metaTotal;
        categoriaId = configOverride.id_categoria || 1; // Capturar categoría del modal
    }
    // ---------------------------------------------------------
    // CASO B: Venimos directo de la tarjeta (Click carrito rápido)
    // ---------------------------------------------------------
    else {
        // Buscamos los inputs de la tarjeta específica en el Index/Tienda
        const inputCantidad = document.getElementById('input_' + idZapato);
        const elementoPrecio = document.getElementById('precio_item_' + idZapato);

        // Determinamos si el usuario tiene seleccionado el paquete de 6 o 12
        let esDoce = false;
        if (elementoPrecio) {
            const paqueteTexto = elementoPrecio.getAttribute('data-paquete-actual');
            const contenedorBtn = elementoPrecio.closest('.container__item_01');
            const btnDoceActivo = contenedorBtn ? contenedorBtn.querySelector('.btn__paquete_doce.activo') : null;

            esDoce = (paqueteTexto === 'doce') || (btnDoceActivo !== null);
        }

        paresPorPaquete = esDoce ? 12 : 6;

        // Calculamos la cantidad de paquetes
        let cantidadPaquetes = 1;
        if (inputCantidad) {
            cantidadPaquetes = parseInt(inputCantidad.value) || 1;
        }

        totalParesMeta = paresPorPaquete * cantidadPaquetes;

        // FIX 1: Recuperar la categoría guardada por el puente
        categoriaId = (typeof idCategoriaActualGlobal !== 'undefined') ? idCategoriaActualGlobal : 1;
    }

    // ---------------------------------------------------------
    // 2. GUARDAMOS EL ESTADO GLOBAL DE LA DISTRIBUCIÓN
    // ---------------------------------------------------------
    estadoDistribucion = {
        idProducto: idZapato,
        metaTotal: totalParesMeta,
        conteoActual: 0,
        tallas: {},
        tallaEditando: null,
        basePaquete: paresPorPaquete,
        precioUnitario: (typeof precioZapatoActual !== 'undefined') ? precioZapatoActual : 0,
        categoria_id: categoriaId,
        idColor: configOverride ? configOverride.idColor : (idColorActual || null),
        infoOverride: configOverride ? configOverride.infoVisual : null
    };

    // ---------------------------------------------------------
    // 3. ACTUALIZAR INTERFAZ (UI) INICIAL
    // ---------------------------------------------------------
    const lblMeta = document.getElementById('lbl-meta-pares');
    if (lblMeta) lblMeta.textContent = totalParesMeta;

    const panelEditor = document.getElementById('panel-editor-talla');
    if (panelEditor) panelEditor.classList.remove('activo');

    if (typeof actualizarFeedbackSuma === 'function') {
        actualizarFeedbackSuma();
    }

    // ---------------------------------------------------------
    // 4. GENERAR EL GRID DINÁMICO DE BOTONES DE TALLAS
    // ---------------------------------------------------------
    const grid = document.getElementById('grid-tallas');

    if (grid) {
        grid.innerHTML = '';

        // AJUSTE: Solo tallas de 22.5 a 26 según tus IDs de base de datos
        let tallasLocales = [22.5, 23, 23.5, 24, 24.5, 25, 25.5, 26];

        tallasLocales.forEach(talla => {
            const btnWrapper = document.createElement('div');
            btnWrapper.style.position = 'relative';

            const btn = document.createElement('button');
            btn.textContent = talla;
            btn.classList.add('btn-talla');

            const safeId = talla.toString().replace('.', '-');
            btn.id = `btn-talla-${safeId}`;

            btn.onclick = () => activarEditorTalla(talla);

            const badge = document.createElement('div');
            badge.classList.add('badge-cantidad');
            badge.id = `badge-talla-${safeId}`;
            badge.textContent = '0';
            badge.style.display = 'none';

            btnWrapper.appendChild(btn);
            btnWrapper.appendChild(badge);
            grid.appendChild(btnWrapper);
        });
    }

    // ---------------------------------------------------------
    // 5. CONFIGURAR SLIDER Y MOSTRAR MODAL
    // ---------------------------------------------------------
    const slider = document.getElementById('slider-talla');
    if (slider) {
        slider.max = totalParesMeta;
        slider.value = 0;
    }

    const inputManual = document.getElementById('input-talla-manual');
    if (inputManual) {
        inputManual.value = 0;
    }

    const modal = document.getElementById('modal-tallas');

    if (modal) {
        modal.classList.add('activo');
        modal.style.display = 'flex';
    }

    // Activamos la talla 24 por defecto (ID 4 en tu DB)
    if (typeof activarEditorTalla === 'function') {
        activarEditorTalla(24);
    }
}

/**
 * Puente para capturar datos reales y enviarlos al modal de tallas
 * @param {number} id - ID del zapato
 * @param {string} nombre - Nombre del zapato
 * @param {number} precio - Precio unitario (con oferta aplicada)
 * @param {string} imagen - Ruta de la imagen principal
 * @param {number} id_categoria - ID numérico de la categoría (botines, casual, etc)
 */
function abrirModalTallasConDatos(id, nombre, precio, imagen, id_categoria) {
    // 1. Sincronizamos las variables globales
    idZapatoActual = id;
    nombreZapatoActual = nombre;
    precioZapatoActual = parseFloat(precio);
    imagenZapatoActual = imagen;

    // GUARDAMOS LA CATEGORÍA en la global por seguridad
    idCategoriaActualGlobal = id_categoria;

    const contenedorTarjeta = document.getElementById('item_' + id).closest('.container__item_01');
    const btnColorActivo = contenedorTarjeta.querySelector('.btn__color.activo');

    idColorActual = btnColorActivo ? btnColorActivo.getAttribute('data-id-color') : null;

    // 2. Cálculos de paquetes (tu lógica actual)
    const inputCantidad = document.getElementById('input_' + id);
    const cantidadPaquetes = inputCantidad ? (parseInt(inputCantidad.value) || 1) : 1;
    const elementoPrecio = document.getElementById('precio_item_' + id);
    const paresPorPaquete = (elementoPrecio && elementoPrecio.getAttribute('data-paquete-actual') === 'doce') ? 12 : 6;
    const metaCalculada = paresPorPaquete * cantidadPaquetes;

    // 3. LA CORRECCIÓN: Pasamos los datos dentro del objeto 'config'
    // Esto evita que abrirModalTallas tenga que "adivinar" o leer el DOM de nuevo
    const configRápida = {
        basePaquete: paresPorPaquete,
        metaTotal: metaCalculada,
        id_categoria: id_categoria,
        idColor: idColorActual, // <--- AGREGAMOS ESTA LÍNEA
        infoVisual: {
            nombre: nombre,
            imagen: imagen,
            precioTotalCalculado: precio
        }
    };

    if (typeof abrirModalTallas === 'function') {
        // Ahora enviamos el objeto como segundo argumento
        abrirModalTallas(id, configRápida);
    }
}

// ... (Las funciones auxiliares del slider se quedan igual) ...
function activarEditorTalla(talla) {
    estadoDistribucion.tallaEditando = talla;
    document.getElementById('lbl-talla-editor').textContent = talla;
    document.querySelectorAll('.btn-talla').forEach(b => b.classList.remove('talla__activa'));
    document.getElementById(`btn-talla-${talla.toString().replace('.', '-')}`).classList.add('talla__activa');
    document.getElementById('panel-editor-talla').classList.add('activo');

    const cant = estadoDistribucion.tallas[talla] || 0;
    const max = (estadoDistribucion.metaTotal - estadoDistribucion.conteoActual) + cant;

    const slider = document.getElementById('slider-talla');
    const input = document.getElementById('input-talla-manual');
    slider.max = estadoDistribucion.metaTotal;
    slider.value = cant;
    input.value = cant;
    input.max = max;
}

function actualizarDesdeSlider(v) { validarYGuardarValor(parseInt(v)); }
function actualizarDesdeInput(v) { validarYGuardarValor(parseInt(v) || 0); }
function ajustarValorEditor(c) {
    const input = document.getElementById('input-talla-manual');
    validarYGuardarValor((parseInt(input.value) || 0) + c);
}

function validarYGuardarValor(nuevoValor) {
    if (nuevoValor < 0) nuevoValor = 0;
    const talla = estadoDistribucion.tallaEditando;
    const cantAnt = estadoDistribucion.tallas[talla] || 0;
    const max = (estadoDistribucion.metaTotal - estadoDistribucion.conteoActual) + cantAnt;

    if (nuevoValor > max) nuevoValor = max;

    if (nuevoValor > 0) estadoDistribucion.tallas[talla] = nuevoValor;
    else delete estadoDistribucion.tallas[talla];

    document.getElementById('slider-talla').value = nuevoValor;
    document.getElementById('input-talla-manual').value = nuevoValor;
    actualizarBadgeBoton(talla, nuevoValor);
    recalcularTotal();
}

function actualizarBadgeBoton(talla, cantidad) {
    const badge = document.getElementById(`badge-talla-${talla.toString().replace('.', '-')}`);
    if (badge) {
        badge.textContent = cantidad;
        if (cantidad > 0) {
            badge.classList.add('visible');
            badge.parentElement.querySelector('button').classList.add('talla__activa');
        } else {
            badge.classList.remove('visible');
            if (estadoDistribucion.tallaEditando !== talla) badge.parentElement.querySelector('button').classList.remove('talla__activa');
        }
    }
}

function recalcularTotal() {
    let suma = 0;
    Object.values(estadoDistribucion.tallas).forEach(c => suma += c);
    estadoDistribucion.conteoActual = suma;
    actualizarFeedbackSuma();
}

function actualizarFeedbackSuma() {
    const div = document.getElementById('feedback-suma');
    const btn = document.getElementById('btn-confirmar-tallas');
    const actual = estadoDistribucion.conteoActual;
    const meta = estadoDistribucion.metaTotal;
    div.textContent = `Llevas ${actual} de ${meta} pares asignados`;
    div.className = 'feedback-suma';
    if (actual === meta) {
        div.classList.add('completo');
        div.innerHTML = `✅ ¡Pedido completo! (${actual} de ${meta})`;
        btn.disabled = false;
        btn.classList.add('habilitado');
    } else {
        if (actual > meta) div.classList.add('exceso');
        btn.disabled = true;
        btn.classList.remove('habilitado');
    }
}


/**
 * ACCIÓN FINAL: Agregar al carrito
 * Ahora verifica si tenemos info "Override" (del modal detalle) o si debe leer del DOM
 */
function confirmarYAgregarAlCarrito() {
    const idZapato = estadoDistribucion.idProducto;
    const pares = estadoDistribucion.basePaquete; // 6 o 12
    const cantidadPaquetes = estadoDistribucion.metaTotal / pares;
    const idGrupoUnico = 'GRP-' + Date.now();
    let nombre, imagenSrc, colorTexto, precioTotal;

    let precioUnitarioBase = estadoDistribucion.precioUnitario;


    // Aplicamos el factor de descuento por paquete: 6 = 10% (0.9), 12 = 20% (0.8)
    const factorDescuento = (pares === 12) ? 0.80 : 0.90;

    // CÁLCULO REAL: (Precio Unitario * Pares * Descuento * Cantidad de Paquetes)
    const precioFinalCalculado = precioUnitarioBase * pares * factorDescuento * cantidadPaquetes;



    // --- (Mantén tu lógica de A y B igual para obtener nombre, imagen y precio) ---
    if (estadoDistribucion.infoOverride) {
        const info = estadoDistribucion.infoOverride;
        nombre = info.nombre;
        imagenSrc = info.imagen;
        colorTexto = info.colorNombre;
        precioTotal = info.precioTotalCalculado;
    } else {
        const imagenElemento = document.getElementById('item_' + idZapato);
        const infoContainer = imagenElemento.closest('.container__item_01');
        nombre = infoContainer.querySelector('h2').textContent;
        imagenSrc = imagenElemento.src;
        colorTexto = imagenElemento.alt || "Estándar";
        const precioTexto = document.getElementById('precio_item_' + idZapato).textContent;
        precioTotal = parseFloat(precioTexto.replace('$', '').replace(',', ''));
    }

    // 1. Objeto para la interfaz local (Se queda igual)
    const nuevoProductoLocal = {
        id_grupo: idGrupoUnico,
        id: idZapato,
        nombre: nombreZapatoActual,
        imagen: imagenZapatoActual,
        color: (typeof idColorActual !== 'undefined') ? idColorActual : "Estándar",
        paquete: (pares === 6) ? 'seis' : 'doce',
        cantidadTotal: estadoDistribucion.metaTotal,
        precioTotal: precioFinalCalculado, // <--- AQUÍ YA VA EL TOTAL REAL ($1,632)
        desgloseTallas: { ...estadoDistribucion.tallas }
    };

    // 2. CORRECCIÓN: Preparar datos para el Servidor
    const productoParaServer = {
        id: estadoDistribucion.idProducto,

        idColor: estadoDistribucion.idColor || null,

        id_grupo: idGrupoUnico,
        paquete: estadoDistribucion.basePaquete === 12 ? 'doce' : 'seis',
        id_categoria: estadoDistribucion.categoria_id,
        precioUnitario: estadoDistribucion.precioUnitario,
        desgloseTallas: estadoDistribucion.tallas
    };

    // 3. ÚNICA LLAMADA AL SERVIDOR
    fetch(API_CARRITO, {  // <--- CAMBIO AQUÍ
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            accion: 'agregar',
            producto: productoParaServer
        })
    })

        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Si se guardó en la BD, recargamos el carrito local para mostrar los cambios
                cargarCarritoDesdeBD();
                cerrarModalTallas();

                // Abrimos el carrito lateral automáticamente
                document.getElementById('carrito-lateral').classList.add('activo');
                document.getElementById('carrito-overlay').classList.add('activo');
            } else {
                alert("Error al guardar: " + data.msg);
            }
        })
        .catch(err => console.error("Error en la inserción:", err));
}

function eliminarGrupoDelCarrito(idGrupo) {
    // Hemos eliminado la línea del confirm() para que el borrado sea directo

    // 1. Llamada a la base de datos para borrar el grupo
    fetch(API_CARRITO, { // <--- CAMBIO AQUÍ
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            accion: 'eliminar_grupo',
            id_grupo: idGrupo
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // 2. Filtramos el arreglo local para quitar el producto de la vista
                carritoDeCompras = carritoDeCompras.filter(p => p.id_grupo !== idGrupo);

                // 3. Redibujamos el carrito lateral inmediatamente
                renderizarCarritoHTML();

                // Si prefieres que la página se refresque por completo, usa:
                // location.reload(); 
            } else {
                console.error("Error al eliminar de la BD: " + data.msg);
            }
        })
        .catch(err => console.error("Error de conexión:", err));
}

function cargarCarritoDesdeBD() {
    // Solo intentamos cargar si hay un usuario logueado
    if (typeof usuarioLogueado === 'undefined' || !usuarioLogueado) return;

    fetch(API_CARRITO, { // <--- CAMBIO AQUÍ
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'obtener_carrito' })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Llenamos nuestro arreglo global con lo que hay en la base de datos
                carritoDeCompras = data.carrito;
                renderizarCarritoHTML(); // Dibujamos el carrito lateral
            }
        })
        .catch(err => console.error("Error al cargar carrito:", err));
}

// ACTUALIZA TU DOMContentLoaded PARA INCLUIR LA CARGA
document.addEventListener('DOMContentLoaded', () => {
    inicializarCarritoLateral();
    cargarCarritoDesdeBD(); // <--- LLAMADA CLAVE
    // ... resto de tus inicializaciones ...
});

function cerrarModalTallas() {
    const modal = document.getElementById('modal-tallas');
    if (modal) {
        // 1. Quitamos la clase de CSS
        modal.classList.remove('activo');
        // 2. IMPORTANTE: Anulamos el estilo flex para que el modal desaparezca
        modal.style.display = 'none';
    }
}

// ... (renderizarCarritoHTML, eliminarItemDelCarrito, inicializarCarritoLateral SE QUEDAN IGUAL) ...
// Copia aquí las funciones: renderizarCarritoHTML, eliminarItemDelCarrito, inicializarCarritoLateral de la respuesta anterior


/* ================= MODAL DE PRODUCTO (DETALLE) ================= */

/* ================= MODAL DE PRODUCTO (DETALLE) CON FILTRO DE COLOR ================= */

function abrirModalProducto(producto) {
    if (!producto) return;

    // CORRECCIÓN: Guardar el objeto completo para que modalAgregarAlCarrito lo vea
    modalProductoActual = producto;
    idZapatoActualEnModal = producto.id; // Usamos 'producto', no 'datos'

    // Reseteamos variables de estado del modal
    modalPaqueteSeleccionado = 'seis';
    modalPrecioBase = parseFloat(producto.precio);

    const inputCant = document.getElementById('modalCantidad');
    if (inputCant) inputCant.value = 1;

    // Llenar Textos con seguridad
    const nom = document.getElementById('modalNombre');
    const cat = document.getElementById('modalCategoria');
    if (nom) nom.textContent = producto.nombre;
    if (cat) cat.textContent = producto.categoria;

    // Imagen Principal
    const imgPrincipal = document.getElementById('modalImagenPrincipal');
    if (imgPrincipal) {
        imgPrincipal.src = producto.imagenPortada || '../../Assets/Imagenes/default.png';
    }

    // --- GENERACIÓN DE COLORES ---
    const coloresContainer = document.getElementById('modalColores');
    if (coloresContainer) {
        coloresContainer.innerHTML = '';
        if (producto.colores && producto.colores.length > 0) {
            producto.colores.forEach((colorObj, index) => {
                const btn = document.createElement('button');
                btn.classList.add('modal__color');
                btn.style.backgroundColor = colorObj.hex;
                btn.title = colorObj.nombre;
                btn.onclick = () => {
                    coloresContainer.querySelectorAll('.modal__color').forEach(c => c.classList.remove('activo'));
                    btn.classList.add('activo');
                    modalColorSeleccionado = index;
                    cargarImagenesPorColor(colorObj.id, producto.imagenesPorColor);
                };
                coloresContainer.appendChild(btn);
                if (index === 0) btn.click(); // Seleccionar primero
            });
        }
    }

    // --- TALLAS (Visual) ---
    const tallasContainer = document.getElementById('modalTallas');
    if (tallasContainer) {
        tallasContainer.innerHTML = '<h3>Tallas Disponibles:</h3><div style="display:flex; gap:5px; flex-wrap:wrap;">';
        if (producto.tallas) {
            producto.tallas.forEach(t => {
                tallasContainer.innerHTML += `<span style="padding:5px; border:1px solid #ccc; border-radius:4px; font-size:12px;">${t}</span>`;
            });
        }
        tallasContainer.innerHTML += '</div>';
    }

    // Lógica de Favoritos (Usando la variable correcta)
    verificarEstadoFavorito(producto.id);

    modalCalcularPrecio();
    document.getElementById('modalProducto').classList.add('activo');
}


// 1. Consultar si es favorito al abrir
function verificarEstadoFavorito(idZapato) {
    const btn = document.getElementById('btn-favorito-modal');
    if (!btn) return;
    btn.classList.remove('activo');

    if (typeof usuarioLogueado === 'undefined' || !usuarioLogueado) return;


    // 3. CAMBIO CLAVE: Usar la ruta dinámica
    const urlDestino = API_FAVORITOS;

    const formData = new FormData();
    formData.append('accion', 'verificar');
    formData.append('id_zapato', idZapato);

    fetch(urlDestino, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            // 4. Si el servidor dice que SÍ es favorito, llenamos la estrella
            if (data.status === 'success' && data.is_favorite) {
                btn.classList.add('activo');
            }
        })
        .catch(err => console.error("Error al verificar favorito:", err));
}

// 2. Acción al hacer clic en la estrella
function toggleFavoritoModal() {
    if (typeof usuarioLogueado === 'undefined' || !usuarioLogueado) { // Validación segura
        alert("Inicia sesión para guardar tus favoritos.");
        // Asegúrate de que abrirModalAuth exista o redirige al login
        if(typeof abrirModalAuth === 'function') abrirModalAuth();
        return;
    }

    const btn = document.getElementById('btn-favorito-modal');
    const formData = new FormData();
    formData.append('accion', 'toggle');
    formData.append('id_zapato', idZapatoActualEnModal);

    // USAR LA VARIABLE DINÁMICA DE RUTA
    const urlDestino = API_FAVORITOS

    fetch(urlDestino, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.action === 'added') {
                    btn.classList.add('activo');
                } else {
                    btn.classList.remove('activo');
                }
            } else {
                console.error("Error del servidor:", data.msg); // Para saber por qué falló en la base de datos
            }
        })
        .catch(err => console.error("Error de conexión:", err));
}

/**
 * FUNCIÓN AUXILIAR: Carga las miniaturas del ID de color seleccionado
 */
function cargarImagenesPorColor(idColor, mapaImagenes) {
    const miniaturasContainer = document.getElementById('modalMiniaturas');
    const imagenPrincipal = document.getElementById('modalImagenPrincipal');

    // Limpiar lo que había antes
    miniaturasContainer.innerHTML = '';

    // Buscar las fotos en el mapa usando el ID (ej: "1" o "2")
    const imagenes = mapaImagenes[idColor] || [];

    if (imagenes.length > 0) {
        // Poner la primera foto de este color en grande
        imagenPrincipal.src = imagenes[0];

        // Crear las miniaturas
        imagenes.forEach((rutaImg, i) => {
            const img = document.createElement('img');
            img.src = rutaImg;
            img.classList.add('miniatura');
            if (i === 0) img.classList.add('miniatura__activa');

            img.onclick = () => {
                imagenPrincipal.src = rutaImg;
                miniaturasContainer.querySelectorAll('.miniatura').forEach(m => m.classList.remove('miniatura__activa'));
                img.classList.add('miniatura__activa');
            };
            miniaturasContainer.appendChild(img);
        });
    } else {
        // Fallback si el color no tiene fotos asignadas en la BD
        imagenPrincipal.src = '../../Assets/Imagenes/default.png';
        miniaturasContainer.innerHTML = '<span style="font-size:12px; color:#999; padding:10px;">Sin fotos para este color</span>';
    }
}

function cerrarModalProducto() { document.getElementById('modalProducto').classList.remove('activo'); }

function modalCambiarPaquete(paquete) {
    modalPaqueteSeleccionado = paquete;
    document.querySelectorAll('.modal__paquetes button').forEach(btn => {
        btn.classList.remove('activo');
        if ((paquete === 'seis' && btn.textContent.includes('6')) || (paquete === 'doce' && btn.textContent.includes('12'))) {
            btn.classList.add('activo');
        }
    });
    modalCalcularPrecio();
}

function modalControlarCantidad(cambio) {
    const input = document.getElementById('modalCantidad');
    let valor = parseInt(input.value) || 1;
    valor += cambio; if (valor < 1) valor = 1; input.value = valor;
    modalCalcularPrecio();
}

function modalValidarEntrada() {
    const input = document.getElementById('modalCantidad');
    let valor = parseInt(input.value) || 1; if (valor < 1) valor = 1; input.value = valor;
    modalCalcularPrecio();
}

function modalCalcularPrecio() {
    const cantidad = parseInt(document.getElementById('modalCantidad').value) || 1;
    let precioUnitario = modalPrecioBase;

    // Calcular unitario con descuento
    if (modalPaqueteSeleccionado === 'seis') precioUnitario = modalPrecioBase * 6 * 0.9;
    else if (modalPaqueteSeleccionado === 'doce') precioUnitario = modalPrecioBase * 12 * 0.8;

    const precioTotal = precioUnitario * cantidad;
    document.getElementById('modalPrecio').textContent = `$${precioTotal.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
}


/**
 * PUENTE CLAVE: Del Modal Detalle -> Al Modal Tallas
 */
function modalAgregarAlCarrito() {
    if (!modalProductoActual) {
        console.error("No hay un producto cargado en el modal");
        return;
    }

    const cantidadPaquetes = parseInt(document.getElementById('modalCantidad').value) || 1;
    const paresPorPaquete = (modalPaqueteSeleccionado === 'doce') ? 12 : 6;
    const metaTotal = paresPorPaquete * cantidadPaquetes;

    const precioTexto = document.getElementById('modalPrecio').textContent;
    const precioTotalCalculado = parseFloat(precioTexto.replace('$', '').replace(',', ''));

    const imgActual = document.getElementById('modalImagenPrincipal').src;

    // --- CAMBIO AQUÍ: Capturamos tanto el NOMBRE como el ID ---
    let nombreColorReal = "Estándar";
    let idColorReal = null; // <--- Nueva variable

    if (modalProductoActual.colores && modalProductoActual.colores[modalColorSeleccionado]) {
        nombreColorReal = modalProductoActual.colores[modalColorSeleccionado].nombre;
        idColorReal = modalProductoActual.colores[modalColorSeleccionado].id; // <--- Capturamos el ID real
    }

    const config = {
        metaTotal: metaTotal,
        basePaquete: paresPorPaquete,
        id_categoria: modalProductoActual.id_categoria, // Aseguramos que pase la categoría
        idColor: idColorReal, // <--- PASAMOS EL ID AL SIGUIENTE MODAL
        infoVisual: {
            nombre: modalProductoActual.nombre,
            imagen: imgActual,
            colorNombre: nombreColorReal,
            precioTotalCalculado: precioTotalCalculado
        }
    };

    cerrarModalProducto();
    setTimeout(() => {
        abrirModalTallas(modalProductoActual.id, config);
    }, 100);
}

/* ---------------- INICIALIZACIÓN ---------------- */
// (Tu inicialización se queda igual)
document.addEventListener('DOMContentLoaded', (event) => {
    inicializarPreciosPorDefecto();
    inicializarCarritoLateral();
    const mp = document.getElementById('modalProducto');
    if (mp) mp.addEventListener('click', (e) => { if (e.target === mp) cerrarModalProducto(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (document.getElementById('modalProducto')?.classList.contains('activo')) cerrarModalProducto();
            if (document.getElementById('modal-tallas')?.style.display === 'flex') cerrarModalTallas();
        }
    });

    // Cierre al hacer clic en la parte oscura del modal de tallas
    const modalTallas = document.getElementById('modal-tallas');
    if (modalTallas) {
        modalTallas.addEventListener('click', (e) => {
            // Si el clic fue en el fondo (el overlay) y no en la cajita blanca
            if (e.target === modalTallas) {
                cerrarModalTallas();
            }
        });
    }

});

function inicializarPreciosPorDefecto() {
    // ... (Tu función inicializarPreciosPorDefecto de siempre) ...
    // Asegúrate de pegarla aquí si copias todo el archivo
    document.querySelectorAll('[id^="precio_item_"]').forEach(elemento => {
        let precioIndividual = parseFloat(elemento.getAttribute('data-precio-individual'));
        if (isNaN(precioIndividual) || precioIndividual === 0) precioIndividual = parseFloat(elemento.getAttribute('data-precio-base'));
        const precioSeis = precioIndividual * 6 * 0.9;
        elemento.textContent = '$' + precioSeis.toFixed(2);
        elemento.setAttribute('data-paquete-actual', 'seis');
        elemento.setAttribute('data-precio-paquete', precioSeis);
        if (!elemento.getAttribute('data-precio-individual')) elemento.setAttribute('data-precio-individual', precioIndividual);
        const contenedor = elemento.closest('.container__item_01');
        if (contenedor) {
            contenedor.querySelector('.btn__paquete_seis')?.classList.add('activo');
            contenedor.querySelector('.btn__paquete_doce')?.classList.remove('activo');
        }
    });
}

// NO OLVIDES PEGAR LA FUNCIÓN renderizarCarritoHTML y eliminarItemDelCarrito TAMBIÉN AQUÍ ABAJO
// (Las mismas que te di en la respuesta larga anterior)
function renderizarCarritoHTML() {
    const contenedor = document.getElementById('carrito-contenido');
    const elementoTotal = document.getElementById('carrito-precio-final');

    // 1. Validar que los elementos del DOM existan antes de continuar
    if (!contenedor || !elementoTotal) return;

    // 2. Limpiar el contenedor
    contenedor.innerHTML = '';
    let totalGlobal = 0;

    // 3. SEGURO: Si no hay productos, mostramos mensaje de vacío
    if (!carritoDeCompras || carritoDeCompras.length === 0) {
        contenedor.innerHTML = '<p style="text-align: center; margin-top: 20px; color: var(--color_cuatro);">Tu carrito está vacío.</p>';
        elementoTotal.textContent = "$0.00";
        return;
    }

    // 4. Dibujar los productos
    carritoDeCompras.forEach((producto, index) => {
        // Acumular total global asegurando que sea número (Evita el error de $0.00)
        // Tomamos el precioTotal que el PHP calculó desde la columna 'subtotal' de la BD
        const subtotalProducto = parseFloat(producto.precioTotal) || 0;
        totalGlobal += subtotalProducto;

        // FIX DE RUTAS: Usamos la ruta absoluta que viene del PHP (/Mayoreo_Etnia/...)
        // Esto garantiza que la imagen cargue en Index, Ofertas o Populares
        const rutaImagen = producto.imagen || '/Mayoreo_Etnia/Assets/Imagenes/default.png';
        const nombreColor = producto.color || 'Estándar';

        // LÓGICA DE PAQUETES
        const basePaquete = (producto.paquete === 'seis') ? 6 : 12;
        const numPaquetes = producto.cantidadTotal / basePaquete;
        // Calculamos el precio de un solo paquete para el desglose visual
        const precioPorPaquete = (numPaquetes > 0) ? (subtotalProducto / numPaquetes) : 0;

        // GENERAR TAGS DE TALLAS ORDENADAS
        const tallasOrdenadas = Object.keys(producto.desgloseTallas || {}).sort((a, b) => parseFloat(a) - parseFloat(b));
        let htmlTallas = '<div class="contenedor-tags-tallas">';

        if (tallasOrdenadas.length > 0) {
            tallasOrdenadas.forEach(t => {
                htmlTallas += `<span class="tag-talla">T${t}: <b>${producto.desgloseTallas[t]}</b></span>`;
            });
        } else {
            htmlTallas += '<span class="tag-talla error">Sin especificar</span>';
        }
        htmlTallas += '</div>';

        // 5. CONSTRUIR ELEMENTO HTML
        const itemDiv = document.createElement('div');
        itemDiv.classList.add('item-carrito');
        itemDiv.innerHTML = `
            <img src="${rutaImagen}" alt="${nombreColor}">
            <div class="item-info">
                <h4>${producto.nombre}</h4>
                <p class="info-meta">Color: ${nombreColor}</p>
                
                ${htmlTallas}
                
                <p class="info-precios">
                    <span class="info-paq">${numPaquetes} Paq. (${basePaquete} pares c/u)</span><br>
                    <span class="info-calculo">${numPaquetes} x $${precioPorPaquete.toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
                </p>
                <p class="precio-final-item">$${subtotalProducto.toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
            </div>
            <button class="btn-eliminar-item" onclick="eliminarGrupoDelCarrito('${producto.id_grupo}')">&times;</button>
        `;
        contenedor.appendChild(itemDiv);
    });

    // 6. ACTUALIZAR SUBTOTAL FINAL EN EL FOOTER
    elementoTotal.textContent = "$" + totalGlobal.toLocaleString('en-US', { minimumFractionDigits: 2 });
}



function eliminarItemDelCarrito(index) {
    carritoDeCompras.splice(index, 1);
    renderizarCarritoHTML();
}

function inicializarCarritoLateral() {
    const btnAbrir = document.getElementById('btn-abrir-carrito');
    const btnCerrar = document.getElementById('btn-cerrar-carrito');
    const overlay = document.getElementById('carrito-overlay');
    const carritoLateral = document.getElementById('carrito-lateral');
    function cerrarCarrito() { carritoLateral.classList.remove('activo'); overlay.classList.remove('activo'); }
    if (btnAbrir) { btnAbrir.addEventListener('click', (e) => { e.preventDefault(); carritoLateral.classList.add('activo'); overlay.classList.add('activo'); }); }
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarCarrito);
    if (overlay) overlay.addEventListener('click', cerrarCarrito);
}

/* =======================================================
   LÓGICA DE COMPRA Y VALIDACIÓN DE SESIÓN
   ======================================================= */

function validarYComprar() {
    // 1. Verificar si el carrito está vacío
    if (carritoDeCompras.length === 0) {
        alert("Tu carrito está vacío.");
        return;
    }

    // 2. Verificar si el usuario está logueado (Variable definida en el PHP)
    if (!usuarioLogueado) {
        // Si NO está logueado, abrimos el modal de Login
        // Y guardamos un aviso para saber que quería comprar
        alert("Necesitas iniciar sesión para completar tu compra.");

        // Asumiendo que tienes la función abrirModalAuth del paso anterior
        // Si esa función está en el HTML (componentes_modales), JS la reconoce.
        abrirModalAuth();
        return;
    }

    // 3. Si está logueado y tiene productos, procedemos
    procesarPedidoFinal();
}

function procesarPedidoFinal() {
    // Aquí iría la lógica final (enviar a WhatsApp o Pasarela de Pago)
    alert("¡Sesión validada! Redirigiendo a WhatsApp/Pago... (Lógica pendiente)");

    // Ejemplo: window.location.href = "checkout.php";
}

function seleccionarColorTarjeta(btn, idImagen, rutaImg, nombreZapato) {
    // 1. Cambiar la imagen visualmente
    establecerImagen(idImagen, rutaImg, nombreZapato);

    // 2. Gestionar la clase 'activo' visualmente
    const contenedor = btn.closest('.colores__item_01');
    contenedor.querySelectorAll('.btn__color').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');

    // 3. Sincronizar el ID de color seleccionado globalmente
    idColorActual = btn.getAttribute('data-id-color');
}