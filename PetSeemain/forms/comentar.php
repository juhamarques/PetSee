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
    $lat = isset($_POST['lat']) && $_POST['lat'] !== '' ? $_POST['lat'] : null;
    $lon = isset($_POST['lon']) && $_POST['lon'] !== '' ? $_POST['lon'] : null;

    if ($id_resposta === null) {
        $stmt = $conn->prepare("INSERT INTO Comentario (idUsuario, idAnuncio, texto, lat, lon) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissd", $id_usuario, $id_anuncio, $texto, $lat, $lon);
    } else {
        $stmt = $conn->prepare("INSERT INTO Comentario (idUsuario, idAnuncio, idResposta, texto, lat, lon) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisdd", $id_usuario, $id_anuncio, $id_resposta, $texto, $lat, $lon);
    }

    $stmt->execute();
    $stmt->close();
    fecharConexao($conn);

    header("Location: ../detalhesAnuncio.php?id=" . $id_anuncio);
    exit;
?>