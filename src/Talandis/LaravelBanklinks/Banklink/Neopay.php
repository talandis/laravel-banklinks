<?php

namespace Talandis\LaravelBanklinks\Banklink;

class Neopay extends Banklink
{
    protected $state;

    protected $projectId;

    protected $projectKey;

    protected $includeIat;

    protected $test;

    protected $description;

    protected $amount;

    protected $decodedPayload = [];

    protected function getConfigurationFields()
    {
        return array(
            'project_id' => 'projectId',
            'project_key' => 'projectKey',
            'include_iat' => 'includeIat',
            'test' => 'test',
        );
    }

    protected function getServiceId($type)
    {
        return null;
    }

    public function setConfiguration($configuration)
    {
        parent::setConfiguration($configuration);
    }

    public function getRequestMethod()
    {
        return 'GET';
    }

    public function getRequestUrl()
    {
        $fields = [
            'projectId' => $this->projectId,
            'transactionId' => uniqid($this->orderId . '.', true),
            'orderId' => $this->orderId,
            'amount' => $this->test && $this->amount > 1 ? 0.99 : $this->amount,
            'currency' => 'EUR',
            'paymentPurpose' => $this->description,
            'flow' => 'redirect+',
            'serviceType' => 'pisp',
            'clientRedirectUrl' => $this->returnUrl,
            'bank' => $this->bank,
            'exp' => strtotime('+2 days'),
        ];

        if (!empty($this->includeIat)) {
            $fields['iat'] = time();
        }

        return 'https://psd2.neopay.lt/widget.html?' . $this->generateJwt($fields, $this->projectKey);
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
        return [
            'data',
            'pending'
        ];
    }

    protected function getPaymentSuccessFields()
    {
        return [
            'token',
        ];
    }

    protected function getPaymentReturnFields()
    {
        return [
            'token',
        ];
    }

    public function retrieveOrderId($paymentReference)
    {
        list($orderId, $uniqueHash) = explode('.', $paymentReference['transactionId'], 2);

        $this->orderId = $orderId;
    }

    public function isReturnResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentReturnFields()) && $this->decodedPayload['status'] === 'signed';
    }

    public function isCancelResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentCancelFields());
    }

    public function isPaidResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentSuccessFields()) && $this->decodedPayload['status'] === 'success' && $this->decodedPayload['action'] === 'signed';
    }

    public function isValidResponse($data, $fields)
    {
        $isValidResponse = parent::isValidResponse($data, $fields);

        if ($isValidResponse) {
            $this->retrieveOrderId($this->decodedPayload);
        }

        return $isValidResponse;
    }

    protected function validateSignature($data, $fields)
    {
        list ($header, $payload, $signature) = explode('.', $data['data'] ?? $data['token']);

        $this->decodedPayload = json_decode($this->base64UrlDecode($payload), true);

        $isValidToken = $this->verifyJwtSignature($header, $payload, $signature, $this->projectKey);

        if (!$isValidToken) {
            return false;
        }

        if (!empty($this->decodedPayload->exp) && $this->decodedPayload->exp < time()) {
            return false;
        }

        if (!empty($this->decodedPayload->iat) && $this->decodedPayload->iat > time()) {
            return false;
        }

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

    protected function generateJwt($payload, $signingKey)
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];
        $header = $this->base64UrlEncode(json_encode($header));
        $payload = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $signingKey, true));
        $jwt = "$header.$payload.$signature";
        return $jwt;

    }

    protected function base64UrlEncode($text)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    protected function base64UrlDecode($text)
    {
        return base64_decode(
            str_replace(
                ['_', '-'],
                ['/', '+'],
                $text
            )
        );
    }

    protected function verifyJwtSignature($header, $payload, $signature, $signingKey)
    {
        $comparisonSignature = hash_hmac('sha256', "$header.$payload", $signingKey, true);
        $tokenSignature = $this->base64UrlDecode($signature);

        return hash_equals($comparisonSignature, $tokenSignature);
    }
}
