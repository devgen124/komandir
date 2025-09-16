<?php

declare(strict_types=1);

namespace {

    defined('ABSPATH') or exit;
}

namespace Cdek\Validator {

    use Cdek\CdekApi;
    use Cdek\Config;
    use Cdek\Exceptions\CacheException;
    use Cdek\Exceptions\External\ApiException;
    use Cdek\Exceptions\External\CoreAuthException;
    use Cdek\Helpers\CheckoutHelper;
    use Cdek\MetaKeys;
    use Cdek\Model\Tariff;
    use Throwable;

    class CheckoutValidator
    {
        private bool $checkOffice;
        public function __construct(bool $checkOffice = true)
        {
            $this->checkOffice = $checkOffice;
        }

        public function __invoke(): void
        {
            $rate = CheckoutHelper::getSelectedShippingRate();

            if( is_null($rate) ) {
                return;
            }

            $meta = $rate->get_meta_data();

            if ( in_array((int)$meta[MetaKeys::TARIFF_MODE], Tariff::listOfficeDeliveryModes(), true) ) {
                if ( empty($meta[MetaKeys::OFFICE_CODE]) && $this->checkOffice ) {
                    wc_add_notice(esc_html__('Order pickup point not selected.', 'cdekdelivery'), 'error');
                }
            } else {
                if ( empty(CheckoutHelper::getCurrentValue('address_1')) ) {
                    wc_add_notice(esc_html__('No shipping address.', 'cdekdelivery'), 'error');
                }

                $city   = CheckoutHelper::getCurrentValue('city');
                $postal = CheckoutHelper::getCurrentValue('postcode');

                if ( (new CdekApi)->cityCodeGet($city, $postal) === null ) {
                    wc_add_notice(
                        sprintf(/* translators: 1: Name of a city 2: ZIP code */ esc_html__(
                            'Failed to determine locality in %1$s %2$s',
                            'cdekdelivery',
                        ),
                            $city,
                            $postal,
                        ),
                        'error',
                    );
                }
            }

            $phone = CheckoutHelper::getCurrentValue('phone');

            if ( empty($phone) ) {
                wc_add_notice(esc_html__('Phone number is required.', 'cdekdelivery'), 'error');
            } else {
                try {
                    PhoneValidator::new()($phone, CheckoutHelper::getCurrentValue('country'));
                } catch (CoreAuthException|ApiException|CacheException $e) {
                    return;
                } catch (Throwable $e) {
                    wc_add_notice($e->getMessage(), 'error');
                }
            }
        }
    }
}
