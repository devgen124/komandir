<?php

require_once 'sms-auth-controller.php';

class PopupController {

    public static function init() {

        $actions = [
            'login_send_code',
            'login_send_sms',
            'change_phone_send_code',
            'change_phone_send_sms',
            'create_customer',
            'authorize',
            'send_code_again'
        ];

        foreach ($actions as $action) {
            add_action("wp_ajax_nopriv_$action", [__CLASS__, "komandir_$action"]);
            add_action("wp_ajax_$action", [__CLASS__, "komandir_$action"]);
        }
    }

    public static function komandir_login_send_code() {

        check_ajax_referer('komandir-nonce', 'nonce_code');

        $response = array();

        if (!isset($_POST['phone'])) wp_die();

        $raw_phone = $_POST['phone'];

        if (self::is_phone(wc_sanitize_phone_number($raw_phone))) {

            $phone = wc_sanitize_phone_number($raw_phone);

            $last_time = WC()->session->get('sms_timestamp') ? (int) WC()->session->get('sms_timestamp') : 0;

            $now_time = time();
            $diff = $now_time - $last_time;

            if ($diff < 45) {

                $showDiff = 45 - $diff;

                $response['error_message'][] = "Повторно код можно отправить через $showDiff сек.";
            } else {
                WC()->session->set('phone_number', $phone);

                try {

                    self::send_code($phone);
                    $response['success'] = true;
                    $response['phone_number'] = $phone;
                } catch (Exception $e) {
                    $response['console_message'] = $e->getMessage();
                }
            }
        } else if (!trim($raw_phone)) {

            $response['error_message'][] = 'Заполните поле';
            $response['error_fields'][] = 'phone';
        } else {

            $response['error_message'][] = 'Неверный формат';
            $response['error_fields'][] = 'phone';
        }

        echo json_encode($response);

        wp_die();
    }

    public static function komandir_change_phone_send_code() {

        check_ajax_referer('komandir-nonce', 'nonce_code');

        $response = array();

        if (!isset($_POST['phone'])) wp_die();

        $raw_phone = $_POST['phone'];

        if (self::is_phone(wc_sanitize_phone_number($raw_phone))) {

            $current_user = wp_get_current_user();

            $current_user_phone = get_user_meta($current_user->ID, 'billing_phone', true);

            $phone = wc_sanitize_phone_number($raw_phone);

            if (self::get_user_by_phone($phone)) {

                $response['error_message'][] = 'Номер телефона занят';
                $response['error_fields'][] = 'phone';
            } else if ($current_user_phone == $phone) {

                $response['error_message'][] = 'Указан текущий номер телефона';
                $response['error_fields'][] = 'phone';
            } else {

                $last_time = WC()->session->get('sms_timestamp') ? (int) WC()->session->get('sms_timestamp') : 0;

                $now_time = time();
                $diff = $now_time - $last_time;

                if ($diff > 0 && $diff < 45) {

                    $response['error_message'][] = "Повторно код можно отправить через $diff сек.";
                } else {
                    WC()->session->set('phone_number', $phone);

                    try {

                        self::send_code($phone);
                        $response['success'] = true;
                        $response['phone_number'] = $phone;
                    } catch (Exception $e) {
                        $response['console_message'] = $e->getMessage();
                    }
                }
            }
        } else {

            $response['error_message'][] = !trim($raw_phone) ? 'Заполните поле' : 'Неверный формат';
            $response['error_fields'][] = 'phone';
        }

        echo json_encode($response);

        wp_die();
    }

    private static function is_phone($phone) {

        if (0 < strlen(trim(preg_replace('/[\s\#0-9_\-\+\/\(\)\.]/', '', $phone)))) {

            return false;
        } else {

            return true;
        }
    }

    private static function send_code($phone) {

        $smsAuth = new SmsAuthController('komandir124', 'Kom24A22');
        $response = array();

        try {

            $result = $smsAuth->generateCode(
                $phone,
                'komandir124',
                4,
                'Ваш код авторизации: {код}'
            );

            $code = (string) $result->success->attributes()['code'];

            WC()->session->set('sms_code', $code);
        } catch (Exception $e) {

            throw new Exception($e->getMessage());
        }
    }

    private static function send_code_test($phone) {
        $code = '1234';

        WC()->session->set('sms_code', $code);
        WC()->session->set('sms_timestamp', time());
    }

