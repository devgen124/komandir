<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе установки.
 * Необязательно использовать веб-интерфейс, можно скопировать файл в "wp-config.php"
 * и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://ru.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define( 'DB_NAME', "pomukk9g_db" );

/** Имя пользователя MySQL */
define( 'DB_USER', "pomukk9g_db" );

/** Пароль к базе данных MySQL */
define( 'DB_PASSWORD', "komandir2020*" );

/** Имя сервера MySQL */
define( 'DB_HOST', "localhost" );

/** Кодировка базы данных для создания таблиц. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Схема сопоставления. Не меняйте, если не уверены. */
define( 'DB_COLLATE', '' );

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу. Можно сгенерировать их с помощью
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}.
 *
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными.
 * Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '/J2+5][K={8*L,QJL>7KIfmGn9[vT%`}HMC*+{~nI0>#z}MqnAlr YKO4?v<]6t ' );
define( 'SECURE_AUTH_KEY',  'Q7%5Xd,+k@HwDBkZJ*}`S>s/,Sy:S4i%{~&R|/g7drX*Sgp5e}ZJgocftO5z{hxa' );
define( 'LOGGED_IN_KEY',    'QaGx:HmAeQ@S.EoUF{7`-FR*_3a6KLH,FFjY|K.I$W{_>Z4/`0-n+,nh4r.ZZwUc' );
define( 'NONCE_KEY',        'o<m]&%#LS5AwUu8%,mIIGa,i2?hg6F{$gkH y})zdL,HK|B~YsPC(]nx^4*Tq;|y' );
define( 'AUTH_SALT',        '*%t);a-,FF;ooBr;K#j;3#xE0Q?;%^BG8JqJU)mgB${Y~*So)[(ZYRQt1#_%<ukE' );
define( 'SECURE_AUTH_SALT', ':*[TXt;inC?&g<]ayMOP`]LY)cb->jw{$4EIsWz@-GYqH;+iIe#8mpGrdHb>Yf4T' );
define( 'LOGGED_IN_SALT',   '5e?t`UM: GKZVn=Zq*#|_*lmUZ=fM:[=r<N[&/qTCxTA.d{n7}w55K_P86*b]hF7' );
define( 'NONCE_SALT',       'JSYeb$ywZ|T?_D6=IkW%5H1S2={qW><H2`%sa!RL9Uf{r{+@E{[Zjm.`(8S~+G#u' );

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 *
 * Информацию о других отладочных константах можно найти в документации.
 *
 * @link https://ru.wordpress.org/support/article/debugging-in-wordpress/
 */

define('WP_DEBUG', false);
 

/* Произвольные значения добавляйте между этой строкой и надписью "дальше не редактируем". */

// define( 'WP_DEBUG_LOG', true );

// define( 'WP_DEBUG_DISPLAY', false );
 
// @ini_set( 'display_errors', 0 );

define('WP_MEMORY_LIMIT','64M');

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Инициализирует переменные WordPress и подключает файлы. */
require_once ABSPATH . 'wp-settings.php';
