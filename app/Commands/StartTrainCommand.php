<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Lingualeo\Handler;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;

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
        $lingualeoHandler = new Handler();
        $answer = $lingualeoHandler->startTrain();
        if(!empty($answer['error_msg'])) {
            $data = [
                'chat_id' => $chat_id,
                'text'    => $answer['error_msg'],
            ];
            return Request::sendMessage($data);
        }
        $data = [
            'chat_id' => $chat_id,
            'text'    => $answer['text'],
        ];

        return Request::sendMessage($data);
    }
}
