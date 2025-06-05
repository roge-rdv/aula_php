[file name]: login.php
[file content begin]
<?php
session_start();
require 'conexao.php';
require 'validacoes.php'; // ADICIONADO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf_form = isset($_POST['cpf']) ? $_POST['cpf'] : '';
    $senha_form = isset($_POST['senha']) ? $_POST['senha'] : '';

    if (empty($cpf_form) || empty($senha_form)) {
        die("CPF e Senha são obrigatórios! <a href='index.php'>Voltar</a>");
    }

    $cpf_limpo_form = preg_replace("/[^0-9]/", "", $cpf_form);

    // VALIDAÇÃO CPF ANTES DE CONSULTAR BANCO
    if (!validarCPF($cpf_limpo_form)) {
        die("CPF inválido! <a href='index.php'>Voltar</a>");
    }

    $sql = "SELECT cpf, nome, senha FROM usuarios WHERE cpf = '$cpf_limpo_form'";
    $resultado = $conexao->query($sql);

    if ($resultado && $resultado->num_rows == 1) {
        $usuario_db = $resultado->fetch_assoc();

        if ($senha_form === $usuario_db['senha']) {
            $_SESSION['usuario_cpf'] = $usuario_db['cpf']; 
            $_SESSION['nome'] = $usuario_db['nome'];
            $_SESSION['logado'] = true;
            
            header("Location: principal.php");
            exit();
        } else {
            echo "<h2>Login Falhou!</h2><p>Senha incorreta.</p><a href='index.php'>Tentar novamente</a>";
        }
    } else {
        echo "<h2>Login Falhou!</h2><p>Usuário com CPF informado não encontrado.</p><a href='index.php'>Tentar novamente</a>";
    }
    $conexao->close();
} else {
    header("Location: index.php");
    exit();
}
?>