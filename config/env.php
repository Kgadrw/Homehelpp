<?php
/**
 * Load project-root .env into putenv / $_ENV (once).
 * Usage: require_once __DIR__ . '/env.php';
 */
if (defined('HH_ENV_LOADED')) {
    return;
}
define('HH_ENV_LOADED', true);

(static function (): void {
    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envFile) || !is_readable($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);
        if ($name === '' || !preg_match('/^[A-Z_][A-Z0-9_]*$/i', $name)) {
            continue;
        }
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
            $value = str_replace(['\\n', '\\"', "\\'"], ["\n", '"', "'"], $value);
        }
        // Do not override real process env (production hosts often inject secrets)
        $existing = getenv($name);
        if ($existing !== false && $existing !== '') {
            $_ENV[$name] = $existing;
            continue;
        }
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
})();

if (!function_exists('hh_env')) {
    /**
     * Read an env value with optional default.
     */
    function hh_env(string $key, string $default = ''): string
    {
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return (string)$_ENV[$key];
        }
        $v = getenv($key);
        if ($v !== false && $v !== '') {
            return (string)$v;
        }
        return $default;
    }
}
