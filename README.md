Russian

<h1 align="center">trongrid-wrapper</h1>

## Введение

Реализует базовую функицональность для работы с сетью TRON. Поддерживает TRX и USDT. С помощью конфигурационного файла можно расширить список поддерживаемых токенов. 

## Поддерживаемые методы

- Создать новый адрес `generateAddress()`
- Проверить адрес `validateAddress(Address $address)`
- Получить модель Address `buildAddress(string $address, string $privateKey)`
- Проверить баланс `getBalance(string $address, string $token)`
- Transaction transfer (offline signature) `makeTransaction(Address $fromAddress, string $toAddress, string $token, string $amount)`

## Быстрый старт

### Установка

PHP8
``` php
composer require skybodrik/trongrid-wrapper
```

### Примеры использования

``` php
use Skybodrik\TrongridWrapper\TrongridWrapper;

// Ключ из https://www.trongrid.io/
$apiKey = 'aba25637-4d5e-4ed8-8925-87d9a7e48ae0';

$config = new NileTestnetConfig($apiKey); // Тестнет Nile https://nile.tronscan.org
//$config = new MainnetConfig($apiKey); // Майннет

// Получить баланс
$wrapper = new TrongridWrapper($config);
$balance = $wrapper->getBalance('TVhT5bZJgqaXN6ssekAgAWL4JSKHJUC62T', 'USDT');

// Получить новый адрес
$address = $wrapper->generateAddress();

// Совершить транзакцию
$tnx = $wrapper->makeTransaction(
    $wrapper->buildAddress(
        'TVhT5bZJgqaXN6ssekAgAWL4JSKHJUC62T',
        '0xddb912d53cc6b851e509ba8fb94a9d3d824c8f19b875dcb2388ec21a32ebda4d'
    ),
    'TE1Hv1N4mh8wztb2UzRUFpF4AStGQVVrB5',
    'USDT',
    21
);
```