<?php
include_once("config_gestor.php");

// Obtener los valores de los filtros desde el formulario (si están definidos)
$correo = isset($_GET['correo']) ? $_GET['correo'] : '';
$nombres_apellidos = isset($_GET['nombres_apellidos']) ? $_GET['nombres_apellidos'] : '';
$nombre_usuario = isset($_GET['nombre_usuario']) ? $_GET['nombre_usuario'] : '';
$area = isset($_GET['area']) ? $_GET['area'] : '';
$cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';
$rol = isset($_GET['rol']) ? $_GET['rol'] : '';

// Construir la consulta con los filtros
$query = "SELECT * FROM administradores WHERE 1=1";

// Agregar condiciones de filtro dinámicamente según los valores del formulario
if ($correo) {
    $query .= " AND correo LIKE '%$correo%'";
}
if ($nombres_apellidos) {
    $query .= " AND nombres_apellidos LIKE '%$nombres_apellidos%'";
}
if ($nombre_usuario) {
    $query .= " AND nombre_usuario LIKE '%$nombre_usuario%'";
}
if ($area) {
    $query .= " AND area LIKE '%$area%'";
}
if ($cargo) {
    $query .= " AND cargo LIKE '%$cargo%'";
}
if ($rol) {
    $query .= " AND rol LIKE '%$rol%'";
}

$query .= " ORDER BY nombre_usuario ASC";
$result = $dbConn->query($query);
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de usuarios</title>
    <link rel="stylesheet" href="../../css/style_gestor.css">
</head>
<body>
<header class="header">
    <img src=".././../../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    <li class="nav__item__user">
        <a href="http://localhost/GateGourmet/Index/index_admin.php" class="cerrar__sesion__link">
            <img src="../../../Imagenes/regresar.png" alt="Usuario" class="img__usuario">
            <div class="cerrar__sesion">Volver al inicio</div>
        </a>
    </li>
</header>

<!-- Filtros -->
<form method="GET" action="">
    <div class="filter-container">
        <input type="text" id="correo" name="correo" placeholder="Correo Electrónico" value="<?= htmlspecialchars($correo) ?>">
        <input type="text" id="nombres_apellidos" name="nombres_apellidos" placeholder="Nombres y Apellidos" value="<?= htmlspecialchars($nombres_apellidos) ?>">
        <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Nombre de Usuario" value="<?= htmlspecialchars($nombre_usuario) ?>">
        <input type="text" id="area" name="area" placeholder="Área" value="<?= htmlspecialchars($area) ?>">
        <input type="text" id="cargo" name="cargo" placeholder="Cargo" value="<?= htmlspecialchars($cargo) ?>">
        <select id="rol" name="rol">
            <option value="">Rol</option>
            <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Administrador</option>
            <option value="user" <?= $rol === 'user' ? 'selected' : '' ?>>Usuario</option>
        </select>
        <button type="submit" class="filter-button">Filtrar</button>
        <a href="index_gestor_admin.php" class="filter-button">Limpiar Filtros</a>
    </div>
</form>

<!-- Botones adicionales -->
<a href="add_gestor_admin.php" class="botones boton_adicionar">Adicionar administradores</a>
<a href="http://localhost/GateGourmet/Gestor_usuarios/php/Inactivos/index_inactivos.php" class="botones boton_inactivos">Ver inactivos</a>
<a href="http://localhost/GateGourmet/Gestor_usuarios/php/user/index_gestor.php" class="botones boton_volver">Ver usuarios</a>

<div>
    <table class="tabla_principal">
        <th class="cuadro_titulo">Administradores</th>
        <tr class="tabla_secundaria">
            <th>CORREO ELECTRONICO</th>
            <th>NOMBRES Y APELLIDOS</th>
            <th>NOMBRE DE USUARIO</th>
            <th>CONTRASEÑA</th>
            <th>AREA PERTENECE</th>
            <th>CARGO</th>
            <th>ROL</th>
            <th>ESTADO</th>
            <th>EDICIÓN</th>
        </tr>
        <?php
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nombres_apellidos']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre_usuario']) . "</td>";
            echo "<td>" . str_repeat('*', strlen($row['contrasena'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['area']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cargo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['rol']) . "</td>";
            echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
            echo "<td class='acciones'>
                    <a href='edit_gestor_admin.php?nombre_usuario=" . htmlspecialchars($row['nombre_usuario']) . "'>Editar</a> | 
                    <a href='desactivar_gestor_admin.php?nombre_usuario=" . htmlspecialchars($row['nombre_usuario']) . "' 
                       onclick=\"return confirm('¿Está seguro de desactivar este registro?')\">Desactivar</a>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<a href="export_excel.php" class="botones boton_excel">Descargar usuarios activos en Excel</a>

<footer class="footer">
    <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
</footer>

</body>
</html>
