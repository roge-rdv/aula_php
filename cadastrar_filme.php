<?php
session_start();
require 'conexao.php';

// checa se ta logado msm
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}

// excluir filme
if (isset($_GET['excluir_filme'])) {
    // pega id do filme
    $filme_id = intval($_GET['excluir_filme']);
    $sql = "DELETE FROM filmes WHERE filme = $filme_id";
    if ($conexao->query($sql)) {
        $_SESSION['mensagem'] = "Filme excluído com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao excluir filme.";
    }
    header("Location: cadastrar_filme.php");
    exit();
}

// muda genero do filme
if (isset($_POST['alterar_genero_filme'])) {
    $filme_id = intval($_POST['filme_id']);
    $novo_genero = intval($_POST['novo_genero']);
    $sql = "UPDATE filmes SET genero = $novo_genero WHERE filme = $filme_id";
    if ($conexao->query($sql)) {
        $_SESSION['mensagem'] = "Gênero do filme alterado com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao alterar gênero do filme.";
    }
    header("Location: cadastrar_filme.php" . (isset($_GET['filtro_genero']) ? "?filtro_genero=" . intval($_GET['filtro_genero']) : ""));
    exit();
}

// editar filme (nome, ano, genero)
if (isset($_POST['salvar_edicao_filme'])) {
    $filme_id = intval($_POST['filme_id']);
    $novo_nome = $_POST['novo_nome'];
    $novo_ano = $_POST['novo_ano'];
    $novo_genero = intval($_POST['novo_genero']);
    // aqui faz update
    $sql = "UPDATE filmes SET descricao = '$novo_nome', ano = '$novo_ano', genero = $novo_genero WHERE filme = $filme_id";
    if ($conexao->query($sql)) {
        $_SESSION['mensagem'] = "Filme alterado com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao alterar filme.";
    }
    header("Location: cadastrar_filme.php" . (isset($_GET['filtro_genero']) ? "?filtro_genero=" . intval($_GET['filtro_genero']) : ""));
    exit();
}

// cadastrar novo filme
if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    && !isset($_POST['alterar_genero_filme'])
    && !isset($_POST['salvar_edicao_filme'])
) {
    $nome = $_POST['nome'];
    $ano = $_POST['ano'];
    $genero_id = intval($_POST['genero']);

    // valida se preencheu td
    if (empty($nome) || empty($ano) || $genero_id <= 0) {
        $_SESSION['erro'] = "Preencha todos os campos obrigatórios!";
    } else {
        // insere no banco
        $sql = "INSERT INTO filmes (descricao, ano, genero) VALUES ('$nome', $ano, $genero_id)";
        if ($conexao->query($sql)) {
            $_SESSION['mensagem'] = "Filme cadastrado com sucesso!";
        } else {
            $_SESSION['erro'] = "Erro ao cadastrar filme.";
        }
    }
    header("Location: cadastrar_filme.php");
    exit();
}

// pega filtro se tiver
$filtro_genero = isset($_GET['filtro_genero']) ? intval($_GET['filtro_genero']) : 0;

// pega generos do banco
$generos = [];
$sql_generos = "SELECT genero, descricao FROM generos WHERE status = 1";
$resultado = $conexao->query($sql_generos);
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $generos[] = $row;
    }
}

// pega filmes
$filmes = [];
if ($filtro_genero > 0) {
    $sql_filmes = "SELECT f.filme, f.descricao AS nome_filme, f.ano, g.descricao AS genero 
                   FROM filmes f INNER JOIN generos g ON f.genero = g.genero WHERE f.genero = $filtro_genero";
} else {
    $sql_filmes = "SELECT f.filme, f.descricao AS nome_filme, f.ano, g.descricao AS genero 
                   FROM filmes f INNER JOIN generos g ON f.genero = g.genero";
}
$resultado = $conexao->query($sql_filmes);
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $filmes[] = $row;
    }
}

// se ta editando algum filme
$editar_filme_id = isset($_GET['editar_filme']) ? intval($_GET['editar_filme']) : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Filme</title>
    <style>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4a6fa5;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .acoes a {
            text-decoration: none;
            margin: 0 5px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .editar {
            background-color: #4CAF50;
            color: white;
        }
        .editar:hover {
            background-color: #45a049;
        }
        .excluir {
            background-color: #f44336;
            color: white;
        }
        .excluir:hover {
            background-color: #da190b;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="principal.php" style="display:inline-block; margin-bottom:15px;">&laquo; Voltar para Principal</a>
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
                            <option value="<?= $genero['genero'] ?>">
                                <?= htmlspecialchars($genero['descricao']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Cadastrar Filme</button>
            </form>
        </div>

        <div class="card">
            <h2>Filmes Cadastrados</h2>
            <?php if (!empty($filmes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Filme</th>
                            <th>Ano</th>
                            <th>Gênero</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filmes as $filme): ?>
                            <tr>
                                <?php if ($editar_filme_id === intval($filme['filme'])): ?>
                                    <form method="post" style="display:contents;">
                                        <td>
                                            <input type="text" name="novo_nome" value="<?= htmlspecialchars($filme['nome_filme']) ?>" required style="width:95%;">
                                        </td>
                                        <td>
                                            <input type="text" name="novo_ano" value="<?= htmlspecialchars($filme['ano']) ?>" required style="width:95%;">
                                        </td>
                                        <td>
                                            <select name="novo_genero" required>
                                                <option value="">Selecione</option>
                                                <?php foreach ($generos as $genero): ?>
                                                    <option value="<?= $genero['genero'] ?>" <?= ($genero['descricao'] == $filme['genero']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($genero['descricao']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="acoes">
                                            <input type="hidden" name="filme_id" value="<?= $filme['filme'] ?>">
                                            <button type="submit" name="salvar_edicao_filme" style="padding: 5px 10px;">Salvar</button>
                                            <a href="cadastrar_filme.php<?= isset($_GET['filtro_genero']) ? '?filtro_genero=' . intval($_GET['filtro_genero']) : '' ?>" style="margin-left:5px;">Cancelar</a>
                                        </td>
                                    </form>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($filme['nome_filme']) ?></td>
                                    <td><?= htmlspecialchars($filme['ano']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($filme['genero']) ?>
                                    </td>
                                    <td class="acoes">
                                        <a href="cadastrar_filme.php?editar_filme=<?= $filme['filme'] ?><?= isset($_GET['filtro_genero']) ? '&filtro_genero=' . intval($_GET['filtro_genero']) : '' ?>" class="editar">Editar</a>
                                        <a href="cadastrar_filme.php?excluir_filme=<?= $filme['filme'] ?>" 
                                           class="excluir" 
                                           onclick="return confirm('Tem certeza que deseja excluir este filme?')">Excluir</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum filme cadastrado ainda.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>