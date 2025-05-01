<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}

// Processa a exclusão de usuário
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']); // Sanitiza o ID

    try {
        $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário excluído com sucesso!";
        } else {
            $_SESSION['erro'] = "Erro ao excluir usuário.";
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
    }
    header("Location: cadastrar_usuario.php");
    exit();
}

// Processa o cadastro de novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cpf = preg_replace("/[^0-9]/", "", $_POST['cpf']); // Remove formatação
    $senha = trim($_POST['senha']);

    // Validação básica
    if (empty($nome) || empty($cpf) || empty($senha)) {
        $_SESSION['erro'] = "Todos os campos são obrigatórios!";
    } elseif (strlen($cpf) != 11) {
        $_SESSION['erro'] = "CPF inválido! Deve conter 11 dígitos.";
    } else {
        try {
            // Verifica se o CPF já existe
            $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE cpf = ?");
            $stmt->bind_param("s", $cpf);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $_SESSION['erro'] = "CPF já cadastrado!";
            } else {
                // Hash da senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Insere no banco
                $stmt = $conexao->prepare("INSERT INTO usuarios (nome, cpf, senha) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nome, $cpf, $senha_hash);
                
                if ($stmt->execute()) {
                    $_SESSION['mensagem'] = "Usuário cadastrado com sucesso!";
                } else {
                    $_SESSION['erro'] = "Erro ao cadastrar usuário.";
                }
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
        }
    }
    header("Location: cadastrar_usuario.php");
    exit();
}

// Busca todos os usuários
$usuarios = [];
try {
    $stmt = $conexao->prepare("SELECT id, nome, cpf FROM usuarios");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    $_SESSION['erro'] = "Erro ao carregar usuários: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="password"] {
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
            <h2>Cadastrar Novo Usuário</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nome Completo:</label>
                    <input type="text" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label>CPF (apenas números):</label>
                    <input type="text" name="cpf" pattern="\d{11}" title="11 dígitos numéricos" required>
                </div>
                
                <div class="form-group">
                    <label>Senha:</label>
                    <input type="password" name="senha" minlength="6" required>
                </div>
                
                <button type="submit">Cadastrar Usuário</button>
            </form>
        </div>

        <!-- Listagem de usuários -->
        <div class="card">
            <h2>Usuários Cadastrados</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nome']) ?></td>
                            <td><?= formatarCPF($usuario['cpf']) ?></td>
                            <td class="acoes">
                                <a href="alterar_usuario.php?id=<?= $usuario['id'] ?>" class="editar">Editar</a>
                                <a href="cadastrar_usuario.php?excluir=<?= $usuario['id'] ?>" 
                                   class="excluir" 
                                   onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
// Função para formatar CPF
function formatarCPF($cpf) {
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}
?>
