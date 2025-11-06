<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header('Location: ../entrar.html');
    exit;
}

include_once("conexao.php");
$conn = abrirConexao();
$idUsuario = $_SESSION['idUsuario'];

try {
    // Verificar tipo de usuário
    $stmt = $conn->prepare("SELECT tipo FROM Usuario WHERE idUsuario = ?");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Erro ao buscar tipo de usuário");
    }
    
    $tipo = $result->fetch_assoc()['tipo'];

    if ($tipo === 'pessoal') {
        // Atualizar usuário pessoal
        $stmt = $conn->prepare("UPDATE Usuario SET nome = ?, CEP = ? WHERE idUsuario = ?");
        if (!$stmt) {
            throw new Exception("Erro ao preparar query de usuário pessoal");
        }
        
        $stmt->bind_param("ssi", $_POST['nome'], $_POST['CEP'], $idUsuario);
        
    } else {
        // Atualizar usuário comercial
        $stmt = $conn->prepare("
            UPDATE Comercial 
            SET nome = ?, telefone = ?, categoria = ? 
            WHERE idUsuario = ?
        ");
        if (!$stmt) {
            throw new Exception("Erro ao preparar query de usuário comercial");
        }
        
        $stmt->bind_param("sssi", 
            $_POST['nome_empresa'], 
            $_POST['telefone'],
            $_POST['categoria'],
            $idUsuario
        );
    }

    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar atualização: " . $stmt->error);
    }

    header('Location: ../perfil.php?success=1');
    exit;

} catch (Exception $e) {
    error_log("Erro na atualização do perfil: " . $e->getMessage());
    header('Location: ../perfil.php?error=1&msg=' . urlencode($e->getMessage()));
    exit;
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    fecharConexao($conn);
}
