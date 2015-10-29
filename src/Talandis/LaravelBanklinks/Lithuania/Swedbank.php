<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class Swedbank extends iPizza
{

    protected $configName = 'lithuania-swedbank';

    protected $requestUrl = 'https://ib.swedbank.lt/banklink';

    protected function getAdditionalFields()
    {
        return array(
            'VK_ENCODING' => $this->requestEncoding
        );
    }

    protected function getServiceId($type)
    {
        if ($type == self::PAYMENT_REQUEST) {
            return '1002';
        }

        return parent::getServiceId($type);
    }

    public function getPaymentRequestFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_STAMP',
            'VK_AMOUNT',        // Two fields missing from standard iPizza
            'VK_CURR',
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
            'VK_REC_ACC',        // different name from iPizza
            'VK_REC_NAME',
            'VK_SND_ACC',
            'VK_SND_NAME',
            'VK_REF',
            'VK_MSG',
            'VK_T_DATE'
        );
    }

    public function isReturnResponse( $data )
    {
        return $this->isValidResponse( $data, $this->getPaymentSuccessFields() ) && $data['VK_AUTO'] == 'N' && $data['VK_SERVICE'] != $this->getServiceId(self::PAYMENT_CANCEL);
    }

    public function isPaidResponse( $data )
    {
        return $this->isValidResponse( $data, $this->getPaymentSuccessFields() ) && $data['VK_AUTO'] == 'Y';
    }
}