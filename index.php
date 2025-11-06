<?php
  session_start();
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
        <img  src="assets/img/logoPetSee/logoPetSeee.png" loading="lazy" alt="logoPetSee" class="imagem-logo">
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

<main class="main" style="margin-top: 60px;">
  <div class="container py-5">
    <div class="row">

      <!-- Filtro -->
      <aside class="col-lg-3 mb-4" style="margin-top: 3%;">
        <div class="filter-card p-4">
          <h4 class="mb-4">Filtro</h4>

          <form id="filter-form">
            <!-- Nome -->
            <div class="filter-group mb-4">
              <label for="filter-name" class="filter-label">Nome do Animal</label>
              <input type="text" id="filter-name" class="form-control">
            </div>

            <!-- Tipo -->
            <div class="filter-group mb-4">
              <p class="filter-label">Tipo de Animal</p>
              <div class="radio-group" id="type-group">
                <label><input type="checkbox" name="type" value="cachorro"><span>Cachorro</span></label>
                <label><input type="checkbox" name="type" value="gato"><span>Gato</span></label>
                <label><input type="checkbox" name="type" value="passaro"><span>Pássaro</span></label>
                <label><input type="checkbox" name="type" value="roedor"><span>Roedor</span></label>
              </div>
            </div>

            <!-- Sexo -->
            <div class="filter-group mb-4">
              <p class="filter-label">Sexo</p>
              <div class="radio-group" id="sex-group">
                <label><input type="checkbox" name="sex" value="macho"><span>Macho</span></label>
                <label><input type="checkbox" name="sex" value="femea"><span>Fêmea</span></label>
              </div>
            </div>

            <!-- Status -->
            <div class="filter-group mb-4">
              <p class="filter-label">Status</p>
              <div class="radio-group" id="status-group">
                <label><input type="checkbox" name="status" value="perdido"><span>Perdido</span></label>
                <label><input type="checkbox" name="status" value="resgatado"><span>Resgatado</span></label>
                <label><input type="checkbox" name="status" value="adocao"><span>Para Adoção</span></label>
                <label><input type="checkbox" name="status" value="solucionado"><span>Caso Solucionado</span></label>
              </div>
            </div>

            <!-- Distância -->
            <div class="filter-group mb-4">
              <label for="filter-distance" class="filter-label">Distância (<span id="distance-value">5</span> km)</label>
              <input type="range" id="filter-distance" class="form-range" min="1" max="20" step="1" value="5">
              <div class="form-text small mt-2">Unidade: km. Ajuste o alcance para mostrar anúncios na sua região.</div>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" value="1" id="use-my-cep">
                <label class="form-check-label small" for="use-my-cep">Usar meu CEP (em vez da localização atual)</label>
              </div>
              <div class="mt-2" id="cep-entry" style="display:none;">
                <label for="user-cep" class="form-label small">Informe seu CEP (ex: 08246-000)</label>
                <input id="user-cep" name="user-cep" type="text" class="form-control form-control-sm" placeholder="00000-000">
              </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Filtrar</button>
          </form>
        </div>
      </aside>

      <!-- Anúncios -->
      <section class="col-lg-9">
        <div class="hero-container mb-5 d-flex">
           <h1>Cuide de quem você ama!</h1>
           <?php if (isset($_SESSION['idUsuario'])): ?>
             <a href="anuncio.html" class="btn btn-anuncie">Anuncie aqui</a>
           <?php else: ?>
             <a href="entrar.html" class="btn btn-anuncie">Anuncie aqui</a>
           <?php endif; ?>
         </div>

        <div class="row g-4">
          <?php
            include_once("forms/conexao.php");
            $conn = abrirConexao();
            $userLat = $_SESSION['lat'] ?? null;
            $userLon = $_SESSION['lon'] ?? null;

            $conn->query("CREATE INDEX IF NOT EXISTS idx_anuncio_data ON Anuncio(dataAnuncio)");
            $conn->query("CREATE INDEX IF NOT EXISTS idx_animal_anuncio ON Animal(idAnimal)");

            $limit = 20;
            $result = $conn->query("SELECT A.idAnuncio, A.situacao, A.dataAnuncio, A.telefone, A.cep,
                                         A.bairro, A.cidade,
                                         AN.nome, IM.caminho, ASP.especie, ASP.sexo, ASP.raca, ASP.porte,
                                         ASP.observacao
                                  FROM Anuncio A
                                  JOIN Animal AN ON A.idAnimal = AN.idAnimal
                                  JOIN Aspectos ASP ON A.idAspectos = ASP.idAspectos
                                  JOIN Imagens IM ON ASP.idImagem = IM.idImagem
                                  ORDER BY A.dataAnuncio DESC
                                  LIMIT $limit");

            $cacheFile = __DIR__ . '/assets/cache/cep_coords.json';
            $cepCache = [];
            if (file_exists($cacheFile)) {
                $cepCache = json_decode(file_get_contents($cacheFile), true) ?: [];
            }

            function normalizarSituacao(?string $s): string {
              $s = (string)$s;

              $s = preg_replace('/\p{C}+/u', '', $s);     
              $s = preg_replace('/\s+/u', ' ', $s);
              $s = trim($s);

              if (function_exists('mb_strtolower')) {
                $s = mb_strtolower($s, 'UTF-8');
              } else {
                $s = strtolower($s);
              }

              if (function_exists('transliterator_transliterate')) {
                $s = transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC', $s);
              } else {
                $map = ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
                        'é'=>'e','ê'=>'e','è'=>'e','ë'=>'e',
                        'í'=>'i','ì'=>'i','ï'=>'i',
                        'ó'=>'o','ô'=>'o','õ'=>'o','ò'=>'o','ö'=>'o',
                        'ú'=>'u','ù'=>'u','ü'=>'u',
                        'ç'=>'c'];
                $s = strtr($s, $map);
              }

              $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s);
              return trim($s);
            }

            while ($row = $result->fetch_assoc()) {
              $situacaoRaw  = (string)($row['situacao'] ?? '');
              $situacaoNorm  = normalizarSituacao($situacaoRaw);

              if ($situacaoNorm === '') {
                $map = 'unknown';
              } elseif (str_contains($situacaoNorm, 'perd')) {
                $map = 'perdido';
              } elseif (str_contains($situacaoNorm, 'resgat')) {
                $map = 'resgatado';
              } elseif (str_contains($situacaoNorm, 'encontr') || str_contains($situacaoNorm, 'resol') ) {
                $map = 'solucionado';
              } elseif (str_contains($situacaoNorm, 'adoc')) {
                $map = 'adocao';
              } else {
                $map = 'unknown';
              }

              $statusClass = match ($map) {
                'perdido'    => 'status-lost',
                'resgatado'  => 'status-resgatado',
                'solucionado' => 'status-solucionado',
                'adocao'     => 'status-adocao',
                default      => 'status-unknown'
              };

              $labelSituacao = match ($map) {
                'perdido'    => 'Perdido',
                'resgatado'  => 'Resgatado',
                'solucionado' => 'Caso Solucionado',
                'adocao'     => 'Adoção',
                default      => ($situacaoRaw !== '' ? ucfirst($situacaoRaw) : 'Indefinido')
              };

              $cep = $row['cep'] ?? null;
              $latAttr = '';
              $lonAttr = '';
              $displayLocal = '';
              if (!empty($cep)) {
                $displayLocal = htmlspecialchars($cep, ENT_QUOTES, 'UTF-8');
                if (!empty($cepCache[$cep]['lat']) && !empty($cepCache[$cep]['lon'])) {
                  $latAttr = ' data-lat="' . htmlspecialchars($cepCache[$cep]['lat'], ENT_QUOTES, 'UTF-8') . '"';
                  $lonAttr = ' data-lon="' . htmlspecialchars($cepCache[$cep]['lon'], ENT_QUOTES, 'UTF-8') . '"';
                } else {
                  $query = $cep . ', Brasil';
                  $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1
                  ]);
                  
                  $opts = [
                      "http" => [
                          "header" => "User-Agent: PetSee/1.0",
                          "timeout" => 2.0
                      ]
                  ];
                  
                  $context = stream_context_create($opts);
                  $resp = @file_get_contents($url, false, $context);
                  
                  if ($resp !== false) {
                    $d = json_decode($resp, true);
                    if (!empty($d[0]['lat']) && !empty($d[0]['lon'])) {
                      $cepCache[$cep] = [
                        'lat' => (float)$d[0]['lat'],
                        'lon' => (float)$d[0]['lon'],
                        'ts' => time()
                      ];
                      file_put_contents($cacheFile, json_encode($cepCache));
                      $latAttr = ' data-lat="' . htmlspecialchars($d[0]['lat'], ENT_QUOTES, 'UTF-8') . '"';
                      $lonAttr = ' data-lon="' . htmlspecialchars($d[0]['lon'], ENT_QUOTES, 'UTF-8') . '"';
                    }
                  }
                }
              }

              echo '<div class="col-md-4" data-ad-container>';
              $dataStatusAttr = ' data-status="' . htmlspecialchars($map, ENT_QUOTES, 'UTF-8') . '"';
              echo '<a href="detalhesAnuncio.php?id=' . $row['idAnuncio'] . '" class="card-link">';
              echo '<div class="ad-card"' . $latAttr . $lonAttr . $dataStatusAttr . '>';
              echo '<div class="status-bar ' . htmlspecialchars($map, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($labelSituacao, ENT_QUOTES, 'UTF-8') . '</div>';
              echo '  <div class="ad-image-container">';
              echo '    <span class="ad-status ' . $statusClass . '" role="status">' . htmlspecialchars($labelSituacao, ENT_QUOTES, 'UTF-8') . '</span>';
              echo '    <img src="' . htmlspecialchars($row['caminho']) . '" alt="' . htmlspecialchars($row['nome']) . '">';
              echo '  </div>';
              echo '  <div class="ad-info">';
              echo '    <h5 class="ad-name">' . htmlspecialchars($row['nome']) . '</h5>';
              echo '    <p class="ad-location">' . htmlspecialchars(($row['bairro'] ?? '') . ', ' . ($row['cidade'] ?? '')) . '</p>';
              echo '  </div>';
              echo '</div>';
              echo '</a>';
              echo '</div>';
            }

            fecharConexao($conn);
          ?>
        </div>
      </section>
    </div>
  </div>
