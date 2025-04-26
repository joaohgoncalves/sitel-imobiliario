<?php
session_start();

// Verifica se o usuário é um corretor
if (!isset($_SESSION['id']) || $_SESSION['perfil'] !== 'corretor') {
    // Se não for um corretor, redireciona para a página de login
    header("Location: login.php");
    exit();
}

// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "imobiliaria";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Verifica se o e-mail já está registrado no banco de dados
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $erro = "Este e-mail já está registrado.";
    } else {
        // Prepara a query para inserir os dados no banco
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, 'corretor')");
        $stmt->bind_param("sss", $nome, $email, $senha_hash);

        // Verifica se a inserção foi bem-sucedida
        if ($stmt->execute()) {
            echo "Conta criada com sucesso!";
            $_SESSION['id'] = $stmt->insert_id;
            $_SESSION['email'] = $email;
            $_SESSION['perfil'] = 'corretor'; // Define o perfil do usuário como corretor
            header("Location: painel_administrativo.php");
            exit();
        } else {
            $erro = "Erro ao criar a conta. Tente novamente.";
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Imobiliária</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f2f2f2;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-container h2 {
            text-align: center;
        }
        .form-container label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Criar Conta</h2>
    <?php if (isset($erro)): ?>
        <p class="error"><?= $erro ?></p>
    <?php endif; ?>

    <form action="criar_conta.php" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Criar Conta</button>
    </form>
</div>

</body>
</html>
