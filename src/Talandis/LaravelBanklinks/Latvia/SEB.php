<?php

namespace Talandis\LaravelBanklinks\Latvia;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class SEB extends iPizza
{

    protected $version = '001';

    protected $language = 'LAT';

    protected $configName = 'latvia-seb';

    protected $requestUrl = 'https://ibanka.seb.lv/ipc/epakindex.jsp';

    protected function getAdditionalFields()
    {
        return array(
            'VK_ENCODING' => $this->requestEncoding
        );
    }

    public function getPaymentRequestData($orderId, $sum, $description)
    {

        $requestData = array(
            'IB_SERVICE' => $this->getServiceId(self::PAYMENT_REQUEST),
            'IB_VERSION' => $this->version,
            'IB_SND_ID' => $this->sellerId,
            'IB_CURR' => $this->currency,
            'IB_LANG' => $this->language,
            'IB_FEEDBACK' => $this->callbackUrl,
            'IB_NAME' => $this->sellerName,
            'IB_PAYMENT_ID' => $orderId,
            'IB_AMOUNT' => $sum,
            'IB_PAYMENT_DESC' => $description,
        );

        return $requestData;
    }

    protected function getServiceId($type)
    {
        switch ($type) {
            case self::PAYMENT_REQUEST:
                return '0002';
            case self::PAYMENT_SUCCESS:
            case self::PAYMENT_CANCEL:
                return '0004';
            case self::PAYMENT_RETURN:
                return '0003';
        }

        throw new \LogicException(sprintf('Invalid service type: %s', $type));
    }

    public function getPaymentRequestFields()
    {
        return array(
            'IB_SND_ID',
            'IB_SERVICE',
            'IB_VERSION',
            'IB_AMOUNT',
            'IB_CURR',
            'IB_NAME',
            'IB_PAYMENT_ID',
            'IB_PAYMENT_DESC',
        );
    }

    public function getPaymentSuccessFields()
    {
        return $this->getPaymentCancelFields();
    }

    public function getPaymentCancelFields()
    {
        return array(
            'IB_SND_ID',
            'IB_SERVICE',
            'IB_VERSION',
            'IB_REC_ID',
            'IB_PAYMENT_ID',
            'IB_PAYMENT_DESC',
            'IB_FROM_SERVER',
            'IB_STATUS'
        );
    }

    public function getPaymentReturnFields()
    {
        return array(
            'IB_SND_ID',
            'IB_SERVICE',
            'IB_VERSION',
            'IB_PAYMENT_ID',
            'IB_AMOUNT',
            'IB_CURR',
            'IB_REC_ID',
            'IB_REC_ACC',
            'IB_REC_NAME',
            'IB_PAYER_ACC',
            'IB_PAYER_NAME',
            'IB_PAYMENT_DESC',
            'IB_PAYMENT_DATE',
            'IB_PAYMENT_TIME'
        );
    }

}