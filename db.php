<?php
$servername = "localhost"; // O nome do servidor (geralmente "localhost")
$username = "root"; // Seu nome de usuário do banco de dados (no caso do Laragon, geralmente é "root")
$password = ""; // A senha do banco de dados (no Laragon, geralmente é vazia)
$dbname = "imobiliaria"; // O nome do banco de dados que você está utilizando

// Criação da conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificação de erro na conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
