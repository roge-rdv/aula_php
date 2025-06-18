<?php
session_start();
require 'conexao.php';
require 'validacao.php';

// checa login
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}

// editar usuario
if (isset($_POST['editar_usuario'])) {
    $cpf_original = $_POST['cpf_original'];
    $nome_novo = trim($_POST['nome_edit']);
    $cpf_novo = preg_replace("/[^0-9]/", "", $_POST['cpf_edit']);
    $senha_nova = $_POST['senha_edit'];

    // valida campos
    if (empty($nome_novo) || empty($cpf_novo)) {
        $_SESSION['erro_usuario'] = "Nome e CPF são obrigatórios!";
        $_SESSION['editar_cpf'] = $cpf_original;
        header("Location: cadastrar_usuario.php?editar_cpf=$cpf_original");
        exit();
    } elseif (strlen($cpf_novo) != 11) {
        $_SESSION['erro_usuario'] = "CPF deve ter exatamente 11 dígitos!";
        $_SESSION['editar_cpf'] = $cpf_original;
        header("Location: cadastrar_usuario.php?editar_cpf=$cpf_original");
        exit();
    } elseif (!empty($senha_nova) && !validarSenha($senha_nova)) {
        // senha tem q ser forte
        $_SESSION['erro_usuario'] = "A senha deve ter no mínimo 6 caracteres, com pelo menos 1 maiúscula, 1 minúscula, 1 número e 1 caractere especial!";
        $_SESSION['editar_cpf'] = $cpf_original;
        header("Location: cadastrar_usuario.php?editar_cpf=$cpf_original");
        exit();
    } else {
        // se mudou o cpf, checa se ja existe
        if ($cpf_novo != $cpf_original) {
            $sql_check = "SELECT cpf FROM usuarios WHERE cpf = '$cpf_novo'";
            $result_check = $conexao->query($sql_check);
            if ($result_check && $result_check->num_rows > 0) {
                $_SESSION['erro_usuario'] = "CPF já existe no sistema!";
                $_SESSION['editar_cpf'] = $cpf_original;
                header("Location: cadastrar_usuario.php?editar_cpf=$cpf_original");
                exit();
            }
        }

        // faz update
        if (!isset($_SESSION['erro_usuario'])) {
            if (!empty($senha_nova)) {
                $sql_update = "UPDATE usuarios SET nome = '$nome_novo', cpf = '$cpf_novo', senha = '$senha_nova' WHERE cpf = '$cpf_original'";
            } else {
                $sql_update = "UPDATE usuarios SET nome = '$nome_novo', cpf = '$cpf_novo' WHERE cpf = '$cpf_original'";
            }

            if ($conexao->query($sql_update)) {
                $_SESSION['mensagem_usuario'] = "Usuário atualizado com sucesso!";
            } else {
                $_SESSION['erro_usuario'] = "Erro ao atualizar usuário.";
                $_SESSION['editar_cpf'] = $cpf_original;
                header("Location: cadastrar_usuario.php?editar_cpf=$cpf_original");
                exit();
            }
        }
    }
    header("Location: cadastrar_usuario.php");
    exit();
}

// excluir usuario
if (isset($_GET['excluir_cpf'])) {
    $cpf_para_excluir = $_GET['excluir_cpf'];
    $cpf_limpo_excluir = preg_replace("/[^0-9]/", "", $cpf_para_excluir);

    // n deixa excluir a si msm
    if (isset($_SESSION['usuario_cpf']) && $_SESSION['usuario_cpf'] == $cpf_limpo_excluir) {
        $_SESSION['erro_usuario'] = "Você não pode se auto-excluir!";
    } else {
        $sql = "DELETE FROM usuarios WHERE cpf = '$cpf_limpo_excluir'";
        if ($conexao->query($sql)) {
            $_SESSION['mensagem_usuario'] = "Usuário excluído com sucesso!";
        } else {
            $_SESSION['erro_usuario'] = "Erro ao excluir usuário.";
        }
    }
    header("Location: cadastrar_usuario.php");
    exit();
}

// busca usuario pra editar
$usuario_editando = null;
$cpf_editar = null;
if (isset($_GET['editar_cpf'])) {
    $cpf_editar = $_GET['editar_cpf'];
} elseif (isset($_SESSION['editar_cpf'])) {
    $cpf_editar = $_SESSION['editar_cpf'];
    unset($_SESSION['editar_cpf']);
}
if ($cpf_editar) {
    $cpf_limpo_editar = preg_replace("/[^0-9]/", "", $cpf_editar);
    $sql_editar = "SELECT cpf, nome FROM usuarios WHERE cpf = '$cpf_limpo_editar'";
    $resultado_editar = $conexao->query($sql_editar);
    if ($resultado_editar && $resultado_editar->num_rows > 0) {
        $usuario_editando = $resultado_editar->fetch_assoc();
    }
}

