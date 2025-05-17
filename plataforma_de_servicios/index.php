<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Servicios a Domicilio</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        header {
            background-color: #2c3e50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        nav a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .banner {
            background: url('banner.jpg') center/cover no-repeat;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2em;
            font-weight: bold;
            text-shadow: 2px 2px 4px #000;
        }

        main {
            padding: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">
            <img src="/img/logo.png" alt="Logo Servicios">
            <span>HomeServices</span>
        </div>
        <nav>
            <a href="#">Inicio</a>
            <a href="#">Quiénes somos</a>
            <a href="login.php">Ingresar</a>
        </nav>
    </header>

    <div class="banner">
        Soluciones en Jardinería, Fontanería y más
    </div>

    <main>
        <h2>Bienvenido a ServiYA!</h2>
        <p>Ofrecemos profesionales verificados para trabajos de jardinería, fontanería, electricidad, limpieza y mucho más.</p>
    </main>

</body>

</html>