<?php
session_start();
require 'conexao.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf_form = $_POST['cpf'];
    $senha_form = $_POST['senha'];

    if (empty($nome) || empty($cpf_form) || empty($senha_form)) {
        $_SESSION['erro_cadastro'] = "Todos os campos são obrigatórios!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    $cpf_limpo = preg_replace("/[^0-9]/", "", $cpf_form);

    if (strlen($cpf_limpo) != 11) {
        $_SESSION['erro_cadastro'] = "CPF inválido! Deve conter 11 números.";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    if (strlen($nome) > 25) {
        $_SESSION['erro_cadastro'] = "O nome deve ter no máximo 25 caracteres.";
        header("Location: cadastrar_usuario.php");
        exit();
    }
    if (strlen($senha_form) > 25) {
        $_SESSION['erro_cadastro'] = "A senha deve ter no máximo 25 caracteres.";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    $stmt_check = $conexao->prepare("SELECT cpf FROM usuarios WHERE cpf = ?");
    $stmt_check->bind_param("s", $cpf_limpo);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $_SESSION['erro_cadastro'] = "Este CPF já foi cadastrado!";
        $stmt_check->close();
        header("Location: cadastrar_usuario.php");
        exit();
    }
    $stmt_check->close();

    $stmt_insert = $conexao->prepare("INSERT INTO usuarios (cpf, nome, senha) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $cpf_limpo, $nome, $senha_form);

    if ($stmt_insert->execute()) {
        $_SESSION['sucesso_cadastro'] = "Usuário cadastrado com sucesso! Faça o login.";
        header("Location: cadastrar_usuario.php"); 
        exit();
    } else {
        $_SESSION['erro_cadastro'] = "Erro ao cadastrar usuário: " . $stmt_insert->error;
        header("Location: cadastrar_usuario.php");
        exit();
    }
    $stmt_insert->close();
    $conexao->close();
} else {
    header("Location: cadastrar_usuario.php");
    exit();
}
?>