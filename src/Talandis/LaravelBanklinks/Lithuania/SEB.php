<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class SEB extends iPizza
{

    protected $configName = 'lithuania-seb';

    protected $requestUrl = 'https://e.seb.lt/banklink/in';

    protected $version = '009';

    protected $signingAlgorithm = OPENSSL_ALGO_SHA512;

    protected function getAdditionalFields()
    {
        return array(
            'VK_ENCODING' => $this->requestEncoding
        );
    }

}
