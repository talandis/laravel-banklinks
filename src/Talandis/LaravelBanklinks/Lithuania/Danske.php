<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class Danske extends iPizza
{

    protected $configName = 'lithuania-danske';

    protected $requestUrl = 'https://ebankas.danskebank.lt/ib/site/ibpay/login';

    protected $bankCode = '74000';

    public function getPaymentRequestData($orderId, $sum, $description, $email = null)
    {

        $requestData = parent::getPaymentRequestData($orderId, $sum, $description, $email);
        $requestData['VK_PANK'] = $this->bankCode;

        return $requestData;
    }

    public function getPaymentRequestFields()
    {
        return array(
            'VK_SERVICE',
            'VK_VERSION',
            'VK_SND_ID',
            'VK_STAMP', // Neprivalomas
            'VK_AMOUNT',
            'VK_CURR',
            'VK_TERM',  // Neprivalomas
            'VK_ACC',
            'VK_PCODE', // Neprivalomas
            'VK_PANK',  // Neprivalomas
            'VK_NAME',  // Neprivalomas
            'VK_REF',   // Neprivalomas
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
            'VK_REC_ACC',        // differs name from iPizza
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
