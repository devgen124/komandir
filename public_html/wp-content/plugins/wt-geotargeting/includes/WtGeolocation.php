<?php
use GeoIp2\WebService\Client;

/**
 * Класс обеспечивающий единый интерфейс для работы с сервисами определения месторасположения по IP
 *
 * Date: 23.01.2017
 */
class WtGeolocation
{
    public $ip;
    public $charset = 'utf-8';
    public $cookie = true;

    public $data = array();

    public $error_text = '';

    static $services = array(
        'ip_api_service' => 'IP Geolocation API',
        'sypexgeo_service' => 'Sypex Geo',
        'dadata_service' => 'DaData',
        'maxmind_service' => 'MaxMind',
        'none' => 'Отключить'
    );

    /**
     * base_name - Название базы данных IP-адресов
     *
     * @var array Настройки
     */
    public $options = array(
        'base_name' => 'ip_api_service',
        'sypexgeo_server' => 'api.sypexgeo.net',
        'sypexgeo_api_key' => null,
        'maxmind_language' => array('ru')
    );

    public function __construct($options = null) {
        // Открываем доступ к коду через статический класс Wt
        if (class_exists('Wt')){
            Wt::$geolocation = $this;
        }
        // Сохраняем настройки из БД
        $db_options = get_option('wt_geotargeting_geobase');
        if (is_array($db_options)) $this->options = array_merge($this->options, $db_options);

        // Сохраняем настройки из входящих параметров
        if (is_array($options) && !empty($options)) $this->options = array_merge($this->options, $options);

        if (isset($options['ip']) && $this->isValidIp($options['ip'])) {
            $this->ip = $options['ip'];
        } else {
            $this->ip = $this->getIp();
        }

        // Кодировка
        if (isset($options['charset']) && is_string($options['charset']) && $options['charset'] != 'windows-1251') {
            $this->charset = $options['charset'];
        }
    }

    /**
     * Определяем IP адрес по глобальному массиву $_SERVER
     * IP адреса проверяются начиная с приоритетного, для определения возможного использования прокси
     * 23.01.2017
     *
     * @return IP-адрес
     */
    function getIp() {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR', 'HTTP_X_REAL_IP');
        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) continue;

