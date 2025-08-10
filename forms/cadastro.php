<?php
    include_once ("usuarioBanco.php");
    $cpf = $_POST['cpf'];
    $nome = $_POST['nome'];
    $nascimento = $_POST['nascimento'];
    $cep = $_POST['cep'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    if(cadastro($cpf, $nome, $nascimento, $cep, $email, $senha)){
        header('Location: ../index.html');
    }else{
        header('Location:../erro.html');
    }  
?>