    public static function komandir_login_send_sms() {

        check_ajax_referer('komandir-nonce', 'nonce_code');

        $response = array();

        if (!isset($_POST['sms'])) wp_die();



        if (preg_match('/\d{4}/', $_POST['sms'])) {

            $sms = $_POST['sms'];

            if ($sms != WC()->session->get('sms_code')) {

                $response['error_message'][] = 'Неверный код';
                $response['error_fields'][] = 'sms';
            } else {

                $phone_number = WC()->session->get('phone_number');

                WC()->session->__unset('phone_number');
                WC()->session->__unset('sms_code');
                WC()->session->__unset('sms_timestamp');

                if (self::get_user_by_phone($phone_number)) {

                    $user = self::get_user_by_phone($phone_number);

                    wp_clear_auth_cookie();
                    wp_set_current_user($user->ID);
                    wp_set_auth_cookie($user->ID);

                    $response['reload'] = true;
                } else {

                    WC()->session->__unset('sms_code');
                    WC()->session->__unset('sms_timestamp');

                    $response['success'] = true;
                    $response['phone_number'] = $phone_number;
                }
            }
        } else {

            $response['error_message'][] = empty($_POST['sms']) ? 'Заполните поле' : 'Неверный формат';
            $response['error_fields'][] = 'sms';
        }

        echo json_encode($response);

        wp_die();
    }

    public static function komandir_change_phone_send_sms() {

        check_ajax_referer('komandir-nonce', 'nonce_code');

        $response = array();

        if (!isset($_POST['sms'])) wp_die();

        if (preg_match('/\d{4}/', $_POST['sms'])) {

            $sms = $_POST['sms'];

            if ($sms != WC()->session->get('sms_code')) {

                $response['error_message'][] = 'Неверный код';
                $response['error_fields'][] = 'sms';
            } else {

                $phone_number = WC()->session->get('phone_number');

                $current_user = wp_get_current_user();

                update_user_meta($current_user->ID, 'billing_phone', $phone_number);
                WC()->session->__unset('phone_number');
                WC()->session->__unset('sms_code');
                WC()->session->__unset('sms_timestamp');

                $response['reload'] = true;
            }
        } else {

            $response['error_message'][] = empty($_POST['sms']) ? 'Заполните поле' : 'Неверный формат';
            $response['error_fields'][] = 'sms';
        }

        echo json_encode($response);

        wp_die();
    }

    private static function get_user_by_phone($phone) {

        $users = get_users([
            'meta_key' => 'billing_phone',
            'meta_value' => $phone
        ]);

        return $users ? $users[0] : null;
    }

