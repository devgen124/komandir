<?php

define('RBS_PAYMENT_NAME', 'Sber');

define('RBS_PROD_URL' , 'https://securepayments.sberbank.ru/payment/rest/');
define('RBS_TEST_URL' , 'https://3dsec.sberbank.ru/payment/rest/');

define('RBS_ENABLE_LOGGING', true);
define('RBS_ENABLE_FISCALE_OPTIONS', true);

define('RBS_MEASUREMENT_NAME', 'шт'); //FFD v1.05
define('RBS_MEASUREMENT_CODE', 0); //FFD v1.2

define('RBS_SKIP_CONFIRMATION_STEP', true);
define('RBS_CUSTOMER_EMAIL_SEND', true); //PLUG-4667
define('RBS_ENABLE_CALLBACK', 1);