</main>

  <footer id="footer" class="footer">
    <div class="team section light-background">
      <div class="container footer-top">
        <div class="row gy-4">
          <div class="col-lg-5 col-md-12 footer-about">
            <a href="index.php" class="logo d-flex align-items-center">
              <span class="sitename">PetSee</span>
            </a>
            <p>Plataforma que auxilia tutores a encontrarem seus pets desaparecidos. Também conta com uma aba de dados sobre cuidados animais, e divulga campanhas de vacinação, adoção e castração gratuita ou de baixo custo. Além disso, fornece a localização e informações de hospitais veterinários públicos e ONGs</p>
            <div class="social-links d-flex mt-4">
              <a href="https://www.instagram.com/ifspsmp/">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/></svg>
              </a>
              <a href="https://www.facebook.com/smp.ifsp/?locale=pt_BR">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/></svg>
              </a>
              <a href="https://br.linkedin.com/school/ifspoficial/">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/></svg>
              </a>
            </div>
          </div>

          <div class="col-lg-2 col-6 footer-links">
            <h4>Links Úteis</h4>
            <ul>
              <li><a href="#">Início</a></li>
              <li><a href="cachorros.html">Cuidados</a></li>
              <li><a href="serviços.php">Serviços</a></li>
              <li><a href="empresa.html">Empresa</a></li>
              <li><a href="contato.html">Contato</a></li>
            </ul>
          </div>

          <div class="col-lg-2 col-6 footer-links">
            <h4>Nossos Serviços</h4>
            <ul>
              <li><a href="#">Design Web</a></li>
              <li><a href="#">Desenvolvimento Web</a></li>
              <li><a href="#">Gerenciamento de Produtos</a></li>
              <li><a href="#">Marketing</a></li>
              <li><a href="#">Design Gráfico</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-12 footer-contact text-center text-md-start">
            <h4>Contate-nos</h4>
            <p>R. Ten. Miguel Delia, 105 -</p>
            <p>São Miguel, Paulista - SP,</p>
            <p>08021-090</p>
            <p class="mt-4"><strong>Telefone:</strong> <span>+55 (11) 2032-5389</span></p>
            <p><strong>Email:</strong> <span>petsee.contato@gmail.com</span></p>
          </div>

        </div>
      </div>

      <div class="container copyright text-center mt-4">
        <p>© <span>Copyright</span> <strong class="px-1 sitename">PetSee</strong> <span>Todos Direitos Reservados</span></p>
      </div>
    </div>
  </footer>

  <script>
    window.USER_LAT = <?php echo is_numeric($userLat) ? json_encode((float)$userLat) : 'null'; ?>;
    window.USER_LON = <?php echo is_numeric($userLon) ? json_encode((float)$userLon) : 'null'; ?>;
    window.USER_CEP = <?php echo json_encode($_SESSION['CEP'] ?? ''); ?>;
  </script>

  <script>
    if (window.USER_LAT === null || window.USER_LON === null) {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
          window.USER_LAT = pos.coords.latitude;
          window.USER_LON = pos.coords.longitude;
        });
      }
    }
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
  <script src="assets/js/index.js"></script>

  <script>
  function geocodeCepLocationIQ(cep, callback) {
    fetch(`https://us1.locationiq.com/v1/search?key=pk.ef0da208da5ffacd621170b02a50736b&q=${encodeURIComponent(cep + ', Brasil')}&format=json`)
      .then(resp => resp.json())
      .then(j => {
        if (Array.isArray(j) && j.length > 0) {
          var lat = parseFloat(j[0].lat);
          var lon = parseFloat(j[0].lon);
          callback(lat, lon);
        } else {
          callback(null, null);
        }
      })
      .catch(() => {
        callback(null, null);
      });
  }
  window.geocodeCepLocationIQ = geocodeCepLocationIQ;
  </script>

</body>
</html>