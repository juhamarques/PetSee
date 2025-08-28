<?php
    session_start();
    if (!isset($_SESSION['idUsuario'])) {
        header('Location: entrar.html');
        exit;
    }

    include_once("conexao.php");
    $conn = abrirConexao();

    $id_anuncio = $_POST['id_anuncio'];
    $id_usuario = $_SESSION['idUsuario'];
    $texto = $_POST['texto'];
    $id_resposta = isset($_POST['id_resposta']) && $_POST['id_resposta'] !== '' ? $_POST['id_resposta'] : null;

    if ($id_resposta === null) {
        $stmt = $conn->prepare("INSERT INTO Comentario (idUsuario, idAnuncio, texto) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_usuario, $id_anuncio, $texto);
    } else {
        $stmt = $conn->prepare("INSERT INTO Comentario (idUsuario, idAnuncio, idResposta, texto) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $id_usuario, $id_anuncio, $id_resposta, $texto);
    }

    $stmt->execute();
    $stmt->close();
    fecharConexao($conn);

    header("Location: anuncio.php?id=" . $id_anuncio);
    exit;
?>