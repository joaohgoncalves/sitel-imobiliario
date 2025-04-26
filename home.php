<?php
$conn = new mysqli("localhost", "root", "", "imobiliaria");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$where = [];
$params = [];

// Filtros
$filtros_ativos = false;

if (!empty($_GET['tipo'])) {
    $where[] = 'i.tipo_id = ?';
    $params[] = intval($_GET['tipo']);
    $filtros_ativos = true;
}

if (!empty($_GET['cidade'])) {
    $where[] = 'i.cidade_id = ?';
    $params[] = intval($_GET['cidade']);
    $filtros_ativos = true;
}

if (!empty($_GET['valor'])) {
    $where[] = 'i.valor <= ?';
    $params[] = floatval($_GET['valor']);
    $filtros_ativos = true;
}

if (!empty($_GET['valor_min'])) {
    $where[] = 'i.valor >= ?';
    $params[] = floatval($_GET['valor_min']);
    $filtros_ativos = true;
}

if (!empty($_GET['finalidade'])) {
    $where[] = 'i.finalidade = ?';
    $params[] = $conn->real_escape_string($_GET['finalidade']);
    $filtros_ativos = true;
}

$whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Query base
$sql = "
    SELECT i.id AS imovel_id, i.titulo, i.descricao, i.valor, 
           t.nome AS tipo, c.nome AS cidade, img.imagem, i.finalidade
    FROM imoveis i
    LEFT JOIN tipos t ON i.tipo_id = t.id
    LEFT JOIN cidades c ON i.cidade_id = c.id
    LEFT JOIN imagens_imovel img ON i.id = img.imovel_id
    $whereSQL
    ORDER BY i.id DESC
    LIMIT 30
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Erro ao preparar a consulta: " . $conn->error);
}

$imoveis = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['imovel_id'];
    if (!isset($imoveis[$id])) {
        $imoveis[$id] = [
            'titulo' => $row['titulo'],
            'descricao' => $row['descricao'],
            'valor' => $row['valor'],
            'tipo' => $row['tipo'],
            'cidade' => $row['cidade'],
            'finalidade' => $row['finalidade'],
            'imagens' => [],
        ];
    }

    if (!empty($row['imagem'])) {
        $imoveis[$id]['imagens'][] = 'fotos/' . $row['imagem'];
    }
}

