<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../entrar.html");
    exit();
}

include_once ("../forms/conexao.php");
$conn = abrirConexao();

$idUsuario = $_SESSION['userId'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['avatar']['type'];

    if (in_array($file_type, $allowed_types)) {
        $upload_dir = '../assets/img/perfil/avatars/';
        $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = 'avatar_' . $idUsuario . '.' . $file_extension;
        $upload_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_file)) {
            $sql = "UPDATE Usuario SET foto_perfil = ? WHERE idUsuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_filename, $idUsuario);
            $stmt->execute();
            
            header("Location: ../perfil.php");
            exit();
        }
    }
}

header("Location: ../perfil.php");
exit();
?>