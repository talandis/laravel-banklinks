<?php

namespace Talandis\LaravelBanklinks\Banklink;

abstract class iPizza extends Banklink
{

    protected $requestEncoding = '';

    protected $version = '008';

    protected $language = 'LIT';

    protected $currency = 'EUR';

    protected $signatureField = 'VK_MAC';

    protected $signatureReturnedField = 'VK_MAC';

    protected $orderIdField = 'VK_REF';

    protected function getServiceId($type)
    {
        switch ($type) {
            case self::PAYMENT_REQUEST:
                return '1001';
            case self::PAYMENT_SUCCESS:
                return '1101';
            case self::PAYMENT_CANCEL:
                return '1901';
            case self::PAYMENT_ERROR:
                return '1902';
            case self::PAYMENT_RETURN:
                return '1201';
        }

        throw new \LogicException(sprintf('Invalid service type: %s', $type));
    }

    public function getPaymentRequestData($orderId, $sum, $description, $email = null )
    {

        $requestData = array(
            'VK_SERVICE' => $this->getServiceId(self::PAYMENT_REQUEST),
            'VK_VERSION' => $this->version,
            'VK_SND_ID' => $this->sellerId,
            'VK_STAMP' => $orderId,
            'VK_AMOUNT' => $sum,
            'VK_CURR' => $this->currency,
            'VK_ACC' => $this->sellerAccountNumber,
            'VK_NAME' => $this->sellerName,
            'VK_REF' => $this->getReferenceNumber($orderId),
            'VK_MSG' => $description,
            'VK_RETURN' => $this->callbackUrl,
            'VK_CANCEL' => $this->cancelUrl,
            'VK_LANG' => $this->language
        );

        return $requestData;
    }

    public function getPaymentRequestFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_STAMP',
            'VK_AMOUNT',
            'VK_CURR',
            'VK_ACC',
            'VK_NAME',
            'VK_REF',
            'VK_MSG'
        );
    }

    public function getPaymentSuccessFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_REC_ID',
            'VK_STAMP',
            'VK_T_NO',
            'VK_AMOUNT',
            'VK_CURR',
            'VK_ACC',
            'VK_REC_NAME',
            'VK_SND_ACC',
            'VK_SND_NAME',
            'VK_REF',
            'VK_MSG',
            'VK_T_DATE'
        );
    }

    public function getPaymentReturnFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_REC_ID',
            'VK_STAMP',
            'VK_AMOUNT',
            'VK_CURR',
            'VK_ACC',
            'VK_REC_NAME',
            'VK_SND_ACC',
            'VK_SND_NAME',
            'VK_REF',
            'VK_MSG'
        );
    }

    public function getPaymentCancelFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_REC_ID',
            'VK_STAMP',
            'VK_REF',
            'VK_MSG'
        );
    }

    protected function validateSignature($data, $fields)
    {

        $hash = $this->generateHash($data, $fields);

        $key = openssl_pkey_get_public('file://' . $this->publicKey);

        return (openssl_verify($hash, base64_decode($data[$this->signatureReturnedField]), $key) == 1);

    }

    protected function getRequestSignature($data, $fields)
    {
        $hash = $this->generateHash($data, $fields);

        $keyId = openssl_get_privatekey('file://' . $this->privateKey, $this->passphrase);

        if (!$keyId) {
            throw new \ErrorException(openssl_error_string());
        }

        openssl_sign($hash, $signature, $keyId);
        openssl_free_key($keyId);

        $result = base64_encode($signature);

        return $result;
    }

    protected function generateHash($data, $fields)
    {

        $hash = '';
        foreach ($fields as $fieldName) {

            if (empty($data[$fieldName])) {
                continue;
            }

            $content = $data[$fieldName];

            if (!empty($this->requestEncoding)) {
                $hash .= sprintf("%03d", mb_strlen($content, $this->requestEncoding)) . $content;
            } else {
                $hash .= sprintf("%03d", strlen($content)) . $content;
            }

        }

        return $hash;
    }

    protected function getEncodingField()
    {
        return 'VK_ENCODING';
    }

    public function isReturnResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentReturnFields()) && $data['VK_SERVICE'] == $this->getServiceId(self::PAYMENT_RETURN);
    }

    public function isCancelResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentCancelFields()) && $data['VK_SERVICE'] == $this->getServiceId(self::PAYMENT_CANCEL);
    }

    public function isPaidResponse($data)
    {
        return $this->isValidResponse($data, $this->getPaymentSuccessFields()) && $data['VK_SERVICE'] == $this->getServiceId(self::PAYMENT_SUCCESS);
    }

}
