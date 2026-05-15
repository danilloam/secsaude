<?php
require_once __DIR__ . '/CORE/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erro = "";
$sucesso = "";

/* CSRF */
if (empty($_SESSION['csrf_cadastro'])) {
    $_SESSION['csrf_cadastro'] = bin2hex(random_bytes(32));
}

/* GERA LOGIN AUTOMÁTICO */
function gerarLogin($nome, $sobrenome, $pdo) {
    $base = strtolower(substr($nome, 0, 1) . $sobrenome);
    $base = preg_replace('/[^a-z0-9]/', '', $base);

    $login = $base;
    $i = 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");

    while (true) {
        $stmt->execute([$login]);
        $existe = $stmt->fetchColumn();

        if (!$existe) {
            return $login;
        }

        $login = $base . $i;
        $i++;
    }
}

/* POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_cadastro'], $_POST['csrf'])) {
        $erro = "Sessão inválida.";
    } else {

        $nome = trim($_POST['nome'] ?? '');
        $sobrenome = trim($_POST['sobrenome'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $distrito = $_POST['distrito_id'] ?? null;
        $unidade = $_POST['unidade_id'] ?? null;

        if (!$nome || !$sobrenome || !$senha) {
            $erro = "Preencha todos os campos.";
        } elseif (strlen($senha) < 6) {
            $erro = "Senha deve ter pelo menos 6 caracteres.";
        } else {

            $pdo = mysqlConnect();

            $login = gerarLogin($nome, $sobrenome, $pdo);
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (nome, sobrenome, login, password, distrito_id, unidade_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $nome,
                $sobrenome,
                $login,
                $hash,
                $distrito,
                $unidade
            ]);

            $sucesso = "Usuário criado com sucesso! Login gerado: " . $login;
        }
    }
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Criar Usuário</title>
    <link rel="stylesheet" href="./dist/css/adminlte.css">
</head>

<body class="login-page bg-body-secondary">

<div class="login-box">
    <div class="card card-outline card-primary">

        <div class="card-header text-center">
            <h3>Criar Usuário</h3>
        </div>

        <div class="card-body">

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form method="post">

                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_cadastro']) ?>">

                <div class="form-floating mb-2">
                    <input type="text" name="nome" class="form-control" required>
                    <label>Nome</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="text" name="sobrenome" class="form-control" required>
                    <label>Sobrenome</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="password" name="senha" class="form-control" required>
                    <label>Senha</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="text" name="distrito_id" class="form-control">
                    <label>Distrito ID</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="text" name="unidade_id" class="form-control">
                    <label>Unidade ID</label>
                </div>

                <button class="btn btn-primary w-100">Criar usuário</button>

            </form>

        </div>
    </div>
</div>

</body>
</html>