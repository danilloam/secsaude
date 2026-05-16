<?php
session_start();

$usuarios = [
    "andreza" => "123456",
    "joao"  => "123456",
    "maria" => "123456",
    "gestor" => "123456"
];

// 🔓 LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// 🔐 LOGIN
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['usuario'] ?? '';
    $pass = $_POST['senha'] ?? '';

    if (isset($usuarios[$user]) && $usuarios[$user] === $pass) {
    $_SESSION['logado'] = true;
    $_SESSION['usuario'] = $user;
} else {
    $erro = "Usuário ou senha inválidos!";
}
}

// 🔒 PROTEÇÃO
$logado = $_SESSION['logado'] ?? false;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Sistema - Distrito Sanitário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
}
.card {
    border-radius: 10px;
}
.menu-btn {
    height: 100px;
    font-size: 18px;
    font-weight: bold;
}
</style>

</head>
<body>

<div class="container mt-5">

<?php if (!$logado): ?>

    <!-- 🔐 LOGIN -->
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>🔐 Login do Sistema</h4>
                </div>

                <div class="card-body">

                    <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= $erro ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Usuário</label>
                            <input type="text" name="usuario" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Senha</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Entrar</button>
                    </form>

                </div>
            </div>

        </div>
    </div>

<?php else: ?>

    <!-- 🏠 MENU -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>🏥 Sistema Distrito Sanitário</h3>
        <a href="?logout=1" class="btn btn-danger">Sair</a>
    </div>

    <div class="row g-3">

        <div class="col-md-4">
            <a href="agenda.php" class="btn btn-primary w-100 menu-btn">
                📊 Agenda por Unidade
            </a>
        </div>

        <div class="col-md-4">
            <a href="falecomsuaequipe.php" class="btn btn-success w-100 menu-btn">
                💬 Fale com sua Equipe
            </a>
        </div>

        <div class="col-md-4">
            <a href="interdicoes.php" class="btn btn-warning w-100 menu-btn">
                🚧 Interdições
            </a>
        </div>

    </div>

<?php endif; ?>

</div>

</body>
</html>