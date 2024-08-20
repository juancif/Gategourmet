<?php
include_once("config_gestor.php");

// Consulta a la base de datos
$result = $dbConn->query("SELECT * FROM usuarios ORDER BY documento ASC");
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de usuarios</title>
    <link rel="stylesheet" href="../../css/style_gestor.css">
</head>
<body>
    <div class="cuadro_logo">
        <img src="../../../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
        <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link"><img src="../../../Imagenes/regresar.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Volver al inicio</div></a>
            </li>
    </div>

    <div>
        <table class="tabla_principal">
        <th class="cuadro_titulo">Usuarios</th>
            <tr class="tabla_secundaria">
                <th>NOMBRE DE USUARIO</th>
                <th>CONTRASEÑA</th>
                <th>CORREO ELECTRONICO</th>
                <th>NOMBRES Y APELLIDOS</th>
<<<<<<< HEAD:Gestor_usuarios/php/User/index_gestor.php
                <th>DOCUMENTO</th>
                <th>AREA PERTENECE</th>
                <th>CARGO</th>
                <th>ROL</th>
=======
                <th>documento</th>
                <th>area</th>
                <th>cargo</th>
>>>>>>> cf18b17ca5bb4e27e51670217984bdff237faf3b:Gestor_usuarios/index_gestor.php
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
                        <a href='edit_gestor.php?documento=" . htmlspecialchars($row['documento']) . "'>Editar</a> | 
                        <a href='delete_gestor.php?documento=" . htmlspecialchars($row['documento']) . "' 
                           onclick=\"return confirm('¿Está seguro de eliminar este registro?')\">Eliminar</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
    <a href="add_gestor.php" class="boton_adicionar">Adicionar usuario</a><br/><br/>
    <a href="http://localhost/GateGourmet/Gestor_usuarios/php/admin/index_gestor_admin.php" class="boton_volver">Ver administradores</a><br/><br/>    
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
</body>
</html>
