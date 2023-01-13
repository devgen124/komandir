<?php
/*
Plugin Name: WT Geotargeting
Plugin URI: https://web-technology.biz/cms-wordpress/plugin-wt-geotargeting
Description: Набор инструментов для настройки геотаргетинга.
Version: 1.9
Author: Кусты Роман, АИТ "WebTechnology"
Author URI: https://web-technology.biz
*/

define('WT_GT_PRO_PLUGIN_FILE', __FILE__);
define('WT_GT_PRO_PLUGIN_DIR', dirname(WT_GT_PRO_PLUGIN_FILE));
define('WT_GT_PRO_PLUGIN_BASENAME', plugin_basename(WT_GT_PRO_PLUGIN_FILE));

require_once(WT_GT_PRO_PLUGIN_DIR . '/vendor/autoload.php');

include(WT_GT_PRO_PLUGIN_DIR . '/includes/DaData.php');	// Класс для работы с DaData
include(WT_GT_PRO_PLUGIN_DIR . '/includes/SypexGeo.php');	// Класс для работы с SypexGeo
include(WT_GT_PRO_PLUGIN_DIR . '/includes/IpApiCom.php');	// Класс для работы с IP Geolocation API

include(WT_GT_PRO_PLUGIN_DIR . '/includes/WtKit.php');      // Статический класс и набор инструментов
include(WT_GT_PRO_PLUGIN_DIR . '/includes/wt_data_files.php');

require(WT_GT_PRO_PLUGIN_DIR . '/includes/WtGtAdminBehavior.php');

include(WT_GT_PRO_PLUGIN_DIR . '/includes/WtInitialization.php'); // Настройка библиотек
include(WT_GT_PRO_PLUGIN_DIR . '/includes/WtGeolocation.php');		// Оболочка для работы с Web-сервисами

class WtGeoTargeting
{
    public $modules = array();

    function __construct(){
        add_action('plugins_loaded', array($this, 'pluginsLoaded'));
        add_action('init', array($this, 'initial'));
    }

    static function activation(){
    }

    static function deactivation(){}

    public function uninstall(){}

    function pluginsLoaded(){
        if (defined('ABSPATH') && is_admin()) $this->initialAdmin();

        $this->activationModules();

        new WtInitialization();
    }

    function initial(){
    }

    public function initialAdmin(){
        require(WT_GT_PRO_PLUGIN_DIR . '/includes/wt_gt_admin.php');

        // Регистрация скриптов для админки
        add_action('admin_enqueue_scripts', array($this, 'registerAdminScripts'));

        $wt_gt_pro_admin = new WtGtAdmin();
        $wt_gt_pro_admin->geotargeting = $this;
    }

    public function activationModules(){
        foreach ($this->modules as $key => $module){
            require(WT_GT_PRO_PLUGIN_DIR . '/modules/' . $key . '/' . $key . '.php');
            new $key();
        }
    }

    public function registerScripts(){
    }

    public function registerAdminScripts(){
        wp_register_script(
            'wt-geotargeting-admin',
            plugin_dir_url(WT_GT_PRO_PLUGIN_FILE) . '/js/admin.js',
            array('jquery'),
            '1.0.0'
        );
        wp_enqueue_script('wt-geotargeting-admin');
    }
}

new WtGeoTargeting();
register_activation_hook(WT_GT_PRO_PLUGIN_FILE, array('WtGeoTargeting', 'activation'));
register_deactivation_hook(WT_GT_PRO_PLUGIN_FILE, array('WtGeoTargeting', 'deactivation'));
register_uninstall_hook(WT_GT_PRO_PLUGIN_FILE, array('WtGeoTargeting', 'uninstall'));