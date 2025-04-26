<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "imobiliaria");

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Receber o termo de pesquisa (nome da cidade)
$termo = $_GET['termo'];

// Consultar cidades que começam com o termo inserido
$sql = "SELECT id, nome FROM cidades WHERE nome LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$termo_completo = '%' . $termo . '%';
$stmt->bind_param("s", $termo_completo);

// Executar a consulta
$stmt->execute();
$result = $stmt->get_result();

// Preparar o retorno como JSON
$cidades = [];
while ($row = $result->fetch_assoc()) {
    $cidades[] = $row;
}

// Retornar os resultados em JSON
echo json_encode($cidades);

// Fechar a conexão
$stmt->close();
$conn->close();
?>
