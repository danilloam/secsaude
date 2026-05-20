<?php

$page = 0;

require_once __DIR__ . '/../CORE/config.php';
require_once __DIR__ . '/../CORE/bootstrap.php';
require_once __DIR__ . '/../security/session_guard.php';
require_once __DIR__ . '/../helpers/func.php';

if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| ALTERAR SENHA
|--------------------------------------------------------------------------
*/

$erro = "";
$sucesso = "";

/* CSRF */
if (
    empty($_SESSION['csrf_senha']) ||
    empty($_SESSION['csrf_time_senha']) ||
    (time() - $_SESSION['csrf_time_senha'] > 900)
) {
    $_SESSION['csrf_senha'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_time_senha'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {

    // valida csrf
    if (
        !isset($_POST['csrf']) ||
        !hash_equals($_SESSION['csrf_senha'], $_POST['csrf'])
    ) {

        $erro = "Sessão inválida.";

    } else {

        $senha_atual = trim($_POST['senha_atual'] ?? '');
        $nova_senha  = trim($_POST['nova_senha'] ?? '');
        $confirma    = trim($_POST['confirma_senha'] ?? '');

        if ($nova_senha !== $confirma) {

            $erro = "As novas senhas não coincidem.";

        } elseif (strlen($nova_senha) < 6) {

            $erro = "A nova senha deve ter pelo menos 6 caracteres.";

        } else {

            // busca senha atual
            $stmt = mysqlConnect()->prepare("
                SELECT password
                FROM users
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$_SESSION['user']['id']]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($senha_atual, $user['password'])) {

                $erro = "Senha atual incorreta.";

            } else {

                // gera hash
                $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                // atualiza senha
                $stmt = mysqlConnect()->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $nova_hash,
                    $_SESSION['user']['id']
                ]);

                unset(
                    $_SESSION['csrf_senha'],
                    $_SESSION['csrf_time_senha']
                );

                $sucesso = "Senha alterada com sucesso!";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| ATUALIZAR USUÁRIO
|--------------------------------------------------------------------------
*/

$erro_usuario = "";
$sucesso_usuario = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_usuario'])) {

    $nome       = trim($_POST['nome'] ?? '');
    $sobrenome  = trim($_POST['sobrenome'] ?? '');

    if (empty($nome)) {

        $erro_usuario = "Informe o nome.";

    } else {

        $foto_blob = $_SESSION['user']['foto'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | UPLOAD FOTO BLOB
        |--------------------------------------------------------------------------
        */

        if (
            isset($_FILES['foto']) &&
            $_FILES['foto']['error'] === UPLOAD_ERR_OK
        ) {

            $tmp  = $_FILES['foto']['tmp_name'];
            $size = $_FILES['foto']['size'];

            $ext = strtolower(
                pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)
            );

            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $permitidos)) {

                $erro_usuario = "Formato de imagem inválido.";

            } elseif ($size > 5 * 1024 * 1024) {

                $erro_usuario = "A imagem deve ter no máximo 5MB.";

            } else {

                $foto_blob = file_get_contents($tmp);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SALVAR
        |--------------------------------------------------------------------------
        */

        if (!$erro_usuario) {

            $stmt = mysqlConnect()->prepare("
                UPDATE users
                SET
                    nome = ?,
                    sobrenome = ?,
                    foto = ?
                WHERE id = ?
            ");

            $stmt->bindParam(1, $nome);
            $stmt->bindParam(2, $sobrenome);
            $stmt->bindParam(3, $foto_blob, PDO::PARAM_LOB);
            $stmt->bindParam(4, $_SESSION['user']['id']);

            $stmt->execute();

            /*
            |--------------------------------------------------------------------------
            | ATUALIZA SESSÃO
            |--------------------------------------------------------------------------
            */

            $_SESSION['user']['nome'] = $nome;
            $_SESSION['user']['sobrenome'] = $sobrenome;
            $_SESSION['user']['foto'] = $foto_blob;

            $sucesso_usuario = "Informações atualizadas com sucesso!";
        }
    }
}

?>

<?php include "./includes/top.php"; ?>

<!--begin::Sidebar-->
<?php include "./includes/menu.php"; ?>
<!--end::Sidebar-->

<main class="app-main" id="main" tabindex="-1">

    <div class="app-content-header">
        <div class="container-fluid">

            <div class="row">

                <div class="col-sm-6">
                    <h3 class="mb-0">Configurações</h3>
                </div>

                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-end">

                        <li class="breadcrumb-item">
                            <a href="#">Home</a>
                        </li>

                        <li
                            class="breadcrumb-item active"
                            aria-current="page"
                        >
                            Configurações
                        </li>

                    </ol>

                </div>

            </div>

        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="row g-3">

                <!-- MENU -->
                <div class="col-md-3">

                    <div
                        class="list-group list-group-flush nav nav-pills flex-column"
                        id="settings-nav"
                        role="tablist"
                    >

                        <a
                            href="#account"
                            class="list-group-item list-group-item-action active"
                            data-bs-toggle="pill"
                            role="tab"
                        >
                            <i class="bi bi-person me-2"></i>
                            Informações
                        </a>

                        <a
                            href="#security"
                            class="list-group-item list-group-item-action"
                            data-bs-toggle="pill"
                            role="tab"
                        >
                            <i class="bi bi-shield-lock me-2"></i>
                            Segurança
                        </a>

                    </div>

                </div>

                <!-- CONTEÚDO -->
                <div class="col-md-9">

                    <div class="tab-content">

                        <!-- ACCOUNT -->
                        <div
                            class="tab-pane fade show active"
                            id="account"
                            role="tabpanel"
                        >

                            <?php if ($erro_usuario): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($erro_usuario) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($sucesso_usuario): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($sucesso_usuario) ?>
                                </div>
                            <?php endif; ?>

                            <div class="card">

                                <div class="card-header">
                                    <h3 class="card-title">
                                        Informações do Usuário
                                    </h3>
                                </div>

                                <div class="card-body">

                                    <form
                                        method="POST"
                                        enctype="multipart/form-data"
                                        class="row g-3"
                                    >

                                        <!-- FOTO -->
                                        <div class="col-12 text-center">

                                            <?php

                                            if (!empty($_SESSION['user']['foto'])) {

                                                $base64 = base64_encode(
                                                    $_SESSION['user']['foto']
                                                );

                                                $foto = 'data:image/jpeg;base64,' . $base64;

                                            } else {

                                                $foto = "https://ui-avatars.com/api/?name=" .
                                                    urlencode($_SESSION['user']['nome']);
                                            }

                                            ?>

                                            <img
                                                src="<?= $foto ?>"
                                                class="rounded-circle border mb-3"
                                                width="120"
                                                height="120"
                                                style="object-fit: cover;"
                                            >

                                        </div>

                                        <!-- INPUT FOTO -->
                                        <div class="col-md-12">

                                            <label class="form-label">
                                                Foto do usuário
                                            </label>

                                            <input
                                                type="file"
                                                name="foto"
                                                class="form-control"
                                                accept=".jpg,.jpeg,.png,.webp"
                                            >

                                        </div>

                                        <!-- NOME -->
                                        <div class="col-md-6">

                                            <label class="form-label">
                                                Nome
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="nome"
                                                value="<?= htmlspecialchars($_SESSION['user']['nome'] ?? '') ?>"
                                                required
                                            >

                                        </div>

                                        <!-- SOBRENOME -->
                                        <div class="col-md-6">

                                            <label class="form-label">
                                                Sobrenome
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="sobrenome"
                                                value="<?= htmlspecialchars($_SESSION['user']['sobrenome'] ?? '') ?>"
                                            >

                                        </div>

                                        <!-- LOGIN -->
                                        <div class="col-md-12">

                                            <label class="form-label">
                                                Login
                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                value="<?= htmlspecialchars($_SESSION['user']['login'] ?? '') ?>"
                                                disabled
                                            >

                                        </div>

                                        <!-- BOTÃO -->
                                        <div class="col-12">

                                            <button
                                                type="submit"
                                                name="salvar_usuario"
                                                class="btn btn-primary"
                                            >
                                                <i class="bi bi-save me-1"></i>
                                                Salvar Alterações
                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                        <!-- SECURITY -->
                        <div
                            class="tab-pane fade"
                            id="security"
                            role="tabpanel"
                        >

                            <?php if ($erro): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($erro) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($sucesso): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($sucesso) ?>
                                </div>
                            <?php endif; ?>

                            <div class="card">

                                <div class="card-header">
                                    <h3 class="card-title">
                                        Alterar Senha
                                    </h3>
                                </div>

                                <div class="card-body">

                                    <form method="POST" class="row g-3">

                                        <input
                                            type="hidden"
                                            name="csrf"
                                            value="<?= htmlspecialchars($_SESSION['csrf_senha'] ?? '') ?>"
                                        >

                                        <!-- SENHA ATUAL -->
                                        <div class="col-md-12">

                                            <label
                                                class="form-label"
                                                for="pwd-current"
                                            >
                                                Senha atual
                                            </label>

                                            <input
                                                type="password"
                                                class="form-control"
                                                id="pwd-current"
                                                name="senha_atual"
                                                required
                                            >

                                        </div>

                                        <!-- NOVA SENHA -->
                                        <div class="col-md-6">

                                            <label
                                                class="form-label"
                                                for="pwd-new"
                                            >
                                                Nova senha
                                            </label>

                                            <input
                                                type="password"
                                                class="form-control"
                                                id="pwd-new"
                                                name="nova_senha"
                                                minlength="6"
                                                required
                                            >

                                        </div>

                                        <!-- CONFIRMAR -->
                                        <div class="col-md-6">

                                            <label
                                                class="form-label"
                                                for="pwd-confirm"
                                            >
                                                Confirmar nova senha
                                            </label>

                                            <input
                                                type="password"
                                                class="form-control"
                                                id="pwd-confirm"
                                                name="confirma_senha"
                                                minlength="6"
                                                required
                                            >

                                        </div>

                                        <!-- BOTÃO -->
                                        <div class="col-12">

                                            <button
                                                type="submit"
                                                name="alterar_senha"
                                                class="btn btn-primary"
                                            >
                                                <i class="bi bi-key me-1"></i>
                                                Alterar senha
                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

</main>

<!--begin::Footer-->
<footer class="app-footer">

    <div class="float-end d-none d-sm-inline">
        Anything you want
    </div>

    <strong>
        Copyright © 2014-2026
        <a
            href="https://adminlte.io"
            class="text-decoration-none"
        >
            AdminLTE.io
        </a>.
    </strong>

    All rights reserved.

</footer>
<!--end::Footer-->

<div class="sidebar-overlay"></div>

<!--begin::Third Party Plugin(OverlayScrollbars)-->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>

<!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>

<!--begin::Required Plugin(Bootstrap 5)-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

<!--begin::Required Plugin(AdminLTE)-->
<script src="../js/adminlte.js"></script>

<script>

const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';

const Default = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'leave',
    scrollbarClickScroll: true,
};

document.addEventListener('DOMContentLoaded', function () {

    const sidebarWrapper = document.querySelector(
        SELECTOR_SIDEBAR_WRAPPER
    );

    const isMobile = window.innerWidth <= 992;

    if (
        sidebarWrapper &&
        OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
        !isMobile
    ) {

        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
                theme: Default.scrollbarTheme,
                autoHide: Default.scrollbarAutoHide,
                clickScroll: Default.scrollbarClickScroll,
            },
        });
    }
});

</script>

</body>
</html>