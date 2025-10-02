<?php
    include_once("forms/conexao.php");
    $conn = abrirConexao();

    $id = $_GET['id'] ?? null;
    if (!$id) {
    echo "Anúncio não encontrado.";
    exit;
    }

    $stmt = $conn -> prepare("
    SELECT A.dataAnuncio, A.situacao, A.telefone, A.cep,
            AN.nome AS nomeAnimal,
            ASP.especie, ASP.sexo, ASP.raca, ASP.porte, ASP.observacao,
            IM.caminho AS imagem
    FROM Anuncio A
    JOIN Animal AN ON A.idAnimal = AN.idAnimal
    JOIN Aspectos ASP ON A.idAspectos = ASP.idAspectos
    JOIN Imagens IM ON ASP.idImagem = IM.idImagem
    WHERE A.idAnuncio = ?
    ");
    $stmt -> bind_param("i", $id);
    $stmt -> execute();
    $anuncio = $stmt -> get_result() -> fetch_assoc();
    $stmt -> close();
  $stmt = $conn->prepare("
  SELECT C.texto, C.idComentario, C.idResposta, C.data, U.nome AS autor
  FROM Comentario C
  JOIN Usuario U ON C.idUsuario = U.idUsuario
  WHERE C.idAnuncio = ?
  ORDER BY C.idComentario ASC
  ");
  $stmt -> bind_param("i", $id);
  $stmt -> execute();
  $comentarios = $stmt -> get_result() -> fetch_all(MYSQLI_ASSOC);
  $stmt -> close();

  fecharConexao($conn);
?>
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
<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logoPetSee/logoPetSeenew.png" loading="lazy" alt="logoPetSee" class="imagem-logo">
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
  </header>
  
  <main class="anuncio-detalhado">
    <button class="btn-fechar" onclick="location.href='index.php'">×</button>
  <h1><?php echo htmlspecialchars($anuncio['nomeAnimal']); ?></h1>
  <img src="<?php echo $anuncio['imagem']; ?>" alt="Imagem do animal" style="max-width: 100%; height: auto;">
  <p><strong>Espécie:</strong> <?php echo htmlspecialchars($anuncio['especie']); ?></p>
  <p><strong>Sexo:</strong> <?php echo htmlspecialchars($anuncio['sexo']); ?></p>
  <p><strong>Raça:</strong> <?php echo htmlspecialchars($anuncio['raca']); ?></p>
  <p><strong>Porte:</strong> <?php echo htmlspecialchars($anuncio['porte']); ?></p>
  <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($anuncio['observacao'])); ?></p>
  <p><strong>Localização (CEP):</strong> <?php echo htmlspecialchars($anuncio['cep'] ?? ''); ?></p>
  <p><strong>Telefone para contato:</strong> <?php echo htmlspecialchars($anuncio['telefone']); ?></p>
  <p><strong>Reportado em:</strong> <?php echo date("d/m/Y", strtotime($anuncio['dataAnuncio'])); ?></p>
  <p><strong>Status:</strong> <?php echo ucfirst($anuncio['situacao']); ?></p>

  <hr>

  <section class="comentarios">
    <h2>Comentários</h2>
    <?php foreach ($comentarios as $comentario): ?>
      <div class="comentario">
        <p><strong><?php echo htmlspecialchars($comentario['autor']); ?>:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($comentario['texto'])); ?></p>
        <small><?php echo date("d/m/Y H:i", strtotime($comentario['data'])); ?></small>
      </div>
    <?php endforeach; ?>

    <form action="forms/comentar.php" method="POST" class="form-comentario">
      <input type="hidden" name="id_anuncio" value="<?php echo $id; ?>">
      <textarea name="texto" placeholder="Escreva seu comentário..." required></textarea>
      <button type="submit">Enviar comentário</button>
    </form>
  </section>
</main>
</body>
</html>