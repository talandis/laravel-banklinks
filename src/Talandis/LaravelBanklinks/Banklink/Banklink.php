<?php

namespace Talandis\LaravelBanklinks\Banklink;

use \Config;

abstract class Banklink
{
    protected $version;

    protected $sellerId;

    protected $sellerAccountNumber;

    protected $sellerName;

    protected $language;

    protected $currency;

    protected $privateKey;

    protected $passphrase;

    protected $publicKey;

    protected $callbackUrl;

    protected $cancelUrl;

    protected $returnUrl;

    protected $configName = null;

    protected $signatureField;

    protected $signatureReturnedField;

    protected $orderId;

    protected $orderIdField;

    const PAYMENT_REQUEST = 1;
    const PAYMENT_SUCCESS = 2;
    const PAYMENT_CANCEL = 3;
    const PAYMENT_ERROR = 3;
    const PAYMENT_RETURN = 4;

    protected abstract function getServiceId( $type );

    protected abstract function getPaymentRequestData($orderId, $sum, $description );

    protected abstract function getPaymentRequestFields();

    protected abstract function getPaymentCancelFields();

    protected abstract function getPaymentSuccessFields();

    protected abstract function getPaymentReturnFields();

    protected abstract function validateSignature( $data, $fields );

    protected abstract function getRequestSignature( $data, $id );

    public function isReturnResponse( $data )
    {
        return $this->isValidResponse( $data, $this->getPaymentReturnFields() );
    }

    public function isCancelResponse( $data )
    {
        return $this->isValidResponse( $data, $this->getPaymentCancelFields() );
    }

    public function isPaidResponse( $data )
    {
        return $this->isValidResponse( $data, $this->getPaymentSuccessFields() );
    }

    public function __construct( $configName = null )
    {

        if (!empty( $configName )) {
            $this->configName = $configName;
        }

        if ( empty( $this->configName )) {
            throw new \LogicException( 'Missing banklink configuration name' );
        }

        $configuration = \Config::get('laravel-banklinks::' . $this->configName );

        $fieldsMap = array(
            'seller_id' => 'sellerId',
            'seller_acc_num' => 'sellerAccounNumber',
            'seller_name' => 'sellerName',
            'private_key' => 'privateKey',
            'public_key' => 'publicKey',
            'private_key_passphrase' => 'passphrase',
            'currency' => 'currency',
            'language' => 'language',
            'request_url' => 'requestUrl'
        );

        foreach ( $fieldsMap as $configurationField => $classVariable ) {
            if (!empty( $configuration[ $configurationField ] ) ) {
                $this->$classVariable = $configuration[ $configurationField ];
            }
        }

    }

    public function getReferenceNumber($orderId)
    {
        return $orderId;
    }

    public function getPaymentRequest( $orderId, $sum, $description )
    {

        $requestData = $this->getPaymentRequestData($orderId, $sum, $description );

        $requestData[ $this->signatureField ] = $this->getRequestSignature($requestData, $this->getPaymentRequestFields() );

        $requestData = array_merge($requestData, $this->getAdditionalFields());

        return $requestData;
    }

    protected function getAdditionalFields()
    {
        return array();
    }

    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setCallbackUrl($url)
    {
        $this->callbackUrl = $url

        return $this;
    }

    public function setCancelUrl( $url )
    {
        $this->cancelUrl = $url;

        return $this;
    }

    public function setReturnUrl( $url )
    {
        $this->returnUrl = $url;

        return $this;
    }

    public function isValidResponse($data, $fields)
    {

        if (!empty( $data[ $this->orderIdField ])) {
            $this->orderId = $data[ $this->orderIdField ];
        }

        return $this->validateSignature($data, $fields);
    }

}