<?php
// Conexión a la base de datos MySQL
$servername = "localhost";
$username = "root";
$password = "";
$database = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializar variables de búsqueda
$busqueda_proceso = isset($_GET['proceso']) ? $_GET['proceso'] : '';
$busqueda_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$busqueda_cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';

// Consulta SQL usando consultas preparadas para evitar inyección SQL
$stmt = $conn->prepare("SELECT id, macroproceso, proceso, usuario, cargo, email, rol FROM procesos WHERE proceso LIKE ? AND usuario LIKE ? AND cargo LIKE ?");
$proceso_like = "%$busqueda_proceso%";
$usuario_like = "%$busqueda_usuario%";
$cargo_like = "%$busqueda_cargo%";
$stmt->bind_param("sss", $proceso_like, $usuario_like, $cargo_like);
$stmt->execute();
$result = $stmt->get_result();

// Obtener el color según el macroproceso
function obtenerColor($macroproceso) {
    switch ($macroproceso) {
        case 'GESTION CORPORATIVA':
        case 'COMPLIANCE':
            return 'yellow-background'; // Clase CSS para amarillo
        case 'SUPPLY CHAIN':
        case 'CULINARY EXCELLENCE':
        case 'SERVICE DELIVERY':
        case 'ASSEMBLY':
        case 'SERVICIOS INSTITUCIONALES':
            return 'red-background'; // Clase CSS para rojo
        case 'FINANCIERA':
        case 'COSTOS':
        case 'COMUNICACIONES':
        case 'TECNOLOG?A DE LA INFORMACI?N':
        case 'TALENTO HUMANO':
        case 'MANTENIMIENTO':
        case 'SERVICIO AL CLIENTE':
        case 'SECURITY':
            return 'green-background'; // Clase CSS para verde
        default:
            return ''; // Sin color
    }
}

// Manejo de formulario de agregado de proceso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $macroproceso = $_POST['macroproceso'];
    $proceso = $_POST['proceso'];
    $usuario = $_POST['usuario'];
    $cargo = $_POST['cargo'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];

    // Validaciones simples
    if (empty($macroproceso) || empty($proceso) || empty($usuario) || empty($cargo) || empty($email) || empty($rol)) {
        echo "<script>alert('Todos los campos son obligatorios.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('El email ingresado no es válido.');</script>";
    } else {
        // Insertar en la base de datos
        $stmt_insert = $conn->prepare("INSERT INTO procesos (macroproceso, proceso, usuario, cargo, email, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("ssssss", $macroproceso, $proceso, $usuario, $cargo, $email, $rol);
        if ($stmt_insert->execute()) {
            echo "<script>alert('Proceso agregado exitosamente.');</script>";
        } else {
            echo "<script>alert('Error al agregar el proceso: " . $stmt_insert->error . "');</script>";
        }
        $stmt_insert->close();
    }
}

// Obtener usuarios para el menú desplegable
$usuarios = [];
$sql_usuarios = "SELECT nombre_usuario, cargo, correo, rol FROM usuarios UNION SELECT nombre_usuario, cargo, correo, rol FROM administradores";
$result_usuarios = $conn->query($sql_usuarios);

