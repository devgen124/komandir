<?php

/**
 * Класс для работы с сервисом DaData
 * https://dadata.ru/api/iplocate
 *
 * Date: 21.04.2020
 */
class DaData
{
    public $ip;

    public $apiKey = null;

    public $error = false;
    public $error_text = '';

    function getData(){

        $ch = curl_init('https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address?ip=' . $this->ip);
        curl_setopt($ch, CURLOPT_HTTPGET , true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Token ' . $this->apiKey
        ]);

        $result = curl_exec($ch);
        $error_text = curl_error($ch);

        if (isset($error_text)) $this->error_text = $error_text;
        if (empty($result)){
            $this->error = true;
            return false;
        }

        $data = json_decode($result, true);

        return $data;
    }

}