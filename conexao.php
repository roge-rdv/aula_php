<?php
$host = 'localhost';
$usuario_db = 'root';
$senha_db = '';
$banco = 'login_aula';

$conexao = new mysqli($host, $usuario_db, $senha_db, $banco );

if ($conexao -> connect_error) {
    die("Erro de conexão: " . $conexao -> connect_error);
}
?>