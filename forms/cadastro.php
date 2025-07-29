<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
</head>
<body>
    <?php
        $conn = new mysqli("localhost", "root", "1234", "PetSee");

        if ($conn->connect_error){
            echo("Erro ao abrir conexão");
        }else{
            echo("Conexão feita com sucesso");
            $cpf = $_POST['cpf'];
            $nome = $_POST['nome'];
            $nascimento = $_POST['nascimento'];
            $cep = $_POST['cep'];
            $email = $_POST['email'];
            $senha = $_POST['senha'];

            $stmt = $conn->prepare("INSERT INTO Usuario(cpf, nome, nascimento, CEP, email, senha) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $cpf, $nome, $nascimento, $cep, $email, $senha);

            if ($stmt -> execute()){
                $conn -> close();
                header('Location: ../index.html');
            }else{
                echo("Erro ao inserir registro: ").$stmt -> error;
            }
            $conn -> close();
        }
    ?>
</body>
</html>