<?php
namespace Deepbajwa3\Instamojo;
use Exception;
class Instamojo{
    public static function giveMeFormUrl($user,$amount,$purpose,$phone = null){
        self::checkConfigValues();
        $curl = self::setupCURL("https://www.instamojo.com/api/1.1/payment-requests/");
        $payload = self::createPaymentPayload($user, $amount, $purpose, $phone);
        $response = self::closeCurl($curl, $payload);
        $finalResponse = json_decode($response);
        return $finalResponse->payment_request->longurl;
    }
    public static function giveMePaymentDetails(){
        $payment_id = filter_input(INPUT_GET, 'payment_id');
        $payment_request_id = filter_input(INPUT_GET, 'payment_request_id');
        $curl = self::setupCURL("https://www.instamojo.com/api/1.1/payment-requests/{$payment_request_id}/{$payment_id}/");
        $response = curl_exec($curl);
        curl_close($curl);
        $decoded_response = json_decode($response);
        return $decoded_response->payment_request;
    }
    private static function checkConfigValues(){
        if (!config('instamojo.key')){
            throw new Exception('Please set the Instamojo API key in your env file');
        }elseif(!config('instamojo.token')) {
            throw new Exception('Please set the Instamojo token in your env file');
        }elseif(!config('instamojo.redirect_url')) {
            throw new Exception('Please set the redirect url in your env file');
        }elseif(!config('instamojo.webhook_url')) {
            throw new Exception('Please set the webhook url in your env file');
        }elseif(!config('instamojo.salt')) {
            throw new Exception('Please set the instamojo salt in your env file');
        }else{
            return true;
        }
    }
    private static function createPaymentPayload($user, $amount, $purpose, $phone = null){
        if(is_null($phone)){
            $phone = $user->phone;
        }
        $payload = [
            'purpose' => $purpose,
            'amount' => $amount,
            'phone' => $phone,
            'buyer_name' => $user->name,
            'redirect_url' => config('instamojo.redirect_url'),
            'send_email' => false,
            'webhook' => config('instamojo.webhook_url'),
            'send_sms' => false,
            'email' => $user->email,
            'allow_repeated_payments' => false 
        ];
        return $payload;
    }
    private static function setupCURL($apiEndpoint){
        if(extension_loaded("curl")){
            $ch = curl_init();
            $api_key = config('instamojo.key');
            $api_token = config('instamojo.token');
            curl_setopt($ch, CURLOPT_URL, "$apiEndpoint");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Api-Key:{$api_key}","X-Auth-Token:{$api_token}"]);
            return $ch;
        }else{
            throw new Exception('CURL extension is not loaded');
        }
    }
    private static function closeCurl($curl,$payload){
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
