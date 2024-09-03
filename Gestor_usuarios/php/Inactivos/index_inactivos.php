<?php
include_once("config_inactivos.php");

// Consulta a la base de datos
$result = $dbConn->query("SELECT * FROM inactivos ORDER BY nombre_usuario ASC");
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
                <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link"><img src="../../../Imagenes/regresar.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Volver al inicio</div></a>
            </li>
    </header>
    <a href="http://localhost/GateGourmet/Gestor_usuarios/php/user/index_gestor.php" class="botones boton_volver">Volver</a>
    <div>
        <table class="tabla_principal">
        <th class="cuadro_titulo">Inactivos</th>
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
                echo "<td>" . str_repeat('*', strlen($row['contrasena']));
                echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cargo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['rol']) . "</td>";
                echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                echo "<td class='acciones'>
                        <a href='edit_inactivos.php?nombre_usuario=" . htmlspecialchars($row['nombre_usuario']) . "'>Editar</a> | 
                        <a href='delete_inactivos.php?nombre_usuario=" . htmlspecialchars($row['nombre_usuario']) . "' 
                           onclick=\"return confirm('¿Está seguro de eliminar este registro?')\">Desactivar</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>    
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
</body>
</html>