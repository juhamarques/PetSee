<?php
session_start();

if (!isset($_SESSION['idUsuario'])) {
    header("Location: ../entrar.html");
    exit();
}

include_once ("../forms/conexao.php");
$conn = abrirConexao();
$idUsuario = $_SESSION['idUsuario'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['avatar']['type'];

     if (in_array($file_type, $allowed_types)) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . '.' . $ext;
        $upload_dir = '../assets/img/perfil/avatars/';
        $relative_path = 'assets/img/perfil/avatars/' . $novo_nome;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $caminho = $upload_dir . $novo_nome;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $caminho)) {
            $stmt = $conn->prepare("INSERT INTO Imagens (caminho) VALUES (?)");
            $stmt->bind_param("s", $relative_path);
            if ($stmt->execute()) {
                $novoIdImagem = $conn->insert_id;
                $stmt = $conn->prepare("UPDATE Usuario SET idImagem = ? WHERE idUsuario = ?");
                $stmt->bind_param("ii", $novoIdImagem, $idUsuario);
                $stmt->execute();
            } else {
                header("Location: ../erro.html");
                exit();
            }
        } else {
            header("Location: ../erro.html");
            exit();
        }
    } else {
        header("Location: ../erro.html");
        exit();
    }
}

header("Location: ../perfil.php");
exit();
?>