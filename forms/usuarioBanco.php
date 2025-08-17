<?php
    include_once ("conexao.php");

    function login($email, $senha){
        $sql = "select idUsuario from Usuario where email = ? AND senha = ?"; 
        $conn = abrirConexao();

        if ($conn -> connect_error){
            return false;
        }else{
            $stmt = $conn -> prepare($sql);
            $stmt -> bind_param("ss", $email, $senha);
            $stmt -> execute();
            
            $result = $stmt -> get_result();
            
            if ($row = $result -> fetch_assoc()) {
                $idUsuario = $row['idUsuario'];
            }else{
                $idUsuario = false;
            }
            fecharConexao($conn);
            return $idUsuario;
        }
    }
    
    function cadastroPessoal($cpf, $nome, $nascimento, $cep, $email, $senha, $tipo_usuario, $idImagem = 1){
         $conn = abrirConexao();
         if ($conn->connect_error){
            return false;
         }else{
            $stmt = $conn->prepare("INSERT INTO Usuario(idImagem, cpf, nome, nascimento, CEP, email, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt -> bind_param("isssssss", $idImagem, $cpf, $nome, $nascimento, $cep, $email, $senha, $tipo_usuario);

            if ($stmt -> execute()){
                fecharConexao($conn);
                return true;
            }else{
                fecharConexao($conn);
                return false;
            }
         }
    }

    function cadastroEndereco($idBairro, $logradouro, $nome, $numero, $cep) {
        $conn = abrirConexao();
        if ($conn->connect_error) {
            return false;
        } else {
            $stmt = $conn->prepare("INSERT INTO Endereco(idBairro, logradouro, nome, numero, CEP) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issis", $idBairro, $logradouro, $nome, $numero, $cep);
            if ($stmt->execute()) {
                $idEndereco = $conn->insert_id;
                fecharConexao($conn);
                return $idEndereco;
            } else {
                fecharConexao($conn);
                return false;
            }
        }
    }

    function cadastroComercial($nome_empresa, $email_empresa, $cnpj, $telefone, $idEndereco, $senha, $idImagem = 1){
        $conn = abrirConexao();
        if ($conn -> connect_error) {
            return false;
        } else {
            $tipo_usuario = 'comercial';
            $stmt_usuario = $conn-> prepare("INSERT INTO Usuario(idImagem, email, senha, tipo, cpf, nome, nascimento, CEP) VALUES (?, ?, ?, ?, '', '', '2000-01-01', '')");
            $stmt_usuario -> bind_param("isss", $idImagem, $email_empresa, $senha, $tipo_usuario);
            $stmt_usuario -> execute();
            $idUsuario = $conn -> insert_id;
            
            if ($idUsuario) {
                $stmt_comercial = $conn -> prepare("INSERT INTO Comercial(idUsuario, idEndereco, cnpj, nome, categoria, email, senha, telefone) VALUES (?, ?, ?, ?, '', ?, ?, ?)");
                $stmt_comercial -> bind_param("iisssss", $idUsuario, $idEndereco, $cnpj, $nome_empresa, $email_empresa, $senha, $telefone);

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