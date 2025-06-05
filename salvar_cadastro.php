[file name]: cadastrar_usuario.php
[file content begin]
<?php
session_start();
require 'conexao.php';
require 'validacoes.php'; // ADICIONADO

// ... código existente ...

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