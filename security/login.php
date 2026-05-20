
<?php
require_once __DIR__ . '/../CORE/bootstrap.php';

function gerarCaptcha(): void {
    $a = random_int(10, 99);
    $b = random_int(10, 99);

    $_SESSION['captcha'] = [
        'a' => $a,
        'b' => $b,
        'result' => $a + $b
    ];
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$erro = "";

$pdo = mysqlConnect();

/**
 * CSRF
 */
if (empty($_SESSION['csrf']) || (time() - ($_SESSION['csrf_time'] ?? 0) > 900)) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_time'] = time();
}

/**
 * LOGIN ATTEMPTS
 */
$loginInput = $_POST['login'] ?? '';

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM login_attempts 
    WHERE (ip_address = ? OR login = ?) 
    AND attempt_time > (NOW() - INTERVAL 15 MINUTE)
");

$stmt->execute([$ip, $loginInput]);
$tentativas = (int)$stmt->fetchColumn();

/**
 * LOGIN
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $erro = "Sessão inválida.";
    }

    if (empty($erro)) {
        if ($tentativas >= 50) $erro = "Bloqueado temporariamente.";
        elseif ($tentativas >= 10) $erro = "Muitas tentativas.";
    }

    if (empty($erro) && $tentativas >= 5) {
        if ((int)($_POST['captcha'] ?? 0) !== ($_SESSION['captcha']['result'] ?? -1)) {
            $erro = "Captcha inválido.";
            gerarCaptcha();
        }
    }

    if (empty($erro)) {

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            // desativa sessões antigas
            $pdo->prepare("
                UPDATE user_sessions 
                SET is_active = 0 
                WHERE user_id = ?
            ")->execute([$user['id']]);

            session_regenerate_id(true);

            $_SESSION['user'] = $user;

            $_SESSION['fingerprint'] = session_fingerprint();
            $_SESSION['created'] = time();
            $_SESSION['last_activity'] = time();
			$sessionId = session_id();
            $pdo->prepare("
                INSERT INTO user_sessions 
                (user_id, session_id, fingerprint, is_active, last_activity)
                VALUES (?, ?, ?, 1, ?)
            ")->execute([
                $user['id'],
                $sessionId,
                $_SESSION['fingerprint'],
                time()
            ]);

            $pdo->prepare("
                INSERT INTO security_logs (user_id, event, ip_address, created_at)
                VALUES (?, 'LOGIN', ?, ?)
            ")->execute([
                $user['id'],
                $ip,
                time()
            ]);
			header("Location: " . BASE_URL . "/home");
         
            exit;

        } else {

            usleep(random_int(300000, 900000));

            $pdo->prepare("
                INSERT INTO login_attempts (login, ip_address)
                VALUES (?, ?)
            ")->execute([$login, $ip]);

            $erro = "Credenciais inválidas.";

            if ($tentativas >= 5) gerarCaptcha();
        }
    }
}

if ($tentativas >= 5 && empty($_SESSION['captcha'])) {
    gerarCaptcha();
}
?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sistema Distrito | P&aacute;gina de Login</title>

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE 4 | Login Page v2" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../dist/css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../dist/css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="login-page bg-body-secondary">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <a
            href="../index2.html"
            class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover"
          >
            <h1 class="mb-0"><b>Sistema</b>Distrito</h1>
          </a>
        </div>
        <div class="card-body login-card-body">
          <p class="login-box-msg">Fa&ccedil;a o login para iniciar a Sess&atilde;o</p>
			<?php if ($erro): ?>
			<div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
			<?php endif; ?>
          <form method="post">
            <div class="input-group mb-1">
              <div class="form-floating">
			  <?php $csrf = $_SESSION['csrf'] ?? ''; ?>
			  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input id="login" name="login" type="text" class="form-control" value="" placeholder="" Required />
                <label for="login">Login</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-person-fill"></span>
              </div>
            </div>
            <div class="input-group mb-1">
              <div class="form-floating">
                <input id="loginPassword" type="password" name="password" class="form-control" placeholder="" Required />
                <label for="loginPassword">Senha</label>
              </div>
              <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
              </div>
            </div>
            <!--begin::Row-->
            <div class="row">
              <div class="col-8 d-inline-flex align-items-center">
                <div class="mb-2">
				<?php if ($tentativas >= 5 && isset($_SESSION['captcha_a'], $_SESSION['captcha_b'])): ?>
    <label>
        Quanto é <?= $_SESSION['captcha_a'] ?> + <?= $_SESSION['captcha_b'] ?> ?
    </label>
    <input type="text" name="captcha" class="form-control" autocomplete="off">
<?php endif; ?>
                 
                </div>
              </div>
              <!-- /.col -->
              <div class="col-4">
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </form>
<a href="esquecisenha.php">Esqueci minha senha</a>
          <!-- /.social-auth-links -->

        </div>
        <!-- /.login-card-body -->
      </div>
    </div>
    <!-- /.login-box -->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../dist/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

        // Disable OverlayScrollbars on mobile devices to prevent touch interference
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
    <!--end::OverlayScrollbars Configure-->
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
