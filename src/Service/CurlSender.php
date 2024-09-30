<?php

namespace App\Service;

class CurlSender
{
    /**
     * METHOD FOR SENDING PAYMENT REQUEST
     */
    public function sendPaymentRequest($rawData, $signature)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://psp.localhost/invoice-create");

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-signature: ' .$signature));

        $response = curl_exec($ch);
    
        curl_close($ch);    
        
        return $response;
    }
}