if ($result_usuarios->num_rows > 0) {
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row; // Almacenar cada usuario en un array
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Procesos</title>
    <link rel="stylesheet" href="procesos.css">
    <link rel="icon" href="/ruta/al/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1>MAPEO DE PROCESOS</h1>
    </header>

    <main>
        <section class="container">
            <!-- Formulario para agregar nuevo proceso -->
            <form method="POST" action="" class="form-agregar">
                <div class="form-group">
                    <label for="macroproceso">Macroproceso:</label>
                    <select id="macroproceso" name="macroproceso" required>
                        <option value="">Seleccione un macroproceso</option>
                        <option value="GESTION CORPORATIVA">GESTION CORPORATIVA</option>
                        <option value="COMPLIANCE">COMPLIANCE</option>
                        <option value="SUPPLY CHAIN">SUPPLY CHAIN</option>
                        <option value="CULINARY EXCELLENCE">CULINARY EXCELLENCE</option>
                        <option value="SERVICE DELIVERY">SERVICE DELIVERY</option>
                        <option value="ASSEMBLY">ASSEMBLY</option>
                        <option value="SERVICIOS INSTITUCIONALES">SERVICIOS INSTITUCIONALES</option>
                        <option value="FINANCIERA">FINANCIERA</option>
                        <option value="COSTOS">COSTOS</option>
                        <option value="COMUNICACIONES">COMUNICACIONES</option>
                        <option value="TECNOLOGÍA DE LA INFORMACIÓN">TECNOLOGÍA DE LA INFORMACIÓN</option>
                        <option value="TALENTO HUMANO">TALENTO HUMANO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="SERVICIO AL CLIENTE">SERVICIO AL CLIENTE</option>
                        <option value="SECURITY">SECURITY</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="proceso">Proceso:</label>
                    <select id="proceso" name="proceso" required>
                        <option value="">Seleccione un proceso</option>
                        <option value="DES - DIRECCIONAMIENTO ESTRATÉGICO">DES - DIRECCIONAMIENTO ESTRATÉGICO</option>
                        <option value="GMC - GESTION DE MEJORA CONTINUA">GMC - GESTION DE MEJORA CONTINUA</option>
                        <option value="IND - INDUCCIÓN ORGANIZACIONAL">IND - INDUCCIÓN ORGANIZACIONAL</option>
                        <option value="EYS - EVALUACIÓN Y SEGUIMIENTO">EYS - EVALUACIÓN Y SEGUIMIENTO</option>
                        <option value="GER - GESTIONAR LAS RELACIONES">GER - GESTIONAR LAS RELACIONES</option>
                        <option value="ENP - EVALUAR NUEVOS PROYECTOS DE INVERSIÓN Y OPERACIONALES">ENP - EVALUAR NUEVOS PROYECTOS DE INVERSIÓN Y OPERACIONALES</option>
                        <option value="ALM - SEGURIDAD ALIMENTARIA">ALM - SEGURIDAD ALIMENTARIA</option>
                        <option value="AMB - MEDIO AMBIENTE">AMB - MEDIO AMBIENTE</option>
                        <option value="SEO - SEGURIDAD OPERACIONAL - RAMP SAFETY">SEO - SEGURIDAD OPERACIONAL - RAMP SAFETY</option>
                        <option value="SFI - SEGURIDAD FÍSICA">SFI - SEGURIDAD FÍSICA</option>
                        <option value="SST - SEGURIDAD Y SALUD EN EL TRABAJO">SST - SEGURIDAD Y SALUD EN EL TRABAJO</option>
                        <option value="SAG - SAGRILAFT">SAG - SAGRILAFT</option>
                        <option value="PTE - POLÍTICA Y ÉTICA EMPRESARIAL">PTE - POLÍTICA Y ÉTICA EMPRESARIAL</option>
                        <option value="COM - COMPRAS">COM - COMPRAS</option>
                        <option value="ABS - ABASTECIMIENTO">ABS - ABASTECIMIENTO</option>
                        <option value="IDS - SISTEMAS INTERNOS DE ENTREGA">IDS - SISTEMAS INTERNOS DE ENTREGA</option>
                        <option value="SIM - SOLICITUD INTERNA DE MATERIALES">SIM - SOLICITUD INTERNA DE MATERIALES</option>
                        <option value="PDP - PLANEACIÓN DE LA PRODUCCIÓN">PDP - PLANEACIÓN DE LA PRODUCCIÓN</option>
                        <option value="CRO - CONTROL DE RECURSOS OPERATIVOS">CRO - CONTROL DE RECURSOS OPERATIVOS</option>
                        <option value="CDM - CONTROL DE MATERIALES">CDM - CONTROL DE MATERIALES</option>
                        <option value="PCA - CARNICERIA">PCA - CARNICERIA</option>
                        <option value="PDE - DESINFECCIÓN">PDE - DESINFECCIÓN</option>
                        <option value="PFV - FRUTAS Y VERDURAS">PFV - FRUTAS Y VERDURAS</option>
                        <option value="PFI - FRITURAS">PFI - FRITURAS</option>
                        <option value="PPA - PASTAS">PPA - PASTAS</option>
                        <option value="PAN - PANADERIA">PAN - PANADERIA</option>
                        <option value="CBA - CATERING DE BANQUETES">CBA - CATERING DE BANQUETES</option>
                        <option value="CAO - CATERING DE ALIMENTOS">CAO - CATERING DE ALIMENTOS</option>
                        <option value="DEC - DECORACIÓN DE PLATOS">DEC - DECORACIÓN DE PLATOS</option>
                        <option value="PCC - COCINA FRÍA">PCC - COCINA FRÍA</option>
                        <option value="PCH - COCINA CALIENTE">PCH - COCINA CALIENTE</option>
                        <option value="TCC - CONTROL DE CALIDAD">TCC - CONTROL DE CALIDAD</option>
                        <option value="SAA - SOPORTE ALIMENTARIO">SAA - SOPORTE ALIMENTARIO</option>
                        <option value="SCC - SOPORTE A CLIENTES">SCC - SOPORTE A CLIENTES</option>
                        <option value="CSD - COMERCIALIZACIÓN Y DISTRIBUCIÓN">CSD - COMERCIALIZACIÓN Y DISTRIBUCIÓN</option>
                        <option value="SFS - SISTEMAS FINANCIEROS">SFS - SISTEMAS FINANCIEROS</option>
                        <option value="SEI - SERVICIO EXTERNO INTERNO">SEI - SERVICIO EXTERNO INTERNO</option>
                        <option value="PIT - PROYECTOS INTEGRADOS DE TERCEROS">PIT - PROYECTOS INTEGRADOS DE TERCEROS</option>
                        <option value="TTT - CAPACITACIONES">TTT - CAPACITACIONES</option>
                        <option value="SEG - SEGURIDAD">SEG - SEGURIDAD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="usuario">Usuario:</label>
                    <select id="usuario" name="usuario" required>
                        <option value="">Seleccione un usuario</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>"
                                    data-cargo="<?php echo htmlspecialchars($usuario['cargo']); ?>"
                                    data-email="<?php echo htmlspecialchars($usuario['correo']); ?>"
                                    data-rol="<?php echo htmlspecialchars($usuario['rol']); ?>">
                                <?php echo htmlspecialchars($usuario['nombre_usuario']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cargo">Cargo:</label>
                    <input type="text" id="cargo" name="cargo" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <input type="text" id="rol" name="rol" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn"><i class="fas fa-plus"></i> Agregar Proceso</button>
                </div>
            </form>

            <!-- Formulario de búsqueda -->
            <form method="GET" action="" class="form-agregar">
    <div class="form-group">
        <label for="macroproceso">Buscar por Macroproceso:</label>
        <select id="macroproceso" name="macroproceso">
            <option value="">Seleccione un macroproceso</option>
            <option value="GESTION CORPORATIVA">GESTION CORPORATIVA</option>
            <option value="COMPLIANCE">COMPLIANCE</option>
            <option value="SUPPLY CHAIN">SUPPLY CHAIN</option>
            <option value="CULINARY EXCELLENCE">CULINARY EXCELLENCE</option>
            <option value="SERVICE DELIVERY">SERVICE DELIVERY</option>
            <option value="ASSEMBLY">ASSEMBLY</option>
            <option value="SERVICIOS INSTITUCIONALES">SERVICIOS INSTITUCIONALES</option>
            <option value="FINANCIERA">FINANCIERA</option>
            <option value="COSTOS">COSTOS</option>
            <option value="COMUNICACIONES">COMUNICACIONES</option>
            <option value="TECNOLOGÍA DE LA INFORMACIÓN">TECNOLOGÍA DE LA INFORMACIÓN</option>
            <option value="TALENTO HUMANO">TALENTO HUMANO</option>
            <option value="MANTENIMIENTO">MANTENIMIENTO</option>
            <option value="SERVICIO AL CLIENTE">SERVICIO AL CLIENTE</option>
            <option value="SECURITY">SECURITY</option>
        </select>
    </div>

    <div class="form-group">
        <label for="proceso">Buscar por Proceso:</label>
        <select id="proceso" name="proceso">
            <option value="">Seleccione un proceso</option>
            <option value="DES - DIRECCIONAMIENTO ESTRATÉGICO">DES - DIRECCIONAMIENTO ESTRATÉGICO</option>
                        <option value="GMC - GESTION DE MEJORA CONTINUA">GMC - GESTION DE MEJORA CONTINUA</option>
                        <option value="IND - INDUCCIÓN ORGANIZACIONAL">IND - INDUCCIÓN ORGANIZACIONAL</option>
                        <option value="EYS - EVALUACIÓN Y SEGUIMIENTO">EYS - EVALUACIÓN Y SEGUIMIENTO</option>
                        <option value="GER - GESTIONAR LAS RELACIONES">GER - GESTIONAR LAS RELACIONES</option>
                        <option value="ENP - EVALUAR NUEVOS PROYECTOS DE INVERSIÓN Y OPERACIONALES">ENP - EVALUAR NUEVOS PROYECTOS DE INVERSIÓN Y OPERACIONALES</option>
                        <option value="ALM - SEGURIDAD ALIMENTARIA">ALM - SEGURIDAD ALIMENTARIA</option>
                        <option value="AMB - MEDIO AMBIENTE">AMB - MEDIO AMBIENTE</option>
                        <option value="SEO - SEGURIDAD OPERACIONAL - RAMP SAFETY">SEO - SEGURIDAD OPERACIONAL - RAMP SAFETY</option>
                        <option value="SFI - SEGURIDAD FÍSICA">SFI - SEGURIDAD FÍSICA</option>
                        <option value="SST - SEGURIDAD Y SALUD EN EL TRABAJO">SST - SEGURIDAD Y SALUD EN EL TRABAJO</option>
                        <option value="SAG - SAGRILAFT">SAG - SAGRILAFT</option>
                        <option value="PTE - POLÍTICA Y ÉTICA EMPRESARIAL">PTE - POLÍTICA Y ÉTICA EMPRESARIAL</option>
                        <option value="COM - COMPRAS">COM - COMPRAS</option>
                        <option value="ABS - ABASTECIMIENTO">ABS - ABASTECIMIENTO</option>
                        <option value="IDS - SISTEMAS INTERNOS DE ENTREGA">IDS - SISTEMAS INTERNOS DE ENTREGA</option>
                        <option value="SIM - SOLICITUD INTERNA DE MATERIALES">SIM - SOLICITUD INTERNA DE MATERIALES</option>
                        <option value="PDP - PLANEACIÓN DE LA PRODUCCIÓN">PDP - PLANEACIÓN DE LA PRODUCCIÓN</option>
                        <option value="CRO - CONTROL DE RECURSOS OPERATIVOS">CRO - CONTROL DE RECURSOS OPERATIVOS</option>
                        <option value="CDM - CONTROL DE MATERIALES">CDM - CONTROL DE MATERIALES</option>
                        <option value="PCA - CARNICERIA">PCA - CARNICERIA</option>
                        <option value="PDE - DESINFECCIÓN">PDE - DESINFECCIÓN</option>
                        <option value="PFV - FRUTAS Y VERDURAS">PFV - FRUTAS Y VERDURAS</option>
                        <option value="PFI - FRITURAS">PFI - FRITURAS</option>
                        <option value="PPA - PASTAS">PPA - PASTAS</option>
                        <option value="PAN - PANADERIA">PAN - PANADERIA</option>
                        <option value="CBA - CATERING DE BANQUETES">CBA - CATERING DE BANQUETES</option>
                        <option value="CAO - CATERING DE ALIMENTOS">CAO - CATERING DE ALIMENTOS</option>
                        <option value="DEC - DECORACIÓN DE PLATOS">DEC - DECORACIÓN DE PLATOS</option>
                        <option value="PCC - COCINA FRÍA">PCC - COCINA FRÍA</option>
                        <option value="PCH - COCINA CALIENTE">PCH - COCINA CALIENTE</option>
                        <option value="TCC - CONTROL DE CALIDAD">TCC - CONTROL DE CALIDAD</option>
                        <option value="SAA - SOPORTE ALIMENTARIO">SAA - SOPORTE ALIMENTARIO</option>
                        <option value="SCC - SOPORTE A CLIENTES">SCC - SOPORTE A CLIENTES</option>
                        <option value="CSD - COMERCIALIZACIÓN Y DISTRIBUCIÓN">CSD - COMERCIALIZACIÓN Y DISTRIBUCIÓN</option>
                        <option value="SFS - SISTEMAS FINANCIEROS">SFS - SISTEMAS FINANCIEROS</option>
                        <option value="SEI - SERVICIO EXTERNO INTERNO">SEI - SERVICIO EXTERNO INTERNO</option>
                        <option value="PIT - PROYECTOS INTEGRADOS DE TERCEROS">PIT - PROYECTOS INTEGRADOS DE TERCEROS</option>
                        <option value="TTT - CAPACITACIONES">TTT - CAPACITACIONES</option>
                        <option value="SEG - SEGURIDAD">SEG - SEGURIDAD</option>
            <!-- Añade aquí más opciones según tus necesidades -->
        </select>
    </div>

    <form method="GET" action="" class="form-buscar">
    <div class="form-group">
        <label for="usuario">Buscar por Usuario:</label>
        <select id="usuario" name="usuario">
            <option value="">Seleccione un usuario</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>"
                        data-cargo="<?php echo htmlspecialchars($usuario['cargo']); ?>"
                        data-email="<?php echo htmlspecialchars($usuario['correo']); ?>"
                        data-rol="<?php echo htmlspecialchars($usuario['rol']); ?>">
                    <?php echo htmlspecialchars($usuario['nombre_usuario']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </div>
</form>
            <!-- Tabla de procesos -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Macroproceso</th>
                            <th>Proceso</th>
                            <th>Usuario</th>
                            <th>Cargo</th>
                            <th>Email</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            // Iterar a través de los resultados de la consulta
                            while ($row = $result->fetch_assoc()) {
                                // Obtener el color para la fila de la tabla
                                $colorClass = obtenerColor($row['macroproceso']);
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["macroproceso"]) . "</td>";
                                echo "<td class='$colorClass'>" . htmlspecialchars($row["proceso"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["usuario"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["cargo"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["rol"]) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No hay datos disponibles</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        const usuarioSelect = document.getElementById('usuario');
        const cargoInput = document.getElementById('cargo');
        const emailInput = document.getElementById('email');
        const rolInput = document.getElementById('rol');

        usuarioSelect.addEventListener('change', function () {
            const selectedOption = usuarioSelect.options[usuarioSelect.selectedIndex];
            cargoInput.value = selectedOption.getAttribute('data-cargo') || '';
            emailInput.value = selectedOption.getAttribute('data-email') || '';
            rolInput.value = selectedOption.getAttribute('data-rol') || '';
        });
    </script>

    <?php
    // Cerrar conexión
    $conn->close();
    ?>
</body>
</html>

