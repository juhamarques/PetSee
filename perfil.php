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

  if (!isset($_SESSION['userId'])) {
    header("Location: entrar.html");
    exit();
  }

  include_once ("forms/conexao.php");
  $conn = abrirConexao();

  $idUsuario = $_SESSION['userId'];
  $sqlUsuario = "SELECT * FROM Usuario WHERE idUsuario = ?";
  $stmtUsuario = $conn->prepare($sqlUsuario);
  $stmtUsuario -> bind_param("i", $idUsuario);
  $stmtUsuario -> execute();
  $resultUsuario = $stmtUsuario -> get_result();
  $usuario = $resultUsuario -> fetch_assoc();

  $dadosCompletos = $usuario;

  if (isset($usuario['tipo']) && $usuario['tipo'] == 'comercial') {
    $sqlComercial = "SELECT * FROM Comercial WHERE idUsuario = ?";
    $stmtComercial = $conn->prepare($sqlComercial);
    $stmtComercial -> bind_param("i", $idUsuario);
    $stmtComercial -> execute();
    $resultComercial = $stmtComercial -> get_result();
    $dadosComercial = $resultComercial -> fetch_assoc();
    $dadosCompletos = array_merge($usuario, $dadosComercial);
  }

  fecharConexao($conn);

  $html_dados = ""; 
  if (isset($dadosCompletos['tipo']) && ($dadosCompletos['tipo'] == 'pessoal' || $dadosCompletos['tipo'] == '0')) {
    $html_dados = "
      <div class='perfil-campo-tipo'>
        <p>{$dadosCompletos['tipo']}</p>
      </div>
      <div class='perfil-campo'>
        <label>Email:</label>
        <p>{$dadosCompletos['email']}</p>
      </div>
      <div class='perfil-campo'>
        <label>Data de Nascimento:</label>
        <p>{$dadosCompletos['nascimento']}</p>
      </div>
      <div class='perfil-campo'>
        <label>CPF:</label>
        <p>{$dadosCompletos['cpf']}</p>
      </div>
      <div class='perfil-campo'>
        <label>CEP:</label>
        <p>{$dadosCompletos['CEP']}</p>
      </div>
      <div class='perfil-botoes'>
        <a href='api/logout.php' class='btn-sair'>Sair</a>
      </div>
    ";
  } else if (isset($dadosCompletos['tipo']) && $dadosCompletos['tipo'] == 'comercial') {
    $html_dados = "
      <div class='perfil-campo-tipo'>
        <p>{$dadosCompletos['tipo']}</p>
      </div>
      <div class='perfil-campo'>
        <label>Email Comercial:</label>
        <p>{$dadosCompletos['email_empresa']}</p>
      </div>
      <div class='perfil-campo'>
        <label>Telefone:</label>
        <p>{$dadosCompletos['telefone']}</p>
      </div>
      <div class='perfil-campo'>
        <label>CNPJ:</label>
        <p>{$dadosCompletos['cnpj']}</p>
      </div>
      <div class='perfil-campo'>
        <label>CEP:</label>
        <p>{$dadosCompletos['endereco_cep']}</p>
      </div>
      <div class='perfil-botoes'>
        <a href='api/logout.php' class='btn-sair'>Sair da Conta</a>
        <a href='cadastrar_empresa.php' class='btn-cadastrar-empresa'>Cadastrar Empresa</a>
      </div>
    ";
  } else {
    $html_dados = "
      <p>Dados de perfil não disponíveis para este tipo de usuário.</p>
      <div class='perfil-botoes'>
        <a href='api/logout.php' class='btn-sair'>Sair</a>
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
          <li><a href="index.html">Início</a></li>
          <li><a href="cuidados.html">Cuidados</a></li>
          <li><a href="serviços.html">Serviços</a></li>
          <li><a href="empresa.html">Empresa</a></li>
          <li><a href="contato.html">Contato</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

        <a id="login-button" class="btn-entrar" href="#" target="_self" class="active">Entrar</a> 
    </div>
  </header><br>

  <main>
    <div class="perfil-container">
        <div class="perfil-header">

          <div class="perfil-avatar">
            <?php
              $caminho_foto = 'assets/img/perfil/perfil vazio.jpg';
              if (!empty($usuario['foto_perfil'])) {
                $caminho_foto = 'assets/img/perfil/avatars/' . $usuario['foto_perfil'];
              }
            ?>
            <img src="<?php echo $caminho_foto; ?>" alt="Avatar do Usuário" id="user-avatar">
            
            <form id="avatar-form" action="api/upload_avatar.php" method="POST" enctype="multipart/form-data">
              <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display: none;">
                
              <a href="#" class="edit-avatar-btn" id="edit-avatar-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-camera">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                <circle cx="12" cy="13" r="4"></circle>
                </svg>
              </a>
            </form>
          </div>

          <?php
            $nome_a_exibir = '';
            if (!empty($dadosCompletos['nome'])) {
              $nome_a_exibir = $dadosCompletos['nome'];
            } else if (!empty($dadosCompletos['nome_empresa'])) {
              $nome_a_exibir = $dadosCompletos['nome_empresa'];
            }
          ?>
          <h2 class="perfil-nome" id="user-nome"><?php echo htmlspecialchars($nome_a_exibir); ?></h2>
          <a href="#" class="btn-editar-perfil">Editar Perfil</a>
        </div>

        <div id="perfil-dados" class="perfil-dados-container">
          <?php echo $html_dados; ?><br>
        </div>
    </div>
  </main>

  ,<script>
    document.addEventListener('DOMContentLoaded', function() {
      const avatarInput = document.getElementById('avatar-input');
      const editAvatarBtn = document.getElementById('edit-avatar-btn');
      const avatarForm = document.getElementById('avatar-form');

      editAvatarBtn.addEventListener('click', function(e) {
          e.preventDefault();
          avatarInput.click();
      });

      avatarInput.addEventListener('change', function() {
          if (this.files && this.files[0]) {
              avatarForm.submit();
          }
      });
    });
  </script>
  
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