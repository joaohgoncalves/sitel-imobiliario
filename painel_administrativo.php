<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se o usuário está logado e se é corretor
if (!isset($_SESSION['id']) || !isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'corretor') {
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
$conn = new mysqli("localhost", "root", "", "imobiliaria");

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Função para excluir imóvel
if (isset($_GET['excluir_id'])) {
    $imovel_id = $_GET['excluir_id'];
    $sql_excluir = "DELETE FROM imoveis WHERE id = ?";
    if ($stmt = $conn->prepare($sql_excluir)) {
        $stmt->bind_param("i", $imovel_id);
        if ($stmt->execute()) {
            $mensagem = "✅ Imóvel excluído com sucesso!";
        } else {
            $mensagem = "❌ Erro ao excluir o imóvel.";
        }
        $stmt->close();
    } else {
        $mensagem = "❌ Erro ao preparar a consulta de exclusão.";
    }
}

// Definindo quantidade de imóveis por página
$imoveis_por_pagina = 9;

// Verificando página atual
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;

// Calculando o offset
$offset = ($pagina - 1) * $imoveis_por_pagina;

// Contar total de imóveis
$sql_total = "SELECT COUNT(*) AS total FROM imoveis";
$result_total = $conn->query($sql_total);
$total_imoveis = $result_total->fetch_assoc()['total'];

// Calcular total de páginas
$total_paginas = ceil($total_imoveis / $imoveis_por_pagina);

// Buscar imóveis para a página atual
$sql_listar_imoveis = "SELECT i.id, i.titulo, i.valor, t.nome AS tipo, c.nome AS cidade 
                       FROM imoveis i
                       JOIN tipos_imovel t ON i.tipo_id = t.id
                       JOIN cidades c ON i.cidade_id = c.id
                       ORDER BY i.id DESC
                       LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_listar_imoveis);
