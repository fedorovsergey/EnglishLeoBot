<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$API_KEY = '306141394:AAGbmIBKOFxkFFGb9mTT11iNWKZCVOHPahM';
$BOT_NAME = 'EnglishLeobot';
try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($API_KEY, $BOT_NAME);
    $commands_folder = __DIR__ . '/app/Commands/';
    $telegram->addCommandsPath($commands_folder);
    // Handle telegram webhook request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    file_put_contents(__DIR__ . '/log.txt', date('Y-m-d H:i:s'). ' - '. print_r($e), FILE_APPEND);
}