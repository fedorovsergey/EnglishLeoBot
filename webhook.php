<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';
define('ROOT', __DIR__ . '/');
define('CLASS_ROOT', ROOT . 'app/');

spl_autoload_register(function ($class_name) {
    include CLASS_ROOT.str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
});

try {
    // Create Telegram API object
    $telegramCredentials = require CLASS_ROOT . 'config/telegram.php';
    $telegram = new Longman\TelegramBot\Telegram($telegramCredentials['api_key'], $telegramCredentials['bot_name']);
    
    //Включаем логирование 
    Longman\TelegramBot\TelegramLog::initialize();
    Longman\TelegramBot\TelegramLog::initErrorLog(ROOT . $BOT_NAME . '_error.log');
    Longman\TelegramBot\TelegramLog::initDebugLog(ROOT . $BOT_NAME . '_debug.log');
    Longman\TelegramBot\TelegramLog::initUpdateLog(ROOT . $BOT_NAME . '_update.log');

    //подключаем свои команды
    $commands_folder = CLASS_ROOT . 'Commands/';
    $telegram->addCommandsPath($commands_folder);

    //включаем базу данных
    \Database\Db::initialize(require CLASS_ROOT . 'config/database.php');

    // Handle telegram webhook request
    $telegram->handle();
} catch (\Exception $e) {
    Longman\TelegramBot\TelegramLog::error($e);
}