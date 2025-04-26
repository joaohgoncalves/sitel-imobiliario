<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conectar ao banco de dados
    $conn = new mysqli("localhost", "root", "", "imobiliaria");

    if ($conn->connect_error) {
        die("Erro na conexão com o banco: " . $conn->connect_error);
    }

    // Obtendo dados do formulário
    $imovel_id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $tipo_id = $_POST['tipo_id'];
    $valor = $_POST['valor'];
    $cidade_id = $_POST['cidade_id'];
    $bairro = $_POST['bairro'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $area_total = $_POST['area_total'];
    $quartos = $_POST['quartos'];
    $banheiros = $_POST['banheiros'];
    $vagas_garagem = $_POST['vagas_garagem'];

    // Atualizar dados do imóvel
    $sql = "UPDATE imoveis SET
        titulo = ?, descricao = ?, tipo_id = ?, valor = ?, cidade_id = ?, bairro = ?, endereco = ?, numero = ?, area_total = ?, quartos = ?, banheiros = ?, vagas_garagem = ?
        WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssiddssssiiii", $titulo, $descricao, $tipo_id, $valor, $cidade_id, $bairro, $endereco, $numero, $area_total, $quartos, $banheiros, $vagas_garagem, $imovel_id);

        if ($stmt->execute()) {
            $mensagem = "✅ Imóvel atualizado com sucesso!";
        } else {
            $mensagem = "❌ Erro ao atualizar o imóvel.";
        }

        $stmt->close();
    } else {
        $mensagem = "❌ Erro ao preparar a consulta.";
    }

    // Fechar a conexão com o banco
    $conn->close();
} else {
    // Conectar ao banco de dados
    $conn = new mysqli("localhost", "root", "", "imobiliaria");

    if ($conn->connect_error) {
        die("Erro na conexão com o banco: " . $conn->connect_error);
    }

    // Buscar dados do imóvel
    $imovel_id = $_GET['id'];
    $sql = "SELECT * FROM imoveis WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $imovel_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $imovel = $result->fetch_assoc();
        } else {
            $mensagem = "❌ Imóvel não encontrado.";
        }

        $stmt->close();
    } else {
        $mensagem = "❌ Erro ao preparar a consulta.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Imóvel</title>
    <style>
        /* Use o mesmo estilo do painel de administração */
    </style>
</head>
<body>
    <h2>Editar Imóvel</h2>

    <?php if ($mensagem): ?>
        <div class="mensagem <?= strpos($mensagem, 'sucesso') !== false ? 'success' : 'error' ?>">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $imovel['id'] ?>">

        <label>Título</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($imovel['titulo']) ?>" required>

        <label>Descrição</label>
        <textarea name="descricao"><?= htmlspecialchars($imovel['descricao']) ?></textarea>

        <label>Tipo</label>
        <select name="tipo_id" required>
            <option value="">Selecione</option>
            <?php
            // Buscar tipos de imóveis
            $conn = new mysqli("localhost", "root", "", "imobiliaria");
            $sql = "SELECT id, nome FROM tipos_imovel";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'" . ($row['id'] == $imovel['tipo_id'] ? ' selected' : '') . ">" . $row['nome'] . "</option>";
            }
            $conn->close();
            ?>
        </select>

        <label>Valor (R$)</label>
        <input type="number" step="0.01" name="valor" value="<?= $imovel['valor'] ?>" required>

        <label>Cidade</label>
        <input type="text" name="cidade" value="<?= $imovel['cidade'] ?>" required>
        <!-- Adicionar lista de cidades, como na parte de cadastro de imóvel -->

        <label>Bairro</label>
        <input type="text" name="bairro" value="<?= $imovel['bairro'] ?>">

        <label>Endereço</label>
        <input type="text" name="endereco" value="<?= $imovel['endereco'] ?>">

        <label>Número</label>
        <input type="text" name="numero" value="<?= $imovel['numero'] ?>">

        <label>Área Total (m²)</label>
        <input type="number" step="0.01" name="area_total" value="<?= $imovel['area_total'] ?>" required>

        <label>Quartos</label>
        <input type="number" name="quartos" value="<?= $imovel['quartos'] ?>" required>

        <label>Banheiros</label>
        <input type="number" name="banheiros" value="<?= $imovel['banheiros'] ?>" required>

        <label>Vagas de Garagem</label>
        <input type="number" name="vagas_garagem" value="<?= $imovel['vagas_garagem'] ?>" required>

        <button type="submit">Salvar Alterações</button>
    </form>

    <a href="admin.php" class="btn">Voltar ao Painel de Administração</a>
</body>
</html>
