<?php
include_once("/config/config_gestor.php");

// Consulta a la base de datos
$result = $dbConn->query("SELECT * FROM administradores ORDER BY documento ASC");
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de usuarios</title>
    <link rel="stylesheet" href="/Gestor_usuarios/css/style_gestor.css">
</head>
<body>
    <div class="cuadro_logo">
        <img src="../Imagenes/logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
        <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link"><img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Volver al inicio</div></a>
            </li>
    </div>
    <div>
        <table class="tabla_principal">
        <th class="cuadro_titulo">Administradores</th>
            <tr class="tabla_secundaria">
                <th>NOMBRE DE USUARIO</th>
                <th>CONTRASEÑA</th>
                <th>CORREO ELECTRONICO</th>
                <th>NOMBRES Y APELLIDOS</th>
                <th>DOCUMENTO</th>
                <th>AREA PERTENECE</th>
                <th>CARGO</th>
                <th>ROL</th>
                <th>EDICIÓN</th>
            </tr>
            <?php
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['nombre_usuario']) . "</td>";
                echo "<td>" . str_repeat('*', strlen($row['contrasena']));
                echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nombres_apellidos']) . "</td>";
                echo "<td>" . htmlspecialchars($row['documento']) . "</td>";
                echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cargo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['rol']) . "</td>";
                echo "<td class='acciones'>
                        <a href='edit_gestor_admin.php?documento=" . htmlspecialchars($row['documento']) . "'>Editar</a> | 
                        <a href='delete_gestor_admin.php?documento=" . htmlspecialchars($row['documento']) . "' 
                           onclick=\"return confirm('¿Está seguro de eliminar este registro?')\">Eliminar</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
    <a href="add_gestor_admin.php" class="boton_adicionar">Adicionar administradores</a><br/><br/>
    <a href="http://localhost/GateGourmet/Gestor_usuarios/php/user/index_gestor.php" class="boton_volver">Volver</a><br/><br/>    
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
</body>
</html>