<?php 
loadEnv(__DIR__ . '/.env');
function loadEnv(string $path): void
{
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;

        if (!str_contains($line, '=')) continue;
		$line = preg_replace('/\s+#.*$/', '', $line);
		[$name, $value] = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        // remove aspas se tiver
        $value = trim($value, "\"'");

        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}