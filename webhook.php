<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';
define('ROOT', __DIR__ . '/');
define('CLASS_ROOT', ROOT . 'app/');

spl_autoload_register(function ($class_name) {
    include CLASS_ROOT.str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
});

$API_KEY = '306141394:AAGbmIBKOFxkFFGb9mTT11iNWKZCVOHPahM';
$BOT_NAME = 'EnglishLeobot';
try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($API_KEY, $BOT_NAME);
    $commands_folder = __DIR__ . '/app/Commands/';
    $telegram->addCommandsPath($commands_folder);

    Longman\TelegramBot\TelegramLog::initialize();
    Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . '/' . $BOT_NAME . '_error.log');
    Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . '/' . $BOT_NAME . '_debug.log');
    Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . '/' . $BOT_NAME . '_update.log');
    // Handle telegram webhook request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . '/' . $BOT_NAME . '_error.log');
}