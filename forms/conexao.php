<?php
    function abrirConexao(){
        return new mysqli("localhost","root","","PetSee");
    }
    function fecharConexao($conn){
         $conn -> close();
    }
?>