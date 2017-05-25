<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class SEB extends iPizza
{

    protected $configName = 'lithuania-seb';

    protected $requestUrl = 'https://e.seb.lt/banklink/in';

    protected function getAdditionalFields()
    {
        return array(
            'VK_ENCODING' => $this->requestEncoding
        );
    }

}
