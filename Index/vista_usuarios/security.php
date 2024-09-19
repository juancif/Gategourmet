<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GateGourmet</title>
    <link rel="stylesheet" href="../style_index_user.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <style>
        .com{
            margin-top: 10px;
            margin-left: -25px
        }
    </style>
    <nav class="nav__principal">
        <ul class="nav__list">
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Gestor_usuarios/index_gestor.php" class="nav__link"><img src="../../imagenes/security.png" alt="Seguridad" class="imgs__menu">Listado maestro</a>
            </li>
            <li class="nav__item">
                <a href="http://localhost/GateGourmet/Gestor_usuarios/index_gestor.php" class="nav__link"><img src="../../imagenes/security.png" alt="Seguridad" class="imgs__menu">Crear documento</a>
            </li>
            <li class="nav__item">
                <a href="https://gategrouphq.sharepoint.com/:x:/s/Prueba.gg/EbHH2VRCeqpDubiulAy5SMQBUjs9iZgN5-FTvMeLACBnWA?e=elWHnc" class="nav__link"><img src="../../imagenes/config.png" alt="Configuracióm" class="imgs__menu">Indicadores</a>
            </li>
            <li class="nav__item">
                <a href="" class="nav__link"><img src="../../Imagenes/macroprocesos2.png" alt="macroprocesos" class="imgs__menu">Procesos</a>
            </li>
            <li class="nav__item">
                <a href="" class="nav__link"><img src="../../Imagenes/macroprocesos2.png" alt="macroprocesos" class="imgs__menu">Reportes</a>
            </li>
            <li class="nav__item__user">
                <a href="http://localhost/GateGourmet/login/login3.php" class="cerrar__sesion__link"><img src="../../Imagenes/image.png" alt="Usuario" class="img__usuario"><div class="cerrar__sesion">Cerrar sesión</div></a>
            </li>
        </ul>
    </nav>
    <div class="recuadroimagen"><img src="../../Imagenes/Logo_oficial_B-N.png" class="logoindex">
        <img src="../../Imagenes/logo__recuadro__gategourmet.png" alt="img4" class="triangulo">
    </div>
    <div class="cuadro1" id="cuadro1">
        <div class="recuadro1_perso">
        <a href="http://localhost/GateGourmet/Index/index_user.php" class="link1"><h3 class="h3__2">Security</h3></a></div>
        </div>
        <div class="circulo_user"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FGesti%C3%B3n%20Corporativa&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3 class="h3__2">Control de riesgos fisicos</h3></a></div>
        <div class="circulo_user2 circulo_user"><a href="https://gategrouphq.sharepoint.com/sites/Prueba.gg/Shared%20Documents/Forms/AllItems.aspx?newTargetListUrl=%2Fsites%2FPrueba%2Egg%2FShared%20Documents&viewpath=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FForms%2FAllItems%2Easpx&id=%2Fsites%2FPrueba%2Egg%2FShared%20Documents%2FDocumentos%5F%5Fprueba%2FCompliance&viewid=7e698b00%2D50a8%2D4a9f%2Daf64%2D4414983a1399" class="link1"><h3>Seguridad fisica</h3></a></div>
    </div>
    <!-- <script>
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
    </script> -->
</body>
</html>

