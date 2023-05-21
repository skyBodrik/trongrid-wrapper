<?php

namespace Skybodrik\TrongridWrapper;

use GuzzleHttp\Client as GuzzleClient;
use IEXBase\TronAPI\Exception\TronException;
use IEXBase\TronAPI\Provider\HttpProvider;
use IEXBase\TronAPI\Tron;
use InvalidArgumentException;
use Skybodrik\TrongridWrapper\Config\MainnetConfig;
use Skybodrik\TrongridWrapper\Exception\TrongridWrapperException;
use Skybodrik\TrongridWrapper\Helpers\Formatter;
use Skybodrik\TrongridWrapper\Helpers\Utils;
use Skybodrik\TrongridWrapper\Tron\Address;
use Phactor\Key;

class TrongridWrapper
{

    private $config;

    private $httpClient;

    public function __construct(MainnetConfig $config)
    {
        $this->config = $config;
        $this->httpClient = new GuzzleClient();
    }

    /**
     * Obtain new wallet address
     *
     * @return Address
     * @throws TrongridWrapperException
     */
    public function generateAddress(): Address
    {
        $attempts = 0;
        $validAddress = false;

        do {
            if ($attempts++ === 5) {
                throw new TrongridWrapperException('Could not generate valid key');
            }

            $key = new Key([
                'private_key_hex' => '',
                'private_key_dec' => '',
                'public_key' => '',
                'public_key_compressed' => '',
                'public_key_x' => '',
                'public_key_y' => ''
            ]);
            $keyPair = $key->GenerateKeypair();
            $privateKeyHex = $keyPair['private_key_hex'];
            $pubKeyHex = $keyPair['public_key'];

            //We cant use hex2bin unless the string length is even.
            if (strlen($pubKeyHex) % 2 !== 0) {
                continue;
            }

            try {
                $addressHex = Address::ADDRESS_PREFIX . Address::publicKeyToAddress($pubKeyHex);
                $addressBase58 = Address::getBase58CheckAddress($addressHex);
            } catch (InvalidArgumentException $e) {
                throw new TrongridWrapperException($e->getMessage());
            }

            $address = new Address($addressBase58, $privateKeyHex, $addressHex);
            $validAddress = $this->validateAddress($address);
        } while (!$validAddress);

        if (!isset($address) || !($address instanceof Address)) {
            throw new TrongridWrapperException('Failed to generate new address');
        }

        return $address;
    }

    /**
     * Checks the address
     *
     * @param Address $address
     * @return bool
     * @throws TrongridWrapperException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validateAddress(Address $address): bool
    {
        if (!$address->isValid()) {
            return false;
        }

        $response = $this->httpClient->request('POST', $this->buildApiUrl('wallet/validateaddress', []), [
            'body' => sprintf('{"address":"%s","visible":true}', $address),
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'TRON-PRO-API-KEY' => $this->config->getApiKey(),
            ],
        ]);

        if ($statusCode = $response->getStatusCode() !== 200) {
            throw new TrongridWrapperException('The response has a code. ' . $statusCode .  ' Expected 200. ');
        }

        $content = json_decode($response->getBody()->getContents(), true);

        return $content['result'] === true;
    }

    /**
     * Create new transaction
     *
     * @param Address $fromAddress
     * @param string $toAddress
     * @param string $token for example: TRX, USDT
     * @param string $amount
     * @return array
     * @throws TronException
     * @throws TrongridWrapperException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeTransaction(Address $fromAddress, string $toAddress, string $token, string $amount)
    {
        $configTokenList = $this->config->getTokenList();

        if (!isset($configTokenList[$token])) {
            throw new TrongridWrapperException('Unknown token ' . $token);
        }

        $host = $this->config->getNetworkUrl();
        $fullNode = new HttpProvider($host);
        $solidityNode = new HttpProvider($host);
        $eventServer = new HttpProvider($host);
        try {
            $tronInstance = new Tron($fullNode, $solidityNode, $eventServer);
            $tronInstance->setAddress($fromAddress->getAddress());
            $tronInstance->setPrivateKey($fromAddress->getPrivateKey());

            if ($token === 'TRX') {
                try {
                    $transaction = $tronInstance->getTransactionBuilder()->sendTrx($toAddress, $amount, $fromAddress->getAddress());
                    $signedTransaction = $tronInstance->signTransaction($transaction);
                    $response = $tronInstance->sendRawTransaction($signedTransaction);
                } catch (TronException $e) {
                    throw new TrongridWrapperException($e->getMessage(), $e->getCode());
                }

                if (isset($response['result']) && $response['result'] == true) {
                    return [
                        'txID' => $transaction['txID'],
                        'rawData' => $transaction['raw_data'],
                        'contractRet' => 'PACKING',
                    ];
                } else {
                    throw new TrongridWrapperException(hex2bin($response['message']));
                }
            }

            // IF token is not TRX, go next
            $tokenConfig = $configTokenList[$token];

            if (strtoupper($tokenConfig['network']) === 'TRC20') { // Обрабатываем TRC20 контракты
                $toHexAddress = $tronInstance->address2HexString($toAddress);
                $toFormat = Formatter::toAddressFormat($toHexAddress);
                try {
                    $amount = Utils::toMinUnitByDecimals($amount, $tokenConfig['decimals']);
                } catch (InvalidArgumentException $e) {
                    throw new TrongridWrapperException($e->getMessage());
                }
                $numberFormat = Formatter::toIntegerFormat($amount);

                $contractAddress = $tokenConfig['contract'];
                $contractAddressHex = $tronInstance->address2HexString($contractAddress);

                $response = $this->httpClient->request('POST', $this->buildApiUrl('wallet/triggersmartcontract', []), [
                    'body' => json_encode([
                        'contract_address' => $contractAddressHex,
                        'function_selector' => 'transfer(address,uint256)',
                        'parameter' => "{$toFormat}{$numberFormat}",
                        'fee_limit' => 100000000,
                        'call_value' => 0,
                        'owner_address' => $fromAddress->getHexAddress(),
                    ]),
                    'headers' => [
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                        'TRON-PRO-API-KEY' => $this->config->getApiKey(),
                    ],
                ]);

                if ($statusCode = $response->getStatusCode() !== 200) {
                    throw new TrongridWrapperException('The response has a code. ' . $statusCode .  ' Expected 200. ');
                }

                $content = json_decode($response->getBody()->getContents(), true);

                if (isset($content['result']['code'])) {
                    throw new TrongridWrapperException(hex2bin($content['result']['message']));
                }

                $transaction = $content['transaction'];

                try {
                    $tradeobj = $tronInstance->signTransaction($transaction);
                    $response = $tronInstance->sendRawTransaction($tradeobj);
                } catch (TronException $e) {
                    throw new TrongridWrapperException($e->getMessage(), $e->getCode());
                }

                if (isset($response['result']) && $response['result'] == true) {
                    return [
                        'txID' => $transaction['txID'],
                        'rawData' => $transaction['raw_data'],
                        'contractRet' => 'PACKING',
                    ];
                } else {
                    throw new TrongridWrapperException(hex2bin($response['result']['message']));
                }
            }
        } catch (TronException $e) {
            throw new TrongridWrapperException($e->getMessage(), $e->getCode());
        }

        throw new TrongridWrapperException('Something went wrong');
    }

    /**
     * Create address model
     *
     * @param string $address
     * @param string $privateKey
     * @return Address
     * @throws TronException
     */
    public function buildAddress(string $address = '', string $privateKey = '')
    {
        $host = $this->config->getNetworkUrl();
        $fullNode = new HttpProvider($host);
        $solidityNode = new HttpProvider($host);
        $eventServer = new HttpProvider($host);
        $tronInstance = new Tron($fullNode, $solidityNode, $eventServer);
        $hexAddress = $tronInstance->address2HexString($address);

        return new Address($address, $privateKey, $hexAddress);
    }

