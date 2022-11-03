<?php
if (!class_exists('Wt')) {

    /**
     * Class Wt
     * ver.9
     */
    class Wt
    {
        /**
         * Объекты данных
         * 23.09.2016
         */
        static $obj;

        /**
         * Тема
         * 17.11.2016
         */
        static $theme;

        static $gt;
        static $geolocation;
        static $location;

        static $cache_data;

        protected static $instance;

        protected function __construct()
        {
            self::$obj = new stdClass;
            self::$theme = new stdClass;

            add_shortcode('wt_kit', array($this, 'shortcodeMain'));
        }

        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
            }

            return static::$instance;
        }

        /**
         * Регистрация объекта данных
         * 14.12.2016
         *
         * @param $name
         * @param $value
         * @return bool
         */
        public static function setObject($name, $value)
        {
            if (empty($name) || empty($value)) return false;

            self::$obj->$name = $value;
        }

        /**
         * Регистрация настроек темы
         * 14.12.2016
         *
         * @param $name
         * @param $value
         * @return bool
         */
        public static function setTheme($name, $value)
        {
            if (empty($name) || empty($value)) return false;

            self::$theme->$name = $value;
        }

        /**
         * Вывод постов в виде выпадающего списка
         * Функция является модифицированной копией wp_dropdown_pages()
         * 08.11.2016
         */
        static function wp_dropdown_posts($args = '')
        {
            $defaults = array(
                'post_type' => 'post',
                'depth' => 0,
                'child_of' => 0,
                'selected' => 0,
                'select' => array(
                    'name' => 'post_id',
                    'id' => '',
                    'class' => '',
                ),
                'echo' => 1,

                'show_option_none' => '',
                'show_option_no_change' => '',
                'option_none_value' => '',
                'value_field' => 'ID',
                'numberposts' => -1
            );

            $r = wp_parse_args($args, $defaults);

            $posts = get_posts($r);

            // Удаляем HTML теги из заголовка
            foreach ($posts as $post) {
                $post->post_title = strip_tags($post->post_title);
            }

            $output = '';
            // Back-compat with old system where both id and name were based on $name argument
            if (empty($r['select']['id'])) {
                $r['select']['id'] = $r['name'];
            }

            if (!empty($posts)) {
                $class = '';
                if (!empty($r['select']['class'])) {
                    $class = " class='" . esc_attr($r['select']['class']) . "'";
                }

                $output = "<select name='" . esc_attr($r['select']['name']) . "'" . $class . " id='" . esc_attr($r['select']['id']) . "'>\n";
                if ($r['show_option_no_change']) {
                    $output .= "\t<option value=\"-1\">" . $r['show_option_no_change'] . "</option>\n";
                }
                if ($r['show_option_none']) {
                    $output .= "\t<option value=\"" . esc_attr($r['option_none_value']) . '">' . $r['show_option_none'] . "</option>\n";
                }
                $output .= walk_page_dropdown_tree($posts, $r['depth'], $r);
                $output .= "</select>\n";
            }

            if ($r['echo']) {
                echo $output;
            }
            return $output;
        }

        // Wt::debugLogAdd('text log');
        static function debugLogAdd($content = null)
        {
            // Имя файла с логами
            $filename = WP_CONTENT_DIR . '/wt_debug.txt';

            $file_content = date("Y-m-d H:i:s");

            $file_content .= PHP_EOL;

            if (is_array($content) || is_object($content)){
                $file_content .= print_r($content, true);;
            }elseif (!empty($content)){
                $file_content .= $content;
            }

            if (!empty($content)) {
                $file_content .= PHP_EOL;
            }

            // Если файл существует - дописываем данные
            if (file_exists($filename)) {
                $file_content .= PHP_EOL;
                $file_content .= file_get_contents($filename);
            }

            // Записываем данные в файл
            file_put_contents($filename, $file_content);
        }

        /**
         * Шорткод [wt_kit] для произвольного отображения контента
         *
         * @param $atts
         * @param null $content
         * @return |null
         */
        public function shortcodeMain($atts, $content = null)
        {
            $atts = shortcode_atts(array(
                'post_type' => null,
                'label' => null,
                'get' => 'content'
            ), $atts);

            if (empty($atts['post_type'])) return null;
            if (empty($atts['label'])) return null;

            $args = array(
                'post_type' => $atts['post_type'],
                'name' => $atts['label'],
                'posts_per_page' => -1
            );

            $query = new WP_Query($args);

            if (empty($query->posts[0])) return null;

            $post = $query->posts[0];

            $return = do_shortcode($post->post_content);
            $return = wpautop($return);

            return $return;
        }

        static function getUserIdByMeta($meta_key, $meta_value)
        {
            global $wpdb;

            $meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

            $user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %d AND meta_value = %s", $meta_key, $meta_value));

            return $user_id;
        }

        static function getPostIdByMeta($meta_key, $meta_value)
        {
            global $wpdb;

            $meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

            $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %d AND meta_value = %s", $meta_key, $meta_value));

            return $post_id;
        }

        static function getPostIdsByMeta($meta_key, $meta_value)
        {
            $cache_key = 'getPostsIdByMeta_' . $meta_key . '_' . $meta_value;

            $post_ids = wp_cache_get( $cache_key );

            if( false === $post_ids ){
                global $wpdb;
                $meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

                $post_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %d AND meta_value = %s", $meta_key, $meta_value));

                wp_cache_set( $cache_key, $post_ids );
            }

            return $post_ids;
        }
    }

    Wt::getInstance();
}