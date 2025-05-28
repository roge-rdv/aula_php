<?php
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
    <link rel="stylesheet" href="style.css"> <style>
        body { font-family: 'Arial', sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; }
        .container { width: 90%; max-width: 1024px; margin: 20px auto; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); border-radius: 10px; overflow: hidden; background-color: #fff;}
        .header { background-color: #4a6fa5; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 1.5em; }
        .logout-btn { background-color: #f0f0f0; color: #333; border: none; padding: 8px 15px; border-radius: 5px; text-decoration: none; }
        .content { display: flex; }
        .sidebar { width: 25%; background-color: #6d8cc7; padding: 20px; color: white; min-height: 300px; }
        .sidebar h3 { margin-top: 0; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin-bottom: 10px; }
        .sidebar ul li a { color: white; text-decoration: none; }
        .sidebar ul li a:hover { text-decoration: underline; }
        .main-content { width: 75%; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Olá, <?php echo $_SESSION['nome']; ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>
        
        <div class="content">
            <div class="sidebar">
                <h3>Menu</h3>
                <ul>
                    <li><a href="principal.php">Dashboard</a></li>
                    <li><a href="cadastrar_usuario.php">Gerenciar Usuários</a></li>
                    <li><a href="cadastrar_filme.php">Gerenciar Filmes</a></li>
                    <li><a href="#">Outra Coisa (Em Breve)</a></li>
                </ul>
            </div>
            <div class="main-content">
                <h2>Bem-vindo(a) ao Sistema!</h2>
                <p>Este é o painel principal. Use o menu ao lado para navegar.</p>
                <p>Aqui você pode gerenciar usuários e filmes da sua locadora/catálogo.</p>
                <p>Data de hoje: <?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>