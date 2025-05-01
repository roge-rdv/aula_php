<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}

// Processa exclusão de usuário
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Usuário excluído com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao excluir usuário.";
    }
    header("Location: cadastrar_usuario.php");
    exit();
}

// Processa o cadastro de novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cpf = preg_replace("/[^0-9]/", "", $_POST['cpf']); // Remove formatação
    $senha = trim($_POST['senha']);

    if (empty($nome) || empty($cpf) || empty($senha)) {
        $_SESSION['erro'] = "Preencha todos os campos!";
    } else {
        // Verifica se CPF já existe
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $stmt->bind_param("s", $cpf);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['erro'] = "CPF já cadastrado!";
        } else {
            // Cadastra novo usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("INSERT INTO usuarios (nome, cpf, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $cpf, $senha_hash);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Usuário cadastrado com sucesso!";
            } else {
                $_SESSION['erro'] = "Erro ao cadastrar: " . $conexao->error;
            }
        }
    }
    header("Location: cadastrar_usuario.php");
    exit();
}

// Busca todos os usuários cadastrados
$stmt = $conexao->prepare("SELECT id, nome, cpf FROM usuarios");
$stmt->execute();
$resultado = $stmt->get_result();
$usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4a6fa5;
            color: white;
        }
        .acoes a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 5px;
        }
        .editar {
            background-color: #4CAF50;
            color: white;
        }
        .excluir {
            background-color: #f44336;
            color: white;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
        button {
            background-color: #4a6fa5;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Cadastrar Novo Usuário</h2>
            
            <!-- Mensagens de feedback -->
            <?php if (isset($_SESSION['mensagem'])): ?>
                <div style="color: green; margin: 10px 0;"><?php echo $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['erro'])): ?>
                <div style="color: red; margin: 10px 0;"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
            <?php endif; ?>

            <!-- Formulário de cadastro -->
            <form method="POST">
                <div class="form-group">
                    <label>Nome:</label>
                    <input type="text" name="nome" required>
                </div>
                <div class="form-group">
                    <label>CPF:</label>
                    <input type="text" name="cpf" placeholder="000.000.000-00" required>
                </div>
                <div class="form-group">
                    <label>Senha:</label>
                    <input type="password" name="senha" required>
                </div>
                <button type="submit">Cadastrar</button>
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
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo formatarCPF($usuario['cpf']); ?></td>
                            <td class="acoes">
                                <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="editar">Editar</a>
                                <a href="cadastrar_usuario.php?excluir=<?php echo $usuario['id']; ?>" class="excluir" onclick="return confirm('Tem certeza?')">Excluir</a>
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
    return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cpf);
}
?>
