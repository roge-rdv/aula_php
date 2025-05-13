<?php
session_start();
require 'conexao.php';

// Verifica se está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}

// Processa o cadastro do filme
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $ano = trim($_POST['ano']);
    $genero_id = intval($_POST['genero']);
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($nome) || empty($ano) || $genero_id <= 0) {
        $_SESSION['erro'] = "Preencha todos os campos obrigatórios!";
    } else {
        try {
            $stmt = $conexao->prepare("INSERT INTO filmes (nome, ano, genero_id, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $nome, $ano, $genero_id, $status);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Filme cadastrado com sucesso!";
            } else {
                $_SESSION['erro'] = "Erro ao cadastrar filme.";
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
        }
    }
    header("Location: cadastrar_filme.php");
    exit();
}

// Busca os gêneros ativos (status=1)
$generos = [];
try {
    $stmt = $conexao->prepare("SELECT id, descricao FROM generos WHERE status = 1");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $generos = $resultado->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    $_SESSION['erro'] = "Erro ao carregar gêneros: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Filme</title>
    <style>
        /* Estilo igual ao cadastrar_usuario.php */
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            background-color: #f0f2f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #4a6fa5;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #3b5998;
        }
        .mensagem {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .checkbox-group {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Mensagens de feedback -->
        <?php if(isset($_SESSION['mensagem'])): ?>
            <div class="mensagem sucesso">
                <?= $_SESSION['mensagem'] ?>
                <?php unset($_SESSION['mensagem']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['erro'])): ?>
            <div class="mensagem erro">
                <?= $_SESSION['erro'] ?>
                <?php unset($_SESSION['erro']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de cadastro -->
        <div class="card">
            <h2>Cadastrar Novo Filme</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nome do Filme:</label>
                    <input type="text" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label>Ano de Lançamento:</label>
                    <input type="text" name="ano" required>
                </div>
                
                <div class="form-group">
                    <label>Gênero:</label>
                    <select name="genero" required>
                        <option value="">Selecione um gênero</option>
                        <?php foreach ($generos as $genero): ?>
                            <option value="<?= $genero['id'] ?>"><?= htmlspecialchars($genero['descricao']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="status" value="1" checked> Ativo
                    </label>
                </div>

                <button type="submit">Cadastrar Filme</button>
            </form>
        </div>
    </div>
</body>
</html>
