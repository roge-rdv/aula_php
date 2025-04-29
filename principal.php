<?php
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 90%;
            max-width: 1024px; 
            margin: 20px auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .header {
            width: 100%;
            background-color: #4a6fa5;
            color: white;
            padding: 15px 0;
            position: relative;
        }
        
        .header h1 {
            display: inline-block;
            margin: 0;
            padding: 0 20px;
            font-size: 24px;
        }
        
        .logout-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #fff;
            color: #4a6fa5;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: #f1f1f1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .content {
            display: flex;
            min-height: 300px;
        }
        
        .sidebar {
            width: 25%;
            background-color: #6d8cc7;
            padding: 20px;
            color: white;
        }
        
        .main-content {
            width: 75%;
            padding: 20px;
            background-color: white;
        }
        
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</h1>
            <a href="logout.php"><button class="logout-btn">Logout</button></a>
        </header>
        
        <div class="content">
            <div class="sidebar">
                <h3>Menu</h3>
                <ul>
                    <li>Dashboard</li>
                    <a href= "cadastraruser.php"> <li>Cadastrar</li> </a>
                    <li>Configurações</li>
                </ul>
            </div>
            <div class="main-content">
                <h2>Bem-vindo ao seu painel</h2>
                <p>Aqui você vê o resultado das Aulas Propostas no Curso de ADS</p>
            </div>
        </div>
    </div>
</body>
</html>