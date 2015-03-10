<?php

namespace Talandis\LaravelBanklinks\Lithuania;

use Talandis\LaravelBanklinks\Banklink\Solo;

class Nordea extends Solo
{

    protected $requestUrl = 'https://netbank.nordea.com/pnbepay/epayn.jsp';

    protected $configName = 'lithuania-nordea';

}