<?php
    include_once ("conexao.php");

    function login($email, $senha){
        $sql = "select * from Usuario where email = ? AND senha = ?";
        $conn = abrirConexao();

        if ($conn -> connect_error){
            echo("Erro ao abrir conexão");
            return false;
        }else{
            $stmt = $conn -> prepare($sql);
            $stmt -> bind_param("ss", $email, $senha);
            $stmt -> execute();

            $result = $stmt -> get_result();
            $achouUsuario = false;
            $nome = "";
                while ($row = $result -> fetch_assoc()) {
                    echo "Email: " . $row['email'] . "<br>";
                    echo "Senha: " . $row['senha'] . "<br>";
                    echo "<hr>";
                    $achouUsuario = true;
                }
                fecharConexao($conn);
                return $achouUsuario;
        }
    }

    function cadastro($cpf, $nome, $nascimento, $cep, $email, $senha){
         $conn = abrirConexao();
         if ($conn->connect_error){
            echo("Erro ao abrir conexão");
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