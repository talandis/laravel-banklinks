<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class DNB extends iPizza
{

    protected $configName = 'lithuania-dnb';

    protected $requestUrl = 'https://ib.dnb.lt/loginb2b.aspx';

    protected $bankCode = '40100';

    protected function getAdditionalFields()
    {
        return array(
            'VK_ENCODING' => $this->requestEncoding
        );
    }

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
            'VK_STAMP',
            'VK_AMOUNT',
            'VK_CURR',
            'VK_ACC',
            'VK_PANK',          /// Additional field to iPizza
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
            'VK_REC_ACC',        // different name from iPizza
            'VK_REC_NAME',
            'VK_SND_ACC',
            'VK_SND_NAME',
            'VK_REF',
            'VK_MSG',
            'VK_T_DATE'
        );
    }
}