// pega todos usuarios
$usuarios_cadastrados = [];
$sql_usuarios = "SELECT cpf, nome FROM usuarios ORDER BY nome";
$resultado_usuarios = $conexao->query($sql_usuarios);

if ($resultado_usuarios) {
    while ($linha = $resultado_usuarios->fetch_assoc()) {
        $usuarios_cadastrados[] = $linha;
    }
}

// msgs de erro/sucesso
if(isset($_SESSION['erro_cadastro'])) {
    $_SESSION['erro_usuario'] = $_SESSION['erro_cadastro'];
    unset($_SESSION['erro_cadastro']);
}
if(isset($_SESSION['sucesso_cadastro'])) {
     $_SESSION['mensagem_usuario'] = $_SESSION['sucesso_cadastro'];
     unset($_SESSION['sucesso_cadastro']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="style.css"> <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f0f0f0; }
        .container { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 10px; background-color: #ffe0e0; }
        .success { color: green; border: 1px solid green; padding: 10px; margin-bottom: 10px; background-color: #e0ffe0;}
        table { width: 100%; border-collapse: collapse; margin-top:15px;}
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .acoes a { margin-right: 5px; text-decoration: none; padding: 5px 8px; border-radius:3px; }
        .excluir { background-color: #ffe0e0; color: darkred; }
        .editar { background-color: #e0f0ff; color: darkblue; }
        .cancelar { background-color: #f0f0f0; color: #333; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; margin-bottom: 3px; }
        .form-group input { width: 95%; padding: 8px; border: 1px solid #ccc; border-radius: 3px; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        h2 { border-bottom: 1px solid #eee; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="principal.php" style="display:inline-block; margin-bottom:15px;">&laquo; Voltar para Principal</a>

        <?php if ($usuario_editando): ?>
        <div class="card">
            <h2>Editar Usuário</h2>
            <?php
            if (isset($_SESSION['erro_usuario'])) {
                echo '<div class="error">' . htmlspecialchars($_SESSION['erro_usuario']) . '</div>';
                unset($_SESSION['erro_usuario']);
            }
            ?>
            <form method="POST" action="cadastrar_usuario.php">
                <input type="hidden" name="cpf_original" value="<?= htmlspecialchars($usuario_editando['cpf']) ?>">
                <div class="form-group">
                    <label for="nome_edit">Nome (max 25 caracteres):</label>
                    <input type="text" id="nome_edit" name="nome_edit" maxlength="25" value="<?= htmlspecialchars($usuario_editando['nome']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="cpf_edit">CPF (só números, 11 dígitos):</label>
                    <input type="text" id="cpf_edit" name="cpf_edit" maxlength="11" value="<?= htmlspecialchars($usuario_editando['cpf']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="senha_edit">Nova Senha (deixe vazio para manter a atual):</label>
                    <input type="password" id="senha_edit" name="senha_edit" maxlength="25">
                </div>
                <button type="submit" name="editar_usuario">Salvar Alterações</button>
                <a href="cadastrar_usuario.php" class="cancelar" style="display:inline-block; padding:10px 15px; margin-left:10px; text-decoration:none; border-radius:3px;">Cancelar</a>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <h2>Cadastrar Novo Usuário</h2>
            <?php
            if (isset($_SESSION['erro_usuario'])) {
                echo '<div class="error">' . htmlspecialchars($_SESSION['erro_usuario']) . '</div>';
                unset($_SESSION['erro_usuario']);
            }
            if (isset($_SESSION['mensagem_usuario'])) {
                echo '<div class="success">' . htmlspecialchars($_SESSION['mensagem_usuario']) . '</div>';
                unset($_SESSION['mensagem_usuario']);
            }
            ?>
            <form method="POST" action="salvar_cadastro.php">
                <div class="form-group">
                    <label for="nome">Nome (max 25 caracteres):</label>
                    <input type="text" id="nome" name="nome" maxlength="25" required>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF (só números, 11 dígitos):</label>
                    <input type="text" id="cpf" name="cpf" maxlength="11" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha (max 25 caracteres):</label>
                    <input type="password" id="senha" name="senha" maxlength="25" required>
                </div>
                <button type="submit">Cadastrar Usuário</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>Usuários Cadastrados</h2>
            <?php if (count($usuarios_cadastrados) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios_cadastrados as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td><?= htmlspecialchars($usuario['cpf']) ?></td>
                                <td class="acoes">
                                    <a href="cadastrar_usuario.php?editar_cpf=<?= htmlspecialchars($usuario['cpf']) ?>"
                                       class="editar">Editar</a>
                                    <a href="cadastrar_usuario.php?excluir_cpf=<?= htmlspecialchars($usuario['cpf']) ?>"
                                       class="excluir"
                                       onclick="return confirm('Tem certeza que deseja excluir este usuário: <?= htmlspecialchars(addslashes($usuario['nome'])) ?> (CPF: <?= htmlspecialchars($usuario['cpf']) ?>)?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum usuário cadastrado.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
if (isset($conexao)) { 
    $conexao->close();
}
?>