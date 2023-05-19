<?php

namespace Skybodrik\TrongridWrapper\Config;

use Skybodrik\TrongridWrapper\Exception\TrongridWrapperException;

class MainnetConfig
{
    protected $networkUrl = 'https://api.trongrid.io';
    protected $apiKey;
    protected $tokenList = [
        'TRX' => [],
        'USDT' => [
            'contract' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            'network' => 'TRC20',
            'decimals'=> 6,
        ]
    ];

    public function __construct(string $apiKey)
    {
        if (empty($this->networkUrl)) {
            throw new TrongridWrapperException('NetworkUrl is empty!');
        }

        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     */
    public function getNetworkUrl()
    {
        return substr($this->networkUrl, -1, 0) === '/'
            ? $this->networkUrl : $this->networkUrl . '/';
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return array[]
     */
    public function getTokenList()
    {
        return $this->tokenList;
    }
}