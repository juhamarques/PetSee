<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
  echo json_encode(['success' => false, 'msg' => 'Usuário não autenticado (sessão ausente)']);
  exit;
}

include_once("../forms/conexao.php");
$conn = abrirConexao();
if (!$conn) {
  echo json_encode(['success' => false, 'msg' => 'Erro na conexão com o banco']);
  exit;
}
$conn->set_charset('utf8mb4');

$idUsuario = $_SESSION['idUsuario'];
$nome = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

// validação simples
if (!$nome || !$categoria || !$endereco || !$descricao) {
  fecharConexao($conn);
  echo json_encode(['success' => false, 'msg' => 'Campos obrigatórios ausentes']);
  exit;
}

$stmt = $conn->prepare("INSERT INTO Estabelecimento (nome, categoria, telefone, endereco, descricao, idUsuario) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
  $err = $conn->error;
  fecharConexao($conn);
  echo json_encode(['success' => false, 'msg' => 'Erro no prepare: '.$err]);
  exit;
}

$bind = $stmt->bind_param("sssssi", $nome, $categoria, $telefone, $endereco, $descricao, $idUsuario);
if (!$bind) {
  $err = $stmt->error;
  $stmt->close();
  fecharConexao($conn);
  echo json_encode(['success' => false, 'msg' => 'Erro no bind_param: '.$err]);
  exit;
}

$ok = $stmt->execute();
if (!$ok) {
  $err = $stmt->error ?: $conn->error;
  $stmt->close();
  fecharConexao($conn);
  echo json_encode(['success' => false, 'msg' => 'Erro no execute: '.$err]);
  exit;
}

$stmt->close();
fecharConexao($conn);
echo json_encode(['success' => true]);
exit;
