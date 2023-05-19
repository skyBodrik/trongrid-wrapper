<?php

use Skybodrik\TrongridWrapper\Config\MainnetConfig;
use PHPUnit\Framework\TestCase;
use Skybodrik\TrongridWrapper\Config\NileTestnetConfig;
use Skybodrik\TrongridWrapper\TrongridWrapper;

final class TrongridWrapperTest extends TestCase
{
    public function testGetBalance()
    {
        $wrapper = new TrongridWrapper(new NileTestnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $balance = $wrapper->getBalance('TVhT5bZJgqaXN6ssekAgAWL4JSKHJUC62T', 'USDT');
        var_dump($balance); die;
    }

    public function testGenerateAddress()
    {
        $wrapper = new TrongridWrapper(new NileTestnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $address = $wrapper->generateAddress();
        var_dump($address); die;
    }

    public function testMakeTransaction()
    {
        $wrapper = new TrongridWrapper(new NileTestnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $tx = $wrapper->makeTransaction(
            $wrapper->buildAddress(
                'TVhT5bZJgqaXN6ssekAgAWL4JSKHJUC62T',
                '0xddb912d53cc6b851e509ba8fb94a9d3d824c8f19b875dcb2388ec21a32ebda4d'
            ),
            'TE1Hv1N4mh8wztb2UzRUFpF4AStGQVVrB5',
            'USDT',
            21
        );
        var_dump($tx); die;
    }
}