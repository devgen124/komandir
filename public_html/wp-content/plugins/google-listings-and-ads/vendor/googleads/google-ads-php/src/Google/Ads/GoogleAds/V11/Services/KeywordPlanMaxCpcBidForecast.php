<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v11/services/keyword_plan_service.proto

namespace Google\Ads\GoogleAds\V11\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The forecast of the campaign at a specific bid.
 *
 * Generated from protobuf message <code>google.ads.googleads.v11.services.KeywordPlanMaxCpcBidForecast</code>
 */
class KeywordPlanMaxCpcBidForecast extends \Google\Protobuf\Internal\Message
{
    /**
     * The max cpc bid in micros.
     *
     * Generated from protobuf field <code>optional int64 max_cpc_bid_micros = 3;</code>
     */
    protected $max_cpc_bid_micros = null;
    /**
     * The forecast for the Keyword Plan campaign at the specific bid.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v11.services.ForecastMetrics max_cpc_bid_forecast = 2;</code>
     */
    protected $max_cpc_bid_forecast = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $max_cpc_bid_micros
     *           The max cpc bid in micros.
     *     @type \Google\Ads\GoogleAds\V11\Services\ForecastMetrics $max_cpc_bid_forecast
     *           The forecast for the Keyword Plan campaign at the specific bid.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V11\Services\KeywordPlanService::initOnce();
        parent::__construct($data);
    }

    /**
     * The max cpc bid in micros.
     *
     * Generated from protobuf field <code>optional int64 max_cpc_bid_micros = 3;</code>
     * @return int|string
     */
    public function getMaxCpcBidMicros()
    {
        return isset($this->max_cpc_bid_micros) ? $this->max_cpc_bid_micros : 0;
    }

    public function hasMaxCpcBidMicros()
    {
        return isset($this->max_cpc_bid_micros);
    }

    public function clearMaxCpcBidMicros()
    {
        unset($this->max_cpc_bid_micros);
    }

    /**
     * The max cpc bid in micros.
     *
     * Generated from protobuf field <code>optional int64 max_cpc_bid_micros = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setMaxCpcBidMicros($var)
    {
        GPBUtil::checkInt64($var);
        $this->max_cpc_bid_micros = $var;

        return $this;
    }

    /**
     * The forecast for the Keyword Plan campaign at the specific bid.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v11.services.ForecastMetrics max_cpc_bid_forecast = 2;</code>
     * @return \Google\Ads\GoogleAds\V11\Services\ForecastMetrics|null
     */
    public function getMaxCpcBidForecast()
    {
        return $this->max_cpc_bid_forecast;
    }

    public function hasMaxCpcBidForecast()
    {
        return isset($this->max_cpc_bid_forecast);
    }

    public function clearMaxCpcBidForecast()
    {
        unset($this->max_cpc_bid_forecast);
    }

    /**
     * The forecast for the Keyword Plan campaign at the specific bid.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v11.services.ForecastMetrics max_cpc_bid_forecast = 2;</code>
     * @param \Google\Ads\GoogleAds\V11\Services\ForecastMetrics $var
     * @return $this
     */
    public function setMaxCpcBidForecast($var)
    {
        GPBUtil::checkMessage($var, \Google\Ads\GoogleAds\V11\Services\ForecastMetrics::class);
        $this->max_cpc_bid_forecast = $var;

        return $this;
    }

}

