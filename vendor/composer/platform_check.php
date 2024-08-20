<?php

// platform_check.php @generated by Composer

$issues = array();

<<<<<<< HEAD
<<<<<<< HEAD
if (!(PHP_VERSION_ID >= 50500)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 5.5.0". You are running ' . PHP_VERSION . '.';
=======
if (!(PHP_VERSION_ID >= 80100)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 8.1.0". You are running ' . PHP_VERSION . '.';
}

if (PHP_INT_SIZE !== 8) {
    $issues[] = 'Your Composer dependencies require a 64-bit build of PHP.';
>>>>>>> cf18b17ca5bb4e27e51670217984bdff237faf3b
=======
if (!(PHP_VERSION_ID >= 70100)) {
    $issues[] = 'Your Composer dependencies require a PHP version ">= 7.1.0". You are running ' . PHP_VERSION . '.';
>>>>>>> parent of cf18b17 (Actualizacion36)
}

if ($issues) {
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
    }
    if (!ini_get('display_errors')) {
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDERR, 'Composer detected issues in your platform:' . PHP_EOL.PHP_EOL . implode(PHP_EOL, $issues) . PHP_EOL.PHP_EOL);
        } elseif (!headers_sent()) {
            echo 'Composer detected issues in your platform:' . PHP_EOL.PHP_EOL . str_replace('You are running '.PHP_VERSION.'.', '', implode(PHP_EOL, $issues)) . PHP_EOL.PHP_EOL;
        }
    }
    trigger_error(
        'Composer detected issues in your platform: ' . implode(' ', $issues),
        E_USER_ERROR
    );
}
