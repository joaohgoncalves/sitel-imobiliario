<?php
$mensagem = "";
$dadosImovel = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "imobiliaria");

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    // Sanitize and trim inputs
    $titulo = htmlspecialchars(trim($_POST['titulo'] ?? ''));
    $descricao = htmlspecialchars(trim($_POST['descricao'] ?? ''));
    $tipo_id = $_POST['tipo_id'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $cidade_id = $_POST['cidade_id'] ?? '';
    $bairro = htmlspecialchars(trim($_POST['bairro'] ?? ''));
    $endereco = htmlspecialchars(trim($_POST['endereco'] ?? ''));
    $numero = $_POST['numero'] ?? '';
    $area_total = $_POST['area_total'] ?? '';
    $quartos = $_POST['quartos'] ?? '';
    $banheiros = $_POST['banheiros'] ?? '';
    $vagas_garagem = $_POST['vagas_garagem'] ?? '';
    $finalidade = $_POST['finalidade'] ?? '';

    if (empty($titulo) || empty($tipo_id) || empty($valor) || empty($cidade_id) || empty($descricao) || empty($finalidade)) {
        $mensagem = "❌ Todos os campos obrigatórios devem ser preenchidos.";
    } elseif (!is_numeric($valor) || $valor <= 0) {
        $mensagem = "❌ O valor deve ser um número válido e maior que zero.";
    } elseif (!is_numeric($area_total) || !is_numeric($quartos) || !is_numeric($banheiros) || !is_numeric($vagas_garagem)) {
        $mensagem = "❌ Verifique os valores numéricos.";
    } elseif (!is_numeric($cidade_id)) {
        $mensagem = "❌ ID da cidade inválido.";
    } elseif (!is_numeric($numero)) {
        $mensagem = "❌ O número deve ser numérico.";
    } else {
        $imagens = [];
        if (isset($_FILES['imagens']['name']) && $_FILES['imagens']['error'][0] === 0) {
            $totalImagens = count($_FILES['imagens']['name']);
            for ($i = 0; $i < $totalImagens; $i++) {
                $ext = pathinfo($_FILES['imagens']['name'][$i], PATHINFO_EXTENSION);
                if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $mensagem = "❌ Imagem com formato inválido.";
                    break;
                } else {
                    $nomeImagem = uniqid("imovel_") . "_" . $i . "." . $ext;
                    $destino = "fotos/" . $nomeImagem;
                    if (move_uploaded_file($_FILES['imagens']['tmp_name'][$i], $destino)) {
                        $imagens[] = $nomeImagem;
                    } else {
                        $mensagem = "❌ Erro ao salvar imagem.";
                        break;
                    }
                }
            }
        }

        if (empty($mensagem)) {
            $sql = "INSERT INTO imoveis (titulo, descricao, tipo_id, valor, cidade_id, bairro, endereco, numero, area_total, quartos, banheiros, vagas_garagem, finalidade) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $mensagem = "❌ Erro na consulta SQL: " . $conn->error;
            } else {
            $stmt->bind_param(
    'ssdiissiiiiis', // Aqui adicionamos um 's' para o campo 'finalidade'
    $titulo,
    $descricao,
    $tipo_id,
    $valor,
    $cidade_id,
    $bairro,
    $endereco,
    $numero,
    $area_total,
    $quartos,
    $banheiros,
    $vagas_garagem,
    $finalidade // Este é o valor que faltava na string de tipo
);

            
            

                if ($stmt->execute()) {
                    $imovelId = $conn->insert_id;
                    foreach ($imagens as $imagem) {
                        $sqlImagem = "INSERT INTO imagens_imovel (imovel_id, imagem) VALUES (?, ?)";
                        $stmtImagem = $conn->prepare($sqlImagem);
                        $stmtImagem->bind_param("is", $imovelId, $imagem);
                        $stmtImagem->execute();
                        $stmtImagem->close();
                    }
                    $mensagem = "✅ Imóvel cadastrado com sucesso!";
                } else {
                    $mensagem = "❌ Erro ao cadastrar imóvel: " . $stmt->error;
                }

                $stmt->close();
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Imóvel</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: grid;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            padding: 12px;
            background: #ffe600;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #ffce00;
        }

        .mensagem {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            padding: 10px;
            border-radius: 6px;
        }

        .sucesso {
            background: #d4edda;
            color: #155724;
        }

        .erro {
            background: #f8d7da;
            color: #721c24;
        }

        .preview-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .preview-container img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        .upload-box {
            padding: 20px;
            border: 2px dashed #ccc;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            color: #777;
        }

        .upload-box:hover {
            background: #f0f0f0;
        }

        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            padding: 6px;
            cursor: pointer;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .preview-container div:hover .remove-btn {
            opacity: 1;
        }

        .remove-btn:hover {
            background-color: rgba(255, 0, 0, 1);
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Cadastro de Imóvel</h1>

    <?php if ($mensagem): ?>
        <div class="mensagem <?= strpos($mensagem, '✅') !== false ? 'sucesso' : 'erro' ?>">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Título</label>
        <input type="text" name="titulo" required>

        <label>Descrição</label>
        <textarea name="descricao" rows="3" required></textarea>

        <label>Tipo</label>
        <select name="tipo_id" required>
            <option value="">Selecione</option>
            <option value="1">Casa</option>
            <option value="2">Apartamento</option>
            <option value="3">Terreno</option>
        </select>

        <label>Finalidade</label>
        <select name="finalidade" required>
            <option value="">Selecione</option>
            <option value="venda">Venda</option>
            <option value="locacao">Locação</option>
            <option value="temporada">Temporada</option>
        </select>

        <label>Valor</label>
        <input type="number" name="valor" id="valor" required oninput="onlyNumber(event)">

        <label>Cidade</label>
        <select name="cidade_id" required>
            <option value="">Selecione a cidade</option>
            <?php
                $conn = new mysqli("localhost", "root", "", "imobiliaria");
                if ($conn->connect_error) {
                    die("Erro de conexão: " . $conn->connect_error);
                }

                $result = $conn->query("SELECT id, nome FROM cidades");
                while ($cidade = $result->fetch_assoc()) {
                    echo "<option value='" . $cidade['id'] . "'>" . $cidade['nome'] . "</option>";
                }
                $conn->close();
            ?>
        </select>

        <label>Bairro</label>
        <input type="text" name="bairro">

        <label>Endereço</label>
        <input type="text" name="endereco">

        <label>Número</label>
        <input type="number" name="numero">

        <label>Área Total</label>
        <input type="number" name="area_total">

        <label>Quartos</label>
        <input type="number" name="quartos">

        <label>Banheiros</label>
        <input type="number" name="banheiros">

        <label>Vagas de Garagem</label>
        <input type="number" name="vagas_garagem">

        <label>Imagens</label>
        <input type="file" name="imagens[]" multiple>

        <div class="preview-container" id="image-preview"></div>

        <button type="submit">Cadastrar Imóvel</button>
    </form>
</div>

<script>
    // Preview images before submitting
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
        const files = e.target.files;
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                const div = document.createElement('div');
                const img = document.createElement('img');
                img.src = event.target.result;
                const removeBtn = document.createElement('button');
                removeBtn.textContent = '×';
                removeBtn.classList.add('remove-btn');
                div.appendChild(img);
                div.appendChild(removeBtn);
                preview.appendChild(div);
            }

            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>
