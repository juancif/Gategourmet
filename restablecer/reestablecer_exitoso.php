<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éxito</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap">
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: url('../Imagenes/fondogg3.webp') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
        }

        .header {
            background: #1117;
            backdrop-filter: blur(10px);
            text-align: center;
            padding: 20px 40px;
            box-shadow: 0 4px 6px rgba(143, 187, 138, 0.1);
        }

        .logo {
            max-width: 500px;
            margin: 0 auto;
        }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: #2d6e50; /* Verde oliva más oscuro */
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            color: white;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 2em;
        }

        p {
            margin-bottom: 30px;
            font-size: 16px;
            color: #fff;
        }

        .button {
            display: inline-block;
            padding: 12px 25px;
            background: #white; /* Verde oliva más oscuro */
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .button:active {
            transform: scale(1);
        }

        .footer {
            background: #1117;
            backdrop-filter: blur(10px);
            text-align: center;
            padding: 20px;
            color: #fff;
            box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
        }

        .footer a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header class="header">
        <img src="../Imagenes/Logo_oficial_B-N.png" alt="Gate Gourmet Logo" class="logo">
    </header>
    <main class="main-content">
        <div class="container">
            <h1>¡Éxito!</h1>
            <p>Se ha enviado un enlace de restablecimiento de contraseña a tu correo electrónico.</p>
            <a href="http://10.24.217.100/GateGourmet/login/login3.php" class="button">Volver a la Página de Inicio</a>
        </div>
    </main>
    <footer class="footer">
        <p>&copy; 2024 Gate Gourmet. Todos los derechos reservados.</p>
        <a href="#">Política de Privacidad</a>
        <a href="#">Términos y Condiciones</a>
    </footer>
</body>
</html>
