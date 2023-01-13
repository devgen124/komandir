<?php

/**
 * Класс для работы с сервисом IP Geolocation API
 * https://ip-api.com/
 */
class IpApiCom
{
    public $ip;
    public $charset = 'utf-8';

    public $error = false;
    public $error_text = '';

    function getData(){
        $ch = curl_init('http://ip-api.com/json/' . $this->ip . '?lang=ru&fields=status,message,country,countryCode,region,regionName,city,district,lat,lon');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        $string = curl_exec($ch);
        $error_text = curl_error($ch);

        if (isset($error_text)) $this->error_text = $error_text;
        if (empty($string)){
            $this->error = true;
            return false;
        }

        $data = json_decode($string, true);
        return $data;
    }
}