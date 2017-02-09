<?php

namespace Lingualeo;

use function GuzzleHttp\json_decode;
use Longman\TelegramBot\TelegramLog;
use Models\User;

class Handler {
    private function login(User $user)
    {
        TelegramLog::debug('Lingualeo login request start');
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

    public function getNewTraining(User $user)
    {
        $i=0;
        $getTrainingAnswer = [];
        do {
            TelegramLog::debug('getTrainingAnswer try '.$i);
            if(!empty($getTrainingAnswer['error_msg']) && $getTrainingAnswer['error_msg'] == 'Authorization required') {
                TelegramLog::debug('getTrainingAnswer try login'.$i);
                $this->login($user);
            }
            $getTrainingAnswer = $this->doGetTrainingRequest($user);
            if(!empty($getTrainingAnswer) && empty($getTrainingAnswer['error_msg'])) {
                break;
            }
        } while ($i<3);

        if(empty($getTrainingAnswer)) {
            throw new Exception('Answer is empty');
        }
        if(!empty($getTrainingAnswer['error_msg'])) {
            throw new Exception($getTrainingAnswer['error_msg']);
        }
        return $getTrainingAnswer;
    }

    private function doGetTrainingRequest(User $user) {
        $train = 'https://lingualeo.com/training/gettraining/translate_word';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $train);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $user->getCookiePath());
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($curl);
        curl_close($curl);

        if(strpos($result, 'r_password')!== false) {
            TelegramLog::debug('Lingualeo Authorization required');
            return ['error_msg' => 'Authorization required'];
        }

        return json_decode($result, 1);
    }

    public function sendFinishedTraining(User $user, array $data)
    {
        $train = 'https://lingualeo.com/training/result/translate_word';
        $postData = [
            'name' => 'translate_word',
            'wordSetId'=>0,
            'words' => $data
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest"));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $train);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $user->getCookiePath());
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($curl);
        curl_close($curl);

        if(strpos($result, 'r_password')!== false) {
            TelegramLog::debug('Lingualeo Authorization required');
            return ['error_msg' => 'Authorization required'];
        }
        TelegramLog::debug(print_r(json_decode($result, 1), true));
        return json_decode($result, 1);
    }
}