<?php
session_start();
require 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $senha = trim($_POST['senha']);

    // Validação básica
    if (empty($nome) || empty($cpf) || empty($senha)) {
        $_SESSION['erro'] = "Preencha todos os campos!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    // Verifica se o CPF já existe
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['erro'] = "CPF já cadastrado!";
        header("Location: cadastrar_usuario.php");
        exit();
    }

    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere no banco
    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, cpf, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $cpf, $senha_hash);

    if ($stmt->execute()) {
        $_SESSION['sucesso'] = "Usuário cadastrado com sucesso!";
        header("Location: principal.php");
    } else {
        $_SESSION['erro'] = "Erro ao cadastrar: " . $conexao->error;
        header("Location: cadastrar_usuario.php");
    }
    exit();
}