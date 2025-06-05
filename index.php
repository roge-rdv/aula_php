[file name]: index.php
[file content begin]
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 400px; margin: 50px auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="login.php" onsubmit="return validarLogin()">
            <div class="form-group">
                <label>CPF:</label>
                <input type="text" name="cpf" placeholder="Somente números">
            </div>
            <div class="form-group">
                <label>Senha:</label>
                <input type="password" name="senha">
            </div>
            <button type="submit">Entrar</button>
        </form>

        <script>
        function validarLogin() {
            const cpf = document.querySelector('input[name="cpf"]').value;
            const cpfLimpo = cpf.replace(/\D/g, '');
            
            if (!validarCPF(cpfLimpo)) {
                alert('CPF inválido!');
                return false;
            }
            return true;
        }

        function validarCPF(cpf) {
            // Mesma implementação JavaScript do cadastrar_usuario.php
            // ... (código idêntico ao do outro arquivo) ...
        }
        </script>
    </div>
</body>
</html>
[file content end]