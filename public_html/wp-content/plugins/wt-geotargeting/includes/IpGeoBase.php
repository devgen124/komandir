<?php

/**
 * Класс для работы с сервисом IpGeoBase
 * http://ipgeobase.ru/
 *
 * Date: 23.01.2017
 */
class IpGeoBase
{
    public $ip;
    public $charset = 'utf-8';

    public $error = false;
    public $error_text = '';

    function getData(){
        $ch = curl_init('http://ipgeobase.ru:7020/geo?ip=' . $this->ip);
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

        // если указана кодировка отличная от windows-1251, изменяем кодировку
        if ($this->charset != 'windows-1251' && function_exists('iconv')) {
            $string = iconv('windows-1251', $this->charset, $string);
        }
        $data = $this->parseString($string);
        return $data;
    }

    /**
     * Парсит полученные в XML данные в случае, если на сервере не установлено расширение Simplexml
     * @return array - возвращает массив с данными
     */
    function parseString($string) {
        $params = array('inetnum', 'country', 'city', 'region', 'district', 'lat', 'lng');
        $data = $out = array();
        foreach ($params as $param) {
            if (preg_match('#<' . $param . '>(.*)</' . $param . '>#is', $string, $out)) {
                $data[$param] = trim($out[1]);
            }
        }
        return $data;
    }
}