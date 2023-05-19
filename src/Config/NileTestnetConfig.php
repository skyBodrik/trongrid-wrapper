<?php

namespace Skybodrik\TrongridWrapper\Config;

class NileTestnetConfig extends MainnetConfig
{
    protected $networkUrl = 'https://nile.trongrid.io';

    protected $tokenList = [
        'TRX' => [],
        'USDT' => [
            'contract' => 'TXLAQ63Xg1NAzckPwKHvzw7CSEmLMEqcdj',
            'network' => 'TRC20',
            'decimals'=> 6,
        ]
    ];
}