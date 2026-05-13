<?php
require_once __DIR__ . '/../CORE/bootstrap.php';

const MAX_ATTEMPTS = 5;
const LOCK_TIME = 300;

if (empty($_SESSION['user']['id'])) {
    header("Location: " . BASE_URL . "/security/login.php");
    exit;
}

if (empty($_SESSION['locked'])) {
    header("Location: " . BASE_URL . "/../index.php");
    exit;
}

$_SESSION['attempts'] ??= 0;
$_SESSION['lock_until'] ??= (time() + LOCK_TIME);

/**
 * EXPIRA BLOQUEIO (SEM AUTO-DESBLOQUEIO)
 */
if (time() >= $_SESSION['lock_until']) {
    $_SESSION['attempts'] = 0;
    $_SESSION['lock_until'] = time() + LOCK_TIME;
}

/**
 * USER
 */
$stmt = mysqlConnect()->prepare("SELECT login, password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: " . BASE_URL . "/security/login.php");
    exit;
}

$nome = $user['login'] ?? 'Usuário';
$erro = '';

/**
 * CSRF
 */
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

/**
 * LOGIN DESBLOQUEIO
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $erro = "Erro de segurança.";

    } elseif (time() < $_SESSION['lock_until']) {

        $password = $_POST['password'] ?? '';

        if (password_verify($password, $user['password'])) {

    $pdo = mysqlConnect();

    $oldId = session_id();

    session_regenerate_id(true);

    $newId = session_id();

    $pdo->prepare("
        UPDATE user_sessions 
        SET session_id = ?
        WHERE session_id = ?
    ")->execute([$newId, $oldId]);

    unset($_SESSION['locked'], $_SESSION['attempts'], $_SESSION['lock_until']);

    $_SESSION['last_activity'] = time();
    $_SESSION['fingerprint'] = session_fingerprint();

    $redirect = $_SESSION['last_page'] ?? '/index.php';
    unset($_SESSION['last_page']);

    header("Location: " . BASE_URL . $redirect);
    exit;
} else {

            $_SESSION['attempts']++;

            if ($_SESSION['attempts'] >= MAX_ATTEMPTS) {
                $_SESSION['lock_until'] = time() + LOCK_TIME;
            }

            $erro = "Senha incorreta.";

            sleep(min($_SESSION['attempts'], 5));
        }
    }
}

$remaining = max(0, $_SESSION['lock_until'] - time());
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Lockscreen</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container text-center mt-5">

    <h3>🔒 Sessão bloqueada</h3>

    <p><strong><?= htmlspecialchars($nome) ?></strong></p>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

        <input type="password" name="password" class="form-control w-25 mx-auto" placeholder="Senha" required>

        <button class="btn btn-primary mt-3">Desbloquear</button>
    </form>

    <?php if ($erro): ?>
        <p class="text-danger mt-3"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <p class="mt-3">
        Tempo restante: <span id="t"></span>
    </p>

    <a href="logout.php">Trocar usuário</a>

</div>

<script>
let r = <?= (int)$remaining ?>;

function f(s){return String(Math.floor(s/60)).padStart(2,'0')+":"+String(s%60).padStart(2,'0');}

setInterval(() => {
    if (r <= 0) location.reload();
    document.getElementById('t').innerText = f(r--);
}, 1000);
</script>

</body>
</html>