    /**
     * Get the token balance of the specified wallet
     *
     * @param string $address
     * @param string
     * @return string
     * @throws TrongridWrapperException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBalance(string $address, string $token = 'TRX')
    {
        $configTokenList = $this->config->getTokenList();

        if (!isset($configTokenList[$token])) {
            throw new TrongridWrapperException('Unknown token ' . $token);
        }

        $host = $this->config->getNetworkUrl();
        $fullNode = new HttpProvider($host);
        $solidityNode = new HttpProvider($host);
        $eventServer = new HttpProvider($host);
        $tronInstance = new Tron($fullNode, $solidityNode, $eventServer);
        $tronInstance->setAddress($address);

        // IF token is not TRX, go next
        $tokenConfig = $configTokenList[$token];

        if (isset($tokenConfig['network']) && strtoupper($tokenConfig['network']) === 'TRC20') { // Обрабатываем TRC20 контракты
            $hexAddress = $tronInstance->address2HexString($address);

            $contractAddress = $tokenConfig['contract'];
            $contractAddressHex = $tronInstance->address2HexString($contractAddress);

            $format = Formatter::toAddressFormat($hexAddress);

            $response = $this->httpClient->request('POST', $this->buildApiUrl('wallet/triggersmartcontract', []), [
                'body' => json_encode([
                    'contract_address' => $contractAddressHex,
                    'function_selector' => 'balanceOf(address)',
                    'parameter' => $format,
                    'owner_address' => $hexAddress,
                ]),
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'TRON-PRO-API-KEY' => $this->config->getApiKey(),
                ],
            ]);

            if ($statusCode = $response->getStatusCode() !== 200) {
                throw new TrongridWrapperException('The response has a code. ' . $statusCode . ' Expected 200. ');
            }

            $content = json_decode($response->getBody()->getContents(), true);

            if (isset($content['result']['code'])) {
                throw new TrongridWrapperException(hex2bin($content['result']['message']));
            }

            try {
                $balance = Utils::toDisplayAmount(hexdec($content['constant_result'][0]), $tokenConfig['decimals']);
            } catch (InvalidArgumentException $e) {
                throw new TrongridWrapperException($e->getMessage());
            }

            return $balance;
        } elseif ($token === 'TRX') {
            return (string) $tronInstance->getBalance(null, true);
        }

        throw new TrongridWrapperException('Something went wrong');
    }

    /**
     * Building an api link
     *
     * @param string $apiMethod
     * @param array $pathParams
     * @param array $queryParams
     * @return string
     */
    private function buildApiUrl(string $apiMethod, array $pathParams = [], array $queryParams = [])
    {
        $apiUrl = $this->config->getNetworkUrl() . $apiMethod;
        if ($pathParams) {
            $apiUrl .= '/' . implode('/', $pathParams);
        }

        if ($queryParams) {
            $urlParams = [];
            foreach ($queryParams as $key => $value) {
                $urlParams[] = urlencode($key . '=' . $value);
            }

            $apiUrl .= '?' . implode('&', $urlParams);
        }

        return $apiUrl;
    }

}