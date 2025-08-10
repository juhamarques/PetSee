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
    
    function cadastroPessoal($cpf, $nome, $nascimento, $cep, $email, $senha, $tipo_usuario){
         $conn = abrirConexao();
         if ($conn->connect_error){
            return false;
         }else{
            $stmt = $conn->prepare("INSERT INTO Usuario(cpf, nome, nascimento, CEP, email, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt -> bind_param("sssssss", $cpf, $nome, $nascimento, $cep, $email, $senha, $tipo_usuario);

            if ($stmt -> execute()){
                fecharConexao($conn);
                return true;
            }else{
                fecharConexao($conn);
                return false;
            }
         }
    }

    function cadastroComercial($nome_empresa, $email_empresa, $cnpj, $telefone, $endereco_cep, $senha, $tipo_usuario){
        $conn = abrirConexao();
        if ($conn -> connect_error) {
            return false;
        } else {
            $stmt_usuario = $conn-> prepare("INSERT INTO Usuario(email, senha, tipo) VALUES (?, ?, ?)");
            $stmt_usuario -> bind_param("sss", $email_empresa, $senha, $tipo_usuario);
            $stmt_usuario -> execute();
            $idUsuario = $conn -> insert_id;
            
            if ($idUsuario) {
                $stmt_comercial = $conn -> prepare("INSERT INTO Comercial(idUsuario, nome_empresa, email_empresa, cnpj, telefone, endereco_cep, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_comercial -> bind_param("isssssss", $idUsuario, $nome_empresa, $email_empresa, $cnpj, $telefone, $endereco_cep, $senha, $tipo_usuario);

                if ($stmt_comercial -> execute()) {
                    fecharConexao($conn);
                    return true;
                } else {
                    fecharConexao($conn);
                    return false;
                }
            } else {
                fecharConexao($conn);
                return false;
            }
        }
    }
?>