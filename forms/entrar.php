<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
</head>
<body>
    <?php
        $sql = "select * from Usuario where nome = ? AND email = ? AND senha = ?";
        $conn = new mysqli("localhost", "root", "1234", "PetSee");

        if ($conn->connect_error){
            echo("Erro ao abrir conexão");
        }else{
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nome, $email, $senha);
            $stmt->execute();

        $result = $stmt->get_result();
        $achouUsuario = false;
        $nome = "";
            while ($row = $result->fetch_assoc()) {
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
            header('Location:index.html');
        }else{
            header('Location:dashboard.php?nome='.$nome);
        }

    ?>
</body>
</html>