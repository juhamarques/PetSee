<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header('Location: ../entrar.html');
    exit;
}
include_once("conexao.php");
$conn = abrirConexao();
$idUsuario = $_SESSION['idUsuario'];
$idAnuncio = $_POST['id_anuncio'] ?? null;
if (!$idAnuncio) {
    echo "ID do anúncio não informado.";
    exit;
}
// Busca o dono do anúncio
$stmt = $conn->prepare("SELECT idUsuario, idAnimal, idAspectos FROM Anuncio WHERE idAnuncio = ?");
$stmt->bind_param("i", $idAnuncio);
$stmt->execute();
$stmt->bind_result($donoId, $idAnimal, $idAspectos);
if (!$stmt->fetch()) {
    $stmt->close();
    echo "Anúncio não encontrado.";
    exit;
}
$stmt->close();
if ($donoId != $idUsuario) {
    echo "Você não tem permissão para editar este anúncio.";
    exit;
}
// Dados do formulário
$nome = trim($_POST['nome'] ?? '');
$especie = trim($_POST['especie'] ?? '');
$sexo = trim($_POST['sexo'] ?? '');
$raca = trim($_POST['raca'] ?? '');
$porte = trim($_POST['porte'] ?? '');
$observacao = trim($_POST['observacao'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$rua = trim($_POST['rua'] ?? '');
$numero = intval($_POST['numero'] ?? 0);
$bairro = trim($_POST['bairro'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$situacao = trim($_POST['situacao'] ?? '');
// Atualiza Animal
$stmt = $conn->prepare("UPDATE Animal SET nome=? WHERE idAnimal=?");
$stmt->bind_param("si", $nome, $idAnimal);
$stmt->execute();
$stmt->close();
// Atualiza Aspectos
$stmt = $conn->prepare("UPDATE Aspectos SET especie=?, sexo=?, raca=?, porte=?, observacao=? WHERE idAspectos=?");
$stmt->bind_param("sssssi", $especie, $sexo, $raca, $porte, $observacao, $idAspectos);
$stmt->execute();
$stmt->close();
// Atualiza Imagem se enviada
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = dirname(__DIR__) . '/assets/img/anuncioAnimal/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $tmpName = $_FILES['foto']['tmp_name'];
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($ext), $extensoesPermitidas, true)) {
        die("Tipo de arquivo não permitido: .$ext");
    }
    $imgNome = uniqid('pet_') . '.' . $ext;
    $destino = $uploadDir . $imgNome;
    if (!move_uploaded_file($tmpName, $destino)) {
        die("Falha ao mover arquivo para: $destino");
    }
    $caminhoRelativo = 'assets/img/anuncioAnimal/' . $imgNome;
    // Atualiza caminho da imagem
    $stmt = $conn->prepare("UPDATE Imagens SET caminho=? WHERE idImagem=(SELECT idImagem FROM Aspectos WHERE idAspectos=?)");
    $stmt->bind_param("si", $caminhoRelativo, $idAspectos);
    $stmt->execute();
    $stmt->close();
}
// Atualiza Anuncio
$stmt = $conn->prepare("UPDATE Anuncio SET cep=?, rua=?, numero=?, bairro=?, cidade=?, situacao=?, telefone=? WHERE idAnuncio=?");
$stmt->bind_param("ssissssi", $cep, $rua, $numero, $bairro, $cidade, $situacao, $telefone, $idAnuncio);
$stmt->execute();
$stmt->close();
fecharConexao($conn);
header("Location: ../detalhesAnuncio.php?id=$idAnuncio");
exit;
?>
