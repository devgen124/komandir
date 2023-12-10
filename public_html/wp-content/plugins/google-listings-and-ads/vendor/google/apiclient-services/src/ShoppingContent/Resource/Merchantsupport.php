<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Automattic\WooCommerce\GoogleListingsAndAds\Vendor\Google\Service\ShoppingContent\Resource;

use Automattic\WooCommerce\GoogleListingsAndAds\Vendor\Google\Service\ShoppingContent\RenderAccountIssuesRequestPayload;
use Automattic\WooCommerce\GoogleListingsAndAds\Vendor\Google\Service\ShoppingContent\RenderAccountIssuesResponse;
use Automattic\WooCommerce\GoogleListingsAndAds\Vendor\Google\Service\ShoppingContent\RenderProductIssuesRequestPayload;
use Automattic\WooCommerce\GoogleListingsAndAds\Vendor\Google\Service\ShoppingContent\RenderProductIssuesResponse;

/**
 * The "merchantsupport" collection of methods.
 * Typical usage is:
 *  <code>
 *   $contentService = new Google\Service\ShoppingContent(...);
 *   $merchantsupport = $contentService->merchantsupport;
 *  </code>
 */
class Merchantsupport extends \Automattic\WooCommerce\GoogleListingsAndAds\Vendor\Google\Service\Resource
{
  /**
   * Provide a list of merchant's issues with a support content and available
   * actions. This content and actions are meant to be rendered and shown in
   * third-party applications. (merchantsupport.renderaccountissues)
   *
   * @param string $merchantId Required. The ID of the account to fetch issues
   * for.
   * @param RenderAccountIssuesRequestPayload $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string languageCode Optional. The [IETF
   * BCP-47](https://tools.ietf.org/html/bcp47) language code used to localize
   * support content. If not set, the result will be in default language ('en-
   * US').
   * @opt_param string timeZone Optional. The [IANA](https://www.iana.org/time-
   * zones) timezone used to localize times in support content. For example
   * 'America/Los_Angeles'. If not set, results will use as a default UTC.
   * @return RenderAccountIssuesResponse
   */
  public function renderaccountissues($merchantId, RenderAccountIssuesRequestPayload $postBody, $optParams = [])
  {
    $params = ['merchantId' => $merchantId, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('renderaccountissues', [$params], RenderAccountIssuesResponse::class);
  }
  /**
   * Provide a list of issues for merchant's product with a support content and
   * available actions. This content and actions are meant to be rendered and
   * shown in third-party applications. (merchantsupport.renderproductissues)
   *
   * @param string $merchantId Required. The ID of the account that contains the
   * product.
   * @param string $productId Required. The
   * [REST_ID](https://developers.google.com/shopping-
   * content/reference/rest/v2.1/products#Product.FIELDS.id) of the product to
   * fetch issues for.
   * @param RenderProductIssuesRequestPayload $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string languageCode Optional. The [IETF
   * BCP-47](https://tools.ietf.org/html/bcp47) language code used to localize
   * support content. If not set, the result will be in default language ('en-
   * US').
   * @opt_param string timeZone Optional. The [IANA](https://www.iana.org/time-
   * zones) timezone used to localize times in support content. For example
   * 'America/Los_Angeles'. If not set, results will use as a default UTC.
   * @return RenderProductIssuesResponse
   */
  public function renderproductissues($merchantId, $productId, RenderProductIssuesRequestPayload $postBody, $optParams = [])
  {
    $params = ['merchantId' => $merchantId, 'productId' => $productId, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('renderproductissues', [$params], RenderProductIssuesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Merchantsupport::class, 'Google_Service_ShoppingContent_Resource_Merchantsupport');