$tipos_result = $conn->query("SELECT id, nome FROM tipos");
$cidades_result = $conn->query("SELECT id, nome FROM cidades");
$finalidades = ['venda' => 'Venda', 'aluguel' => 'Aluguel'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Imóveis - Celia Carnel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #cfcfcf;
            margin: 0;
            padding: 0;
        }

        header {
            background: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c') no-repeat center center/cover;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
        }

        header h1 {
            font-size: 3rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .filtro {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 40px;
        }

        .filtro select, .filtro input {
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 220px;
        }

        .filtro button {
            padding: 12px 30px;
            background-color: #0077cc;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filtro button:hover {
            background-color: #005fa3;
        }

        .titulo {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2D3748;
            margin-bottom: 20px;
        }

        .imoveis {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .imoveis.horizontal {
            grid-template-columns: repeat(3, 1fr);
        }

        .imovel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .imovel:hover {
            transform: translateY(-5px);
        }

        .imagens {
            display: flex;
            overflow-x: auto;
            gap: 10px;
        }

        .imagens img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            flex: 0 0 auto;
        }

        .imovel-info {
            padding: 20px;
        }

        .imovel h3 {
            font-size: 1.2rem;
            color: #2D3748;
            margin-bottom: 10px;
        }

        .imovel p {
            color: #4A5568;
            font-size: 0.95rem;
            margin-bottom: 6px;
        }

        .valor {
            font-weight: bold;
            color: #2f855a;
            margin-top: 10px;
            font-size: 1rem;
        }

        .no-results {
            text-align: center;
            font-size: 1.2rem;
            color: #555;
            margin-top: 40px;
        }
        
        footer {
    background-color: #2D3748;
    color: white;
    padding: 20px 0;
    text-align: center;
    margin-top: 40px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.footer-info p {
    font-size: 0.9rem;
    margin: 0;
}

.footer-links {
    display: flex;
    gap: 20px;
}

.footer-links a {
    color: white;
    text-decoration: none;
    font-size: 1rem;
}

.footer-links a:hover {
    text-decoration: underline;
}
.footerline {
    border-top: 2px solid #333;
    margin-top: 40px;
    width: 100%;
}
footer {
    background-color: #2d3748;
    color: white;
    padding: 20px;
    text-align: center;
    position: relative;
}

.admin-access-footer {
    background-color: #0077cc;
    padding: 10px 20px;
    border-radius: 6px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    display: inline-block;
    margin-top: 10px;
}

.admin-access-footer a {
    color: white;
    font-weight: 600;
    text-decoration: none;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

.admin-access-footer a:hover {
    background-color: #005fa3;
}


        
    </style>
</head>
<body>

<header>
    <h1>Imóveis à Venda</h1>
</header>

<div class="container">

    <form class="filtro" method="GET" action="">
        <select name="tipo">
            <option value="">Tipo</option>
            <?php while ($tipo = $tipos_result->fetch_assoc()): ?>
                <option value="<?= $tipo['id'] ?>" <?= (isset($_GET['tipo']) && $_GET['tipo'] == $tipo['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tipo['nome']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="cidade">
            <option value="">Cidade</option>
            <?php while ($cidade = $cidades_result->fetch_assoc()): ?>
                <option value="<?= $cidade['id'] ?>" <?= (isset($_GET['cidade']) && $_GET['cidade'] == $cidade['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cidade['nome']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input type="number" name="valor_min" placeholder="Valor mínimo (R$)" value="<?= isset($_GET['valor_min']) ? htmlspecialchars($_GET['valor_min']) : '' ?>">

        <input type="number" name="valor" placeholder="Valor máximo (R$)" value="<?= isset($_GET['valor']) ? htmlspecialchars($_GET['valor']) : '' ?>">

        <select name="finalidade">
            <option value="">Finalidade</option>
            <?php foreach ($finalidades as $key => $value): ?>
                <option value="<?= $key ?>" <?= (isset($_GET['finalidade']) && $_GET['finalidade'] == $key) ? 'selected' : '' ?>>
                    <?= $value ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filtrar</button>
    </form>

    <?php if ($filtros_ativos): ?>
        <div class="imoveis">
            <?php foreach ($imoveis as $imovel): ?>
                <div class="imovel">
                    <div class="imagens">
                        <?php if (!empty($imovel['imagens'])): ?>
                            <?php foreach ($imovel['imagens'] as $img): ?>
                                <img src="<?= $img ?>" alt="Imagem do Imóvel">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <img src="https://via.placeholder.com/280x180?text=Sem+Imagem" alt="Sem imagem">
                        <?php endif; ?>
                    </div>
                    <div class="imovel-info">
                        <h3><?= htmlspecialchars($imovel['titulo']) ?></h3>
                        <p><strong>Descrição:</strong> <?= htmlspecialchars($imovel['descricao']) ?></p>
                        <p><strong>Tipo:</strong> <?= htmlspecialchars($imovel['tipo']) ?></p>
                        <p><strong>Cidade:</strong> <?= htmlspecialchars($imovel['cidade']) ?></p>
                        <p><strong>Finalidade:</strong> <?= htmlspecialchars($imovel['finalidade']) ?></p>
                        <p class="valor">R$ <?= number_format($imovel['valor'], 2, ',', '.') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php foreach (['aluguel' => 'Locação', 'venda' => 'Venda'] as $tipo_finalidade => $titulo): ?>
            <div class="titulo">Destaques de <?= $titulo ?></div>

            <?php
            $filtro_finalidade = array_filter($imoveis, fn($i) => $i['finalidade'] == $tipo_finalidade);
            $destacados = array_slice($filtro_finalidade, 0, 9);
            ?>

            <div class="imoveis horizontal">
                <?php foreach (array_slice($destacados, 0, 3) as $imovel): ?>
                    <div class="imovel">
                        <div class="imagens">
                            <?php foreach ($imovel['imagens'] ?: ['https://via.placeholder.com/280x180?text=Sem+Imagem'] as $img): ?>
                                <img src="<?= $img ?>" alt="Imagem do Imóvel">
                            <?php endforeach; ?>
                        </div>
                        <div class="imovel-info">
                            <h3><?= htmlspecialchars($imovel['titulo']) ?></h3>
                            <p><strong>Descrição:</strong> <?= htmlspecialchars($imovel['descricao']) ?></p>
                            <p><strong>Tipo:</strong> <?= htmlspecialchars($imovel['tipo']) ?></p>
                            <p><strong>Cidade:</strong> <?= htmlspecialchars($imovel['cidade']) ?></p>
                            <p><strong>Finalidade:</strong> <?= htmlspecialchars($imovel['finalidade']) ?></p>
                            <p class="valor">R$ <?= number_format($imovel['valor'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($imoveis)): ?>
        <div class="no-results">Nenhum imóvel encontrado.</div>
    <?php endif; ?> 

            <div class="imoveis">
                <?php foreach (array_slice($destacados, 3) as $imovel): ?>
                    <div class="imovel">
                        <div class="imagens">
                            <?php foreach ($imovel['imagens'] ?: ['https://via.placeholder.com/280x180?text=Sem+Imagem'] as $img): ?>
                                <img src="<?= $img ?>" alt="Imagem do Imóvel">
                            <?php endforeach; ?>
                        </div>
                        <div class="imovel-info">
                            <h3><?= htmlspecialchars($imovel['titulo']) ?></h3>
                            <p><strong>Descrição:</strong> <?= htmlspecialchars($imovel['descricao']) ?></p>
                            <p><strong>Tipo:</strong> <?= htmlspecialchars($imovel['tipo']) ?></p>
                            <p><strong>Cidade:</strong> <?= htmlspecialchars($imovel['cidade']) ?></p>
                            <p><strong>Finalidade:</strong> <?= htmlspecialchars($imovel['finalidade']) ?></p>
                            <p class="valor">R$ <?= number_format($imovel['valor'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="container">
  <div class="palette">
    <a id="color1" href="imagem1.jpg" class="color" style="background-image: url('imagem1.jpg');" target="_blank"></a>
    <a id="color2" href="imagem2.jpg" class="color" style="background-image: url('imagem2.jpg');" target="_blank"></a>
    <a id="color3" href="imagem3.jpg" class="color" style="background-image: url('imagem3.jpg');" target="_blank"></a>
    <a id="color4" href="imagem4.jpg" class="color" style="background-image: url('imagem4.jpg');" target="_blank"></a>
    <a id="color5" href="imagem5.jpg" class="color" style="background-image: url('imagem5.jpg');" target="_blank"></a>
  </div>
  
  <footer>
    <div class="footer-container">
        <div class="footer-info">
            <p>&copy; 2025 Celia Carnel. Todos os direitos reservados.</p>
        </div>
        <div class="footer-links">
            <a href="https://www.instagram.com" target="_blank">Instagram</a>
            <a href="https://www.facebook.com" target="_blank">Facebook</a>
            <a href="https://www.linkedin.com" target="_blank">LinkedIn</a>
            <a href="painel_administrativo.php">Acessar Painel Administrativo</a>
        </div>
    </div>
</footer>

    
</div>
</body>
</html>
