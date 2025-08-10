<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['userId']) && !empty($_SESSION['userId'])) {
    $response = ['isLoggedIn' => true];
} else {
    $response = ['isLoggedIn' => false];
}
echo json_encode($response);
?>