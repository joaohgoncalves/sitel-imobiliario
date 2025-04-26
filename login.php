<?php
session_start();

// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "imobiliaria";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Se o usuário já estiver logado, redireciona conforme o perfil
if (isset($_SESSION['id']) && isset($_SESSION['perfil'])) {
    if ($_SESSION['perfil'] === 'corretor') {
        header("Location: painel_corretor.php");
        exit();
    } elseif ($_SESSION['perfil'] === 'adm') {
        header("Location: painel_administrativo.php");
        exit();
    } else {
        session_destroy(); // Encerra sessão inválida
        header("Location: login.php");
        exit();
    }
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Busca id, senha e perfil do usuário
    $stmt = $conn->prepare("SELECT id, senha, perfil FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $senha_hash, $perfil);
        $stmt->fetch();

        if (password_verify($senha, $senha_hash)) {
            $_SESSION['id'] = $id;
            $_SESSION['email'] = $email;
            $_SESSION['perfil'] = $perfil;

            // Redireciona conforme perfil
            if ($perfil === 'corretor') {
                header("Location: painel_administrativo.php");
            } elseif ($perfil === 'adm') {
                header("Location: painel_administrativo.php");
            } else {
                $erro = "Perfil inválido!";
                session_destroy();
            }
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
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
    <title>Login - Imobiliária</title>
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
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .login-container h2 {
            text-align: center;
        }
        .login-container label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-container button:hover {
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

<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($erro)): ?>
        <p class="error"><?= $erro ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
