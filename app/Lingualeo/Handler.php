<?php

namespace Lingualeo;

use function GuzzleHttp\json_decode;
use Longman\TelegramBot\TelegramLog;
use Models\User;

class Handler {
    private function login(User $user)
    {
        TelegramLog::debug('Lingualeo login start');
        $data = [
            "email" => $user->getLogin(),
            "password" => $user->getPassword()
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, 'http://api.lingualeo.com/api/login');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $user->getCookiePath());
        TelegramLog::debug('Lingualeo curl start');
        $r = curl_exec($curl);
        TelegramLog::debug('Lingualeo curl result');
        TelegramLog::debug($r);
        curl_close($curl);
    }

    public function startTrain(User $user)
    {
        $this->login($user);

        $train = 'https://lingualeo.com/training/gettraining/translate_word';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $train);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $user->getCookiePath());
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($curl);
        curl_close($curl);
        TelegramLog::debug('Lingualeo train answer' . $result);
        $gameDataArray = json_decode($result, 1);

        if(!empty($gameDataArray['error_msg'])) {
            //TODO lingualeoAnswer
            TelegramLog::error('Lingualeo error' . $gameDataArray['error_msg']);
            return ['error_msg'=>$gameDataArray['error_msg']];
        }
        $gameDataArray = $gameDataArray['game'];
        foreach($gameDataArray as $question) {
            $questionWord = $question['text'];
            return ['error_msg'=> null, 'text'=> "$questionWord"];
        }
    }
}