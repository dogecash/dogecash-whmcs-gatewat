<?php

/**
 * WHMCS Sample Payment Gateway Module
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * This sample file demonstrates how a payment gateway module for WHMCS should
 * be structured and all supported functionality it can contain.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "gatewaymodule" and therefore all functions
 * begin "gatewaymodule_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function dogecash_MetaData()
{
    return array(
        'DisplayName' => 'DogeCash',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function dogecash_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'DogeCash Gateway',
        ),
        'dogecash_address' => array(
            'FriendlyName' => 'DogeCash Address',
            'Type' => 'text',
            'Size' => '34',
            'Default' => '',
            'Description' => 'Your DogeCash Address',
        ),
        'dogecash_confirmations' => array(
            'FriendlyName' => 'Confirmations',
            'Type' => 'text',
            'Size' => '3',
            'Default' => '10',
            'Description' => 'Number of confirmations upon which the order will be considered as confirmed',
        ),
        'dogecash_maxtime' => array(
            'FriendlyName' => 'Maximum Payment Time (in Minutes)',
            'Type' => 'text',
            'Size' => '3',
            'Default' => '20',
            'Description' => 'Time allowed for a user to make the required payment.',
        ),
        'dogecash_secretkey' => array(
            'FriendlyName' => 'Secret key',
            'Type' => 'password',
            'Size' => '30',
            'Default' => substr(str_repeat(md5(rand()), ceil(20 / 32)), 0, 20),
            'Description' => 'This random key won\'t allow script crackers to impersonate payments. The default value is good enough.',
        ),
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function dogecash_link($params)
{
    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];



    // System Parameters
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleName = $params['paymentmethod'];
    $crypto_address = $params['dogecash_address'];
    $maxTime = $params['dogecash_maxtime'];
    $required_confirmations = $params['dogecash_confirmations'];
    $secretKey = $params['dogecash_secretkey'];

    $cryptoConversion = convertToDogeCash($amount, $currencyCode, $invoiceId, $maxTime);

    $cryptoRate = $cryptoConversion['rate'];
    $cryptoAmount = $cryptoConversion['amount'];
    $order_time = time();

    $hash = hash('sha256', $invoiceId . $cryptoAmount . $secretKey);

    $url = '/modules/gateways/dogecash/receivePayment.php';

    localAPI('UpdateInvoice', [
        'invoiceid' => $invoiceId,
        'notes' => "$cryptoAmount DOGEC"
    ]);

    $postfields = array();
    $postfields['invoice_id'] = $invoiceId;
    $postfields['fiat_amount'] = $amount;
    $postfields['return_url'] = $returnUrl;
    $postfields['currency'] = $currencyCode;
    $postfields['crypto_rate'] = $cryptoRate;
    $postfields['crypto_amount'] = $cryptoAmount;
    $postfields['crypto_address'] = $crypto_address;
    $postfields['payment_maxtime'] = $maxTime;
    $postfields['order_time'] = $order_time;
    $postfields['payment_confirmations'] = $required_confirmations;


    $postfields['callback_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
    $postfields['hash'] = $hash;

    $htmlOutput = '<form method="post" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}


/**
 * Helper function.
 *
 * Receives value & currency and returns value in DogeCash
 * 
 *
 * @return float
 */
function convertToDogeCash($value, $currency, $invoiceId, $maxtime)
{
    try {
        $request = file_get_contents("https://api.coingecko.com/api/v3/coins/dogecash");
        $data = json_decode($request, true);
        $price = $data['market_data']['current_price'][strtolower($currency)];
        $amount = round($value / $price, 2);

        $results = localAPI('GetInvoices', [
            'status' => 'Unpaid',
            'orderby' => 'date'
        ]);

        $difference = 0.001;

        foreach ($results['invoices']['invoice'] as $result) {
            $invoiceDueRate = new DateTime($result["updated_at"]);
            $currentTime = new DateTime();
            $invoiceDueRate->modify("+$maxtime minutes");

            if ($result['id'] != $invoiceId && $result['paymentmethod'] == 'dogecash' && str_replace('DOGEC', '', $result['notes']) == $amount && $currentTime < $invoiceDueRate) {
                $amount += $difference;
            }
        }

        return [
            'rate' => round($price, 2),
            'amount' => $amount
        ];
    } catch (\Throwable $e) {
        echo $e->getMessage();
    }
}
