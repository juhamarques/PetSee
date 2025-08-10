<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>PetSee - cuidados animais</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Arquivos Vendor CSS -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Arquivo CSS -->
  <link href="assets/css/main.css" rel="stylesheet">

</head>
<?php
error_reporting(E_ALL & ~E_NOTICE);
session_start();

// Supondo que você armazena o tipo de usuário na sessão.
// Se não, você precisará fazer uma consulta ao banco para descobrir o tipo.
if (!isset($_SESSION['userId'])) {
    header("Location: entrar.html");
    exit();
}

include_once ("forms/conexao.php");
$conn = abrirConexao();

$idUsuario = $_SESSION['userId'];
$sql = "SELECT * FROM Usuario WHERE idUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

$html_dados = ""; 
if (isset($usuario['tipo']) && $usuario['tipo'] == 'pessoal') {
    $html_dados = "
        <div class='perfil-campo'>
            <label>Nome:</label>
            <p>{$usuario['nome']}</p>
        </div>
        <div class='perfil-campo'>
            <label>Email:</label>
            <p>{$usuario['email']}</p>
        </div>
        <div class='perfil-campo'>
            <label>Data de Nascimento:</label>
            <p>{$usuario['nascimento']}</p>
        </div>
        <div class='perfil-campo'>
            <label>CPF:</label>
            <p>{$usuario['cpf']}</p>
        </div>
        <div class='perfil-campo'>
            <label>CEP:</label>
            <p>{$usuario['cep']}</p>
        </div>
    ";
} else if (isset($usuario['tipo']) && $usuario['tipo'] == 'comercial') {
    $html_dados = "
        <div class='perfil-campo'>
            <label>Nome da Empresa:</label>
            <p>{$usuario['nome_empresa']}</p>
        </div>
        <div class='perfil-campo'>
            <label>CNPJ:</label>
            <p>{$usuario['cnpj']}</p>
        </div>
        <div class='perfil-campo'>
            <label>Email Comercial:</label>
            <p>{$usuario['email']}</p>
        </div>
        <div class='perfil-campo'>
            <label>CEP:</label>
            <p>{$usuario['cep']}</p>
        </div>
    ";
} else {
  $html_dados = "
    <p>Dados de perfil não disponíveis para este tipo de usuário.</p>
    <div class='perfil-campo'>
      <label>Nome:</label>
      <p>{$usuario['nome']}</p>
    </div>
    <div class='perfil-campo'>
      <label>Email:</label>
      <p>{$usuario['email']}</p>
    </div>
  ";
}
?>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logo petsee/logo petsee.png" alt="Logo PetSee" class="imagem-logo">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#" class="active">Início</a></li>
          <li><a href="cuidados.html">Cuidados</a></li>
          <li><a href="serviços.html">Serviços</a></li>
          <li><a href="empresa.html">Empresa</a></li>
          <li><a href="contato.html">Contato</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

        <a id="login-button" class="btn-entrar" href="entrar.html" target="_self">Entrar</a> 
    </div>
  </header><br>

  <main>
    <div class="perfil-container">
        <div class="perfil-header">
          <div class="perfil-avatar">
            <img src="caminho/para/imagem_padrao.png" alt="Avatar do Usuário" id="user-avatar">
            <a href="#" class="edit-avatar-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                <circle cx="12" cy="13" r="4"></circle>
              </svg>
            </a>
          </div>
          <h2 class="perfil-nome" id="user-nome"><?php echo htmlspecialchars($usuario['nome'] ?? $usuario['nome_empresa']); ?></h2>
          <a href="#" class="btn-editar-perfil">Editar Perfil</a>
        </div>

        <div id="perfil-dados" class="perfil-dados-container">
          <?php echo $html_dados; ?>
          <br><a href="api/logout.php" class="btn-sair">Sair</a>
        </div>
    </div>
  </main>
  
  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Arquivos Vendor JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Arquivo Main JS -->
  <script src="assets/js/main.js"></script>
  <script src="assets/js/auth.js"></script>

</body>
</html>