<?php
namespace App\Helper;

class SignatureHelper
{   
    /**
     * METHOD FOR CREATING SIGNATURE
     */
    public static function signData($body, $secretKey)
    {
        return base64_encode(hash_hmac('sha256', $body, $secretKey));
    }
}