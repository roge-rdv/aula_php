<?php
// Inclui o arquivo de conexão com o banco de dados
require 'conexao.php';

echo "<h1>Atualizador de Senhas</h1>";

// Seleciona todos os usuários para verificar suas senhas
$sql_select = "SELECT cpf, senha FROM usuarios";
$resultado = $conexao->query($sql_select);

if ($resultado && $resultado->num_rows > 0) {
    echo "<p>Verificando " . $resultado->num_rows . " usuários...</p>";

    while ($usuario = $resultado->fetch_assoc()) {
        $cpf = $usuario['cpf'];
        $senha_atual = $usuario['senha'];

        // A função password_get_info retorna 'algo' como 0 se não for um hash válido
        // Isso nos ajuda a identificar senhas em texto plano
        if (password_get_info($senha_atual)['algo'] === 0) {
            echo "Processando CPF: $cpf... ";
            
            // Cria o hash da senha em texto plano
            $nova_senha_hash = password_hash($senha_atual, PASSWORD_DEFAULT);

            // Prepara o update para o banco de dados para evitar SQL Injection
            $sql_update = "UPDATE usuarios SET senha = ? WHERE cpf = ?";
            $stmt = $conexao->prepare($sql_update);
            $stmt->bind_param("ss", $nova_senha_hash, $cpf);
            
            // Executa a atualização
            if ($stmt->execute()) {
                echo "<strong style='color:green;'>SENHA ATUALIZADA COM SUCESSO!</strong><br>";
            } else {
                echo "<strong style='color:red;'>FALHA AO ATUALIZAR!</strong><br>";
            }
            $stmt->close();
        } else {
            // Se a senha já estiver no formato de hash, apenas informa
            echo "Processando CPF: $cpf... <span style='color:blue;'>Senha já está no formato seguro.</span><br>";
        }
    }
    echo "<h2>Processo Concluído!</h2>";
    echo "<p style='font-weight:bold; color:red;'>LEMBRE-SE DE APAGAR ESTE ARQUIVO (atualizar_senhas.php) DO SERVIDOR AGORA!</p>";

} else {
    echo "<p>Nenhum usuário encontrado.</p>";
}

$conexao->close();
?>
