<?php
    include_once ("usuarioBanco.php");
    $tipo_cadastro = $_POST['tipo_cadastro'];

    if ($tipo_cadastro == 'pessoal') {
    $cpf = preg_replace('/\D/', '', $_POST['cpf']);
        $nome = $_POST['nome'];
        $nascimento = $_POST['nascimento'];
    $cep = preg_replace('/\D/', '', $_POST['cep']);
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $cpf_formatted = formatCPF($cpf);
    $cep_formatted = formatCEP($cep);

        if (!isValidCPF($cpf)) {
            header('Location: ../erro.html');
            exit;
        }

        if (!isValidEmail($email) || !checkEmailExists($email, false)) {
            header('Location: ../erro.html');
            exit;
        }

        if (!isValidCEP($cep, true, false)) {
            header('Location: ../erro.html');
            exit;
        }

        if (!checkPasswordStrength($senha)) {
            header('Location: ../erro.html');
            exit;
        }

        if (cadastroPessoal($cpf_formatted, $nome, $nascimento, $cep_formatted, $email, $senha, $tipo_cadastro, 1)) {
            header('Location: ../entrar.html');
        } else {
            header('Location: ../erro.html');
        }
    } elseif ($tipo_cadastro == 'comercial') {
    $nome_empresa = $_POST['nome_empresa'];
    $email_comercial= $_POST['email_comercial'];
    $tel = $_POST['tel'];
    $cnpj = preg_replace('/\D/', '', $_POST['cnpj']);
    $senha = $_POST['senha_comercial'];
    $cep_comercial = preg_replace('/\D/', '', $_POST['cep_comercial']);
        $logradouro = $_POST['logradouro'];
        $nome_rua = $_POST['nome_rua'];
        $numero = $_POST['numero'];
        $idBairro = $_POST['idBairro'];

    $cnpj_formatted = formatCNPJ($cnpj);
    $cep_comercial_formatted = formatCEP($cep_comercial);

    $idEndereco = cadastroEndereco($idBairro, $logradouro, $nome_rua, $numero, $cep_comercial_formatted);

        if (!isValidEmail($email_comercial) || !checkEmailExists($email_comercial, false)) {
            header('Location: ../erro.html');
            exit;
        }

        if (!isValidCEP($cep_comercial, true, false)) {
            header('Location: ../erro.html');
            exit;
        }

        if (!checkPasswordStrength($senha)) {
            header('Location: ../erro.html');
            exit;
        }

        if ($idEndereco){
            if (cadastroComercial($nome_empresa, $email_comercial, $cnpj_formatted, $tel, $idEndereco, $senha, 1)) {
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