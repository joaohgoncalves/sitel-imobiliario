<?php
$conn = new mysqli("localhost", "root", "", "imobiliaria");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Excluir
if (isset($_GET['excluir_id'])) {
    $id = $_GET['excluir_id'];
    $conn->query("DELETE FROM imoveis WHERE id = $id");
}

// Buscar imóveis
$sql = "SELECT i.id, i.titulo, i.valor, i.imagem, c.nome AS cidade
        FROM imoveis i
        JOIN cidades c ON i.cidade_id = c.id";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Imóveis</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f7fafc;
            padding: 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2d3748;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 600px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .left {
            width: 40%;
            background-color: #edf2f7;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .left img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            object-fit: cover;
        }

        .cidade {
            margin-top: 10px;
            font-size: 16px;
            color: #4a5568;
            font-weight: bold;
        }

        .right {
            width: 60%;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .titulo {
            font-size: 20px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .valor {
            font-size: 18px;
            color: #2b6cb0;
            margin-bottom: 20px;
        }

        .botoes {
            display: flex;
            gap: 10px;
        }

        .botoes a {
            padding: 10px 14px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .editar {
            background-color: #38a169;
        }

        .editar:hover {
            background-color: #2f855a;
        }

        .excluir {
            background-color: #e53e3e;
        }

        .excluir:hover {
            background-color: #c53030;
        }
    </style>
</head>
<body>

<h1>Imóveis Cadastrados</h1>

<div class="container">
<?php if ($resultado->num_rows > 0): ?>
    <?php while($row = $resultado->fetch_assoc()): ?>
        <div class="card">
            <div class="left">
                <?php if (!empty($row['imagem'])): ?>
                    <img src="<?= htmlspecialchars($row['imagem']) ?>" alt="Imagem do imóvel">
                <?php else: ?>
                    <img src="sem-imagem.png" alt="Sem imagem">
                <?php endif; ?>
                <div class="cidade"><?= htmlspecialchars($row['cidade']) ?></div>
            </div>
            <div class="right">
                <div class="titulo"><?= htmlspecialchars($row['titulo']) ?></div>
                <div class="valor">R$ <?= number_format($row['valor'], 2, ',', '.') ?></div>
                <div class="botoes">
                    <a href="editar_imovel.php?id=<?= $row['id'] ?>" class="editar">Editar</a>
                    <a href="listar_imoveis.php?excluir_id=<?= $row['id'] ?>" class="excluir" onclick="return confirm('Excluir este imóvel?')">Excluir</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center;">Nenhum imóvel cadastrado.</p>
<?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
