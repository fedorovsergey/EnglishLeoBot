<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;
use Models\User;

/**
 * User "/echo" command
 */
class StartTrainCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'train';

    /**
     * @var string
     */
    protected $description = 'Fill me';

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
        $user = User::getByChatId($chat_id);

        if(null === $user) {
            $data = [
                'chat_id'      => $chat_id,
                'text'         => "Вы не вводили свой логин/пароль от Lingualeo!\nЯ не могу загрузить ваши вопросы\nВведите свои учетные данные командой /login",
                'reply_markup' => Keyboard::remove(),
            ];
            return Request::sendMessage($data);
        }

        try {
            $question = $user->getNextQuestion();
        } catch (\Exception $e) {
            TelegramLog::error($e->getMessage());
            return Request::sendMessage(
                [
                    'chat_id' => $chat_id,
                    'text' => 'При выполнении возникла ошибка: ' .$e->getMessage(),
                    'reply_markup' => Keyboard::remove(),
                ]
            );
        }

        if(!$question) {
            $data = [
                'chat_id' => $chat_id,
                'text'    => 'Internal Server Error',
                'reply_markup' => Keyboard::remove(),
            ];
            return Request::sendMessage($data);
        }

        $data = [
            'chat_id'      => $chat_id,
            'text'         => $question->ask(),
            'reply_markup' => $question->getKeyboardAnswers(),
            'parse_mode'   => 'html',
        ];

        return Request::sendMessage($data);
    }
}
