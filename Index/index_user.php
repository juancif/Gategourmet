<?php
session_start();
$area = isset($_SESSION['area']) ? $_SESSION['area'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GateGourmet</title>
    <link rel="stylesheet" href="style_index5.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <nav class="nav__principal">
        <ul class="nav__list">
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/listado_maestro/listado_maestro_user.php" class="nav__link"><img src="../imagenes/security.png" alt="Seguridad" class="imgs__menu">Listado maestro</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Crear_documento/crear.php" class="nav__link"><img src="../imagenes/security.png" alt="Seguridad" class="imgs__menu">Crear documento</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Indicadores/indicadores_user.php" class="nav__link"><img src="../imagenes/config.png" alt="Configuracióm" class="imgs__menu">Indicadores</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/procesos/procesos.php" class="nav__link"><img src="../Imagenes/macroprocesos2.png" alt="macroprocesos" class="imgs__menu">Procesos</a>
            </li>
            <li class="nav__item">
                <a href="" class="nav__link"><img src="../Imagenes/macroprocesos2.png" alt="macroprocesos" class="imgs__menu">Reportes</a>
            </li>
            <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/Movimientos/logout.php" class="cerrar__sesion__link"><img src="../Imagenes/image.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Cerrar sesión</div></a>
            </li>
        </ul>
    </nav>
    <div class="recuadroimagen"><img src="../Imagenes/Logo_oficial_B-N.png" class="logoindex">
        <img src="../Imagenes/logo__recuadro__gategourmet.png" alt="img4" class="triangulo">
    </div>
    <div class="cuadro1" id="cuadro1">
        <div class="recuadro1">
            <div><h3 class="title title__estrag">Estrategicos</h3></div>
            <p class="parrafo_estrategicos">Procesos destinados a definir <br>y controlar las metas de la <br>organizacion, sus politicas y <br>estrategias.</p>
        </div>
        <div class="circulo circulo1"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FGesti%C3%B3n%20Corporativa&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3 class="h3__2">Gestión corporativa</h3></a></div>
        <div class="circulo circulo2"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FCompliance&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3>Compliance</h3></a></div>
    </div>
    <div class="cuadro2" id="cuadro2">
        <div class="recuadro2">
            <div><h3 class="title_misionales">Misionales</h3></div>
            <p class="parrafo_misionales">Procesos que permiten generar el producto/servicio que se entregan al cliente. <br>Agregan valor al cliente.</p>
        </div>
        <div class="circulo circulo3"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FSupply%20Chain&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3 class="h3__2">Supply chain</h3></a></div>
        <div class="circulo circulo4"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FCulinary&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3>Culinary</h3></a></div>
        <div class="circulo circulo5"><a href="https://workdrive.zoho.com/file/hpbd780390dcd8e964441a13bc568d214fb30" class="link1"><h3>Assembly</h3></a></div>
        <div class="circulo circulo6"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3 class="h3__2">Service delivery</h3></a></div>
        <div class="circulo circulo7"><a href="https://1drv.ms/f/s!Aijf4XW5EsnbmQqAF01L3lqjcNLc?e=2rZY8S" class="link1"><h3 class="h3__2">Servicios institucionales</h3></a></div>
    </div>
    <div class="cuadro3" id="cuadro3">
        <div class="recuadro3">
            <div><h3 class="title_soporte">Soporte</h3></div>
            <p class="parrafo_soporte">Procesos que abarcan las actividades necesarias para el correcto funcionamiento de los procesos operativos.</p>
        </div>
        <div class="circulo circulo8"><a href="https://1drv.ms/w/s!Aijf4XW5EsnbmQ0kfSuUy3leGNi0?e=M6FAKA" class="link1"><h3>Financiera</h3></a></div>
        <div class="circulo circulo9"><a href="https://1drv.ms/w/s!Aijf4XW5EsnbmQ0kfSuUy3leGNi0?e=M6FAKAb" class="link1"><h3>Costos</h3></a></div>
        <div class="circulo circulo10"><a href="http://localhost/GateGourmet/Index/index_comunicaciones.php" class="link1"><h3 class="h3__3">Comunicaciones</h3></a></div>
        <div class="circulo circulo11"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3>IT</h3></a></div>
        <div class="circulo circulo12"><a href="http://localhost/GateGourmet/Index/vista_usuarios/security.php" class="link1"><h3>Security</h3></a></div>
        <div class="circulo circulo13"><a href="https://workdrive.zoho.com/file/hpbd780390dcd8e964441a13bc568d214fb30" class="link1"><h3 class="h3__2">Servicio al cliente</h3></a></div>
        <div class="circulo circulo14"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3 class="h3__2">Facilty service</h3></a></div>
        <div class="circulo circulo15"><a href="https://show.zoho.com/show/open/hpbd733318fd8b4834fcebcbc508ace6b9c64" class="link1"><h3 class="h3__2">Talento humano</h3></a></div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const areaUsuario = "<?php echo $area; ?>"; // Aquí tomamos el área del usuario desde PHP

            console.log("Área del usuario:", areaUsuario); // Para verificar que está llegando correctamente

            const circulos = document.querySelectorAll(".circulo");

            circulos.forEach(circulo => {
                const h3Text = circulo.querySelector("h3").textContent.trim();

                console.log("Texto del h3:", h3Text); // Para verificar el texto de cada h3

                if (h3Text !== areaUsuario) {
                    // Deshabilitar la funcionalidad de click
                    const link = circulo.querySelector("a");
                    link.removeAttribute("href");
                    link.style.pointerEvents = "none";
                    link.style.opacity = "0.5"; // Visualmente deshabilitar
                }
            });
        });
    </script>
</body>
</html>

