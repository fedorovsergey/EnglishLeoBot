<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
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

        TelegramLog::debug('Lingualeo startTrain command start');
        $user = User::getByChatId($chat_id);
        TelegramLog::debug('Lingualeo user '.$user->getLogin());

        try {
            $question = $user->getNextQuestion();
        } catch (\Exception $e) {
            TelegramLog::error($e->getMessage());
            return Request::sendMessage(
                [
                    'chat_id' => $chat_id,
                    'text' => 'При выполнении возникла ошибка: ' .$e->getMessage(),
                ]
            );
        }

        if(!$question) {
            $data = [
                'chat_id' => $chat_id,
                'text'    => 'Internal Server Error',
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
