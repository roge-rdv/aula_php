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

    $sql_check = "SELECT cpf FROM usuarios WHERE cpf = '$cpf_limpo'";
    $resultado = $conexao->query($sql_check);

    if ($resultado && $resultado->num_rows > 0) {
        $_SESSION['erro_cadastro'] = "Este CPF já foi cadastrado!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    $sql_insert = "INSERT INTO usuarios (cpf, nome, senha) VALUES ('$cpf_limpo', '$nome', '$senha_form')";
    if ($conexao->query($sql_insert)) {
        $_SESSION['sucesso_cadastro'] = "Usuário cadastrado com sucesso! Faça o login.";
        header("Location: cadastrar_usuario.php"); 
        exit();
    } else {
        $_SESSION['erro_cadastro'] = "Erro ao cadastrar usuário.";
        header("Location: cadastrar_usuario.php");
        exit();
    }
} else {
    header("Location: cadastrar_usuario.php");
    exit();
}
?>