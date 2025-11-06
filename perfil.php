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
      c.categoria, /* Adicionando campo categoria */
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

  include_once ("forms/conexao.php");
  $connCount = abrirConexao();
  $count = 0;
  if ($stmtCount = $connCount->prepare("SELECT COUNT(*) AS total FROM Anuncio WHERE idUsuario = ?")) {
    $stmtCount->bind_param("i", $idUsuario);
    $stmtCount->execute();
    $resCount = $stmtCount->get_result()->fetch_assoc();
    $count = intval($resCount['total'] ?? 0);
    $stmtCount->close();
  }
  fecharConexao($connCount);

  $cep = $dadosCompletos['CEP'] ?? '';
  $cidade = '';
  $estado = '';
  if ($cep) {
    $cep_limpo = preg_replace('/[^0-9]/', '', $cep);
    $url = "https://viacep.com.br/ws/{$cep_limpo}/json/";
    $response = @file_get_contents($url);
    if ($response) {
      $data = json_decode($response, true);
      if ($data && !isset($data['erro'])) {
        $cidade = $data['localidade'];
        $estado = $data['uf'];
      }
    }
  }

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
              <button type=\"submit\" class=\"btn-salvar-cadastro\">Cadastrar</button>
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
        <img src="assets/img/logoPetSee/logoPetSeee.png" loading="lazy" alt="logoPetSee" class="imagem-logo">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Início</a></li>
          <li><a href="cuidados.html">Cuidados</a></li>
          <li><a href="serviços.php">Serviços</a></li>
          <li><a href="empresa.html">Empresa</a></li>
          <li><a href="contato.html">Contato</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

        <a id="login-button" class="btn-entrar" href="#" target="_self" class="active">Entrar</a> 
    </div>
  </header><br>

  <main class="perfil-container">
    <div class="perfil-grid">
      <!-- Coluna Esquerda -->
      <div class="perfil-left">
        <div class="left-top">
          <div class="avatar-circle">
            <?php $caminhoImagem = $dadosCompletos['avatar'] ?: 'assets/img/perfil/perfil_padrao.jpg'; ?>
            <img src="<?php echo htmlspecialchars($caminhoImagem); ?>" alt="Avatar">
            <button type="button" class="edit-avatar-btn" id="edit-avatar-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                <circle cx="12" cy="13" r="4"></circle>
              </svg>
            </button>
            <input type="file" id="avatar-input" style="display: none;">
          </div>

          <div class="user-meta">
            <h3><?php echo htmlspecialchars($dadosCompletos['nome']); ?></h3>
            <p class="location"><?php echo htmlspecialchars("$cidade, $estado"); ?></p>
            <span class="status-badge <?php echo $dadosCompletos['tipo'] === 'comercial' ? 'comercial' : 'pessoal'; ?>">
              <?php echo htmlspecialchars(ucfirst($dadosCompletos['tipo'])); ?>
            </span>
          </div>
        </div>

        <a href="anuncio.html" class="btn-cadastrar-animal">Cadastrar novo animal</a>

        <div class="counter" style="margin-bottom:10px;">
            <div class="num"><?php echo $count; ?></div>
            <div class="label">Animais divulgados</div>
        </div>

        <div class="remover-conta-panel">
          <h4>Remover Conta Permanentemente</h4>
          <p>Deseja remover sua conta e todos seus anúncios? Esta ação não pode ser desfeita.</p>
          <button id="btn-remover-conta" class="btn-remover">Remover</button>
        </div>

        <?php if ($dadosCompletos['tipo'] === 'comercial'): ?>
          <button id="abrirModalEmpresa" class="btn-cadastrar-empresa">Cadastrar empresa no site</button>
          
          <!-- Modal -->
          <div id="modalEmpresa" class="modal-empresa">
            <div class="modal-conteudo">
              <span class="fechar-modal" id="fecharModalEmpresa">&times;</span>
              <h3 class="titulo">Cadastrar nova empresa</h3>
              <p class="texto">Para cadastrar sua empresa no nosso site, preencha corretamente as lacunas. Lembre-se, suas informações aparecerão na aba de Serviços!</p>
              <form id="formCadastrarEmpresa">
                <input type="text" name="nome" placeholder="Nome da empresa" required>
                <input type="text" name="categoria" placeholder="Categoria" required>
                <input type="text" name="telefone" placeholder="Telefone" required>
                <input type="text" name="endereco" placeholder="Endereço completo" required>
                <textarea name="descricao" placeholder="Descrição da empresa" rows="4" required></textarea>
                <button type="submit" class="btn-salvar-cadastro">Cadastrar</button>
              </form>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Coluna Direita -->
      <div class="perfil-right">
        <div class="info-basica">
          <h3>Informações Básicas</h3>
          <form id="perfil-form" action="forms/atualizar_perfil.php" method="POST">
            <?php if ($dadosCompletos['tipo'] === 'pessoal'): ?>
              <div class="form-group">
                <label>E-mail:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($dadosCompletos['email']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Nome:</label>
                <input type="text" value="<?php echo htmlspecialchars($dadosCompletos['nome']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>CPF:</label>
                <input type="text" value="<?php echo htmlspecialchars($dadosCompletos['cpf']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Data de Nascimento:</label>
                <input type="date" value="<?php echo htmlspecialchars($dadosCompletos['nascimento']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>CEP:</label>
                <input type="text" name="CEP" value="<?php echo htmlspecialchars($dadosCompletos['CEP']); ?>" readonly>
              </div>
            <?php else: ?>
              <div class="form-group">
                <label>E-mail:</label>
                <input type="email" value="<?php echo htmlspecialchars($dadosCompletos['email']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Empresa:</label>
                <input type="text" name="nome_empresa" value="<?php echo htmlspecialchars($dadosCompletos['nome']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Categoria:</label>
                <input type="text" name="categoria" value="<?php echo htmlspecialchars($dadosCompletos['categoria']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>CNPJ:</label>
                <input type="text" value="<?php echo htmlspecialchars($dadosCompletos['cnpj']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>CEP:</label>
                <input type="text" value="<?php echo htmlspecialchars($dadosCompletos['endereco_cep']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Logradouro:</label>
                <input type="text" value="<?php echo htmlspecialchars($dadosCompletos['endereco_logradouro']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Número:</label>
                <input type="text" value="<?php echo htmlspecialchars($dadosCompletos['endereco_numero']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Bairro:</label>
                <input type="text" value="<?php echo htmlspecialchars($dadosCompletos['endereco_bairro']); ?>" readonly>
              </div>
            <?php endif; ?>
          </form>

          <div class="form-actions">
            <button type="button" class="btn-editar" onclick="tornarCamposEditaveis()">Editar</button>
            <a href="api/logout.php" class="btn-sair">Sair</a>
          </div>

          <button type="submit" id="btn-salvar" class="btn-salvar" style="display: none;">Salvar Alterações</button>
        </div>
      </div>
    </div>

    <div class="suas-publicacoes" style="margin-top:28px;">
      <h3 class="titulo-publicacoes">Suas Publicações</h3>
      <div class="publicacoes-grid pub-grid" style="margin-top:18px;">
        <?php
          include_once ("forms/conexao.php");
          $conn = abrirConexao();
          $stmt = $conn->prepare("
            SELECT A.idAnuncio, AN.nome AS nomeAnimal, IM.caminho AS imagem, 
                   A.situacao, A.cidade, A.bairro, A.cep
            FROM Anuncio A
            JOIN Animal AN ON A.idAnimal = AN.idAnimal
            LEFT JOIN Aspectos ASP ON A.idAspectos = ASP.idAspectos
            LEFT JOIN Imagens IM ON ASP.idImagem = IM.idImagem
            WHERE A.idUsuario = ?
            ORDER BY A.dataAnuncio DESC
          ");
          $stmt->bind_param("i", $idUsuario);
          $stmt->execute();
          $anunciosUsuario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
          $stmt->close();
          fecharConexao($conn);

          if (empty($anunciosUsuario)) {
            echo '<p style="color:#666;">Você ainda não divulgou nenhum animal.</p>';
          }

          foreach ($anunciosUsuario as $anuncio):
            $situacaoRaw = strtolower($anuncio['situacao'] ?? '');
            if (strpos($situacaoRaw, 'perd') !== false) {
              $label = 'Perdido'; $statusClass = 'status-lost';
            } elseif (strpos($situacaoRaw, 'resgat') !== false) {
              $label = 'Resgatado'; $statusClass = 'status-resgatado';
            } elseif (strpos($situacaoRaw, 'adoc') !== false) {
              $label = 'Para Adoção'; $statusClass = 'status-adocao';
            } elseif (strpos($situacaoRaw, 'encontr') !== false) {
              $label = 'Encontrado'; $statusClass = 'status-encontrado';
            } else {
              $label = ucfirst($anuncio['situacao'] ?? 'Status');
              $statusClass = 'status-unknown';
            }
        ?>
          <a href="detalhesAnuncio.php?id=<?php echo $anuncio['idAnuncio']; ?>" class="pub-card" style="text-decoration:none;">
            <div class="top" style="background:#900; height:140px; position:relative;">
              <span class="ad-status <?php echo $statusClass; ?>" style="position:absolute;top:8px;left:8px;padding:6px 10px;border-radius:6px;background:rgba(0,0,0,0.5);color:#fff;font-weight:700;font-size:13px;"><?php echo htmlspecialchars($label); ?></span>
              <img src="<?php echo htmlspecialchars($anuncio['imagem']); ?>" alt="" style="width:100%;height:140px;object-fit:cover;display:block;">
            </div>
            <div class="info" style="background:#f3f6f4;padding:10px;">
              <h5 class="ad-name" style="margin:0;color:#2f7a59;"><?php echo htmlspecialchars($anuncio['nomeAnimal']); ?></h5>
              <p class="ad-location" style="margin:4px 0 0;color:#6b6b6b;"><?php echo htmlspecialchars(($anuncio['bairro'] ?? '') . ', ' . ($anuncio['cidade'] ?? '')); ?></p>
            </div>
          </a>
        <?php endforeach; ?>
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

    document.getElementById('formCadastrarEmpresa').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      const resp = await fetch('api/cadastrar_estabelecimento.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      });
      const json = await resp.json();
      if (json.success) {
        alert("Empresa cadastrada com sucesso! Ela aparecerá na aba Serviços.");
        modal.style.display = 'none';
        this.reset();
      } else {
        alert("Erro ao cadastrar empresa: " + (json.msg || 'Tente novamente.'));
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
      const btnEditar = document.querySelector('.btn-editar');
      const inputs = document.querySelectorAll('#perfil-form input');
      const formActions = document.querySelector('.form-actions');
      const form = document.getElementById('perfil-form');
      
      let isEditing = false;
      
      btnEditar.addEventListener('click', function() {
        if (!isEditing) {
          // Habilitar edição
          inputs.forEach(input => {
            if (input.name !== 'email') {
              input.removeAttribute('readonly');
            }
          });
          
          // Esconder botões de ação
          formActions.style.display = 'none';
          
          // Adicionar botão salvar
          const btnSalvar = document.createElement('button');
          btnSalvar.type = 'submit';
          btnSalvar.className = 'btn-salvar';
          btnSalvar.textContent = 'Salvar Alterações';
          form.appendChild(btnSalvar);
          
          isEditing = true;
        }
      });
      
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch('forms/atualizar_perfil.php', {
          method: 'POST',
          body: formData
        })
        .then(response => {
          if (response.ok) {
            location.reload();
          } else {
            alert('Erro ao salvar as alterações');
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          alert('Erro ao salvar as alterações');
        });
      });
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