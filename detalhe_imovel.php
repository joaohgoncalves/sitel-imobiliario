<?php
// Estabelecendo a conexão com o banco de dados
$servername = "localhost"; // Substitua com seu servidor
$username = "root"; // Seu usuário do banco de dados
$password = ""; // Sua senha do banco de dados
$dbname = "seu_banco_de_dados"; // Substitua pelo nome do seu banco de dados

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!-- Seção de Imagens no Rodapé -->
<div class="palette">
    <?php
    // Consulta para buscar imagens (substitua pela tabela e coluna corretas)
    $sql_imagens = "SELECT imagem FROM imagens_imovel LIMIT 5"; // Limita a 5 imagens como exemplo
    $result_imagens = $conn->query($sql_imagens);

    // Verificando se há imagens para exibir
    if ($result_imagens->num_rows > 0) {
        // Loop para exibir cada imagem no rodapé
        while ($img = $result_imagens->fetch_assoc()) {
            echo '<a href="fotos/' . $img['imagem'] . '" class="color" style="background-image: url(\'fotos/' . $img['imagem'] . '\');" target="_blank"></a>';
        }
    } else {
        echo "Nenhuma imagem disponível.";
    }

    // Fechar a conexão
    $conn->close();
    ?>
</div>

<?php
// Fechar a conexão
$conn->close();
?>
