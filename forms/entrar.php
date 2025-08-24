<?php
    session_start();
    include_once ("usuarioBanco.php");
    
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $idUsuario = login($email, $senha);
    
    if ($idUsuario != false){
        $_SESSION['idUsuario'] = $idUsuario;
        header('Location:../index.php');
        exit();
    } else {
        header('Location:../erro.html');
        exit(); 
    }
?>