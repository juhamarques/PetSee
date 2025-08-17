<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
    $response = ['isLoggedIn' => true];
} else {
    $response = ['isLoggedIn' => false];
}
echo json_encode($response);
?>