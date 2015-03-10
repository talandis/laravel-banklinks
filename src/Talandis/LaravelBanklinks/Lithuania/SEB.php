<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\iPizza;

class SEB extends iPizza
{

    protected $configName = 'lithuania-seb';

    protected $requestUrl = 'https://ebankas.seb.lt/cgi-bin/vbint.sh/vbnet.w';

    protected function getAdditionalFields()
    {
        return array(
            'VK_ENCODING' => $this->requestEncoding
        );
    }

}