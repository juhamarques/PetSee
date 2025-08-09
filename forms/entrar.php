<?php
    include_once ("usuarioBanco.php");
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $achouUsuario = login($email, $senha);
    if($achouUsuario == false){
        echo("Erro ao entrar. Verifique os campos.");
    }else{
        header('Location:../index.html');
    }
?>