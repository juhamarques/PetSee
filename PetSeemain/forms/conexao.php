<?php
    function abrirConexao(){
        return new mysqli("localhost","root","1234","PetSee");
    }
    function fecharConexao($conn){
         $conn -> close();
    }
?>