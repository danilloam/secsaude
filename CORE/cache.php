<?php

$GLOBALS['__cache_callbacks'] = [];

function remember(string $key, callable $callback, int $ttl = 300, array $params = [])
{
    $cacheKey = cacheKey($key, $params);

    $GLOBALS['__cache_callbacks'][$cacheKey] = $callback;

    // 🔹 tenta cache primeiro
    $data = cacheGet($cacheKey, $ttl);
    if ($data !== false) {
        return $data;
    }

    $file = __DIR__ . '/cache/' . $cacheKey . '.cache';
    $lockFile = $file . '.lock';

    $lockFp = fopen($lockFile, 'c');

    if ($lockFp && flock($lockFp, LOCK_EX)) {

        try {
            clearstatcache(true, $file);

            $data = cacheGet($cacheKey, $ttl);
            if ($data !== false) {
                return $data;
            }

            $data = $callback();

            cacheSet($cacheKey, $data);

            return $data;

        } finally {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
			@unlink($lockFile);
            unset($GLOBALS['__cache_callbacks'][$cacheKey]);
        }
    }

    // fallback
    usleep(50000);

    $data = cacheGet($cacheKey, $ttl);
    if ($data !== false) {
        return $data;
    }

    $data = $callback();

    unset($GLOBALS['__cache_callbacks'][$cacheKey]);

    return $data;
}
/**
 * CONFIGURAÇÃO DE SESSÃO SEGURA
 */

if (getAmbiente() === 'dev' && empty($_SESSION['cache_cleaned'])) {
    cacheClear();
    $_SESSION['cache_cleaned'] = true;
}
function decodeCache(string $content, string $file)
{
    try {
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    } catch (Throwable $e) {
        @unlink($file);
        return false;
    }
}
/**
 * PDO MYSQL
 */
/**
 * FINGERPRINT MAIS SEGURO
 */

/**
 * =========================
 * CONTROLE DE AMBIENTE / CACHE
 * =========================
 */

function cacheKey(string $key, array $params = []): string
{
    ksort($params);
    try {
		$encoded = json_encode($params, JSON_THROW_ON_ERROR);
	} catch (Throwable $e) {
		$encoded = '';
	}
	return hash('sha1', $key . '|' . $encoded);
}
function cacheSet(string $cacheKey, $data): void
{
    if (!usarCache()) return;

    $dir = __DIR__ . '/cache/';
    $file = $dir . $cacheKey . '.cache';

    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    return;
}

    try {
		$serialized = is_string($data)
        ? $data
        : json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
		} catch (Throwable $e) {
			return;
		}

    if (strlen($serialized) > 5_000_000) return;

    $tmp = $file . '.tmp';

    if (file_put_contents($tmp, gzcompress($serialized, 4), LOCK_EX) !== false) {
		@chmod($tmp, 0644);
		rename($tmp, $file);
	} else {
		@unlink($tmp);
	}
}

function cacheGet(string $cacheKey, int $ttl = 300)
{
    if (!usarCache()) return false;

    $file = __DIR__ . '/cache/' . $cacheKey . '.cache';

    $mtime = @filemtime($file);
    if ($mtime === false) return false;

    $age = time() - $mtime;
    if ($age > ($ttl + 30)) return false;

	$fp = fopen($file, 'rb');
	if (!$fp) return false;

	flock($fp, LOCK_SH);
	$content = stream_get_contents($fp);
	flock($fp, LOCK_UN);
	fclose($fp);
	
	
    if (!$content) return false;

    $uncompressed = gzuncompress($content);
    if ($uncompressed === false) {
        @unlink($file);
        return false;
    }
	$staleWindow = min(30, (int)($ttl * 0.2));
	
	if ($age > $ttl && $age < ($ttl + $staleWindow)) {

    $lockFile = $file . '.lock';
    $lockFp = fopen($lockFile, 'c');

    if ($lockFp && flock($lockFp, LOCK_EX | LOCK_NB)) {
        try {
            if (
                PHP_SAPI === 'fpm-fcgi' &&
                function_exists('fastcgi_finish_request') &&
                isset($GLOBALS['__cache_callbacks'][$cacheKey])
            ) {
                fastcgi_finish_request();
                ignore_user_abort(true);
                set_time_limit(5);

                try {
                    $newData = call_user_func($GLOBALS['__cache_callbacks'][$cacheKey]);

                    if ($newData !== null) {
                        cacheSet($cacheKey, $newData);
                    }

                } catch (Throwable $e) {}
            }
        } finally {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
			@unlink($lockFile);
            unset($GLOBALS['__cache_callbacks'][$cacheKey]);
        }
    }

    return decodeCache($uncompressed, $file);
}
    return decodeCache($uncompressed, $file);
}
function getAmbiente(): string
{
    static $ambiente = null;

    if ($ambiente !== null) {
        return $ambiente;
    }

    try {
        $ambiente = strtolower(env('APP_ENV'));
    } catch (Throwable $e) {
        $ambiente = 'dev';
    }

    if (!in_array($ambiente, ['prod', 'dev'])) {
        $ambiente = 'dev';
    }

    return $ambiente;
}

function usarCache(): bool
{
    static $usar = null;

    if ($usar !== null) return $usar;

    $ambiente = getAmbiente();

    if ($ambiente !== 'prod') {
        error_log("AMBIENTE ATUAL: " . $ambiente);
    }

    return $usar = ($ambiente === 'prod');
}
function cacheClear(): bool
{
	 if (getAmbiente() !== 'dev') {
        return false;
    }

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
	 
	 
    $dir = rtrim(__DIR__, '/') . '/cache/';

    if (!is_dir($dir)) {
        return false;
    }

    $files = glob($dir . '*.{cache,lock}', GLOB_BRACE);

    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    return true;
}
