<?php
require 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$erro = "";
$sucesso = "";

/* CSRF */
if (empty($_SESSION['csrf_senha']) || empty($_SESSION['csrf_time_senha']) || (time() - $_SESSION['csrf_time_senha'] > 900)) {
    $_SESSION['csrf_senha'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_time_senha'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_senha'], $_POST['csrf'])) {
        $erro = "Sessão inválida.";
    } else {

        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirma = $_POST['confirma_senha'] ?? '';

        if ($nova_senha !== $confirma) {
            $erro = "As novas senhas não coincidem.";
        } elseif (strlen($nova_senha) < 6) {
            $erro = "A nova senha deve ter pelo menos 6 caracteres.";
        } else {

            // busca senha atual
            $stmt = mysqlConnect()->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']['id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($senha_atual, $user['password'])) {
                $erro = "Senha atual incorreta.";
            } else {

                // atualiza senha
                $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                $stmt = mysqlConnect()->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$nova_hash, $_SESSION['user']['id']]);

                unset($_SESSION['csrf_senha'], $_SESSION['csrf_time_senha']);

                $sucesso = "Senha alterada com sucesso!";
            }
        }
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Alterar Senha</title>
    <link rel="stylesheet" href="./dist/css/adminlte.css">
</head>

<body class="login-page bg-body-secondary">

<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="text-center">Alterar Senha</h3>
        </div>

        <div class="card-body">

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form method="post">

                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_senha'] ?? '') ?>">

                <div class="form-floating mb-2">
                    <input type="password" name="senha_atual" class="form-control" required>
                    <label>Senha atual</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="password" name="nova_senha" class="form-control" required>
                    <label>Nova senha</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="password" name="confirma_senha" class="form-control" required>
                    <label>Confirmar nova senha</label>
                </div>

                <button class="btn btn-primary w-100">Alterar senha</button>

            </form>

        </div>
    </div>
</div>

</body>
</html>