<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gategourmet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}

// Insertar datos
$data = [
    ['GESTION CORPORATIVA', 'DES - DIRECCIONAMIENTO ESTRATÉGICO', 'Sanchez Fernandez Antonio', 'Country Manager', 'asanchez@gategroup.com', 'Observador'],
    ['', 'GMC - GESTION DE MEJORA CONTINUA', '', '', '', ''],
    ['IND - INDUCCIÓN ORGANIZACIONAL', 'Camacho Salcedo Vanessa', 'Manager HR', 'VaCamacho@gategroup.com', 'Aprueba'],
    ['EYS - EVALUACIÓN Y SEGUIMIENTO', 'Rodriguez Camacho Magda', 'Superintendent HR', 'MagRodriguez@gategroup.com', 'Digita'],
    ['GER - GESTIONAR LAS RELACIONES', 'Huertas Carmona Catalina', 'Superintendent Development And Communications', 'chuertascarmona@gategroup.com', 'Digita'],
    ['ENP - EVALUAR NUEVOS PROYECTOS DE INVERSIÓN Y OPERACIONALES', 'Ramirez Dominguez Diana', 'Continuous Improvement Manager', 'DRamirez@gategroup.com', 'Administrador'],
    ['COMPLIANCE', 'ALM - SEGURIDAD ALIMENTARIA', 'Jaimes Puertas Diego', 'Coordinador de mejoramiento Continuo', 'DJaims@gategroup.com', 'Administrador'],
    ['AMB - MEDIO AMBIENTE', '', '', '', '', ''],
    ['SEO - SEGURIDAD OPERACIONAL - RAMP SAFETY', 'Parra Pena Julieth', 'Supervisor de Calidad y Gestion Ambiental', 'jparrapena@gategroup.com', 'Digita'],
    ['SFI - SEGURIDAD FÍSICA', 'Betancur Jorge Ivan', 'EHS Manager', 'jbetancur@gategroup.com', 'Digita'],
    ['SST - SEGURIDAD Y SALUD EN EL TRABAJO', 'Dominguez Posada Natalia', 'Process Owner Assembly', 'NDominguezPosada@gategroup.com', 'Observador'],
    ['SAG - SAGRILAFT', 'Suarez Ortiz William', 'Section Manager Pick & Pack', 'WSuarez@gategroup.com', 'Digita'],
    ['PTE - POLÍTICA Y ÉTICA EMPRESARIAL', 'Sanabria Rodriguez Edwin', 'Section Manager Wash & Pack', 'ESanabria@gategroup.com', 'Digita'],
    ['SUPPLY CHAIN', 'COM - COMPRAS', 'Ordonez Rodriguez Michael', 'Section Manager Laundry', 'miordonez@gategroup.com', 'Digita'],
    ['ABS - ABASTECIMIENTO', 'Roa Pinzon Edwin', 'Section Manager Make & Pack', 'ERoa@gategroup.com', 'Digita'],
    ['IDS - SISTEMAS INTERNOS DE ENTREGA', 'Saenz Gordillo Gabriel', 'Process Owner Planning & Supply Chain', 'GSaenz@gategroup.com', 'Aprueba'],
    ['SIM - SOLICITUD INTERNA DE MATERIALES', 'Cabrera Mendieta Alexis', 'Section Manager - IDS', 'ACabrera@gategroup.com', 'Digita'],
    ['PDP - PLANEACIÓN DE LA PRODUCCIÓN', 'Roa Nieto Alex', 'Manager, Ordering', 'ARoa@gategroup.com', 'Digita'],
    ['CRO - CONTROL DE RECURSOS OPERATIVOS', 'Ladino Leal Oscar', 'Executive Sous Chef', 'OLadino@gategroup.com', 'Aprueba'],
    ['CDM - CONTROL DE MATERIALES', 'Gonzalez Torres Adriana', 'Sous Chef', 'AdGonzalez@gategroup.com', 'Digita'],
    ['CULINARY EXCELLENCE', 'SIM - SOLICITUD INTERNA DE MATERIALES', 'Buitrago Umana Javier', 'Process Owner Service Delivery', 'JBuitrago@gategroup.com', 'Aprueba'],
    ['PCA - CARNICERIA', 'Españá Rodriguez John', 'Manager Transport', 'JEspaña@gategroup.com', 'Digita'],
    ['PDE - DESINFECCION', 'Medina Yazmin', 'Junior Section Manager OP&D', 'yamedina@gategroup.com', 'Digita'],
    ['PFV - FRUTAS Y VERDURAS', 'Manrique Ortiz Giovanni', 'Especialista de Seguridad en Rampa', 'GManrique@gategroup.com', 'Digita'],
    ['COC - COCINA CALIENTE', 'Escobar Nancy', 'Director Comercial', 'nescobar@gategroup.com', 'Aprueba'],
    ['COF - COCINA FRÍA', 'Contreras Palacio Omar', 'Junior Key Account Officer', 'OContreras@gategroup.com', 'Digita'],
    ['PYP - PANADERÍA Y PASTELERÍA', 'Neita Ramirez Luis', 'Senior Manager Facility Services', 'LNeita@gategroup.com', 'Aprueba'],
    ['CAP - CUARTO DE ALMACENAMIENTO DE PRODUCCIÓN', 'Cortes Lozano Andres', 'Especialista en Mantenimiento', 'acorteslozano@gategroup.com', 'Digita'],
    ['CAR - CENTRAL DE ARMADO DE RECETAS', 'Ortiz Caballero Mauricio', 'Jefe Control de Riesgos Físicos', 'MOrtiz@gategroup.com', 'Aprueba'],
    ['DDP - DESARROLLO DE PRODUCTOS', 'Ibanez Luz', 'Jefe de Costos', 'libanez@gategroup.com', 'Aprueba'],
    ['CUK - CULINARY KITTING', 'Venegas Jaison', 'CPC Champion', 'jvenegas@gategroup.com', 'Digita'],
    ['SERVICE DELIVERY', 'ADE - ABORDAMIENTO / DESABORDAMIENTO Y ENTREGAS', 'Garzon Alejandra', 'Manager New Operations', 'AlGarzon@gategroup.com', 'Aprueba'],
    ['CEC - CENTRO DE CONTROL', 'Hernandez Thalia', 'VIP Lounges Junior Section Manager', 'thhernandez@gategroup.com', 'Digita'],
    ['TRA - TRANSPORTE', 'Medellin Carmen', 'Manager Finance', 'CMedellin@gategroup.com', 'Aprueba'],
    ['HFR - HOLDING FRÍO', 'Carabante Nelson', 'Auxiliar Contable', 'NCarabante@gategroup.com', 'Digita'],
    ['HSC - HOLDING SECO', '', '', '', '', ''],
    ['ASSEMBLY', 'HEL - HIGIENIZAR EQUIPO PRIMARIO Y SECUNDARIO', '', '', '', ''],
    ['AUD - AUDÍFONOS', '', '', '', '', ''],
    ['LAV - LAVANDERÍA', '', '', '', '', ''],
    ['ABL - ARMADO BARES Y LICORES', '', '', '', '', ''],
    ['CDD - CENTRO DE DISTRIBUCIÓN', '', '', '', '', ''],
    ['ADC - ARMADO DE CUBIERTOS Y CRISTALES', '', '', '', '', ''],
    ['MYP - MAKE & PACK', '', '', '', '', ''],
    ['SERVICIOS INSTITUCIONALES', 'CAS - CASINO', '', '', '', ''],
    ['FINANCIERA', 'TES - TESORERÍA', '', '', '', ''],
    ['PRE - PRESUPUESTO', '', '', '', '', ''],
    ['NMN - NÓMINA', '', '', '', '', ''],
    ['INV - INVENTARIOS', '', '', '', '', ''],
    ['INF - INFORMES', '', '', '', '', ''],
    ['IMP - IMPUESTOS', '', '', '', '', ''],
    ['FAC - FACTURACIÓN', '', '', '', '', ''],
    ['ACF - ACTIVOS FIJOS', '', '', '', '', ''],
    ['CON - CONTABILIDAD', '', '', '', '', ''],
    ['COSTOS', 'COS - COSTOS', '', '', '', ''],
    ['COMUNICACIONES', 'COI - COMUNICACIONES INTERNAS', '', '', '', ''],
    ['ARC - CORRESPONDENCIA Y ARCHIVO', '', '', '', '', ''],
    ['TECNOLOGÍA DE LA INFORMACIÓN', 'DPA - DESARROLLAR PROYECTOS Y APLICACIONES', '', '', '', ''],
    ['MRE - MANTENIMIENTO DE REDES Y EQUIPOS', '', '', '', '', ''],
    ['PRD - PLANES DE CONTINGENCIA Y RECUPERACIÓN ANTE DESASTRES', '', '', '', '', ''],
    ['ISE - INSTALAR SOFTWARE Y EQUIPOS', '', '', '', '', ''],
    ['MSE - MANEJO DE SOFTWARE Y EQUIPOS', '', '', '', '', ''],
    ['TALENTO HUMANO', 'SYC - SELECCIÓN Y CONTRATACIÓN', '', '', '', ''],
    ['DTH - DESARROLLAR EL TALENTO HUMANO', '', '', '', '', ''],
    ['NOM - NÓMINA', '', '', '', '', ''],
    ['BIE - BIENESTAR', '', '', '', '', ''],
    ['APE - ADMINISTRACIÓN DE PERSONAL', '', '', '', '', ''],
    ['MANTENIMIENTO', 'MPL - MANTENIMIENTO DE PLANTA', '', '', '', ''],
    ['MEQ - MANTENIMIENTO DE EQUIPOS', '', '', '', '', ''],
    ['MAV - MANTENIMIENTO DE VEHÍCULOS', '', '', '', '', ''],
    ['ASE - ASEO', '', '', '', '', ''],
    ['SERVICIO AL CLIENTE', 'DNN - DESARROLLO NUEVOS NEGOCIOS', '', '', '', ''],
    ['GET - GESTIÓN TENDERS', '', '', '', '', ''],
    ['CLI - SERVICIO AL CLIENTE', '', '', '', '', ''],
    ['GDP - GESTIÓN DE PRESENTACIONES', '', '', '', '', ''],
    ['SECURITY', 'CRF - CONTROL DE RIESGOS FÍSICOS', '', '', '', ''],
    ['SFI - SEGURIDAD FÍSICA', '', '', '', '', '']
];

foreach ($data as $row) {
    $macroproceso = $conn->real_escape_string($row[0]);
    $proceso = $conn->real_escape_string($row[1]);
    $usuarios = $conn->real_escape_string($row[2]);
    $cargos = $conn->real_escape_string($row[3]);
    $email = $conn->real_escape_string($row[4]);
    $rol = $conn->real_escape_string($row[5]);

    $sql = "INSERT INTO procesos (MACROPROCESO, PROCESO, USUARIOS, CARGOS, EMAIL, ROL) 
            VALUES ('$macroproceso', '$proceso', '$usuarios', '$cargos', '$email', '$rol')";

    if (!$conn->query($sql)) {
        echo "Error: " . $conn->error;
    }
}

// Cerrar conexión
$conn->close();
?>
