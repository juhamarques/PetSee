<?php
    session_start();
    if (!isset($_SESSION['idUsuario'])) {
        header('Location: entrar.html');
        exit;
    }

    include_once("conexao.php");
    $conn = abrirConexao();
    $uid = $_SESSION['idUsuario'];

    function geocode(string $address): array {
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $address,
            'format' => 'json',
            'limit' => 1
        ]);

        $opts = [
            "http" => [
                "header" => "User-Agent: PetSee"
            ]
        ];
        $context = stream_context_create($opts);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        if (!empty($data[0])) {
            return [(float)$data[0]['lat'], (float)$data[0]['lon']];
        }

        return [null, null];
    }

    $tipo     = $_POST['tipo'];
    $telefone = $_POST['telefone'];

    $sufixoMap = [
        'perdido'    => '',
        'encontrado' => '_enc',
        'adocao'     => '_ado',
    ];
    $suf = $sufixoMap[$tipo] ?? '';

    $nomeField = 'nome' . $suf;
    $nomePet   = trim($_POST[$nomeField] ?? '') ?: 'Sem nome';
    $especie   = $_POST['especie' . $suf];
    $sexo      = $_POST['sexo'    . $suf];
    $raca      = $_POST['raca'    . $suf];
    $porte     = $_POST['porte'   . $suf];
    $obs       = $_POST['detalhes' . $suf] ?? '';
    $endereco  = trim($_POST['local' . $suf] ?? '');
    $dataEvt   = $_POST['data' . $suf] ?? date('Y-m-d');

    if ($endereco === '') {
        die('Endereço não fornecido.' . $conn->error);
    }

    if (!isset($_FILES['foto' . $suf]) || $_FILES['foto' . $suf]['error'] !== UPLOAD_ERR_OK) {
        die('Erro no upload da foto.' . $conn->error);
    }

    $uploadDir = __DIR__ . '/assets/img/anuncioAnimal/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['foto' . $suf]['tmp_name'];
    $ext = pathinfo($_FILES['foto' . $suf]['name'], PATHINFO_EXTENSION);
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($ext), $extensoesPermitidas)) {
        die("Tipo de arquivo não permitido: .$ext");
    }
    $imgNome = uniqid('pet_') . '.' . $ext;
    $destino = $uploadDir . $imgNome;

    error_log("DEBUG: tmpName={$tmpName}");
    error_log("DEBUG: destino={$destino}");

    if (!is_writable($uploadDir)) {
        die("Diretório não gravável: " . $uploadDir);
    }

    if (!move_uploaded_file($tmpName, $destino)) {
        die("Falha ao mover arquivo para: $destino");
    }

    $info = getimagesize($destino);
    if ($info === false) {
        die('Erro: getimagesize falhou para ' . $destino);
    }
    list($width, $height) = $info;
    if ($width <= 0 || $height <= 0) {
        die('Erro: dimensões inválidas da imagem (' . $width . '×' . $height . ')');
    }

    $caminhoRelativo = 'assets/img/anuncioAnimal/' . $imgNome;
    $stmt = $conn -> prepare("INSERT INTO Imagens (caminho) VALUES (?)");
    $stmt -> bind_param("s", $caminhoRelativo);
    $stmt -> execute();
    $idImagem = $conn -> insert_id;
    $stmt -> close();

    $stmt = $conn->prepare("INSERT INTO Aspectos (idImagem, especie, sexo, raca, porte, observacao) VALUES (?,?,?,?,?,?)");
    $stmt -> bind_param("isssss", $idImagem, $especie, $sexo, $raca, $porte, $obs);
    $stmt -> execute();
    $idAspectos = $conn -> insert_id;
    $stmt -> close();

    $stmt = $conn->prepare("INSERT INTO Animal (idUsuario, idAspectos, nome) VALUES (?,?,?)");
    $stmt -> bind_param("iis", $uid, $idAspectos, $nomePet);
    $stmt -> execute();
    $idAnimal = $conn -> insert_id;
    $stmt -> close();

    list($lat, $lng) = geocode($endereco);
    error_log('[GEOCODE] Endereço: ' . $endereco);

    if ($lat === null || $lng === null) {
        error_log('[GEOCODE] Falha ao obter coords para: ' . $endereco);
        echo "<script>
            alert('Endereço inválido ou incompleto. Tente novamente com mais detalhes.');
            window.history.back();
        </script>";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Localidade (latitude, longitude, endereco_texto) VALUES (?,?,?)");
    $stmt -> bind_param("dds", $lat, $lng, $endereco);
    $stmt -> execute();
    $idLocal = $conn -> insert_id;
    $stmt -> close();

    $stmt = $conn->prepare("INSERT INTO Anuncio (idUsuario, idAnimal, idAspectos, idLocal, dataAnuncio, situacao, telefone) VALUES (?,?,?,?,?,?,?)");
    $stmt -> bind_param("iiiisss", $uid, $idAnimal, $idAspectos, $idLocal, $dataEvt, $tipo, $telefone);
    $stmt -> execute();
    $stmt -> close();

    fecharConexao($conn);
    header('Location: index.php');
    exit;
?>