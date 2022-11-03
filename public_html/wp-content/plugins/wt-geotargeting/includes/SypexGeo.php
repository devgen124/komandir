<?php

/**
 * Класс для работы с сервисом SypexGeo
 * https://sypexgeo.net/
 *
 * Date: 19.05.2020
 */
class SypexGeo
{
    public $ip;

    public $server = 'api.sypexgeo.net';
    public $api_key = null;

    public $error = false;
    public $error_text = '';

    function getData(){

        $url = 'http://';
        $url .= $this->server . '/';
        if (!empty($this->api_key)) $url .= $this->api_key . '/';
        $url .= 'json/' . $this->ip;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPGET , true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $result = curl_exec($ch);
        $error_text = curl_error($ch);

        if (isset($error_text)) $this->error_text = $error_text;
        if (empty($result)){
            $this->error = true;
            return false;
        }

        $data = json_decode($result, true);

        if (!empty($data['error'])){
            $this->error = true;
            $this->error_text = $data['error'];
            return false;
        }

        return $data;
    }
}