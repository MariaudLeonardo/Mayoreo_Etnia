<?php
// 1. CONFIGURACIÓN DE RUTAS (PHP)
$rutaAuthJS = "Assets/Scripts/auth.php"; 
$rutaFavoritosJS = "Assets/Scripts/api_favoritos.php"; 
$rutaCarritoJS = "Assets/Scripts/api_carrito.php"; // <--- NUEVA VARIABLE

// Si estamos dentro de la carpeta Assets/Scripts/
if (file_exists("auth.php")) {
    $rutaAuthJS = "auth.php"; 
    $rutaFavoritosJS = "api_favoritos.php"; 
    $rutaCarritoJS = "api_carrito.php"; // <--- RUTA CORTA
}
?>

<div id="modal-tallas" class="modal-tallas-overlay">
    <div class="modal-tallas-content" style="max-width: 500px;">
        <span class="btn-cerrar-modal" onclick="cerrarModalTallas()">&times;</span>
        <h3>Distribuye tus pares</h3>
        <p style="text-align:center; color: var(--color_cuatro); margin-bottom:10px;">
            Meta: <strong id="lbl-meta-pares">6</strong> pares
        </p>
        <div id="grid-tallas" class="grid-tallas"></div>
        <div id="panel-editor-talla" class="panel-distribucion">
            <h4 style="margin:0; color: var(--color_uno);">
                Editando Talla: <span id="lbl-talla-editor" style="font-size:1.2em;">-</span>
            </h4>
            <div class="controles-talla">
                <button class="btn__cantidad_menos_01" onclick="ajustarValorEditor(-1)" style="width:30px; height:30px;">-</button>
                <input type="range" id="slider-talla" min="0" max="10" value="0" class="slider-talla" oninput="actualizarDesdeSlider(this.value)">
                <input type="number" id="input-talla-manual" min="0" class="input-talla-manual" onchange="actualizarDesdeInput(this.value)" onkeypress="return soloNumeros(event)">
                <button class="btn__cantidad_mas_01" onclick="ajustarValorEditor(1)" style="width:30px; height:30px;">+</button>
            </div>
            <p style="font-size: 12px; color: var(--color_cuatro); margin-top: 5px; text-align: center;">Mueve el slider o escribe el número</p>
        </div>
        <div id="feedback-suma" class="feedback-suma">Llevas 0 de 6 pares asignados</div>
        <button id="btn-confirmar-tallas" class="btn-confirmar-distribucion" onclick="confirmarYAgregarAlCarrito()" disabled>COMPLETAR PEDIDO</button>
    </div>
</div>

