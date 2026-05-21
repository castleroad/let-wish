<?php

function env(string $key, $default = null)
{
    static $vars = null;

    if ($vars === null) {
        $vars = [];
        $file = __DIR__ . '/.env';
        if (file_exists($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                [$k, $v] = explode('=', $line, 2);
                $vars[trim($k)] = trim($v);
            }
        }
    }

    return $vars[$key] ?? getenv($key) ?: $default;
}
