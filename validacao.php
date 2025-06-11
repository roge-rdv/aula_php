
<?php

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
  
    if (strlen($cpf) != 11) {
        return false;
    }

    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    if ($cpf[9] != $dv1) {
        return false;
    }

    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    if ($cpf[10] != $dv2) {
        return false;
    }

    return true;
}

function validarSenha($senha) {
    if (strlen($senha) < 6) {
        return false;
    }

    if (!preg_match('/[A-Z]/', $senha)) {
        return false;
    }

    if (!preg_match('/[a-z]/', $senha)) {
        return false;
    }
    
    if (!preg_match('/[0-9]/', $senha)) {
        return false;
    }

    if (!preg_match('/[^A-Za-z0-9]/', $senha)) {
        return false;
    }

    return true;
}
?>