<?php

namespace Skybodrik\TrongridWrapper\Tron;

use Elliptic\EC;
use IEXBase\TronAPI\Support\Base58;
use IEXBase\TronAPI\Support\Base58Check;
use IEXBase\TronAPI\Support\Crypto;
use IEXBase\TronAPI\Support\Hash;
use InvalidArgumentException;
use Skybodrik\TrongridWrapper\Helpers\Utils;

class Address
{
    private $privateKey;
    private $address;
    private $hexAddress = '';

    const ADDRESS_SIZE = 34;
    const ADDRESS_PREFIX = "41";
    const ADDRESS_PREFIX_BYTE = 0x41;

    public function __construct($address = '', $privateKey = '', $hexAddress = '')
    {
        if (strlen($address) === 0) {
            throw new \InvalidArgumentException('Address can not be empty');
        }

        $this->privateKey = $privateKey;
        $this->address = $address;
        $this->hexAddress = $hexAddress;
    }

    /**
     * @return mixed|string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return mixed|string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return mixed|string
     */
    public function getHexAddress()
    {
        return $this->hexAddress;
    }

    public function __toString()
    {
        return $this->getAddress();
    }

    /**
     * Don`t rely on this. Always use TrongridWrapper::validateAddress to double check
     * against tronGrid.
     *
     * @return bool
     */
    public function isValid()
    {
        if (strlen($this->address) !== self::ADDRESS_SIZE) {
            return false;
        }

        $address = Base58Check::decode($this->address, false, 0, false);
        $utf8 = hex2bin($address);

        if (strlen($utf8) !== 25) {
            return false;
        }

        if (strpos($utf8, chr(self::ADDRESS_PREFIX_BYTE)) !== 0) {
            return false;
        }

        $checkSum = substr($utf8, 21);
        $address = substr($utf8, 0, 21);

        $hash0 = Hash::SHA256($address);
        $hash1 = Hash::SHA256($hash0);
        $checkSum1 = substr($hash1, 0, 4);

        if ($checkSum === $checkSum1) {
            return true;
        }

        return false;
    }

    /**
     * Generate the Address of the provided Public key
     *
     * @param string $publicKey
     *
     * @return string
     */
    public static function publicKeyToAddress(string $publicKey)
    {
        if (Utils::isHex($publicKey) === false) {
            throw new InvalidArgumentException('Invalid public key format.');
        }
        $publicKey = Utils::stripZero($publicKey);
        if (strlen($publicKey) !== 130) {
            throw new InvalidArgumentException('Invalid public key length.');
        }
        return substr(Utils::sha3(substr(hex2bin($publicKey), 1)), 24);
    }

    /**
     * Generate the Address of the provided Private key
     *
     * @param string $privateKey
     *
     * @return string
     */
    public static function privateKeyToAddress(string $privateKey)
    {
        return self::publicKeyToAddress(
            self::privateKeyToPublicKey($privateKey)
        );
    }

    /**
     * Generate the Public key for provided Private key
     *
     * @param string $privateKey Private Key
     *
     * @return string
     */
    public static function privateKeyToPublicKey(string $privateKey)
    {
        if (Utils::isHex($privateKey) === false) {
            throw new InvalidArgumentException('Invalid private key format.');
        }
        $privateKey = Utils::stripZero($privateKey);

        if (strlen($privateKey) !== 64) {
            throw new InvalidArgumentException('Invalid private key length.');
        }

        $secp256k1 = new EC('secp256k1');
        $privateKey = $secp256k1->keyFromPrivate($privateKey, 'hex');
        $publicKey = $privateKey->getPublic(false, 'hex');

        return $publicKey;
    }

    public static function getBase58CheckAddress(string $addressHex): string
    {
        $addressBin = hex2bin($addressHex);
        $hash0 = Hash::SHA256($addressBin);
        $hash1 = Hash::SHA256($hash0);
        $checksum = substr($hash1, 0, 4);
        $checksum = $addressBin . $checksum;

        return Base58::encode(Crypto::bin2bc($checksum));
    }
}