$stmt->bind_param("ii", $imoveis_por_pagina, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7fa;
        margin: 0;
        padding: 0;
    }

    #admin-container {
        display: flex;
        min-height: 100vh;
        overflow: hidden;
    }

    .sidebar {
        width: 240px;
        background-color: #1A202C;
        color: white;
        padding: 30px;
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        transition: transform 0.3s ease;
    }

    .sidebar.closed {
        transform: translateX(-240px);
    }

    .sidebar h2 {
        font-size: 1.5rem;
        color: #ffffff;
        margin-bottom: 40px;
        text-align: start;
        font-weight: 600;
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
        display: block;
        padding: 12px;
        border-radius: 8px;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .sidebar ul li a:hover {
        background-color: #4A5568;
        color: #E2E8F0;
        transform: scale(1.05);
    }

    .content {
        margin-left: 240px;
        padding: 30px;
        flex-grow: 1;
        background-color: #ffffff;
        transition: margin-left 0.3s ease;
    }

    .navbar {
        display: flex;
        justify-content: space-between;
        background-color: #ffffff;
        color: #2d3748;
        padding: 15px 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .navbar h1 {
        font-size: 1.5rem;
        color: #2d3748;
    }

    .nav-buttons {
        display: flex;
        gap: 15px;
    }

    .nav-buttons a {
        background-color: #3182CE;
        color: white;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    .nav-buttons a:hover {
        background-color: #2b6cb0;
    }

    .table-container {
        margin-top: 20px;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    table th, table td {
        padding: 14px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #2D3748;
        color: white;
    }

    td {
        color: #2D3748;
    }

    td a {
        color: #3182CE;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s ease;
    }

    td a:hover {
        color: #4C51BF;
    }

    .actions {
        display: flex;
        gap: 10px;
    }

    .actions .edit, .actions .delete {
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s;
    }

    .actions .edit {
        background-color:rgb(255, 255, 255);
        color: white;
    }

    .actions .edit:hover {
        background-color: #2f855a;
    }

    .actions .delete {
        background-color: #e53e3e;
        color: white;
    }

    .actions .delete:hover {
        background-color: #c53030;
    }

    .mensagem {
        margin: 20px auto;
        padding: 15px 25px;
        width: 90%;
        border-radius: 8px;
        font-weight: bold;
        font-size: 16px;
        text-align: center;
    }

    .success {
        background-color: #c6f6d5;
        color: #22543d;
    }

    .error {
        background-color: #feb2b2;
        color: #742a2a;
    }

    .paginacao {
        margin-top: 30px;
        text-align: center;
    }

    .paginacao a {
        display: inline-block;
        margin: 0 5px;
        padding: 10px 15px;
        background-color: #e2e8f0;
        color: #2d3748;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        transition: background-color 0.3s;
    }

   

    .paginacao .pagina-atual {
        background-color: #2D3748;
        color: white;
    }

    /* Posicionando o botão de abrir/fechar dentro da sidebar */
    .sidebar-toggle {
        position: absolute;
        top: 50px;
        right: 20px;
        background-color: #2d374800;
        color: white;
        padding: 1px;
        border-radius: 5px;
        cursor: pointer;
        z-index: 100;
        font-size: 24px;
    }

    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
}

#admin-container {
    display: flex;
    min-height: 100vh;
    overflow: hidden;
}
.sidebar {
    width: 240px;
    background-color: #1A202C;
    color: white;
    padding: 30px;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    transition: transform 0.3s ease-in-out; /* Animação de slide */
    z-index: 1000; /* Garantir que a sidebar fique à frente */


.content {
    margin-left: 240px;
    padding: 30px;
    flex-grow: 1;
    background-color: #ffffff;
    transition: margin-left 0.3s ease-in-out; /* Transição suave para o conteúdo */
    z-index: 0; /* O conteúdo fica atrás da sidebar */
}

}

.sidebar.closed {
    transform: translateX(-240px);
}

.sidebar h2 {
    font-size: 1.5rem;
    color: #ffffff;
    margin-bottom: 40px;
    text-align: start;
    font-weight: 600;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar ul li {
    margin: 15px 0;
    opacity: 0;
    animation: fadeIn 0.5s ease-in-out forwards;
}

.sidebar ul li:nth-child(1) {
    animation-delay: 0.2s;
}

.sidebar ul li:nth-child(2) {
    animation-delay: 0.4s;
}

.sidebar ul li:nth-child(3) {
    animation-delay: 0.6s;
}

.sidebar ul li:nth-child(4) {
    animation-delay: 0.8s;
}

.sidebar ul li a {
    color: #D1D5DB;
    text-decoration: none;
    font-size: 16px;
    display: block;
    padding: 12px;
    border-radius: 8px;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.sidebar ul li a:hover {
    background-color: #4A5568;
    color: #E2E8F0;
    transform: scale(1.05);
}

/* Animação de fade in para os itens da sidebar */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.content {
    margin-left: 240px;
    padding: 30px;
    flex-grow: 1;
    background-color: #ffffff;
    transition: margin-left 0.3s ease-in-out; /* Transição suave para o conteúdo */
}

.navbar {
    display: flex;
    justify-content: space-between;
    background-color: #ffffff;
    color: #2d3748;
    padding: 15px 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.navbar h1 {
    font-size: 1.5rem;
    color: #2d3748;
}

.nav-buttons {
    display: flex;
    gap: 15px;
}

.nav-buttons a {
    background-color: #3182CE;
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.nav-buttons a:hover {
    background-color: #2b6cb0;
}

.table-container {
    margin-top: 20px;
    overflow-x: auto;
    opacity: 0;
    animation: fadeInContent 0.5s ease-in-out forwards;
}

@keyframes fadeInContent {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

table th, table td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #2D3748;
    color: white;
}

td {
    color: #2D3748;
}

td a {
    color: #3182CE;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
}

td a:hover {
    color: #4C51BF;
}

.actions {
    display: flex;
    gap: 10px;
}

.actions .edit, .actions .delete {
    padding: 8px 12px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.3s;
}

.actions .edit {
    background-color:rgb(255, 255, 255);
    color: #566175;
}

.actions .edit:hover {
    background-color: #2f855a;
}

.actions .delete {
    background-color:rgb(255, 255, 255);
    color: red;
}

.actions .delete:hover {
    background-color: #c53030;
}

.mensagem {
    margin: 20px auto;
    padding: 15px 25px;
    width: 90%;
    border-radius: 8px;
    font-weight: bold;
    font-size: 16px;
    text-align: center;
}

.success {
    background-color: #c6f6d5;
    color: #22543d;
}

.error {
    background-color: #feb2b2;
    color: #742a2a;
}

.paginacao {
    margin-top: 30px;
    text-align: center;
}

.paginacao a {
    display: inline-block;
    margin: 0 5px;
    padding: 10px 15px;
    background-color: #e2e8f0;
    color: #2d3748;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s;
}

.paginacao .pagina-atual {
    background-color: #2D3748;
    color: white;
}

.sidebar-toggle {
    position: absolute;
    top: 50px;
    right: 20px;
    background-color: #2d374800;
    color: white;
    padding: 1px;
    border-radius: 5px;
    cursor: pointer;
    z-index: 100;
    font-size: 24px;
}


    </style>
</head>
<body>

<div id="admin-container">
    <div class="sidebar">
        <h2>Administração</h2>
        <ul>
            <li><a href="Gerenciar_contas.php">Gerenciar Contas</a></li>
            <li><a href="cadastrar_imovel.php">Adicionar Imóvel</a></li>
            <li><a href="listar_imoveis.php">Ver Imóveis</a></li>
            <li><a href="home.php">Home</a></li>
        </ul>
        <!-- Botão para abrir/fechar o menu dentro da sidebar -->
        <div class="sidebar-toggle" onclick="toggleSidebar()">☰</div>
    </div>

    <div class="content">
        <div class="navbar">
            <h1>Painel de Administração</h1>
        </div>

        <!-- Mensagem de sucesso ou erro -->
        <?php if (isset($mensagem)): ?>
            <div class="mensagem <?= strpos($mensagem, 'sucesso') !== false ? 'success' : 'error' ?>">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Valor</th>
                        <th>Tipo</th>
                        <th>Cidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['titulo']) ?></td>
                                <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['tipo']) ?></td>
                                <td><?= htmlspecialchars($row['cidade']) ?></td>
                                <td class="actions">
                                    <a href="editar_imovel.php?id=<?= $row['id'] ?>" class="edit">Ver Imóvel</a>
                                    <a href="listar_imoveis.php?excluir_id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Tem certeza que deseja excluir este imóvel?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhum imóvel encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="paginacao">
            <?php if ($pagina > 1): ?>
                <a href="?pagina=<?= $pagina - 1 ?>">&laquo; Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?= $i ?>" class="<?= $i === $pagina ? 'pagina-atual' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($pagina < $total_paginas): ?>
                <a href="?pagina=<?= $pagina + 1 ?>">Próxima &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Função para alternar a sidebar com animação
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('closed');
    adjustContentMargin();
}

// Ajusta a margem do conteúdo conforme a sidebar
function adjustContentMargin() {
    const content = document.querySelector('.content');
    const sidebar = document.querySelector('.sidebar');
    if (sidebar.classList.contains('closed')) {
        content.style.marginLeft = '40px'; // Margem menor quando o menu estiver fechado
    } else {
        content.style.marginLeft = '240px'; // Margem maior quando o menu estiver aberto
    }
}

// Inicializa a sidebar no estado correto
window.onload = function() {
    adjustContentMargin();
}

    // Função para alternar a sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('closed');
        adjustContentMargin();
    }

    // Ajusta a margem do conteúdo conforme a sidebar
    function adjustContentMargin() {
        const content = document.querySelector('.content');
        const sidebar = document.querySelector('.sidebar');
        if (sidebar.classList.contains('closed')) {
            content.style.marginLeft = '40px';
        } else {
            content.style.marginLeft = '300px';
        }
    }

    // Inicializa a sidebar no estado correto
    window.onload = function() {
        adjustContentMargin();
    }
</script>

</body>
</html>

<?php $conn->close(); ?>
