<?php
session_start();

// Verifica se o usuário é um corretor ou administrador
if (!isset($_SESSION['id']) || ($_SESSION['perfil'] !== 'corretor' && $_SESSION['perfil'] !== 'admin')) {
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

// Função para excluir uma conta
if (isset($_GET['excluir'])) {
    $id_excluir = $_GET['excluir'];
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_excluir);
    $stmt->execute();
    $stmt->close();
    header("Location: gerenciar_contas.php"); // Redireciona para a página de gerenciamento de contas
    exit();
}

// Função para editar uma conta
if (isset($_POST['editar'])) {
    $id_editar = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $perfil = $_POST['perfil'];

    $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, perfil = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nome, $email, $perfil, $id_editar);
    $stmt->execute();
    $stmt->close();
    header("Location: gerenciar_contas.php"); // Redireciona após edição
    exit();
}

// Buscar todas as contas
$sql = "SELECT id, nome, email, perfil FROM usuarios";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Contas - Imobiliária</title>
    <style>
    /* Geral */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 10px;
    box-sizing: border-box;
}

#admin-container {
    display: flex;
    min-height: 100vh;
    overflow: hidden;
}
h2 {

    font-size: 2rem;
    color: #2D3748;
    margin-bottom: 20px;
}

/* Sidebar */
.sidebar {
    background-color: #2D3748; /* Cor de fundo da sidebar */
    color: white;
    width: 240px;
    height: 100%;
    padding: 20px;
    position: fixed;
    top: 0;
    left: 0;
    transition: all 0.3s ease;
    box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar h2 {
    text-align: center;
    font-size: 1.5rem;
    color: #ffffff;
    margin-bottom: 40px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar ul li {
    margin: 15px 0;
}

.sidebar ul li a {
    color: #D1D5DB;
    text-decoration: none;
    font-size: 16px;
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.sidebar ul li a:hover {
    background-color: #4A5568; /* Tom mais claro quando passar o mouse */
    color: #E2E8F0;
    transform: scale(1.05);
}

.sidebar ul li a i {
    margin-right: 12px;
}

/* Content */
.content {
    margin-left: 240px;
    padding: 30px;
    flex-grow: 1;
    background-color: #ffffff;
    transition: margin-left 0.3s ease;
}

.content h1 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 20px;
    font-weight: bold;
}

/* Tabela */
.table-container {
    overflow-x: auto;
    margin-top: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
}

table th, table td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #2D3748; /* Azul escuro */
    color: white;
}

td {
    color: #2D3748; /* Cor de texto */
}

td a {
    color: #3182CE; /* Azul para links */
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
}

td a:hover {
    color: #4C51BF; /* Azul mais escuro no hover */
}

/* Botões */
.button {
    padding: 10px 15px;
    font-size: 16px;
    color: white;
    background-color: #4A90E2; /* Azul para botões */
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    text-decoration: none;
}


.button.delete {
    background-color: #e53e3e; /* Vermelho para excluir */
}

.button.delete:hover {
    background-color: #c53030; /* Vermelho mais escuro */
}

.button.edit {
    background-color: #38a169; /* Verde para editar */
}

.button.edit:hover {
    background-color: #2f855a; /* Verde mais escuro */
}

/* Navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    background-color: rgb(255, 255, 255);
    color: #2d3748;
    padding: 15px 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.navbar h1 {
    font-size: 1.5rem;
}

.nav-buttons {
    display: flex;
    gap: 15px;
}

.nav-buttons a {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: transparent;
    height: 40px;
    padding: 0 15px;
    color: #2d3748;
    text-decoration: none;
    font-size: 1rem;
    font-weight: bold;
    border: none;
    margin-left: 20px;
    transition: color 0.3s ease;
}

.nav-buttons a:hover {
    color: #4A90E2; /* Azul no hover do menu */
}

/* Responsividade */
@media (max-width: 768px) {
    #admin-container {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        position: relative;
        height: auto;
        box-shadow: none;
    }

    .content {
        margin-left: 0;
    }
}
/* Formulário de edição aprimorado */
.edit-form-container {
    max-width: 600px;
    margin-top: 40px;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border-left: 6px solid #4A90E2;
}

.edit-form-container h3 {
    font-size: 1.6rem;
    margin-bottom: 25px;
    color: #2D3748;
    font-weight: bold;
    border-bottom: 2px solid #EDF2F7;
    padding-bottom: 10px;
}

.edit-form-container label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #4A5568;
}

.edit-form-container input,
.edit-form-container select {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 20px;
    border: 1px solid #CBD5E0;
    border-radius: 10px;
    font-size: 15px;
    background-color: #F7FAFC;
    color: #2D3748;
    transition: border 0.3s ease, box-shadow 0.3s ease;
}

.edit-form-container input:focus,
.edit-form-container select:focus {
    border-color: #4A90E2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
    outline: none;
    background-color: #ffffff;
}

.edit-form-container button[type="submit"] {
    width: 100%;
    padding: 12px 0;
    font-size: 16px;
    background-color: #38A169;
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.edit-form-container button[type="submit"]:hover {
    background-color: #2F855A;
    transform: translateY(-2px);
}

/* Formulário de edição */
form {
    max-width: 500px;
    margin-top: 30px;
    background-color: #ffffff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

form h3 {
    margin-bottom: 20px;
    color: #2D3748;
    font-size: 1.3rem;
}

form label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #2D3748;
}

form input,
form select {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 20px;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 16px;
    background-color: #f7fafc;
    color: #2D3748;
    transition: border-color 0.3s ease;
}

form input:focus,
form select:focus {
    outline: none;
    border-color: #4A90E2;
    background-color: #ffffff;
}

form button[type="submit"] {
    padding: 12px 20px;
    font-size: 16px;
    background-color: #2D3748;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

form button[type="submit"]:hover {
    background-color: #2f855a;
    transform: translateY(-2px);
}


    </style>
</head>
<body>

<div class="navbar">
        <h1>Painel de Administração</h1>
        <div class="nav-buttons">
            <a href="criar_conta.php" class="button">Criar conta</a>
            <a href="painel_administrativo.php" class="button">Painel adm</a>
            <a href="home.php" class="button">Home</a>
        </div>
    </div>
    

    <?php if (isset($erro)): ?>
        <p class="error"><?= $erro ?></p>
    <?php endif; ?>

    <!-- Tabela com todos os usuários -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Perfil</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['perfil'] ?></td>
                    <td class="actions">
                        <a href="gerenciar_contas.php?editar=<?= $row['id'] ?>" class="btn">Editar</a>
                        <a href="gerenciar_contas.php?excluir=<?= $row['id'] ?>" class="btn">Excluir</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Formulário para editar uma conta -->
    <?php if (isset($_GET['editar'])): ?>
        <?php
        $id_editar = $_GET['editar'];
        $stmt = $conn->prepare("SELECT id, nome, email, perfil FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_editar);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $nome, $email, $perfil);
        $stmt->fetch();
        ?>
        <h3>Editar Conta</h3>
        <form action="gerenciar_contas.php" method="POST">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?= $nome ?>" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?= $email ?>" required>

            <label for="perfil">Perfil:</label>
            <select id="perfil" name="perfil" required>
                <option value="corretor" <?= $perfil == 'corretor' ? 'selected' : '' ?>>Corretor</option>
                <option value="admin" <?= $perfil == 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <button type="submit" name="editar">Salvar Alterações</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$conn->close();
