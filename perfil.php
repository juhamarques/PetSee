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

  if (!isset($_SESSION['idUsuario'])) {
    header("Location: entrar.html");
    exit();
  }
  $idUsuario = $_SESSION['idUsuario'];

  include_once ("forms/conexao.php");
  $conn = abrirConexao();

  $sql = "
    SELECT
      u.idUsuario,
      u.idImagem,
      i.caminho AS avatar,
      u.cpf,
      u.nome AS nome,
      u.nascimento,
      u.CEP AS CEP,
      COALESCE(c.email, u.email) AS email,
      u.tipo,
      c.idComercial,
      c.cnpj,
      c.nome AS nome_empresa,
      c.telefone,
      e.CEP AS endereco_cep,
      e.logradouro AS endereco_logradouro,
      e.nome AS endereco_rua,
      e.numero AS endereco_numero,
      b.nome AS endereco_bairro
    FROM Usuario u
    LEFT JOIN Imagens i ON i.idImagem = u.idImagem
    LEFT JOIN Comercial c ON c.idUsuario = u.idUsuario
    LEFT JOIN Endereco e ON e.idEndereco = c.idEndereco
    LEFT JOIN Bairro b ON b.idBairro = e.idBairro
    WHERE u.idUsuario = ?
  ";

  $stmt = $conn -> prepare($sql);
  $stmt -> bind_param("i", $idUsuario);
  $stmt -> execute();
  $dadosCompletos = $stmt -> get_result() -> fetch_assoc() ?: [];
  $stmt -> close();
  fecharConexao($conn);

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
        <p>" . (isset($dadosCompletos['CEP']) ? $dadosCompletos['CEP'] : '') . "</p>
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
        <p>" . (isset($dadosCompletos['email']) ? $dadosCompletos['email'] : '') . "</p>
      </div>
      <div class='perfil-campo'>
        <label>Telefone:</label>
        <p>" . (isset($dadosCompletos['telefone']) ? $dadosCompletos['telefone'] : '') . "</p>
      </div>
      <div class='perfil-campo'>
        <label>CNPJ:</label>
        <p>{$dadosCompletos['cnpj']}</p>
      </div>
      <div class='perfil-campo'>
        <label>CEP:</label>
        <p>" . (isset($dadosCompletos['endereco_cep']) ? $dadosCompletos['endereco_cep'] : '') . "</p>
      </div>
      <div class='perfil-campo'>
        <label>Bairro:</label>
        <p>" . (isset($dadosCompletos['endereco_bairro']) ? $dadosCompletos['endereco_bairro'] : '') . "</p>
      </div>
      <div class='perfil-campo'>
        <label>Logradouro:</label>
        <p>" . (isset($dadosCompletos['endereco_logradouro']) ? $dadosCompletos['endereco_logradouro'] : '') . "</p>
      </div>
      <div class='perfil-campo'>
        <label>Rua:</label>
        <p>" . (isset($dadosCompletos['endereco_rua']) ? $dadosCompletos['endereco_rua'] : '') . "</p>
      </div>
      <div class='perfil-campo'>
        <label>Número:</label>
        <p>" . (isset($dadosCompletos['endereco_numero']) ? $dadosCompletos['endereco_numero'] : '') . "</p>
      </div>
      <div class='perfil-botoes'>
        <a href='api/logout.php' class='btn-sair'>Sair da Conta</a>
        <button id=\"abrirModalEmpresa\" class=\"btn-cadastrar-empresa\">Cadastrar Empresa</button>
        <div id=\"modalEmpresa\" class=\"modal-empresa\">
          <div class=\"modal-conteudo\">
            <span class=\"fechar-modal\" id=\"fecharModalEmpresa\">&times;</span>
            <h3 class=\"titulo\">Cadastrar nova empresa</h3> 
            <p class=\"texto\">Para cadastrar sua empresa no nosso site, preencha corretamente as lacunas. Lembre-se, suas informações aparecerão na aba de Serviços!</p>
            <form id=\"formCadastrarEmpresa\">
              <input type=\"text\" name=\"nome\" placeholder=\"Nome da empresa\" required>
              <input type=\"text\" name=\"categoria\" placeholder=\"Categoria\" required>
              <input type=\"text\" name=\"telefone\" placeholder=\"Telefone\" required>
              <input type=\"text\" name=\"endereco\" placeholder=\"Endereço completo\" required>
              <textarea name=\"descricao\" placeholder=\"Descrição da empresa\" rows=\"4\" required></textarea>
              <button type=\"submit\" class=\"btn-cadastrar-empresa\">Salvar</button>
            </form>
          </div>
        </div>
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

      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logoPetSee/logoPetSee.png" loading="lazy" alt="logoPetSee" class="imagem-logo">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Início</a></li>
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
              $caminhoImagem= 'assets/img/perfil/perfil_padrao.jpg';
              if (!empty($dadosCompletos['idImagem'])) {
                include_once ("forms/conexao.php");
                $conn = abrirConexao();
                $stmtImg = $conn -> prepare("SELECT caminho FROM Imagens WHERE idImagem = ?");
                $stmtImg -> bind_param("i", $dadosCompletos['idImagem']);
                $stmtImg -> execute();
                $stmtImg -> bind_result($caminhoBanco);
                if ($stmtImg -> fetch() && !empty($caminhoBanco)) {
                  $caminhoImagem = $caminhoBanco;
                }
                $stmtImg -> close();
                fecharConexao($conn);
              }
            ?>
            <img src="<?php echo $caminhoImagem ?>" loading="lazy" id="user-avatar">
            
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
        </div>

        <div id="perfil-dados" class="perfil-dados-container">
          <?php echo $html_dados; ?><br>
        </div>
    </div>
  </main>

  <script>
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

    const abrirModal = document.getElementById('abrirModalEmpresa');
    const fecharModal = document.getElementById('fecharModalEmpresa');
    const modal = document.getElementById('modalEmpresa');

    abrirModal.addEventListener('click', () => {
      modal.style.display = 'block';
    });

    fecharModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });

    document.getElementById('formCadastrarEmpresa').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const empresa = {
        nome: formData.get('nome'),
        categoria: formData.get('categoria'),
        telefone: formData.get('telefone'),
        endereco: formData.get('endereco'),
        descricao: formData.get('descricao')
      };

      const empresas = JSON.parse(localStorage.getItem('empresas')) || [];
      empresas.push(empresa);
      localStorage.setItem('empresas', JSON.stringify(empresas));

      alert("Empresa cadastrada com sucesso! Ela aparecerá na aba Serviços.");
      modal.style.display = 'none';
      this.reset();
    });
  </script>
  
  <!-- Preloader -->
  <div id="preloader">
    <div class="carg-card">
      <div class="carg-content">
        <img src="assets/img/telaCarregamento/dog_load.png" loading="lazy">
        <div class="speech-bubble">Eita! Espere um momento.</div>
      </div>
    </div>
  </div>

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