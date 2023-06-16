<?php

namespace Talandis\LaravelBanklinks\Banklink;

use Kevin\Client;
use Kevin\SecurityManager;

class Kevin extends Banklink
{

    const STATUS_GROUP_COMPLETED = 'completed';

    /** @var \Kevin\Client */
    protected $client;

    protected $state;

    protected $clientId;

    protected $clientSecret;

    protected $endpointSecret;

    protected $creditorName;

    protected $creditorAccountIban;

    protected $kevinSettings = [];

    protected $description;

    protected $amount;

    protected function getConfigurationFields()
    {
        return array(
            'client_id' => 'clientId',
            'client_secret' => 'clientSecret',
            'endpoint_secret' => 'endpointSecret',
            'creditor_name' => 'creditorName',
            'creditor_account_iban' => 'creditorAccountIban',
            'kevin_settings' => 'kevinSettings'
        );
    }

    protected function getServiceId($type)
    {
        return null;
    }

    public function setConfiguration($configuration)
    {
        parent::setConfiguration($configuration);

        $this->client = new Client(
            $this->clientId,
            $this->clientSecret,
            array_merge([
                'error' => 'array',
                'version' => '0.3',
                'lang' => 'lt',
            ], (array)$this->kevinSettings)
        );
    }

    public function getRequestMethod()
    {
        return 'GET';
    }

    public function getRequestUrl()
    {
        $request = $this->client->payment()->initPayment([
            'Redirect-URL' => $this->returnUrl,
            'Webhook-URL' => $this->callbackUrl,
            'bankId' => $this->bank,
            'redirectPreferred' => true,
            'description' => $this->description,
            'currencyCode' => 'EUR',
            'amount' => $this->amount,
            'bankPaymentMethod' => [
                'endToEndId' => '' . $this->orderId,
                'creditorName' => $this->creditorName,
                'creditorAccount' => [
                    'iban' => $this->creditorAccountIban
                ],
            ],
        ]);


        if (!empty($request['error'])) {

            $error = !empty($request['data']) ? $request['data'] : $request['error']['description'];

            throw new \Exception($request['error']['name'] . ': ' . $error, $request['error']['code']);
        }

        return $request['confirmLink'];
    }

    public function getPaymentRequest($orderId, $sum, $description, $email = null)
    {
        $this->orderId = $orderId;
        $this->amount = $sum;
        $this->description = $description;

        return [];
    }

    protected function getPaymentRequestData($orderId, $sum, $description, $email = null)
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
        return [
            'id',
            'bankStatus',
            'statusGroup',
            'type',
        ];
    }

    protected function getPaymentReturnFields()
    {
        return [
            'paymentId',
            'status',
            'statusGroup'
        ];
    }

    public function retrieveOrderId($paymentReference)
    {
        $response = $this->client->payment()->getPayment($paymentReference);

        $this->orderId = $response['bankPaymentMethod']['endToEndId'];
        $this->state = $response['statusGroup'];
    }

    public function isReturnResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentReturnFields()) && $this->state == static::STATUS_GROUP_COMPLETED;
    }

    public function isCancelResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentCancelFields());
    }

    public function isPaidResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentSuccessFields()) && $this->state == static::STATUS_GROUP_COMPLETED;
    }

    public function isValidResponse($data, $fields)
    {
        $isValidResponse = parent::isValidResponse($data, $fields);

        if ($isValidResponse) {
            $this->retrieveOrderId($data['paymentId']);
        }

        return $isValidResponse;
    }

    protected function validateSignature($data, $fields)
    {
        return true;

        $timestampTimeout = 300000;

        $requestBody = file_get_contents('php://input');
        $headers = getallheaders();

        $isValid = SecurityManager::verifySignature(
            $this->endpointSecret,
            $requestBody,
            $headers,
            $this->callbackUrl,
            $timestampTimeout
        );

        return $isValid;
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
