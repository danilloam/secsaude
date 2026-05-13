<?php
if (session_status() === PHP_SESSION_NONE) {

    session_name('secsaude_session');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/', // 🔥 ESSA LINHA RESOLVE
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}