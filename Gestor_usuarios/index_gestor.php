<?php
include_once("config_gestor.php");

// Consulta a la base de datos
$result = $dbConn->query("SELECT * FROM usuarios ORDER BY documento ASC");
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de usuarios</title>
    <link rel="stylesheet" href="style_gestor.css">
</head>
<body>
    <div class="cuadro_logo">
        <img src="../Imagenes/logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </div>

    <div>
        <table class="tabla_principal">
            <tr class="tabla_secundaria">
                <th>NOMBRE DE USUARIO</th>
                <th>CONTRASEÑA</th>
                <th>CORREO ELECTRONICO</th>
                <th>NOMBRES Y APELLIDOS</th>
                <th>documento</th>
                <th>area</th>
                <th>cargo</th>
                <th>EDICIÓN</th>
            </tr>
            <?php
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['nombre_usuario']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contrasena']) . "</td>";
                echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nombres_apellidos']) . "</td>";
                echo "<td>" . htmlspecialchars($row['documento']) . "</td>";
                echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cargo']) . "</td>";
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
    
    <a href="http://localhost/GateGourmet/Gestor_usuarios/add_gestor.php" class="boton_adicionar">Adicionar usuario</a><br/><br/>
    <a href="http://localhost/Gategourmet/login/login_admin.php" class="boton_volver">Volver</a>
    
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
    </footer>
</body>
</html>
