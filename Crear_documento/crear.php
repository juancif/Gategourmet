
<?php
include_once("config_crear.php");

if (isset($_POST['Submit'])) {
    $proceso = $_POST['proceso'];
    $codigo = $_POST['codigo'];
    $titulo_documento = $_POST['titulo_documento'];
    $tipo = $_POST['tipo'];
    $version = $_POST['version'];
    $estado = $_POST['estado'];
    $fecha_aprobacion = $_POST['fecha_aprobacion'];
    $areas = $_POST['areas'];
    $motivo_del_cambio = $_POST['motivo_del_cambio'];
    $tiempo_de_retencion = $_POST['tiempo_de_retencion'];
    $responsable_de_retencion = $_POST['responsable_de_retencion'];
    $lugar_de_almacenamiento_fisico = $_POST['lugar_de_almacenamiento_fisico'];
    $lugar_de_almacenamiento_magnetico = $_POST['lugar_de_almacenamiento_magnetico'];
    $conservacion = $_POST['conservacion'];
    $disposicion_final = $_POST['disposicion_final'];
    $copias_controladas = $_POST['copias_controladas'];
    $fecha_de_vigencia = $_POST['fecha_de_vigencia'];
    $dias = $_POST['dias'];
    $senal_alerta = $_POST['senal_alerta'];
    $obsoleto = isset($_POST['obsoleto']) ? 1 : 0;
    $anulado = isset($_POST['anulado']) ? 1 : 0;
    $en_actualizacion = isset($_POST['en_actualizacion']) ? 1 : 0;

    // Verificar si algún campo está vacío
    if (empty($proceso) || empty($codigo) || empty($titulo_documento) || empty($tipo) || empty($version) || empty($estado) || empty($fecha_aprobacion)) {
        if (empty($proceso)) {
            echo "<font color='red'>Campo: proceso está vacío.</font><br/>";
        }
        if (empty($codigo)) {
            echo "<font color='red'>Campo: código está vacío.</font><br/>";
        }
        if (empty($titulo_documento)) {
            echo "<font color='red'>Campo: título_documento está vacío.</font><br/>";
        }
        if (empty($tipo)) {
            echo "<font color='red'>Campo: tipo está vacío.</font><br/>";
        }
        if (empty($version)) {
            echo "<font color='red'>Campo: versión está vacío.</font><br/>";
        }
        if (empty($estado)) {
            echo "<font color='red'>Campo: estado está vacío.</font><br/>";
        }
        if (empty($fecha_aprobacion)) {
            echo "<font color='red'>Campo: fecha_aprobacion está vacío.</font><br/>";
        }
        echo "<br/><a href='javascript:self.history.back();'>Volver</a>";
    } else {
        try {
            $dbConn->beginTransaction();
        
            $sql = "INSERT INTO listado_maestro 
                (proceso, codigo, titulo_documento, tipo, version, estado, fecha_aprobacion, areas, motivo_del_cambio, tiempo_de_retencion, 
                responsable_de_retencion, lugar_de_almacenamiento_fisico, lugar_de_almacenamiento_magnetico, conservacion, disposicion_final, 
                copias_controladas, fecha_de_vigencia, dias, senal_alerta, obsoleto, anulado, en_actualizacion) 
                VALUES (:proceso, :codigo, :titulo_documento, :tipo, :version, :estado, :fecha_aprobacion, :areas, :motivo_del_cambio, 
                :tiempo_de_retencion, :responsable_de_retencion, :lugar_de_almacenamiento_fisico, :lugar_de_almacenamiento_magnetico, 
                :conservacion, :disposicion_final, :copias_controladas, :fecha_de_vigencia, :dias, :senal_alerta, :obsoleto, :anulado, :en_actualizacion)";
        
            $query = $dbConn->prepare($sql);
            $query->bindparam(':proceso', $proceso);
            $query->bindparam(':codigo', $codigo);
            $query->bindparam(':titulo_documento', $titulo_documento);
            $query->bindparam(':tipo', $tipo);
            $query->bindparam(':version', $version);
            $query->bindparam(':estado', $estado);
            $query->bindparam(':fecha_aprobacion', $fecha_aprobacion);
            $query->bindparam(':areas', $areas);
            $query->bindparam(':motivo_del_cambio', $motivo_del_cambio);
            $query->bindparam(':tiempo_de_retencion', $tiempo_de_retencion);
            $query->bindparam(':responsable_de_retencion', $responsable_de_retencion);
            $query->bindparam(':lugar_de_almacenamiento_fisico', $lugar_de_almacenamiento_fisico);
            $query->bindparam(':lugar_de_almacenamiento_magnetico', $lugar_de_almacenamiento_magnetico);
            $query->bindparam(':conservacion', $conservacion);
            $query->bindparam(':disposicion_final', $disposicion_final);
            $query->bindparam(':copias_controladas', $copias_controladas);
            $query->bindparam(':fecha_de_vigencia', $fecha_de_vigencia);
            $query->bindparam(':dias', $dias);
            $query->bindparam(':senal_alerta', $senal_alerta);
            $query->bindparam(':obsoleto', $obsoleto);
            $query->bindparam(':anulado', $anulado);
            $query->bindparam(':en_actualizacion', $en_actualizacion);
            $query->execute();
        
            $dbConn->commit();
        
            if ($query->rowCount() > 0) {
                // Redirigir a la página deseada después del registro exitoso
                header("Location: http://localhost/GateGourmet/register/registro_exitoso/registro_exitoso.php");
                exit();
            } else {
                echo "<font color='red'>Error al registrar el documento.</font><br/>";
            }
        } catch (Exception $e) {
            $dbConn->rollBack();
            echo "<font color='red'>Error: " . $e->getMessage() . "</font><br/>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Documentos</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Poppins:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="crear.css">
</head>
<body>
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/Index/index_admin.html" class="cerrar__sesion__link"><img src="../Imagenes/regresar.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Volver al inicio</div></a>
            </li>
    <main class="main-content">
        <div class="register-container">
            <div class="register-box">
                <h2>Registro de Documentos</h2>
                <form method="post" action="">
                    <div class="input-group">
                        <label for="proceso">Proceso</label>
                        <input type="text" id="proceso" name="proceso" required>
                    </div>
                    <div class="input-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" required>
                    </div>
                    <div class="input-group">
                        <label for="titulo_documento">Título del Documento</label>
                        <input type="text" id="titulo_documento" name="titulo_documento" required>
                    </div>
                    <div class="input-group">
                        <label for="tipo">Tipo</label>
                        <input type="text" id="tipo" name="tipo" required>
                    </div>
                    <div class="input-group">
                        <label for="version">Versión</label>
                        <input type="text" id="version" name="version" required>
                    </div>
                    <div class="input-group">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado">
                            <option value="">Seleccione una opción</option>
                            <option value="Administrador">Desactualizado</option>
                            <option value="Aprobador">Obsoleto</option>
                            <option value="Digitador">Anulado</option>
                            <option value="Observador">Vigente</option>
                         </select>       
                    </div>
                    <div class="input-group">
                        <label for="fecha_aprobacion">Fecha de Aprobación</label>
                        <input type="date" id="fecha_aprobacion" name="fecha_aprobacion" required>
                    </div>
                    <div class="input-group">
                        <label for="areas">Áreas</label>
                        <select name="areas" id="areas">
                            <option value="">Seleccione una opción</option>
                            <option value="Gestion_corporativa">Gestión corporativa</option>
                            <option value="Compliance">Compliance</option>
                            <option value="Supply_chain">Supply Chain</option>
                            <option value="Culinary_Excellence">Culinary Excellence</option>
                            <option value="Supervisor"  >Service Delivery</option>
                            <option value="Assembly">Assembly</option>
                            <option value="Servicios_institucionales">Servicios institucionales</option>
                            <option value="Financiera">Financiera</option>
                            <option value="Costos">Costos</option>
                            <option value="Comunicaciones">Comunicaciones</option>
                            <option value="Tecnologia_de_la_información">Tecnologia de la información</option>
                            <option value="Talento_humano">Talento Humano</option>
                            <option value="Mateninimiento">Mateninimiento</option>
                            <option value="Servicio_al_cliente">Servicio al cliente</option>
                            <option value="Security">Security</option>
                        </select>
                    </div>
                    <!-- <div class="input-group">
                        <label for="motivo_del_cambio">Motivo del Cambio</label>
                        <textarea id="motivo_del_cambio" name="motivo_del_cambio" placeholder="*Campo no obligatorio"></textarea>
                    </div>
                    <div class="input-group">
                        <label for="tiempo_de_retencion">Tiempo de Retención</label>
                        <input type="text" id="tiempo_de_retencion" name="tiempo_de_retencion" placeholder="*Campo no obligatorio">
                    </div>
                    <div class="input-group">
                        <label for="responsable_de_retencion">Responsable de Retención</label>
                        <input type="text" id="responsable_de_retencion" name="responsable_de_retencion" placeholder="*Campo no obligatorio">
                    </div>
                    <div class="input-group">
                        <label for="lugar_de_almacenamiento_fisico">Lugar de Almacenamiento Físico</label>
                        <textarea id="lugar_de_almacenamiento_fisico" name="lugar_de_almacenamiento_fisico" placeholder="*Campo no obligatorio"></textarea>
                    </div>
                    <div class="input-group">
                        <label for="lugar_de_almacenamiento_magnetico">Lugar de Almacenamiento Magnético</label>
                        <textarea id="lugar_de_almacenamiento_magnetico" name="lugar_de_almacenamiento_magnetico" placeholder="*Campo no obligatorio"></textarea>
                    </div>
                    <div class="input-group">
                        <label for="conservacion">Conservación</label>
                        <textarea id="conservacion" name="conservacion" placeholder="*Campo no obligatorio"></textarea>
                    </div>
                    <div class="input-group">
                        <label for="disposicion_final">Disposición Final</label>
                        <textarea id="disposicion_final" name="disposicion_final" placeholder="*Campo no obligatorio"></textarea>
                    </div> -->
                    <div class="input-group">
                        <label for="copias_controladas">Copias Controladas</label>
                        <input type="number" id="copias_controladas" name="copias_controladas" placeholder="0"></input>
                    </div>
                    <div class="input-group">
                        <label for="fecha_de_vigencia">Fecha de Vigencia</label>
                        <input type="date" id="fecha_de_vigencia" name="fecha_de_vigencia">
                    </div>
                    <div class="input-group">
                        <label for="dias">Días</label>
                        <input type="number" id="dias" name="dias">
                    </div>
                    <!-- <div class="input-group">
                        <label for="senal_alerta">Señal de Alerta</label>
                        <input type="text" id="senal_alerta" name="senal_alerta" placeholder="*Campo no obligatorio">
                    </div>
                    <div class="input-group">
                        <label for="obsoleto">Obsoleto</label>
                        <input type="checkbox" id="obsoleto" name="obsoleto">
                    </div>
                    <div class="input-group">
                        <label for="anulado">Anulado</label>
                        <input type="checkbox" id="anulado" name="anulado">
                    </div>
                    <div class="input-group">
                        <label for="en_actualizacion">En Actualización</label>
                        <input type="checkbox" id="en_actualizacion" name="en_actualizacion">
                    </div> -->
                    <div class="buttons">
                        <input type="submit" name="Submit" value="Agregar" class="Registrarse">
                        <a href="http://localhost/GateGourmet/Index/index_admin.html" class="regresar">Regresar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer">
        <p><a href="#">Ayuda</a> | <a href="#">Términos de servicio</a></p>
        <script src="/script_prueba/script.js"></script>
    </footer>
</body>
</html>
