[file name]: cadastrar_usuario.php
[file content begin]
<?php
session_start();
require 'conexao.php';
require 'validacoes.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['excluir_cpf'])) {
    $cpf_para_excluir = $_GET['excluir_cpf'];
    $cpf_limpo_excluir = preg_replace("/[^0-9]/", "", $cpf_para_excluir);

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

$usuarios_cadastrados = [];
$sql_usuarios = "SELECT cpf, nome FROM usuarios ORDER BY nome";
$resultado_usuarios = $conexao->query($sql_usuarios);

if ($resultado_usuarios) {
    while ($linha = $resultado_usuarios->fetch_assoc()) {
        $usuarios_cadastrados[] = $linha;
    }
}

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
        <form method="POST" action="salvar_cadastro.php" onsubmit="return validarFormulario()"> <!-- ATUALIZADO -->
            <div class="form-group">
                <label for="nome">Nome (max 25 caracteres):</label>
                <input type="text" id="nome" name="nome" maxlength="25">
            </div>
            <div class="form-group">
                <label for="cpf">CPF (só números, 11 dígitos):</label>
                <input type="text" id="cpf" name="cpf" maxlength="11">
            </div>
            <div class="form-group">
                <label for="senha">Senha (mínimo 6 caracteres, 1 maiúscula, 1 minúscula, 1 número, 1 especial):</label> <!-- ATUALIZADO -->
                <input type="password" id="senha" name="senha" maxlength="25">
            </div>
            <button type="submit">Cadastrar Usuário</button>
        </form>

        <script> // ADICIONADO
        function validarFormulario() {
            const cpf = document.getElementById('cpf').value;
            const senha = document.getElementById('senha').value;
            
            if (!validarCPF(cpf)) {
                alert('CPF inválido! Por favor, digite um CPF válido.');
                return false;
            }
            
            if (!validarSenha(senha)) {
                alert('Senha inválida! Deve conter pelo menos:\n- 6 caracteres\n- 1 letra maiúscula\n- 1 letra minúscula\n- 1 número\n- 1 caractere especial');
                return false;
            }
            
            return true;
        }

        function validarCPF(cpf) {
            // Implementação JavaScript similar à do PHP
            cpf = cpf.replace(/\D/g, '');
            
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }
            
            // Cálculo do primeiro DV
            let soma = 0;
            for (let i = 0; i < 9; i++) {
                soma += parseInt(cpf[i]) * (10 - i);
            }
            let resto = soma % 11;
            const dv1 = resto < 2 ? 0 : 11 - resto;
            
            if (parseInt(cpf[9]) !== dv1) {
                return false;
            }
            
            // Cálculo do segundo DV
            soma = 0;
            for (let i = 0; i < 10; i++) {
                soma += parseInt(cpf[i]) * (11 - i);
            }
            resto = soma % 11;
            const dv2 = resto < 2 ? 0 : 11 - resto;
            
            return parseInt(cpf[10]) === dv2;
        }

        function validarSenha(senha) {
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/.test(senha);
        }
        </script>

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
[file content end];