            $ip = trim(strtok($_SERVER[$key], ','));
            if ($this->isValidIp($ip)) {
                return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            }
        }
    }

    /**
     * Проверка валидности IP адреса
     * 23.01.2017
     *
     * @param null $ip IP адрес в формате 1.2.3.4
     * @return bool : true - если ip валидный, иначе false
     */
    function isValidIp($ip = null) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }
        return false; // иначе возвращаем false
    }

    /**
     * Обновляем данные в cookie
     * 22.01.2017
     *
     * @param array $data Новые значения
     * @return array|bool|mixed
     */
    function updateCookie(array $data) {
        // Получаем данные из cookie
        if (isset($_COOKIE['wt_geo_data'])){
            $data_cookie = json_decode($_COOKIE['wt_geo_data']);
        }

        if (!is_array($data_cookie)) $data_cookie = array();

        // Обновляем данные
        foreach ($data as $key => $value) {
            $data_cookie[$key] = $value;
        }

        if (!empty($data_cookie)) {
            setcookie('wt_geo_data', json_encode($data_cookie), time() + 3600 * 24 * 7, '/'); // устанавливаем куки для JS на неделю
            return $data_cookie;
        }else return false;
    }

    /**
     * Сохраняем данные в cookie
     * 27.04.2020
     *
     * @param array $data Новые значения
     * @return array|bool|mixed
     */
    function setCookie(array $data) {
        if (!empty($data)) {
            setcookie('wt_geo_data', json_encode($data), time() + 3600 * 24 * 7, '/'); // устанавливаем куки для JS на неделю
            return $data;
        }else return false;
    }

    /**
     * Очистка значений cookie
     */
    function cleanCookie(){
        setcookie('wt_geo_data', '', time()-3600, '/');
        unset($_COOKIE ['wt_geo_data']);
    }

    /**
     * Обновить геоданные
     */
    function reloadData(){
        if ($this->cookie && !empty($_COOKIE['wt_geo_data'])){
            $data = (array) json_decode($_COOKIE['wt_geo_data']);

            if (json_last_error() == JSON_ERROR_NONE){
                $this->data = $data;
                return;
            }
        }

        if ($this->isBot()) return;

        if ($this->options['base_name'] == 'none') return false;

        $base_date = $this->getGeobaseData($this->options['base_name']);

        if (empty($base_date)) return false;

        $this->setValues($base_date);

        $deactivate_save_region_from_cookie = Wt::$obj->geo->getSetting('deactivate_save_region_from_cookie');
        if (!empty($this->data) && $this->cookie && empty($deactivate_save_region_from_cookie)) {
            setcookie('wt_geo_data', json_encode($this->data), time() + 3600 * 24 * 7, '/'); // устанавливаем куки для JS на неделю
        }
    }

    /**
     * Получить данные о месторасположении по ip
     *
     * @return array - возвращает массив с данными
     */
    function getGeobaseData($base_name, $options = array()) {

        /* Перенастройка устаревшего сервиса IpGeoBase */
        if ($base_name == 'ipgeobase_service') $base_name = 'sypexgeo_service';
        elseif ($base_name == 'ipgeobase_and_maxmind_service') $base_name = 'maxmind_service';


        if ($base_name == 'dadata_service'){
            $service = new DaData();
            $service->ip = $this->ip;
            $service->apiKey = $this->options['dadata_api_key'];
            $data = $service->getData();

            if (!empty($data['location']['data'])) $data = $data['location']['data'];

            if ($service->error){
                $this->error_text .= ' Ошибка при обращении к сервису DaData';
                if (!empty($service->error_text)) $this->error_text .= ' - ' . $service->error_text;
                add_action('admin_notices', array($this, 'noticeError'));
            }

            if (!empty($data['federal_district'])) $data['district'] = $data['federal_district'];
            if (!empty($data['geo_lat'])) $data['lat'] = $data['geo_lat'];
            if (!empty($data['geo_lon'])) $data['lng'] = $data['geo_lon'];

            return $data;

        }elseif ($base_name == 'sypexgeo_service')
        {
            $service = new SypexGeo();
            $service->ip = $this->ip;
            $service->server = $this->options['sypexgeo_server'];
            $service->api_key = $this->options['sypexgeo_api_key'];
            $data_service = $service->getData();

            if (!empty($data_service['location']['data'])) $data = $data_service['location']['data'];

            if ($service->error){
                $this->error_text .= ' Ошибка при обращении к сервису Sypex Geo';
                if (!empty($service->error_text)) $this->error_text .= ' - ' . $service->error_text;
                add_action('admin_notices', array($this, 'noticeError'));
            }

            $data = array(
                'country' => null,
                'district' => null,
                'region' => null,
                'city' => null,
                'lat' => null,
                'lng' => null
            );

            if (!empty($data_service['country']['name_ru'])) $data['country'] = $data_service['country']['name_ru'];
            if (!empty($data_service['region']['name_ru'])) $data['region'] = $data_service['region']['name_ru'];
            if (!empty($data_service['city']['name_ru'])) $data['city'] = $data_service['city']['name_ru'];
            if (!empty($data_service['city']['lat'])) $data['lat'] = $data_service['city']['lat'];
            if (!empty($data_service['city']['lon'])) $data['lng'] = $data_service['city']['lon'];

            return $data;

        }elseif ($base_name == 'maxmind_service')
        {
            try {
                $client = new Client(
                    $this->options['maxmind_user_id'],
                    $this->options['maxmind_license_key'],
                    $this->options['maxmind_language']
                );

                $record = $client->city($this->ip);

            } catch (Exception $e) {
                $this->error_text .= ' Ошибка при обращении к сервису MaxMind - ' . $e->getMessage();
                return false;
            }

            $data = array();

            if (!empty($record->country->isoCode)) $data['country'] = $record->country->isoCode;
            if (!empty($record->mostSpecificSubdivision->name)) $data['region'] = $record->mostSpecificSubdivision->name;
            if (!empty($record->city->name)) $data['city'] = $record->city->name;
            if (!empty($record->location->latitude)) $data['lat'] = $record->location->latitude;
            if (!empty($record->location->longitude)) $data['lng'] = $record->location->longitude;

            return $data;
        }elseif ($base_name == 'ip_api_service')
        {
            $service = new IpApiCom();
            $service->ip = $this->ip;
            $data_service = $service->getData();

            if ($service->error){
                $this->error_text .= ' Ошибка при обращении к сервису IP Geolocation API';
                if (!empty($service->error_text)) $this->error_text .= ' - ' . $service->error_text;
                add_action('admin_notices', array($this, 'noticeError'));
            }

            $data = array(
                'country' => null,
                'district' => null,
                'region' => null,
                'city' => null,
                'lat' => null,
                'lng' => null
            );

            if (!empty($data_service['country'])) $data['country'] = $data_service['country'];
            if (!empty($data_service['district'])) $data['district'] = $data_service['district'];
            if (!empty($data_service['regionName'])) $data['region'] = $data_service['regionName'];
            if (!empty($data_service['city'])) $data['city'] = $data_service['city'];
            if (!empty($data_service['lat'])) $data['lat'] = $data_service['lat'];
            if (!empty($data_service['lon'])) $data['lng'] = $data_service['lon'];

            return $data;
        }
    }

    /**
     * Получить текущие геоданные
     *
     * @return string|array
     */
    function getData() {
        return $this->data;
    }

    /**
     * Получить текущее значение
     *
     * @param $key
     * @return null
     */
    function getValue($key) {
        if (empty($this->data[$key])) return null;
        else return $this->data[$key];
    }

    /**
     * Присвоить новое значение
     *
     * @param $key
     * @param null $value
     * @return bool
     */
    function setValue($key, $value = null){
        if (empty($this->data[$key])) return false;
        if (empty($value)) unset($this->data[$key]);
        else{
            $this->data[$key] = $value;
        }
    }

    /**
     * Сохранить массив значений
     *
     * @param array $data
     */
    function setValues(array $data){
        foreach ($data as $key => $value){
            if (isset($this->data[$key]) && empty($value)) unset($this->data[$key]);
            else{
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Проверка, является ли посетитель роботом поисковой системы / https://toster.ru/q/190331
     *
     * @param string $botname
     * @return bool
     */
    function isBot(&$botname = ''){
        $bots = array(
            'rambler','googlebot','aport','yahoo','msnbot','turtle','mail.ru','omsktele',
            'yetibot','picsearch','sape.bot','sape_context','gigabot','snapbot','alexa.com',
            'megadownload.net','askpeter.info','igde.ru','ask.com','qwartabot','yanga.co.uk',
            'scoutjet','similarpages','oozbot','shrinktheweb.com','aboutusbot','followsite.com',
            'dataparksearch','google-sitemaps','appEngine-google','feedfetcher-google',
            'liveinternet.ru','xml-sitemaps.com','agama','metadatalabs.com','h1.hrn.ru',
            'googlealert.com','seo-rus.com','yaDirectBot','yandeG','yandex',
            'yandexSomething','Copyscape.com','AdsBot-Google','domaintools.com',
            'Nigma.ru','bing.com','dotnetdotcom'
        );
        foreach($bots as $bot)
            if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false){
                $botname = $bot;
                return true;
            }
        return false;
    }

    /**
     * Уведомление об ошибке
     */
    function noticeError() {
        ?>
        <div class="error notice">
            <p>WT GeoTargeting Pro: <?php echo $this->error_text; ?></p>
        </div>
        <?php
    }

    function getServiceName(){
        $service = $this->options['base_name'];

        if (empty($service)) return 'Отсутствует';
        if (!array_key_exists($service, self::$services)) return 'Отсутствует';

        return self::$services[$service];
    }
}