<div id="modalProducto" class="modal__overlay">
    <div class="modal__contenido">
        <button class="modal__cerrar" onclick="cerrarModalProducto()">&times;</button>
        <div class="modal__grid">
            <div class="modal__imagen" style="position: relative;"> 
                
                <button id="btn-favorito-modal" class="btn-favorito-flotante" onclick="toggleFavoritoModal()">
                    <svg width="30" height="30" viewBox="0 0 24 24" class="icono-estrella">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                    </svg>
                </button>

                <img id="modalImagenPrincipal" src="" alt="" class="imagen__principal">
                <div class="miniaturas" id="modalMiniaturas"></div>
            </div>
            <div class="modal__info">
                <h2 id="modalCategoria"></h2>
                <h1 id="modalNombre"></h1>
                <p class="modal__precio" id="modalPrecio"></p>
                <div class="modal__colores" id="modalColores"></div>
                <div class="modal__tallas" id="modalTallas">
                    <h3>Selecciona tu talla:</h3>
                </div>
                <div class="modal__cantidad_container">
                    <h3>Cantidad de Paquetes:</h3>
                    <div class="cantidad__control_01" style="margin: 10px 0;">
                        <button class="btn__cantidad_menos_01" onclick="modalControlarCantidad(-1)">-</button>
                        <input id="modalCantidad" type="text" value="1" class="input__cantidad_01"
                            onchange="modalValidarEntrada()" onkeypress="return soloNumeros(event)">
                        <button class="btn__cantidad_mas_01" onclick="modalControlarCantidad(1)">+</button>
                    </div>
                </div>
                <div class="modal__paquetes">
                    <h3>Pares:</h3>
                    <div style="display: flex; gap: 15px; margin: 10px 0;">
                        <button class="btn__paquete_seis" onclick="modalCambiarPaquete('seis')">6</button>
                        <button class="btn__paquete_doce" onclick="modalCambiarPaquete('doce')">12</button>
                    </div>
                </div>
                <button class="btn__agregar_modal" onclick="modalAgregarAlCarrito()">AGREGAR AL CARRITO</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-auth" class="modal__overlay">
    <div class="modal__contenido" style="max-width: 400px; text-align:center; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
        <button class="modal__cerrar" onclick="cerrarModalAuth()" style="top: 15px; right: 15px; font-size: 24px;">&times;</button>

        <div id="form-login">
            <h2 style="color:var(--color_uno); margin-bottom: 20px;">Iniciar Sesión</h2>
            <form id="frmLogin" onsubmit="procesarAuth(event, 'login')">
                <input type="email" name="email" placeholder="Correo Electrónico" required class="input-busqueda" style="margin-bottom:15px; border:1px solid #ddd; padding: 12px;">
                <input type="password" name="password" placeholder="Contraseña" required class="input-busqueda" style="margin-bottom:20px; border:1px solid #ddd; padding: 12px;">
                <button type="submit" class="btn-buscar-real" style="background:#3498db; color:white; padding: 12px; border-radius: 6px; font-size: 16px; border:none;">ENTRAR</button>
            </form>
            <p style="margin-top:20px; font-size: 14px; color: #666;">¿No tienes cuenta? <a href="#" onclick="toggleAuth('registro')" style="color:#3498db; font-weight:bold; text-decoration: none;">Regístrate aquí</a></p>
        </div>

        <div id="form-registro" style="display:none;">
            <h2 style="color:var(--color_uno); margin-bottom: 20px;">Crear Cuenta</h2>
            <form id="frmRegistro" onsubmit="procesarAuth(event, 'registro')">
                <input type="text" name="nombre" placeholder="Nombre Completo" required class="input-busqueda" style="margin-bottom:15px; border:1px solid #ddd; padding: 12px;">
                <input type="email" name="email" placeholder="Correo Electrónico" required class="input-busqueda" style="margin-bottom:15px; border:1px solid #ddd; padding: 12px;">
                <input type="password" name="password" placeholder="Contraseña (Mín 8 car + número)" pattern="(?=.*\d).{8,}" title="Debe tener al menos 8 caracteres y un número" required class="input-busqueda" style="margin-bottom:20px; border:1px solid #ddd; padding: 12px;">
                <button type="submit" class="btn-buscar-real" style="background:#3498db; color:white; padding: 12px; border-radius: 6px; font-size: 16px; border:none;">REGISTRARSE</button>
            </form>
            <p style="margin-top:20px; font-size: 14px; color: #666;">¿Ya tienes cuenta? <a href="#" onclick="toggleAuth('login')" style="color:#3498db; font-weight:bold; text-decoration: none;">Inicia Sesión aquí</a></p>
        </div>
    </div>
</div>

<div class="carrito-overlay" id="carrito-overlay"></div>
<div class="carrito-lateral" id="carrito-lateral">
    <div class="carrito-header">
        <h3>Tu Carrito</h3>
        <button class="btn-cerrar-carrito" id="btn-cerrar-carrito">&times;</button>
    </div>
    <div class="carrito-body" id="carrito-contenido">
        <p style="text-align: center; margin-top: 20px;">Tu carrito está vacío por ahora.</p>
    </div>
    <div class="carrito-footer">
        <div class="carrito-subtotal">
            <span>SUBTOTAL:</span>
            <span id="carrito-precio-final">$0.00</span>
        </div>
        <button class="btn-hacer-pedido" onclick="validarYComprar()">HACER PEDIDO</button>
    </div>
</div>

<script>
    // INYECCIÓN DE VARIABLES PHP A JAVASCRIPT (RESTAURADO)
    // Estas líneas son las que permiten que el JS sepa a dónde enviar los datos
    const rutaAuthAJAX = "<?= $rutaAuthJS ?>";
    const rutaFavoritosAJAX = "<?= $rutaFavoritosJS ?>"; 
    const rutaCarritoAJAX = "<?= $rutaCarritoJS ?>"; 

    // Funciones de Auth (Mantén tus funciones actuales abajo)
    function abrirModalAuth() { document.getElementById('modal-auth').classList.add('activo'); }
    function cerrarModalAuth() { document.getElementById('modal-auth').classList.remove('activo'); }
    
    function toggleAuth(modo) {
        document.getElementById('form-login').style.display = (modo === 'login') ? 'block' : 'none';
        document.getElementById('form-registro').style.display = (modo === 'registro') ? 'block' : 'none';
    }

    function procesarAuth(e, accion) {
        e.preventDefault();
        const form = (accion === 'login') ? document.getElementById('frmLogin') : document.getElementById('frmRegistro');
        const formData = new FormData(form);
        formData.append('accion', accion);

        fetch(rutaAuthAJAX, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                location.reload(); 
            } else {
                alert(data.msg); 
            }
        })
        .catch(err => console.error('Error Auth:', err));
    }
</script>