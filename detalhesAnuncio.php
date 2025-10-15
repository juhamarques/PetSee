<?php
    session_start();
    include_once("forms/conexao.php");
    $conn = abrirConexao();

    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo "Anúncio não encontrado.";
        exit;
    }

    $stmt = $conn -> prepare("
    SELECT A.*, AN.nome AS nomeAnimal, ASP.*, IM.caminho AS imagem, U.idUsuario AS donoId
    FROM Anuncio A
    JOIN Animal AN ON A.idAnimal = AN.idAnimal
    JOIN Aspectos ASP ON A.idAspectos = ASP.idAspectos
    JOIN Imagens IM ON ASP.idImagem = IM.idImagem
    JOIN Usuario U ON A.idUsuario = U.idUsuario
    WHERE A.idAnuncio = ?
    ");
    $stmt -> bind_param("i", $id);
    $stmt -> execute();
    $anuncio = $stmt -> get_result() -> fetch_assoc();
    $stmt -> close();
    $isDono = isset($_SESSION['idUsuario']) && $_SESSION['idUsuario'] == $anuncio['donoId'];

    $stmt = $conn->prepare("
    SELECT C.texto, C.idComentario, C.idResposta, C.data, C.lat, C.lon, U.nome AS autor
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
        <img src="assets/img/logoPetSee/logoPetSeee.png" loading="lazy" alt="logoPetSee" class="imagem-logo">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#" class="active">Início</a></li>
          <li><a href="cuidados.html">Cuidados</a></li>
          <li><a href="serviços.php">Serviços</a></li>
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
    <div id="anuncio-dados">
      <h1><?php echo htmlspecialchars($anuncio['nomeAnimal']); ?></h1>
      <img src="<?php echo $anuncio['imagem']; ?>" alt="Imagem do animal" style="max-width: 100%; height: auto;">
      <?php if ($isDono): ?>
        <button id="btn-editar-anuncio" class="btn btn-warning" style="display:block;margin:20px auto 10px auto;">Editar anúncio</button>
      <?php endif; ?>
      <div id="map-animal" style="width:100%;height:300px;min-height:300px;margin:20px 0;border-radius:8px;overflow:hidden;background:#eee;text-align:center;line-height:300px;"></div>
      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
      <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          var rua = <?php echo json_encode($anuncio['rua'] ?? ''); ?>;
          var numero = <?php echo json_encode($anuncio['numero'] ?? ''); ?>;
          var bairro = <?php echo json_encode($anuncio['bairro'] ?? ''); ?>;
          var cidade = <?php echo json_encode($anuncio['cidade'] ?? ''); ?>;
          var cep = <?php echo json_encode($anuncio['cep'] ?? ''); ?>;
          var enderecoCompleto = rua + ', ' + numero + ', ' + bairro + ', ' + cidade + ', ' + cep + ', Brasil';
          var mapDiv = document.getElementById('map-animal');
          if (!mapDiv) return;
          function renderMap(lat, lon) {
            mapDiv.style.lineHeight = 'normal';
            var map = L.map('map-animal').setView([lat, lon], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              maxZoom: 19,
              attribution: '© OpenStreetMap'
            }).addTo(map);
            L.marker([lat, lon]).addTo(map)
              .bindPopup('Local do animal ou último local visto')
              .openPopup();
          }
          function geocodeEndereco(endereco) {
            fetch(`https://us1.locationiq.com/v1/search?key=pk.ef0da208da5ffacd621170b02a50736b&q=${encodeURIComponent(endereco)}&format=json`)
              .then(resp => resp.json())
              .then(j => {
                if (Array.isArray(j) && j.length > 0) {
                  var lat = parseFloat(j[0].lat);
                  var lon = parseFloat(j[0].lon);
                  renderMap(lat, lon);
                } else {
                  mapDiv.textContent = 'Não foi possível localizar o endereço informado.';
                }
              })
              .catch(() => {
                mapDiv.textContent = 'Não foi possível carregar o mapa.';
              });
          }
          if (rua && numero && bairro && cidade && cep) {
            geocodeEndereco(enderecoCompleto);
          } else if (cep) {
            geocodeEndereco(cep + ', Brasil');
          } else {
            mapDiv.textContent = 'Localização não disponível.';
          }
        });
      </script>
      <p><strong>Espécie:</strong> <?php echo htmlspecialchars($anuncio['especie']); ?></p>
      <p><strong>Sexo:</strong> <?php echo htmlspecialchars($anuncio['sexo']); ?></p>
      <p><strong>Raça:</strong> <?php echo htmlspecialchars($anuncio['raca']); ?></p>
      <p><strong>Porte:</strong> <?php echo htmlspecialchars($anuncio['porte']); ?></p>
      <p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($anuncio['observacao'])); ?></p>
      <p><strong>Localização (CEP):</strong> <?php echo htmlspecialchars($anuncio['cep'] ?? ''); ?></p>
      <p><strong>Rua:</strong> <?php echo htmlspecialchars($anuncio['rua'] ?? ''); ?></p>
      <p><strong>Número:</strong> <?php echo htmlspecialchars($anuncio['numero'] ?? ''); ?></p>
      <p><strong>Bairro:</strong> <?php echo htmlspecialchars($anuncio['bairro'] ?? ''); ?></p>
      <p><strong>Cidade:</strong> <?php echo htmlspecialchars($anuncio['cidade'] ?? ''); ?></p>
      <p><strong>Telefone para contato:</strong> <?php echo htmlspecialchars($anuncio['telefone']); ?></p>
      <p><strong>Reportado em:</strong> <?php echo date("d/m/Y", strtotime($anuncio['dataAnuncio'])); ?></p>
      <p><strong>Status:</strong> <?php echo ucfirst($anuncio['situacao']); ?></p>
    </div>
    <?php if ($isDono): ?>
    <form id="form-editar-anuncio" action="forms/editar_anuncio.php" method="POST" enctype="multipart/form-data" style="display:none;margin-top:20px;">
      <input type="hidden" name="id_anuncio" value="<?php echo $anuncio['idAnuncio']; ?>">
      <div class="mb-2"><label>Nome</label><input name="nome" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['nomeAnimal']); ?>" required></div>
      <div class="mb-2"><label>Espécie</label><input name="especie" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['especie']); ?>" required></div>
      <div class="mb-2"><label>Sexo</label><select name="sexo" class="form-control" required><option value="macho" <?php if($anuncio['sexo']=='macho')echo 'selected';?>>Macho</option><option value="femea" <?php if($anuncio['sexo']=='femea')echo 'selected';?>>Fêmea</option></select></div>
      <div class="mb-2"><label>Raça</label><input name="raca" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['raca']); ?>"></div>
      <div class="mb-2"><label>Porte</label><input name="porte" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['porte']); ?>" required></div>
      <div class="mb-2"><label>Descrição</label><textarea name="observacao" class="form-control"><?php echo htmlspecialchars($anuncio['observacao']); ?></textarea></div>
      <div class="mb-2"><label>Foto</label><input name="foto" type="file" class="form-control"></div>
      <div class="mb-2"><label>CEP</label><input name="cep" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['cep']); ?>" required></div>
      <div class="mb-2"><label>Rua</label><input name="rua" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['rua']); ?>" required></div>
      <div class="mb-2"><label>Número</label><input name="numero" type="number" class="form-control" value="<?php echo htmlspecialchars($anuncio['numero']); ?>" required></div>
      <div class="mb-2"><label>Bairro</label><input name="bairro" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['bairro']); ?>" required></div>
      <div class="mb-2"><label>Cidade</label><input name="cidade" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['cidade']); ?>" required></div>
      <div class="mb-2"><label>Telefone</label><input name="telefone" type="text" class="form-control" value="<?php echo htmlspecialchars($anuncio['telefone']); ?>" required></div>
      <div class="mb-2"><label>Status</label><select name="situacao" class="form-control" required>
        <option value="perdido" <?php if($anuncio['situacao']=='perdido')echo 'selected';?>>Perdido</option>
        <option value="resgatado" <?php if($anuncio['situacao']=='resgatado')echo 'selected';?>>Resgatado</option>
        <option value="adocao" <?php if($anuncio['situacao']=='adocao')echo 'selected';?>>Para adoção</option>
        <option value="encontrado" <?php if($anuncio['situacao']=='encontrado')echo 'selected';?>>Encontrado (animal voltou ao dono)</option>
      </select></div>
      <button type="submit" class="btn btn-success">Salvar alterações</button>
      <button type="button" class="btn btn-secondary" onclick="document.getElementById('form-editar-anuncio').style.display='none';document.getElementById('anuncio-dados').style.display='block';">Cancelar</button>
    </form>
    <script>
      document.getElementById('btn-editar-anuncio').onclick = function() {
        document.getElementById('anuncio-dados').style.display = 'none';
        document.getElementById('form-editar-anuncio').style.display = 'block';
      };
    </script>
    <?php endif; ?>

    <hr>

    <section class="comentarios">
      <h2>Comentários</h2>
      <?php foreach ($comentarios as $comentario): ?>
        <div class="comentario">
          <p><strong><?php echo htmlspecialchars($comentario['autor']); ?>:</strong></p>
          <p><?php echo nl2br(htmlspecialchars($comentario['texto'])); ?></p>
          <?php if (!empty($comentario['lat']) && !empty($comentario['lon'])): ?>
            <div class="comentario-mapa" style="width:100%;height:200px;margin:10px 0;border-radius:8px;overflow:hidden;background:#eee;">
              <div id="map-com-<?php echo $comentario['idComentario']; ?>" style="width:100%;height:100%;"></div>
              <script>
                document.addEventListener('DOMContentLoaded', function() {
                  var map = L.map('map-com-<?php echo $comentario['idComentario']; ?>').setView([<?php echo $comentario['lat']; ?>, <?php echo $comentario['lon']; ?>], 16);
                  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                  }).addTo(map);
                  L.marker([<?php echo $comentario['lat']; ?>, <?php echo $comentario['lon']; ?>]).addTo(map)
                    .bindPopup('Local marcado pelo usuário')
                    .openPopup();
                });
              </script>
            </div>
          <?php endif; ?>
          <small><?php echo date("d/m/Y H:i", strtotime($comentario['data'])); ?></small>
        </div>
      <?php endforeach; ?>

      <form action="forms/comentar.php" method="POST" class="form-comentario" id="form-comentario">
        <input type="hidden" name="id_anuncio" value="<?php echo $id; ?>">
        <textarea name="texto" placeholder="Escreva seu comentário..." required></textarea>
        <div class="mb-2">
          <label for="search-local" class="form-label">Buscar local onde viu o animal (opcional):</label>
          <input type="text" id="search-local" class="form-control" placeholder="Digite para buscar...">
          <div id="search-suggestions" class="list-group" style="position:relative;z-index:10;"></div>
        </div>
        <div id="map-comentario" style="width:100%;height:250px;min-height:200px;margin:10px 0;border-radius:8px;overflow:hidden;background:#eee;"></div>
        <input type="hidden" name="lat" id="lat-comentario">
        <input type="hidden" name="lon" id="lon-comentario">
        <small class="text-muted">Clique no mapa para marcar o local onde viu o animal (opcional).</small>
        <button type="submit">Enviar comentário</button>
      </form>
      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
      <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
      <script>
      document.addEventListener('DOMContentLoaded', function() {
        var mapDiv = document.getElementById('map-comentario');
        var latInput = document.getElementById('lat-comentario');
        var lonInput = document.getElementById('lon-comentario');
        var searchInput = document.getElementById('search-local');
        var suggestionsDiv = document.getElementById('search-suggestions');
        var marker = null;
        var map = L.map('map-comentario').setView([-23.530621, -46.439629], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '© OpenStreetMap'
        }).addTo(map);
        map.on('click', function(e) {
          if (marker) map.removeLayer(marker);
          marker = L.marker(e.latlng).addTo(map);
          latInput.value = e.latlng.lat;
          lonInput.value = e.latlng.lng;
        });
        searchInput.addEventListener('input', function() {
          var q = searchInput.value.trim();
          if (q.length < 3) {
            suggestionsDiv.innerHTML = '';
            return;
          }
          fetch(`https://us1.locationiq.com/v1/autocomplete?key=pk.ef0da208da5ffacd621170b02a50736b&q=${encodeURIComponent(q + ', São Paulo, Brasil')}&limit=5`)
            .then(resp => resp.json())
            .then(j => {
              suggestionsDiv.innerHTML = '';
              if (Array.isArray(j)) {
                j.forEach(item => {
                  var el = document.createElement('button');
                  el.type = 'button';
                  el.className = 'list-group-item list-group-item-action';
                  el.textContent = item.display_place || item.display_address || item.address.name || item.address.road || item.address.suburb || item.address.city || item.address.state || item.address.country || q;
                  el.onclick = function() {
                    map.setView([parseFloat(item.lat), parseFloat(item.lon)], 16);
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([parseFloat(item.lat), parseFloat(item.lon)]).addTo(map);
                    latInput.value = item.lat;
                    lonInput.value = item.lon;
                    suggestionsDiv.innerHTML = '';
                    searchInput.value = el.textContent;
                  };
                  suggestionsDiv.appendChild(el);
                });
              }
            });
        });
      });
      </script>
    </section>
  </main>

  <script src="assets/js/main.js"></script>
  <script src="assets/js/auth.js"></script>
</body>
</html>