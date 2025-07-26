<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
</head>
<body>
    <?php
        $conn = new mysqli("localhost", "root", "", "PetSee");

        if ($conn->connect_error){
            echo("Erro ao abrir conexão");
        }else{
            echo("Conexão feita com sucesso");
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $nascimento = $_POST['nascimento'];
            $cpf = $_POST['cpf'];
            $cep = $_POST['cep'];
            $senha = $_POST['senha'];

            $stmt = $sconn->prepare("INSERT INTO tb_usuario(cpf, nome, nascimento, CEP, email, senha) VALUES (? ? ?)");
            $stmt->bind_param("sss", $nome, $email, $nascimento, $cpf, $cep, $senha);

            if ($stmt -> execute()){
                echo("Novo registro inserido com sucesso");
            }else{
                echo("Erro ao inserir registro: ").$stmt -> error;
            }
            $conn -> close();
        }
    ?>
</body>
</html>