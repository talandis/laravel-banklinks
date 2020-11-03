<?php

namespace Talandis\LaravelBanklinks\Banklink;

use GuzzleHttp\Client;

class Everypay extends Banklink
{

    const STATE_SETTLED = 'settled';

    /** @var Client */
    protected $client;

    protected $apiUsername;

    protected $accountName;

    protected $apiSecret;

    protected $baseUrl;

    protected $amount;

    protected $bank;

    protected $state;

    protected function getConfigurationFields()
    {
        return array(
            'api_username' => 'apiUsername',
            'api_secret' => 'apiSecret',
            'account_name' => 'accountName',
            'base_url' => 'baseUrl',
        );
    }

    protected function getServiceId($type)
    {
        return null;
    }

    public function setConfiguration($configuration)
    {
        parent::setConfiguration($configuration);

        $this->client = new Client();
    }

    public function getRequestMethod()
    {
        return 'GET';
    }

    public function getRequestUrl()
    {

        $response = $this->client->post($this->baseUrl . '/payments/oneoff', [
            'auth' => [
                $this->apiUsername,
                $this->apiSecret
            ],
            'form_params' => [
                'api_username' => $this->apiUsername,
                'account_name' => $this->accountName,
                'nonce' => uniqid('pay_'),
                'timestamp' => date('c'),
                'amount' => $this->amount,
                //            'email' => $email,
                'order_reference' => $this->orderId,
                'customer_url' => $this->returnUrl,
                'callback_url' => $this->callbackUrl,
            ]
        ]);

        $result = json_decode($response->getBody()->getContents());

        foreach ($result->payment_methods as $method) {
            if ($method->source == $this->bank) {
                return $method->payment_link;
            }
        }

        return $result->payment_link;
    }

    public function getPaymentRequest($orderId, $sum, $description)
    {
        $this->orderId = $orderId;
        $this->amount = $sum;

        return [];
    }

    protected function getPaymentRequestData($orderId, $sum, $description)
    {
    }

    protected function getPaymentRequestFields()
    {
    }

    protected function getPaymentCancelFields()
    {
        return $this->getPaymentReturnFields();
    }

    protected function getPaymentSuccessFields()
    {
        return $this->getPaymentReturnFields();
    }

    protected function getPaymentReturnFields()
    {
        return [
            'payment_reference',
            'is_callback'
        ];
    }

    public function retrieveOrderId($paymentReference)
    {
        $response = $this->client->get($this->baseUrl . '/payments/' . $paymentReference, [
            'auth' => [
                $this->apiUsername,
                $this->apiSecret
            ],
            'query' => [
                'api_username' => $this->apiUsername,
            ]
        ]);

        $result = json_decode($response->getBody()->getContents());

        $this->orderId = $result->order_reference;
        $this->state = $result->payment_state;
    }

    public function isReturnResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentReturnFields()) && $this->state == static::STATE_SETTLED && empty($data['is_callback']);
    }

    public function isCancelResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentCancelFields());
    }

    public function isPaidResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentSuccessFields()) && $this->state == static::STATE_SETTLED && !empty($data['is_callback']);
    }

    public function isValidResponse($data, $fields)
    {
        $isValidResponse = parent::isValidResponse($data, $fields);

        if ($isValidResponse) {
            $this->retrieveOrderId($data['payment_reference']);
        }

        return $isValidResponse;
    }

    protected function validateSignature($data, $fields)
    {
        return true;
    }

    protected function getRequestSignature($data, $id)
    {
        return '';
    }

    protected function generateHash($data, $fields)
    {
        return '';
    }
}
