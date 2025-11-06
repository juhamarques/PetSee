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
  SELECT C.texto, C.idComentario, C.idResposta, C.data, C.lat, C.lon, C.idUsuario AS autorId, U.nome AS autor
  FROM Comentario C
  JOIN Usuario U ON C.idUsuario = U.idUsuario
  WHERE C.idAnuncio = ?
  ORDER BY C.idComentario ASC
  ");
  $stmt -> bind_param("i", $id);
  $stmt -> execute();
  $rawComments = $stmt -> get_result() -> fetch_all(MYSQLI_ASSOC);
  $stmt -> close();

  $commentsById = [];
  $rootComments = [];
  foreach ($rawComments as $c) {
    $c['children'] = [];
    $commentsById[$c['idComentario']] = $c;
  }
  foreach ($commentsById as $cid => $c) {
    if (empty($c['idResposta'])) {
      $rootComments[] = &$commentsById[$cid];
    } else {
      $pid = $c['idResposta'];
      if (isset($commentsById[$pid])) {
        $commentsById[$pid]['children'][] = &$commentsById[$cid];
      } else {
        $rootComments[] = &$commentsById[$cid];
      }
    }
  }

  function render_comment_tree($comments, $depth = 0) {
    foreach ($comments as $comentario) {
      $isReply = $depth > 0;
      $divClass = $isReply ? 'comentario resposta' : 'comentario';
      echo "<div class=\"{$divClass}\" style=\"position:relative; padding-bottom:56px;\">\n";
      echo "  <div style=\"display:flex;align-items:flex-start;gap:8px;\">\n";
      echo "    <div style=\"flex:1;\">\n";
      echo "      <p style=\"margin:0 0 6px 0;\"><strong>" . htmlspecialchars($comentario['autor']) . ":</strong></p>\n";
      echo "      <div style=\"font-size:15px;line-height:1.4;\">" . nl2br(htmlspecialchars($comentario['texto'])) . "</div>\n";
      echo "    </div>\n";
      echo "  </div>\n";
      if (!empty($comentario['lat']) && !empty($comentario['lon'])) {
        $mapId = 'map-com-' . $comentario['idComentario'];
        echo "  <div class=\"comentario-mapa\" style=\"width:100%;height:200px;margin:10px 0;border-radius:8px;overflow:hidden;background:#eee;\">\n";
        echo "    <div id=\"{$mapId}\" style=\"width:100%;height:100%;\"></div>\n";
        echo "  </div>\n";
        echo "  <script>document.addEventListener('DOMContentLoaded', function() {\n";
        echo "    var map = L.map('{$mapId}').setView([{$comentario['lat']}, {$comentario['lon']}], 16);\n";
        echo "    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '© OpenStreetMap'}).addTo(map);\n";
        echo "    L.marker([{$comentario['lat']}, {$comentario['lon']}]).addTo(map).bindPopup('Local marcado pelo usuário').openPopup();\n";
        echo "  });</script>\n";
      }
      echo "  <div style=\"margin-top:6px;display:flex;gap:8px;align-items:center;\">\n";
      echo "    <small>" . date("d/m/Y H:i", strtotime($comentario['data'])) . "</small>\n";
      echo "  </div>\n";
      echo "  <button type=\"button\" class=\"btn-reply btn btn-link\" data-id=\"{$comentario['idComentario']}\" data-author=\"" . htmlspecialchars($comentario['autor']) . "\">Responder</button>\n";
      echo "</div>\n"; 
      if (!empty($comentario['children'])) {
        render_comment_tree($comentario['children'], $depth + 1);
      }
    }
  }

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

  <style>
    .retangulo-central {
      border: 2px solid var(--surface-color);
      border-radius: 10px;
      background: var(--background-color);
      padding: 22px;
      max-width: 980px;
      margin: 90px auto 40px;
      box-sizing: border-box;
      box-shadow: 0 6px 18px rgba(0,0,0,0.04);
      margin-top: 150px;
    }

    .retangulo-central .btn-fechar {
      position: absolute;
      left: 275px;
      top: 115px;
      font-size: 30px;
      background: transparent;
      border: none;
      cursor: pointer;
      z-index: 1100;
    }

    .btn-fechar { 
      left: 12px; 
      right: auto; 
      top: 10px; 
      position: absolute; 
      font-size: 30px; 
      background: transparent; 
      border: none; 
      cursor: pointer; 
      z-index: 1100; 
    }

    .details-top { 
      display: flex; 
      gap: 18px; 
      align-items: stretch; 
    }

    .anuncio-image { 
      flex: 0 0 360px; 
      width: 360px; 
      height: 360px; 
      overflow: hidden; 
      border-radius: 8px; 
      background: #900; 
    }

    .anuncio-image img { 
      width: 100%; 
      height: 100%; 
      object-fit: cover; 
      display:block; 
    }
    
    .anuncio-info { 
      flex: 1; 
      height: 360px; 
      display:flex; 
      flex-direction: column; 
      gap:6px; 
      overflow:auto; 
      font-size: 18px; 
      color: #222;
    }

    .info-text {
      font-size: 19px !important; 
      line-height: 1.45 !important;
      margin: 6px 0 !important;
      color: #6c6b6bff !important;
    }

    .anuncio-info .info-text,
    .info-block p.info-text {
      font-size: 19px !important;
      line-height: 1.45 !important;
    }
 
    .anuncio-title-row { 
      display: flex; 
      align-items: center; 
      gap: 10px; 
      justify-content: flex-start;
      flex-wrap: nowrap;
      width: 100%;
    }

    .anuncio-title-row h1 { 
      margin: 0; 
      font-size:30px; 
      color: var(--surface-color); 
      line-height:1.05;
    }

    .btn-editar-anuncio {
      background: #ffd6d6 !important; 
      color: #4f4f4f !important;
      border: none !important;
      padding: 6px 12px !important;
      border-radius: 6px;
      font-size: 14px;
      margin-left: 0 !important; 
    }

    .status-badge { 
      padding:8px 14px; 
      border-radius:8px; 
      color:#fff; 
      font-weight:800; 
      font-size:18px; 
    }

    .status-perdido {
      background:#c0392b; 
    }

    .status-resgatado { 
      background:#e67e22; 
    }

    .status-adocao { 
      background:#3498db; 
    } 

    .status-encontrado { 
      background:#2ecc71; 
    } 

    .info-block { 
      margin-top:6px; 
      padding-top:6px; 
      border-top:1px solid #e6e6e6; 
      line-height:1.32;
    }

    .info-block h4 { 
      margin:0 0 6px 0; 
      color:#2f7a59; 
      font-weight:700; 
      font-size:18px; 
    }

    .info-block p {
      margin: 4px 0;
      font-size: 18px;
      line-height: 1.4;
      color: #4f4f4f;
    }
    
    .form-comentario { 
      margin-top:12px; 
      margin-bottom:18px; 
      display:flex; 
      flex-direction:column; 
      gap:8px; 
    }

    .form-comentario textarea { 
      width:100%; 
      min-height:90px; 
      padding:8px; 
      border-radius:8px; 
      border:1px solid #ccc; 
      font-size:15px; 
    }

    .btn-comment { 
      background: var(--accent-color); 
      color:#666666; 
      font-weight:700; 
      border-radius:6px; 
      padding:8px 14px; 
      margin-bottom: 18px;
      border:none;
    }

    .form-comentario .actions-row { 
      display:flex; 
      justify-content:flex-end; 
      align-items:center; 
      gap:12px; 
    }

    .comentario { 
      background-color: #f1efef; 
      padding: 12px 20px 64px 20px; 
      border-radius: 8px; 
      margin-bottom: 15px; 
      box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
      position:relative; 
    }

    .comentario p { 
      margin:6px 0; 
      font-size:14px; 
      color: #676666;
    }
    
    .comentario small { 
      color: #959191ff; 
      font-size:13px;
    }

    .comentario.resposta {
      margin-left: 36px;
      padding-left: 12px;
      background: #f9f9f9; 
      border-left: none; 
      box-shadow: 0 2px 6px rgba(0,0,0,0.04);
      border-radius: 8px;
    }

    .comentario { 
      position: relative; 
    }

    .comentario .btn-reply {
      position: absolute;
      right: 12px;
      bottom: 12px;
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 13px;
      color: #4f4f4f !important;
      background: transparent !important;
      border: none !important;
      text-decoration: none !important;
    }
    
    .comentario .btn-reply:hover {
      color: #333333 !important;
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .details-top { flex-direction:column; }
      .anuncio-image { width:100%; height:260px; flex:none; }
      .anuncio-info { height:auto; font-size:16px; } melhor */
    }
  </style>
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
    <div class="retangulo-central" role="region" aria-label="Detalhes do anúncio">
      <button class="btn-fechar" onclick="location.href='index.php'">×</button>

      <div class="details-top" style="margin-top:8px;">
        <div class="anuncio-image" aria-label="imagem do animal">
          <img src="<?php echo htmlspecialchars($anuncio['imagem']); ?>" alt="Imagem do animal">
        </div>

        <div class="anuncio-info">
          <div class="anuncio-title-row">
            <h1><?php echo htmlspecialchars($anuncio['nomeAnimal']); ?></h1>
            <?php if ($isDono): ?>
              <button id="btn-editar-anuncio" class="btn btn-primary btn-sm btn-editar-anuncio" style="margin-left:8px;">Editar</button>
            <?php endif; ?>
          </div>

          <div style="margin-top: -5px;">
            <p class="info-text">Descrição: <?php echo nl2br(htmlspecialchars($anuncio['observacao'])); ?></p>
          </div>

          <div class="info-block">
            <h4>Informações Básicas</h4>
            <p class="info-text"><strong>Espécie:</strong> <?php echo htmlspecialchars($anuncio['especie']); ?></p>
            <p class="info-text"><strong>Sexo:</strong> <?php echo htmlspecialchars($anuncio['sexo']); ?></p>
            <p class="info-text"><strong>Raça:</strong> <?php echo htmlspecialchars($anuncio['raca']); ?></p>
            <p class="info-text"><strong>Porte:</strong> <?php echo htmlspecialchars($anuncio['porte']); ?></p>
          </div>

          <div class="info-block">
            <h4>Informações do Anunciante</h4>
            <p class="info-text"><strong>Telefone:</strong> <?php echo htmlspecialchars($anuncio['telefone']); ?></p>
          </div>

          <div class="info-block">
            <h4>Data e Local</h4>
            <p class="info-text"><strong>Endereço:</strong> <?php echo htmlspecialchars(($anuncio['rua'] ?? '') . ' ' . ($anuncio['numero'] ?? '') . ' - ' . ($anuncio['bairro'] ?? '') . ' - ' . ($anuncio['cidade'] ?? '')); ?></p>
            <p class="info-text"><strong>CEP:</strong> <?php echo htmlspecialchars($anuncio['cep'] ?? ''); ?></p>
            <p class="info-text"><strong>Reportado em:</strong> <?php echo date("d/m/Y", strtotime($anuncio['dataAnuncio'])); ?></p>
          </div>
        </div>
      </div>

      <div id="map-animal" style="width:100%;height:300px;min-height:300px;margin:18px 0;border-radius:8px;overflow:hidden;background:#eee;text-align:center;line-height:300px;"></div>
      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
      <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

      <hr>

      <section class="comentarios">
        <h2 style="color: #60b18b; border-bottom: 2px solid var(--surface-color);">Comentários</h2>

        <form action="forms/comentar.php" method="POST" class="form-comentario" id="form-comentario">
          <input type="hidden" name="id_anuncio" value="<?php echo $id; ?>">
          <input type="hidden" name="id_resposta" id="id-resposta" value="">
          <div id="replying-to" style="display:none;margin-bottom:8px;">
            <small>Respondendo a <span id="replying-to-author"></span></small>
            <button type="button" id="cancel-reply" class="btn btn-sm btn-link">Cancelar</button>
          </div>

          <textarea name="texto" placeholder="Escreva seu comentário..." required></textarea>

          <div class="mb-2" style="margin-top:8px;">
            <label for="search-local" class="form-label" style="color: #676666; font-size: 20px;">Buscar local onde viu o animal (opcional):</label>
            <input type="text" id="search-local" class="form-control" placeholder="Digite para buscar...">
            <div id="search-suggestions" class="list-group" style="position:relative;z-index:10;"></div>
          </div>

          <div id="map-comentario" style="width:100%;height:250px;min-height:200px;margin:10px 0;border-radius:8px;overflow:hidden;background:#eee;"></div>
          <input type="hidden" name="lat" id="lat-comentario">
          <input type="hidden" name="lon" id="lon-comentario">
          <small class="text-muted">Clique no mapa para marcar o local onde viu o animal (opcional).</small>

          <div class="actions-row">
            <button type="submit" class="btn-comment">Comentar</button>
          </div>
        </form>

        <?php
          if (!empty($rootComments)) {
              render_comment_tree($rootComments);
          } else {
              echo "<p>Ainda não há comentários. Seja o primeiro a comentar.</p>";
          }
        ?>
      </section>

    </div>
  </main>

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
      mapDiv.innerHTML = '<div id="map-animal-inner" style="width:100%;height:100%;"></div>';
      var map = L.map('map-animal-inner').setView([lat, lon], 15);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(map);
      L.marker([lat, lon]).addTo(map)
        .bindPopup('Local do animal ou último local visto')
        .openPopup();
    }

    function geocodeEndereco(endereco) {
      fetch('https://us1.locationiq.com/v1/search?key=pk.ef0da208da5ffacd621170b02a50736b&q=' + encodeURIComponent(endereco) + '&format=json')
        .then(function(resp){ return resp.json(); })
        .then(function(j){
          if (Array.isArray(j) && j.length > 0) {
            var lat = parseFloat(j[0].lat);
            var lon = parseFloat(j[0].lon);
            renderMap(lat, lon);
          } else {
            mapDiv.textContent = 'Não foi possível localizar o endereço informado.';
          }
        })
        .catch(function() {
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

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var mapDiv = document.getElementById('map-comentario');
    var latInput = document.getElementById('lat-comentario');
    var lonInput = document.getElementById('lon-comentario');
    var searchInput = document.getElementById('search-local');
    var suggestionsDiv = document.getElementById('search-suggestions');
    var marker = null;
    if (mapDiv) {
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
    }

    if (searchInput) {
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
                el.textContent = item.display_place || item.display_address || item.address && (item.address.road || item.address.suburb || item.address.city) || q;
                el.onclick = function() {
                  if (map) map.setView([parseFloat(item.lat), parseFloat(item.lon)], 16);
                  if (marker && map) map.removeLayer(marker);
                  if (map) marker = L.marker([parseFloat(item.lat), parseFloat(item.lon)]).addTo(map);
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
    }

    function setupReplyButtons() {
      var replyButtons = document.querySelectorAll('.btn-reply');
      var idInput = document.getElementById('id-resposta');
      var replyingTo = document.getElementById('replying-to');
      var replyingToAuthor = document.getElementById('replying-to-author');
      var cancelBtn = document.getElementById('cancel-reply');
      var textarea = document.querySelector('#form-comentario textarea[name="texto"]');

      replyButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
          var cid = btn.getAttribute('data-id');
          var author = btn.getAttribute('data-author') || '';
          if (!idInput) return;
          idInput.value = cid;
          if (replyingTo && replyingToAuthor) {
            replyingToAuthor.textContent = author;
            replyingTo.style.display = 'block';
          }
          if (textarea) textarea.focus();
          var form = document.getElementById('form-comentario');
          if (form) form.scrollIntoView({behavior:'smooth', block:'center'});
        });
      });

      if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
          if (idInput) idInput.value = '';
          if (replyingTo) replyingTo.style.display = 'none';
        });
      }
    }

    setupReplyButtons();

    document.querySelectorAll('.btn-edit-comment').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var cid = btn.getAttribute('data-id');
        var novo = prompt('Editar comentário:');
        if (!novo) return;
        fetch('forms/editar_comentario.php', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: 'idComentario=' + encodeURIComponent(cid) + '&texto=' + encodeURIComponent(novo)
        }).then(r => {
          if (r.ok) location.reload();
          else alert('Falha ao editar (endereço de edição pode não existir).');
        }).catch(()=> alert('Falha na edição.'));
      });
    });

  });
  </script>

  <script src="assets/js/main.js"></script>
  <script src="assets/js/auth.js"></script>

</body>
</html>