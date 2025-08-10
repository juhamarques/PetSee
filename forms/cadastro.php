<?php
    include_once ("usuarioBanco.php");
    $tipo_cadastro = $_POST['tipo_cadastro'];

    if ($tipo_cadastro == 'pessoal') {
        $cpf = $_POST['cpf'];
        $nome = $_POST['nome'];
        $nascimento = $_POST['nascimento'];
        $cep = $_POST['cep'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if (cadastroPessoal($cpf, $nome, $nascimento, $cep, $email, $senha, $tipo_cadastro)) {
            header('Location: ../entrar.html');
        } else {
            header('Location: ../erro.html');
        }
    } elseif ($tipo_cadastro == 'comercial') {
        $nome_empresa = $_POST['nome_empresa'];
        $email_comercial= $_POST['email_comercial'];
        $tel = $_POST['tel'];
        $cnpj = $_POST['cnpj'];
        $cep_comercial = $_POST['cep_comercial'];
        $senha = $_POST['senha_comercial'];

        if (cadastroComercial($nome_empresa, $email_comercial, $tel, $cnpj, $cep_comercial, $senha, $tipo_cadastro)) {
            header('Location: ../entrar.html');
        } else {
            header('Location: ../erro.html');
        }
    } else {
        header('Location: ../erro.html');
    }
?>