<?php
    include_once ("conexao.php");

    function sanitize_digits($value) {
        return preg_replace('/\D/', '', $value);
    }
    function isValidCPF($cpf) {
        $cpf = sanitize_digits($cpf);
        if (strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $rest = $sum % 11;
        $digit1 = ($rest < 2) ? 0 : 11 - $rest;
        if (intval($cpf[9]) !== $digit1) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $rest = $sum % 11;
        $digit2 = ($rest < 2) ? 0 : 11 - $rest;
        if (intval($cpf[10]) !== $digit2) {
            return false;
        }

        return true;
    }

    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    function isValidCNPJ($cnpj) {
        $cnpj = sanitize_digits($cnpj);
        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weights1 = [5,4,3,2,9,8,7,6,5,4,3,2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights1[$i];
        }
        $rest = $sum % 11;
        $digit1 = ($rest < 2) ? 0 : 11 - $rest;
        if (intval($cnpj[12]) !== $digit1) {
            return false;
        }
        $weights2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights2[$i];
        }
        $rest = $sum % 11;
        $digit2 = ($rest < 2) ? 0 : 11 - $rest;
        if (intval($cnpj[13]) !== $digit2) {
            return false;
        }

        return true;
    }

    function isValidCEP($cep, $checkOnline = false, $failOpen = true) {
        $cep = sanitize_digits($cep);
        if (strlen($cep) !== 8) {
            return false;
        }

        if ($checkOnline) {
            $url = 'https://viacep.com.br/ws/' . $cep . '/json/';

            $json = false;
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                $json = @curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_errno($ch);
                curl_close($ch);
                if ($json === false || $err !== 0 || $httpCode >= 400) {
                    $json = false;
                }
            }

            if ($json === false && ini_get('allow_url_fopen')) {
                $context = stream_context_create(['http' => ['timeout' => 3]]);
                $json = @file_get_contents($url, false, $context);
            }

            if ($json === false) {
                return $failOpen ? true : false;
            }

            $data = json_decode($json, true);
            if (!is_array($data) || isset($data['erro'])) {
                return false;
            }
        }

        return true;
    }

    function existsEmail($conn, $email) {
        $sql = "SELECT 1 FROM Usuario WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $exists = ($res && $res->fetch_assoc()) ? true : false;
            $stmt->close();
            if ($exists) return true;
        }

        $sql2 = "SELECT 1 FROM Comercial WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) LIMIT 1";
        if ($stmt2 = $conn->prepare($sql2)) {
            $stmt2->bind_param('s', $email);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $exists2 = ($res2 && $res2->fetch_assoc()) ? true : false;
            $stmt2->close();
            return $exists2;
        }

        $e = $conn->real_escape_string(mb_strtolower(trim($email)));
        $q1 = "SELECT 1 FROM Usuario WHERE LOWER(TRIM(email)) = '" . $e . "' LIMIT 1";
        $r1 = $conn->query($q1);
        if ($r1 && $r1->fetch_assoc()) return true;
        $q2 = "SELECT 1 FROM Comercial WHERE LOWER(TRIM(email)) = '" . $e . "' LIMIT 1";
        $r2 = $conn->query($q2);
        return ($r2 && $r2->fetch_assoc()) ? true : false;
    }
    function existsCPF($conn, $cpf_digits) {
        $sql = "SELECT 1 FROM Usuario WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $cpf_digits);
            $stmt->execute();
            $res = $stmt->get_result();
            $exists = ($res && $res->fetch_assoc()) ? true : false;
            $stmt->close();
            return $exists;
        }

        $c = $conn->real_escape_string($cpf_digits);
        $q = "SELECT 1 FROM Usuario WHERE REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = '" . $c . "' LIMIT 1";
        $r = $conn->query($q);
        return ($r && $r->fetch_assoc()) ? true : false;
    }

    function getComercialSenhaMaxLen($conn) {
        $q = "SHOW COLUMNS FROM Comercial LIKE 'senha'";
        if ($res = $conn->query($q)) {
            if ($row = $res->fetch_assoc()) {
                if (preg_match('/varchar\((\d+)\)/i', $row['Type'], $m)) {
                    return intval($m[1]);
                }
            }
        }
        return null;
    }

    function checkPasswordStrength($senha) {
        if (strlen($senha) < 8) return false;
        if (!preg_match('/[a-z]/', $senha)) return false;
        if (!preg_match('/[A-Z]/', $senha)) return false;
        if (!preg_match('/\d/', $senha)) return false;
        return true;
    }
    function checkEmailExists($email, $failOpen = true) {
        $email = trim($email);
        if (!isValidEmail($email)) return false;

        $parts = explode('@', $email);
        if (count($parts) !== 2) return $failOpen ? true : false;
        $domain = $parts[1];

        $mxHosts = [];
        if (function_exists('getmxrr')) {
            @getmxrr($domain, $mxHosts, $mxWeights);
        }
        if (empty($mxHosts) && function_exists('dns_get_record')) {
            $records = dns_get_record($domain, DNS_MX);
            if (!empty($records)) {
                foreach ($records as $r) {
                    if (!empty($r['target'])) $mxHosts[] = $r['target'];
                }
            }
        }

        if (empty($mxHosts)) {
            $mxHosts[] = $domain;
        }

        foreach ($mxHosts as $mx) {
            $mx = rtrim($mx, '.');
            $conn = @fsockopen($mx, 25, $errno, $errstr, 5);
            if (!$conn) continue;
            stream_set_timeout($conn, 5);
            $res = fgets($conn);
            fputs($conn, "HELO localhost\r\n");
            $res = fgets($conn);
            fputs($conn, "MAIL FROM:<no-reply@localhost>\r\n");
            $res = fgets($conn);
            fputs($conn, "RCPT TO:<" . $email . ">\r\n");
            $res = fgets($conn);
            fclose($conn);
            if (preg_match('/^2\d\d /', $res)) {
                return true;
            } elseif (preg_match('/^5\d\d /', $res) || preg_match('/^4\d\d /', $res)) {
                return false;
            }
        }

        return $failOpen ? true : false;
    }
    function formatCPF($digits) {
        $d = sanitize_digits($digits);
        if (strlen($d) !== 11) return $digits;
        return substr($d,0,3) . '.' . substr($d,3,3) . '.' . substr($d,6,3) . '-' . substr($d,9,2);
    }

    function formatCEP($digits) {
        $d = sanitize_digits($digits);
        if (strlen($d) !== 8) return $digits;
        return substr($d,0,5) . '-' . substr($d,5,3);
    }

    function formatCNPJ($digits) {
        $d = sanitize_digits($digits);
        if (strlen($d) !== 14) return $digits;
        return substr($d,0,2) . '.' . substr($d,2,3) . '.' . substr($d,5,3) . '/' . substr($d,8,4) . '-' . substr($d,12,2);
    }


    function login($email, $senha){
        $sql = "SELECT idUsuario, senha FROM Usuario WHERE email = ?";
        $conn = abrirConexao();

        if ($conn -> connect_error){
            return false;
        }else{
            $stmt = $conn -> prepare($sql);
            $stmt -> bind_param("s", $email);
            $stmt -> execute();

            $result = $stmt -> get_result();

            if ($row = $result -> fetch_assoc()) {
                $stored = $row['senha'];
                $idUsuario = $row['idUsuario'];

                if (password_verify($senha, $stored)) {
                } elseif ($senha === $stored) {
                    $newHash = password_hash($senha, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE Usuario SET senha = ? WHERE idUsuario = ?");
                    if ($upd) {
                        $upd->bind_param('si', $newHash, $idUsuario);
                        $upd->execute();
                        $upd->close();
                    }
                } else {
                    $idUsuario = false;
                }
            } else {
                $idUsuario = false;
            }
            fecharConexao($conn);
            return $idUsuario;
        }
    }

    function cadastroPessoal($cpf, $nome, $nascimento, $cep, $email, $senha, $tipo_usuario, $idImagem = 1){
         $conn = abrirConexao();
         if ($conn->connect_error){
            return false;
         }else{
            $cpf_digits = sanitize_digits($cpf);

            $emailNorm = trim($email);
            if (existsCPF($conn, $cpf_digits)) {
                fecharConexao($conn);
                return false;
            }

            if (existsEmail($conn, $emailNorm)) {
                fecharConexao($conn);
                return false;
            }

            $hashed = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Usuario(idImagem, cpf, nome, nascimento, CEP, email, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt -> bind_param("isssssss", $idImagem, $cpf, $nome, $nascimento, $cep, $email, $hashed, $tipo_usuario);

            if ($stmt -> execute()){
                fecharConexao($conn);
                return true;
            }else{
                fecharConexao($conn);
                return false;
            }
         }
    }
    function cadastroEndereco($idBairro, $logradouro, $nome, $numero, $cep) {
        $conn = abrirConexao();
        if ($conn->connect_error) {
            return false;
        } else {
            $stmt = $conn->prepare("INSERT INTO Endereco(idBairro, logradouro, nome, numero, CEP) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issis", $idBairro, $logradouro, $nome, $numero, $cep);
            if ($stmt->execute()) {
                $idEndereco = $conn->insert_id;
                fecharConexao($conn);
                return $idEndereco;
            } else {
                fecharConexao($conn);
                return false;
            }
        }
    }

    function cadastroComercial($nome_empresa, $email_empresa, $cnpj, $telefone, $idEndereco, $senha, $idImagem = 1){
        $conn = abrirConexao();
        if ($conn -> connect_error) {
            return false;
        } else {
            if (!isValidCNPJ($cnpj)) {
                return false;
            }

            $emailNorm = trim($email_empresa);
            if (existsEmail($conn, $emailNorm)) {
                fecharConexao($conn);
                return false;
            }

            $cnpj_digits = sanitize_digits($cnpj);
            $checkCNPJ = $conn->prepare("SELECT 1 FROM Comercial WHERE REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '/', ''), '-', ''), ' ', '') = ? LIMIT 1");
            if ($checkCNPJ) {
                $checkCNPJ->bind_param('s', $cnpj_digits);
                $checkCNPJ->execute();
                $resc = $checkCNPJ->get_result();
                if ($resc && $resc->fetch_assoc()) {
                    fecharConexao($conn);
                    return false;
                }
                $checkCNPJ->close();
            }

            $hashed = password_hash($senha, PASSWORD_DEFAULT);

            $maxLen = getComercialSenhaMaxLen($conn);
            if ($maxLen !== null && $maxLen < strlen($hashed)) {;
                $conn->rollback();
                fecharConexao($conn);
                return false;
            }
            $tipo_usuario = 'comercial';
            $conn->begin_transaction();
            $stmt_usuario = $conn-> prepare("INSERT INTO Usuario(idImagem, email, senha, tipo, cpf, nome, nascimento, CEP) VALUES (?, ?, ?, ?, '', '', '2000-01-01', '')");
            $stmt_usuario -> bind_param("isss", $idImagem, $emailNorm, $hashed, $tipo_usuario);
            if (!$stmt_usuario->execute()) {
                $conn->rollback();
                fecharConexao($conn);
                return false;
            }
            $idUsuario = $conn -> insert_id;
            
            if ($idUsuario) {
                $stmt_comercial = $conn -> prepare("INSERT INTO Comercial(idUsuario, idEndereco, cnpj, nome, categoria, email, senha, telefone) VALUES (?, ?, ?, ?, '', ?, ?, ?)");
                $stmt_comercial -> bind_param("iisssss", $idUsuario, $idEndereco, $cnpj, $nome_empresa, $emailNorm, $hashed, $telefone);

                if ($stmt_comercial -> execute()) {
                    $conn->commit();
                    fecharConexao($conn);
                    return true;
                } else {
                    $conn->rollback();
                    fecharConexao($conn);
                    return false;
                }
            } else {
                $conn->rollback();
                fecharConexao($conn);
                return false;
            }
        }
    }
?>