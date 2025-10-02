<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['idUsuario'])) {
  echo json_encode(['success' => false, 'msg' => 'Usuário não autenticado']);
  exit;
}
include_once("../forms/conexao.php");
$conn = abrirConexao();
$idUsuario = $_SESSION['idUsuario'];
$nome = $_POST['nome'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$endereco = $_POST['endereco'] ?? '';
$descricao = $_POST['descricao'] ?? '';
if ($nome && $categoria && $endereco && $descricao) {
  $stmt = $conn->prepare("INSERT INTO Estabelecimento (nome, categoria, telefone, endereco, descricao, idUsuario) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssi", $nome, $categoria, $telefone, $endereco, $descricao, $idUsuario);
  $ok = $stmt->execute();
  $stmt->close();
  fecharConexao($conn);
  if ($ok) {
    echo json_encode(['success' => true]);
    exit;
  }
}
fecharConexao($conn);
echo json_encode(['success' => false, 'msg' => 'Erro ao cadastrar']);
exit;
