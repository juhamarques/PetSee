<?php
    include_once ("conexao.php");

    function login($email, $senha){
        $sql = "select IdUsuario from Usuario where email = ? AND senha = ?"; 
        $conn = abrirConexao();

        if ($conn -> connect_error){
            return false;
        }else{
            $stmt = $conn -> prepare($sql);
            $stmt -> bind_param("ss", $email, $senha);
            $stmt -> execute();
            
            $result = $stmt -> get_result();
            
            if ($row = $result -> fetch_assoc()) {
                $userId = $row['IdUsuario'];
            }else{
                $userId = false;
            }
            fecharConexao($conn);
            return $userId;
        }
    }
    
    function cadastro($cpf, $nome, $nascimento, $cep, $email, $senha){
         $conn = abrirConexao();
         if ($conn->connect_error){
            // Apenas retorna false em caso de erro.
            return false;
         }else{
            echo("Conexão feita com sucesso");
            $stmt = $conn->prepare("INSERT INTO Usuario(cpf, nome, nascimento, CEP, email, senha) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt -> bind_param("ssssss", $cpf, $nome, $nascimento, $cep, $email, $senha);

            if ($stmt -> execute()){
                fecharConexao($conn);
                return true;
            }else{
                fecharConexao($conn);
                return false;
            }
         }
    }
?>