<?php
// 1. DETECCIÓN INTELIGENTE DE RUTA
// Verificamos si existe la carpeta Assets desde donde estamos.
// Si existe, estamos en el HOME (Raíz). Si no, estamos en Assets/Scripts.
$rutaPrefix = "";
if (file_exists("Assets/Scripts/conexion.php")) {
    $rutaPrefix = "Assets/Scripts/"; // Estamos en el Home, hay que entrar a la carpeta
}

// 2. CONEXIÓN SEGURA
if (!isset($conexion)) {
    // Intentamos cargar la conexión usando el prefijo correcto
    if (file_exists($rutaPrefix . "conexion.php")) {
        require_once $rutaPrefix . "conexion.php";
    }
}

// Obtener categorías para el filtro
$sqlCat = "SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC";
// Verificamos que la conexión exista para evitar errores fatales si algo falló arriba
if (isset($conexion)) {
    $resCat = $conexion->query($sqlCat);
}
?>

<style>
    .buscador-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.6); z-index: 9998;
    }
    .buscador-menu {
        display: none; position: absolute; top: 80px; right: 10%; width: 300px;
        background-color: #333; color: white; border-radius: 8px; padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3); z-index: 9999;
    }
    .grupo-input { margin-bottom: 15px; }
    .grupo-input label { display: block; margin-bottom: 5px; font-weight: bold; color: #ddd; }
    .input-busqueda { width: 100%; padding: 10px; border-radius: 5px; border: none; outline: none; }
    .lista-categorias { max-height: 200px; overflow-y: auto; border-top: 1px solid #555; padding-top: 10px; }
    .item-categoria { display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #444; cursor: pointer; }
    .item-categoria:hover { background-color: #444; }
    .item-categoria input { margin-right: 10px; transform: scale(1.2); cursor: pointer; }
    .btn-buscar-real { width: 100%; padding: 10px; background-color: white; color: #333; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; margin-top: 15px; transition: 0.3s; }
    .btn-buscar-real:hover { background-color: #ddd; }
</style>

<div class="buscador-overlay" id="buscador-overlay" onclick="cerrarBuscador()"></div>

<div class="buscador-menu" id="buscador-menu">
    <form action="<?= $rutaPrefix ?>busqueda.php" method="GET"> 
        
        <div class="grupo-input">
            <label for="txt-busqueda">Buscar por nombre:</label>
            <input type="text" name="q" id="txt-busqueda" class="input-busqueda" placeholder="Ej: Botín...">
        </div>

        <div class="grupo-input">
            <label>Filtrar por categorías:</label>
            <div class="lista-categorias">
                <?php if (isset($resCat) && $resCat): ?>
                    <?php while ($cat = $resCat->fetch_assoc()): ?>
                        <label class="item-categoria">
                            <input type="checkbox" name="cats[]" value="<?= $cat['id_categoria'] ?>">
                            <span><?= $cat['nombre'] ?></span>
                        </label>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#aaa; font-size:12px;">No se cargaron categorías</p>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn-buscar-real">BUSCAR</button>
    </form>
</div>

<script>
    function toggleBuscador(event) {
        if(event) event.preventDefault();
        const menu = document.getElementById('buscador-menu');
        const overlay = document.getElementById('buscador-overlay');
        
        if (menu.style.display === 'block') {
            menu.style.display = 'none';
            overlay.style.display = 'none';
        } else {
            menu.style.display = 'block';
            overlay.style.display = 'block';
            // Pequeño timeout para asegurar que el input reciba el foco
            setTimeout(() => document.getElementById('txt-busqueda').focus(), 100);
        }
    }

    function cerrarBuscador() {
        document.getElementById('buscador-menu').style.display = 'none';
        document.getElementById('buscador-overlay').style.display = 'none';
    }
</script>