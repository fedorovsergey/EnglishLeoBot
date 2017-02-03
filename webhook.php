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
    
    //Включаем логирование 
    Longman\TelegramBot\TelegramLog::initialize();
    Longman\TelegramBot\TelegramLog::initErrorLog(ROOT . $BOT_NAME . '_error.log');
    Longman\TelegramBot\TelegramLog::initDebugLog(ROOT . $BOT_NAME . '_debug.log');
    Longman\TelegramBot\TelegramLog::initUpdateLog(ROOT . $BOT_NAME . '_update.log');
    
    //подключаем свои команды
    $commands_folder = CLASS_ROOT . 'Commands/';
    $telegram->addCommandsPath($commands_folder);

    //включаем базу данных
    $telegram->enableMySQL(require CLASS_ROOT . 'config/database.php');

    // Handle telegram webhook request
    $telegram->handle();
} catch (\Exception $e) {
    Longman\TelegramBot\TelegramLog::error($e);
}