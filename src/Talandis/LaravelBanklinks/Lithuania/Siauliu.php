<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class Siauliu extends iPizza
{

    protected $configName = 'lithuania-siauliu';

    protected $requestUrl = 'https://online.sb.lt/ib/site/ibpay/login';

    protected $version = '008';

    public function getPaymentRequestData($orderId, $sum, $description)
    {

        $requestData = parent::getPaymentRequestData($orderId, $sum, $description);

        unset( $requestData['VK_CANCEL'] );

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
            'VK_TERM',
            'VK_ACC',
            'VK_PCODE',
            'VK_PANK',
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
            'VK_AMOUNT',
            'VK_CURR',
            'VK_REC_ACC',
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
            'VK_REC_ACC',
            'VK_REC_NAME',
            'VK_SND_ACC',
            'VK_SND_NAME',
            'VK_REF',
            'VK_MSG'
        );
    }

}