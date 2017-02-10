<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\TelegramLog;
use Models\User;

/**
 * Generic message command
 */
class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'Genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle generic message';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $chat_id = $this->getMessage()->getChat()->getId();
        $user = User::getByChatId($chat_id);

        //проверка ответа
        $resultText = $user->checkAnswer($this->getMessage()->getText(true));

        if ($user->trainingIsFinished()) {
            $data = [
                'chat_id'      => $chat_id,
                'text'         => $user->getTrainingSummaryText(),
                'reply_markup' => Keyboard::remove(),
            ];
            return Request::sendMessage($data);
        }
        //Поиск нового вопроса
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
            'text'         => $resultText . $question->ask(),
            'reply_markup' => $question->getKeyboardAnswers(),
            'parse_mode'   => 'html',
        ];
        return Request::sendMessage($data);
    }
}
