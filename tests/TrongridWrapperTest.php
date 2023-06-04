<?php

use Skybodrik\TrongridWrapper\Config\MainnetConfig;
use PHPUnit\Framework\TestCase;
use Skybodrik\TrongridWrapper\Config\NileTestnetConfig;
use Skybodrik\TrongridWrapper\TrongridWrapper;

final class TrongridWrapperTest extends TestCase
{
    public function testGetBalance()
    {
        $wrapper = new TrongridWrapper(new MainnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $balance = $wrapper->getBalance('TCad4HFTZL5Yiy2jj1zEEESmQUEdyVKuFL', 'USDT');

//        $wrapper = new TrongridWrapper(new NileTestnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
//        $balance = $wrapper->getBalance('TVhT5bZJgqaXN6ssekAgAWL4JSKHJUC62T', 'USDT');
        var_dump($balance); die;
    }

    public function testGenerateAddress()
    {
//        $wrapper = new TrongridWrapper(new NileTestnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
//        $address = $wrapper->generateAddress();
        $wrapper = new TrongridWrapper(new MainnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $address = $wrapper->generateAddress();

        var_dump($address); die;
    }

    public function testEstimatedFee()
    {
//        $wrapper = new TrongridWrapper(new MainnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
//        $estimatedFee = $wrapper->getEstimateFee(
//            $wrapper->buildAddress(
//                'TCad4HFTZL5Yiy2jj1zEEESmQUEdyVKuFL',
//                '0xde3bf3f82fcdd50d9d2119fe09429083274fb7ea4c712c47ce14c297c78b84aa'
//            ),
//            'TAJdpd4s8mq4NyT7VSBSCBtMJV64DGbiK7',
//            'USDT',
//            1
//        );

        $wrapper = new TrongridWrapper(new NileTestnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $estimatedFee = $wrapper->getEstimateFee(
            $wrapper->buildAddress(
                'TVhT5bZJgqaXN6ssekAgAWL4JSKHJUC62T',
                '0xddb912d53cc6b851e509ba8fb94a9d3d824c8f19b875dcb2388ec21a32ebda4d'
            ),
            'TE1Hv1N4mh8wztb2UzRUFpF4AStGQVVrB5',
            'USDT',
            210
        );

        var_dump($estimatedFee); die;
    }

    public function testTransactionHistory()
    {
        $wrapper = new TrongridWrapper(new MainnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $address = $wrapper->getTrc20TransactionHistory('TCad4HFTZL5Yiy2jj1zEEESmQUEdyVKuFL');
//        $wrapper->getTransactionEvents('1a26d486c906f9f3b2db3f06f00dc213cc63a25d4a71e381121efa382d606511');

        var_dump($address); die;
    }

    public function testMakeTransaction()
    {
//        $wrapper = new TrongridWrapper(new MainnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
//        $tx = $wrapper->makeTransaction(
//            $wrapper->buildAddress(
//                'TCad4HFTZL5Yiy2jj1zEEESmQUEdyVKuFL',
//                '0xde3bf3f82fcdd50d9d2119fe09429083274fb7ea4c712c47ce14c297c78b84aa'
//            ),
//            'TCad4HFTZL5Yiy2jj1zEEESmQUEdyVKuFL',
//            'TRX',
//            10
//        );

        $wrapper = new TrongridWrapper(new MainnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
        $tx = $wrapper->makeTransaction(
            $wrapper->buildAddress(
                'TCad4HFTZL5Yiy2jj1zEEESmQUEdyVKuFL',
                '0xde3bf3f82fcdd50d9d2119fe09429083274fb7ea4c712c47ce14c297c78b84aa'
            ),
            'TAJdpd4s8mq4NyT7VSBSCBtMJV64DGbiK7',
            'USDT',
            1
        );
//        $wrapper->
//        $wrapper = new TrongridWrapper(new NileTestnetConfig('aba25637-4d5e-4ed8-8925-87d9a7e48ae0'));
//        $tx = $wrapper->makeTransaction(
//            $wrapper->buildAddress(
//                'TVhT5bZJgqaXN6ssekAgAWL4JSKHJUC62T',
//                '0xddb912d53cc6b851e509ba8fb94a9d3d824c8f19b875dcb2388ec21a32ebda4d'
//            ),
//            'TE1Hv1N4mh8wztb2UzRUFpF4AStGQVVrB5',
//            'USDT',
//            210
//        );
//        var_dump($tx); die;

//        $tx = $wrapper->makeTransaction(
//            $wrapper->buildAddress(
//                'TSDwrWH4bnC3483PXGoTA32nU7uQQQgr7e',
//                '0xf9b950ed494bbb5af4873fbe74ed33547d7aa4901bb4ca1aaf0e0b1274ab367c'
//            ),
//            'TJvRBU3bmwDwW5ySpLCHnmSyEb1qjUg9Hm',
//            'TRX',
//            3
//        );
        var_dump($tx); die;
    }
}