<?php
session_start();
require 'conexao.php'; 
require 'validacao.php';

// checa se veio por post msm
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cpf_form = $_POST['cpf'];
    $senha_form = $_POST['senha'];

    // valida se preencheu td
    if (empty($nome) || empty($cpf_form) || empty($senha_form)) {
        $_SESSION['erro_cadastro'] = "Todos os campos são obrigatórios!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    // valida senha forte
    if (!validarSenha($senha_form)) {
        $_SESSION['erro_cadastro'] = "A senha deve ter no mínimo 6 caracteres, com pelo menos 1 maiúscula, 1 minúscula, 1 número e 1 caractere especial!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    // limpa cpf
    $cpf_limpo = preg_replace("/[^0-9]/", "", $cpf_form);

    // checa tamanho do cpf
    if (strlen($cpf_limpo) != 11) {
        $_SESSION['erro_cadastro'] = "CPF deve ter exatamente 11 dígitos!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    // checa se ja existe cpf
    $sql_check = "SELECT cpf FROM usuarios WHERE cpf = '$cpf_limpo'";
    $resultado = $conexao->query($sql_check);

    if ($resultado && $resultado->num_rows > 0) {
        $_SESSION['erro_cadastro'] = "Este CPF já foi cadastrado!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    // insere usuario novo
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
    // se nao veio por post, volta
    header("Location: cadastrar_usuario.php");
    exit();
}
?>