    public static function komandir_create_customer() {
        check_ajax_referer('komandir-nonce', 'nonce_code');

        $password_regexp = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/';

        $response = array();

        $email = filter_input(INPUT_POST, 'login-email', FILTER_VALIDATE_EMAIL);

        if ($email) {

            $user_data = get_user_by('email', $email);

            if ($user_data) {

                $response['error_message'][] = 'Пользователь с данной электронной почтой уже существует';
                $response['error_fields'][] = 'login-email';
            } else {

                $password_not_match_text = 'Пароль должен содержать не менее 8 символов, не менее одной цифры и одной буквы';

                if (!preg_match($password_regexp, $_POST['login-pass-first'])) {

                    $response['error_message'][] = $password_not_match_text;
                    $response['error_fields'][] = 'login-pass-first';
                }

                if (!preg_match($password_regexp, $_POST['login-pass-second'])) {

                    if (in_array(!$password_not_match_text, $response['error_message'])) {
                        $response['error_message'][] = $password_not_match_text;
                    }

                    $response['error_fields'][] = 'login-pass-second';
                }

                if ($_POST['login-pass-first'] != $_POST['login-pass-second']) {

                    $response['error_message'][] = 'Пароли не совпадают';
                    $response['error_fields'][] = 'login-pass-first';
                    $response['error_fields'][] = 'login-pass-second';
                }

                $required_fields = [
                    'Email' => 'login-email',
                    'Пароль' => 'login-pass-first',
                    'Повторите пароль' => 'login-pass-second'
                ];

                foreach ($required_fields as $text => $field) {

                    if (!trim($_POST[$field])) {

                        $response['error_message'][] = "Поле $text должно быть заполнено";
                        $response['error_fields'][] = $field;
                    }
                }

                if (!isset($response['error_message'], $response['error_fields'])) {

                    $account_display_name   = wc_clean(wp_unslash($_POST['login-display-name']));
                    $account_first_name     = !empty($_POST['login-first-name']) ? wc_clean(wp_unslash($_POST['login-first-name'])) : '';
                    $account_last_name      = !empty($_POST['login-second-name']) ? wc_clean(wp_unslash($_POST['login-second-name'])) : '';
                    $account_email          = !empty($_POST['login-email']) ? wc_clean(wp_unslash($_POST['login-email'])) : '';
                    $account_password       = !empty($_POST['login-pass-first']) ? $_POST['login-pass-first'] : '';
                    $phone_number           = !empty($_POST['login-phone']) ? wc_clean(wp_unslash($_POST['login-phone'])) : '';

                    $new_customer_id = wc_create_new_customer($account_email, $account_display_name, $account_password);

                    if (is_wp_error($new_customer_id)) {

                        $response['error_message'][] = $new_customer_id->get_error_message();
                    } else {

                        $current_user       = get_user_by('id', $new_customer_id);

                        $user               = new stdClass();
                        $user->ID           = $current_user->ID;
                        $user->first_name   = $account_first_name;
                        $user->last_name    = $account_last_name;

                        wp_update_user($user);

                        $customer = new WC_Customer();
                        $customer->set_billing_phone($phone_number);
                        update_user_meta($current_user->ID, 'billing_phone', $phone_number);
                        wp_clear_auth_cookie();
                        wp_set_current_user($current_user->ID);
                        wp_set_auth_cookie($current_user->ID);
                        $response['reload'] = true;
                    }
                }
            }
        } else {

            $response['error_message'][] = 'Некорректный email';
            $response['error_fields'][] = 'login-email';
        }

        echo json_encode($response);

        wp_die();
    }

    public static function komandir_authorize() {

        check_ajax_referer('komandir-nonce', 'nonce_code');

        $response = array();

        $login = trim(wp_unslash($_POST['login']));

        if (!$login) {

            $response['error_message'][] = 'Поле Логин не заполнено';
            $response['error_fields'][] = 'login';
        } else {

            $user = null;

            $phone_regexp = '/^(\+7|7|8)?[\s\-]?\(?[489][0-9]{2}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/';

            if (preg_match($phone_regexp, $login)) {
                $response['phone'] = 1;

                $phone_tail = substr(preg_replace('/[\+\-\s\(\)]/', '', $login), -10);

                $phone = '+7' . $phone_tail;

                $user = self::get_user_by_phone($phone);
            } else {
                $response['login'] = is_email($login) ? 'email' : 'login';

                $user = get_user_by(is_email($login) ? 'email' : 'login', $login);
            }



            if ($user) {

                $user_pass_hash = $user->data->user_pass;

                if (isset($_POST['pass'])) {

                    $pass_input = trim($_POST['pass']);

                    if (!$pass_input) {

                        $response['error_message'][] = 'Поле Пароль не заполнено';
                        $response['error_fields'][] = 'pass';
                    } else {

                        if (!wp_check_password($_POST['pass'], $user_pass_hash)) {

                            $response['error_message'][] = 'Пароль неверен';
                            $response['error_fields'][] = 'pass';
                        } else {

                            $cur_user = wp_signon([
                                'user_login' => $user->data->user_login,
                                'user_password' => $_POST['pass'],
                                'remember' => true
                            ], is_ssl());

                            if (is_wp_error($cur_user)) {

                                $response['error_message'][] = $user->get_error_message();
                            } else {

                                $response['reload'] = true;
                            }
                        }
                    }
                }
            } else {
                $response['error_message'][] = 'Пользователь не найден';
                $response['error_fields'][] = 'login';
            }
        }

        echo json_encode($response);

        wp_die();
    }

    public static function komandir_send_code_again() {
        check_ajax_referer('komandir-nonce', 'nonce_code');

        $response = array();

        $phone = WC()->session->get('phone_number');

        WC()->session->set('sms_timestamp', time());


        try {

            self::send_code($phone);
            $response['success'] = true;

        } catch (Exception $e) {
            $response['console_message'] = $e->getMessage();
        }

        echo json_encode($response);

        wp_die();
    }
}

PopupController::init();
