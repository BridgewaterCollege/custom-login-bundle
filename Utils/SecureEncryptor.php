<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\Utils;

class SecureEncryptor {

    public function __construct() {}

    function pkcs7_pad($data, $size)
    {
        $length = $size - strlen($data) % $size;
        return $data . str_repeat(chr($length), $length);
    }

    function pkcs7_unpad($data)
    {
        return substr($data, 0, -ord($data[strlen($data) - 1]));
    }

    public function encrypt($stringToEncrypt, $iv, $ivSize) {
        $encryptedString = openssl_encrypt(
            $this->pkcs7_pad($stringToEncrypt, $ivSize), // padded data
            'AES-256-CBC',        // cipher and mode
            $_SERVER['APP_SECRET'],      // secret key
            0,                    // options (not used)
            $iv                   // initialisation vector
        );

        return $encryptedString;
    }

    public function decrypt($stringToDecrypt, $iv) {
        $decryptedString = $this->pkcs7_unpad(openssl_decrypt(
            $stringToDecrypt,
            'AES-256-CBC',
            $_SERVER['APP_SECRET'],
            0,
            $iv
        ));

        return $decryptedString;
    }
}