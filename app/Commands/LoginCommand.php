<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;
use Models\User;

/**
 * User "/echo" command
 */
class LoginCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'login';

    /**
     * @var string
     */
    protected $description = 'Fill me';

    /**
     * @var string
     */
    protected $usage = '/login <username>\*/<password>';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Command execute method
     *
     * @return mixed
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text    = trim($message->getText(true));

        if ($text === '') {
            return Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Command usage: ' . $this->getUsage()]);
        }
        if(false === strpos($text, '\*/')) {
            return Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Command usage: ' . $this->getUsage()]);
        }

        list($login, $pass) = explode('\*/', $text);
        if(empty($login)) {
            return Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Логин не задан']);
        }
        if(empty($pass)) {
            return Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Пароль не задан']);
        }

        try {
            User::create($login, $pass, $chat_id);
        } catch (\Exception $e) {
            TelegramLog::error($e->getMessage());
            return Request::sendMessage(
                [
                    'chat_id' => $chat_id,
                    'text' => 'При выполнении возникла ошибка: ' .$e->getMessage(),
                ]
            );
        }
        return Request::sendMessage(['chat_id' => $chat_id, 'text' => "Учетные данные успешно сохранены\nНачните новую тренировку /startTrain"]);
    }
}
