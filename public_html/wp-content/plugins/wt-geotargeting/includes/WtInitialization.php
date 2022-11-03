<?php

class WtInitialization
{
	var $data = array();
	var $record = array();

	var $geo_contacts;

    public $settings = array();

	function __construct(){
		// Открываем доступ к коду через статический класс WT
		if (class_exists('Wt')){
			Wt::setObject('geo', $this);
			Wt::$gt = $this;
		}

        // Сохраняем настройки из БД
        $db_settings = get_option('wt_geotargeting_sistem');
        if (is_array($db_settings)) $this->settings = array_merge($this->settings, $db_settings);

		// Регистрируем шорткод и хук для него
		add_shortcode('wt_geotargeting', array (&$this, 'shortcodeGeotargetingAction'));

		$method = apply_filters('wt_geotargeting_initialization_method', false); // Метод определения местоположения посетителя страницы

		// Подгружаем значения региона по умолчанию
		$option_default = get_option('wt_geotargeting_default');
		if (is_array($option_default)) $this->data = array_merge($this->data, $option_default);

		$options = array();

		// ТЕСТОВЫЙ РЕЖИМ
		// Проверяем роль пользователя для включения тестового режима
		if (is_user_logged_in() && current_user_can('administrator'))
		{
			$options_debug = get_option('wt_geotargeting_debug');

			if (!empty($options_debug['mode']) && $options_debug['mode'] != 'disabled') $method = 'debug';

			if (isset($options_debug['mode']) && $options_debug['mode'] == 'ip'
				&& isset($options_debug['ip']) && filter_var($options_debug['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){

				$options['ip'] = $options_debug['ip'];
			}

			if (isset($options_debug['mode']) && $options_debug['mode'] == 'city'){
                if (isset($options_debug['city_name'])) {
                    $debug_geo_data = array('city' => $options_debug['city_name']);
                }elseif (isset($options_debug['city_id'])) {
                    // Проверка и получение выбранного для тестирования города
                    $data_files = new WtGtDataFiles();
                    $debug_geo_data = $data_files->getCityInfo($options_debug['city_id']);
                }
			}

			if (isset($options_debug['mode']) && $options_debug['mode'] == 'country'
				&& isset($options_debug['country_alpha2'])){

				$debug_geo_data = array('country' => $options_debug['country_alpha2']);
			}
		}



		$this->geolocation =  new WtGeolocation($options);

        // Очищаем cookie через GET-запрос
        if (isset($_GET['wt_geo_clean']))
        {
            $wt_geo_clean = strip_tags(urldecode($_GET['wt_geo_clean']));
            if ($wt_geo_clean == 1) $this->geolocation->cleanCookie();
        }

        // Установка региона через GET-запрос
        if ($this->checkDataDefault())
        {
            $method = 'location_get';

            $data_get = $this->setDataDefault();
            $this->data = $data_get;
        }
		
		// ФОРМИРУЕМ И СОХРАНЯЕМ массив значений региона для работы плагина

		if ($method == 'debug' && $options_debug['mode'] == 'ip')
		{
			$this->geolocation->cookie = false;

			// Получаем значения из сервиса геолокации не сохраняя в cookie
			$this->geolocation->reloadData();
			$geo_data = $this->geolocation->getData();
			$this->data = array_merge($this->data, $geo_data);

		}elseif ($method == 'debug'
			&& ($options_debug['mode'] == 'city' || $options_debug['mode'] == 'country') 
			&& count($debug_geo_data) > 0){

			$this->data = array_merge($this->data, $debug_geo_data);

		}



		if (empty($method)) $method = 'ip';

		if ($method == 'ip'){
			$this->geolocation->reloadData();
			$geo_data = $this->geolocation->getData();
			$this->data = array_merge((array)$this->data, (array)$geo_data);
		}

		$this->data = apply_filters('wt_geotargeting_initialization_data', $this->data, $method);

		// Так как хук срабатывает в момент подключения плагина, обращаться к нему необходимо
		// до этого момента (например в mu-plugins).
		do_action('wt_geotargeting_initialization_end', $this->data);
	}


	/**
	 * Сохраняем Default значения
	 * 22.11.2016
	 *
	 * @version 1.4.4
	 * @return array
     */
	function setDataDefault()
    {
		if (isset($_GET['wt_country_by_default'])) $wt_country_by_default = strip_tags(urldecode($_GET['wt_country_by_default']));
		if (isset($_GET['wt_district_by_default'])) $wt_district_by_default = strip_tags(urldecode($_GET['wt_district_by_default']));
		if (isset($_GET['wt_region_by_default'])) $wt_region_by_default = strip_tags(urldecode($_GET['wt_region_by_default']));
		if (isset($_GET['wt_city_by_default'])) $wt_city_by_default = strip_tags(urldecode($_GET['wt_city_by_default']));
		
		$data_default = array(
			'administrative_district' => null,
			'country' => null,
			'district' => null,
			'region' => null,
			'city' => null
		);

		if (!empty($wt_country_by_default)) $data_default['country'] = $wt_country_by_default;
		if (!empty($wt_district_by_default)) $data_default['district'] = $wt_district_by_default;
		if (!empty($wt_region_by_default)) $data_default['region'] = $wt_region_by_default;
		if (!empty($wt_city_by_default)) $data_default['city'] = $wt_city_by_default;


		if (!empty($this->geolocation)){
            $deactivate_save_region_from_cookie = Wt::$obj->geo->getSetting('deactivate_save_region_from_cookie');
            if (empty($deactivate_save_region_from_cookie)){
                $this->geolocation->setCookie($data_default);
            }
        }

		return $data_default;    	
    }


	/**
	 * Присвоить значение текущей страны
	 * 23.12.2016
	 *
	 * @param $value
	 * @return bool
     */
	function setDataCountry($value){
		if (empty($value)) return false;

		$this->geolocation->set_cookie(
			array('country' => $value)
		);

		return true;
	}


	/**
	 * Проверка наличия входящих дефолтных значений региона
	 * 22.11.2016
	 *
	 * @version 1.4.4
	 * @return bool
     */
	function checkDataDefault(){
		if (empty($_GET['wt_country_by_default']) &&
			empty($_GET['wt_district_by_default']) &&
			empty($_GET['wt_region_by_default']) &&
			empty($_GET['wt_city_by_default'])
		) return false;

		return true;
	}


	/**
	 * Шорткод [geotargeting]
	 *
	 * @param $param
	 * @param $content
     */
	function shortcodeGeotargetingAction($param, $content){



		// Определяем выводился-ли ранее контент для указанного типа, если да, то завершаем выполнение
		if (isset($param['type']) && isset($this->record[$param['type']]) &&
			$this->record[$param['type']] > 0)
			return;

		// Проверяем совпадение локаций

		if (!empty($this->data['city'])){

			if (!empty($param['city_show']) && $param['city_show'] == $this->data['city']){
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}

			if (!empty($param['city_not_show']) && $param['city_not_show'] != $this->data['city']){
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}
		}

		if (!empty($this->data['region'])) {

			if (!empty($param['region_show']) && $param['region_show'] == $this->data['region']) {
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}

			if (!empty($param['region_not_show']) &&	$param['region_not_show'] != $this->data['region']) {
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}
		}

		if (!empty($this->data['district'])) {

			if (!empty($param['district_show']) && $param['district_show'] == $this->data['district']) {
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}

			if (!empty($param['district_not_show']) && $param['district_not_show'] != $this->data['district']) {
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}
		}

		if (!empty($this->data['country'])) {

			if (!empty($param['country_show']) && $param['country_show'] == $this->data['country']){
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}

			if (!empty($param['country_not_show']) && $param['country_not_show'] != $this->data['country']){
				if (!empty($param['type'])) $this->record[$param['type']] = 1;
				return do_shortcode($content);
			}
		}

		if (!empty($param['default']) && $param['default'] == true){
			
			if (!empty($param['type'])) $this->record[$param['type']] = 1;
			return do_shortcode($content);
		}

		// Вывод текущих значений
		if (!empty($param['get'])){

			if ($param['get'] == 'ip') return $this->geolocation->ip;

			$return = $this->getRegion($param['get']);

			if (empty($return) && isset($content)) $return = $content;

			return $return;
		}

		return;
	}


	/**
	 * Получение региона
	 *
	 * @version 1.4.3
	 * @param string $type Тип региона (city, region, district, country)
	 * @return null
     */
	public function getRegion($type = 'city'){
		if (!empty($this->data[$type])) return $this->data[$type];

		return NULL;
	}

	/**
	 * Получить привязанную к региону контактную информацию
	 *
	 * @param null $type
	 * @param null $region
	 * @return null
     */
	public function getContact($type = null, $region = NULL){
		if (empty($this->geo_contacts)) $this->geoContactsReload();

		if (!$region && $this->getRegion()) $region = $this->getRegion();

		if (!$region) return NULL;

		if (empty($this->geo_contacts[$region])) return NULL;

		if (empty($type)) return $this->geo_contacts[$region];

		if (!empty($this->geo_contacts[$region][$type])) return $this->geo_contacts[$region][$type];

		return NULL;
	}

	/**
	 * Обновление справочника контактов
	 *
	 * @return bool|null
     */
	public function geoContactsReload(){
		$uploads_path = WP_CONTENT_DIR.'/uploads';
		$file_name = $uploads_path . '/multisite_geo_info.txt';

		if (!file_exists($uploads_path) || !file_exists($file_name)) return FALSE;

		$file_content = file_get_contents($file_name);

		if (empty($file_content)) return NULL;

		$this->geo_contacts = json_decode($file_content, true);
	}

    public function getSetting($name){
        if (empty($this->settings[$name])) return null;

        return $this->settings[$name];
    }
}
?>