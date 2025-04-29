<?php
session_start();
require 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = isset($_POST['cpf']) ? $_POST['cpf'] : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    if (empty($cpf) || empty($senha)) {
        die("Preencha todos os campos!");
    }

    $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario_db = $resultado->fetch_assoc();

        if ($senha === $usuario_db['senha']) {
            $_SESSION['nome'] = $usuario_db['nome'];
            $_SESSION['logado'] = true;
            header("Location: principal.php");
            exit();
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
}
?>