<?php
    session_start();
    include_once ("usuarioBanco.php");
    
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $userId = login($email, $senha);
    
    if ($userId != false){
        $_SESSION['userId'] = $userId;
        header('Location:../index.html');
        exit();
    } else {
        header('Location:../erro.html');
        exit(); 
    }
?>