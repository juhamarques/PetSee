<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
</head>
<body>
    <?php
        //session_start();

        $sql = "select * from Usuario where nome = ? AND email = ? AND senha = ?";
        $conn = new mysqli("localhost", "root", "", "PetSee");

        if ($conn->connect_error){
            echo("Erro ao abrir conexão");
        }else{
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nome, $email, $senha);
            $stmt->execute();

        $result = $stmt->get_result();
        //$user_id = null;
        //$user_name = "";
        //$user_senha = "";
        $achouUsuario = false;
        $nome = "";
            while ($row = $result-> fetch_assoc()) {
                //$user_id = $row['id'];
                //$user_name = $row['nome'];
                //$user_senha = $row['senha'];
                $nome =  $row['nome'];
                echo "ID: " . $row['id'] . "<br>";
                echo "Nome: " . $row['nome'] . "<br>";
                echo "Email: " . $row['email'] . "<br>";
                echo "<hr>";
                $achouUsuario = true;
            }
        }
        $conn->close();
        if($achouUsuario==false){
            header('Location:../index.html');
            exit();
        }else{
            header('Location: ../entrar.html?erro=credenciais_invalidas');
            exit();
        }
        //$stmt->close();
        //$conn->close();
    ?>
</body>
</html>