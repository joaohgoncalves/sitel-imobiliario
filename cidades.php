<?php
$conn = new mysqli("localhost", "root", "", "imobiliaria");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$termo = $_GET['q'] ?? '';
$termo = $conn->real_escape_string($termo);

$sql = "SELECT id, nome FROM cidades WHERE nome LIKE '%$termo%' LIMIT 10";
$result = $conn->query($sql);

$cidades = [];
while ($row = $result->fetch_assoc()) {
    $cidades[] = ['id' => $row['id'], 'nome' => $row['nome']];
}

header('Content-Type: application/json');
echo json_encode($cidades);
?>
