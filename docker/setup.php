<?php

$root = dirname(__DIR__);

function fillEnvironmentValue(string $path, string $key, string $value): void
{
    $content = file_get_contents($path);
    if ($content === false) {
        throw new RuntimeException('Cannot read '.$path);
    }

    $pattern = '/^'.preg_quote($key, '/').'=([^\r\n]*)\r?$/m';
    if (preg_match($pattern, $content, $matches)) {
        if (trim($matches[1], " \t\"'\r\n") !== '') {
            return;
        }
        $content = preg_replace_callback($pattern, fn () => $key.'='.$value, $content);
    } else {
        $content = rtrim($content)."\n".$key.'='.$value."\n";
    }

    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException('Cannot write '.$path);
    }
}

foreach (['.env', '.env.testing'] as $name) {
    if (! file_exists($root.'/'.$name) && ! copy($root.'/'.$name.'.example', $root.'/'.$name)) {
        throw new RuntimeException('Cannot create '.$name);
    }
}

fillEnvironmentValue($root.'/.env', 'APP_KEY', 'base64:'.base64_encode(random_bytes(32)));
fillEnvironmentValue($root.'/.env', 'DB_PASSWORD', bin2hex(random_bytes(24)));
fillEnvironmentValue($root.'/.env', 'ADMIN_PASSWORD', bin2hex(random_bytes(24)));
fillEnvironmentValue($root.'/.env', 'MYSQL_ROOT_PASSWORD', bin2hex(random_bytes(24)));
fillEnvironmentValue($root.'/.env.testing', 'APP_KEY', 'base64:'.base64_encode(random_bytes(32)));

preg_match('/^DB_PASSWORD=([^\r\n]*)/m', file_get_contents($root.'/.env'), $password);
fillEnvironmentValue($root.'/.env.testing', 'DB_PASSWORD', $password[1]);

echo "Local environment files are ready. Existing non-empty values were preserved.\n";
