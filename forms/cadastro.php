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

        if (cadastroPessoal($cpf, $nome, $nascimento, $cep, $email, $senha, $tipo_cadastro, 1)) {
            header('Location: ../entrar.html');
        } else {
            header('Location: ../erro.html');
        }
    } elseif ($tipo_cadastro == 'comercial') {
        $nome_empresa = $_POST['nome_empresa'];
        $email_comercial= $_POST['email_comercial'];
        $tel = $_POST['tel'];
        $cnpj = $_POST['cnpj'];
        $senha = $_POST['senha_comercial'];
        $cep_comercial = $_POST['cep_comercial'];
        $logradouro = $_POST['logradouro'];
        $nome_rua = $_POST['nome_rua'];
        $numero = $_POST['numero'];
        $idBairro = $_POST['idBairro'];

        $idEndereco = cadastroEndereco($idBairro, $logradouro, $nome_rua, $numero, $cep_comercial);

        if ($idEndereco){
            if (cadastroComercial($nome_empresa, $email_comercial, $cnpj, $tel, $idEndereco, $senha, 1)) {
                header('Location: ../entrar.html');
            } else {
                header('Location: ../erro.html');
            }
        } else {
            header('Location: ../erro.html');
        }
    } else {
        header('Location: ../erro.html');
    }
?>