<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Clientbtn
{
    /**
     * Your merchant's client key
     * @static
     */
    public static $clientKey;
    public static $clientSecret;
    public static $partnerId;
    public static $date;
    public static $privateKey;

    /**
     * true for production
     * false for sandbox mode
     * @static
     */
    public static $isProduction = false;

    /**
     * Default options for every request
     * @static
     */
    public static $curlOptions = array();
    const SANDBOX_BASE_URL = 'https://devapi.btn.co.id/v1';
    const SANDBOX_VA_URL = 'https://vabtn-dev.btn.co.id';
    const PRODUCTION_BASE_URL = 'https://api.btn.co.id/v1';
    const SNAP_SANDBOX_BASE_URL = 'https://devapi.btn.co.id/snap/v1';
    const SNAP_PRODUCTION_BASE_URL = 'https://api.btn.co.id/snap/v1';

    public function config($params)
    {
        Clientbtn::$clientKey = $params['client_key'];
        Clientbtn::$partnerId = $params['partner_id'];
        Authbtn::$clientSecret = $params['api_secret'];
        Clientbtn::$date = $params['date'];
        Clientbtn::$isProduction = $params['production'];
    }

    /**
     * @return string Veritrans API URL, depends on $isProduction
     */
    public static function getBaseUrl()
    {
        return Clientbtn::$isProduction ?
            Clientbtn::PRODUCTION_BASE_URL : Clientbtn::SANDBOX_BASE_URL;
    }
    public static function getVaUrl()
    {
        return Clientbtn::$isProduction ?
            Clientbtn::PRODUCTION_BASE_URL : Clientbtn::SANDBOX_VA_URL;
    }

    public static function getSnapBaseUrl()
    {
        return Clientbtn::$isProduction ?
            Clientbtn::SNAP_PRODUCTION_BASE_URL : Clientbtn::SNAP_SANDBOX_BASE_URL;
    }

    /**
     * Send GET request
     * @param string  $url
     * @param string  $client_key
     * @param mixed[] $data_hash
     */
    public static function get($url, $token, $client_key, $partner_id, $date, $data_hash)
    {
        return self::remoteCall($url, $token, $client_key, $partner_id, $date, $data_hash, false);
    }

    /**
     * Send POST request
     * @param string  $url
     * @param string  $client_key
     * @param string  $partner_id
     * @param string  $date
     * @param mixed[] $data_hash
     */
    public static function post($url, $token, $client_key, $partner_id, $date, $data_hash)
    {
        return self::remoteCall($url, $token, $client_key, $partner_id, $date, $data_hash, true);
    }


    /**
     * Actually send request to API client
     * @param string  $url
     * @param string  $client_key
     * @param string  $partner_id
     * @param string  $date
     * @param mixed[] $data_hash
     * @param bool    $post
     */
    public static function remoteCall($url, $token, $client_key, $partner_id, $date, $data_hash, $post = true)
    {
        $ch = curl_init();

        Clientbtn::$privateKey = file_get_contents(dirname(__FILE__) . "/auth/h2h_btn.ppk");
        $stringToSign = $client_key . "|" . $date;
        $signature = null;
        openssl_sign($stringToSign, $signature, Clientbtn::$privateKey, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);
        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array(
                "Authorization = $token",
                "Content-Type: application/json",
                "X-TIMESTAMP: $date",
                "X-SIGNATURE: $signatureBase64",
                "X-PARTNER-ID: $partner_id",
                "X-CLIENT-KEY: $client_key",
                "X-EXTERNAL-ID: API84756KM0000",
                "CHANNEL-ID = 02030",
                "Origin: wastu.digital"
            ),
            CURLOPT_RETURNTRANSFER => 1,
        ];

        if ($post) {
            $curl_options[CURLOPT_POST] = 1;

            if ($data_hash) {
                $body = json_encode($data_hash);
                $curl_options[CURLOPT_POSTFIELDS] = $body;
            } else {
                $curl_options[CURLOPT_POSTFIELDS] = '';
            }
        }
        curl_setopt_array($ch, $curl_options);

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        // curl_close($ch);

        if($result === false) {
            return 'Curl error: ' . curl_error($ch);
        } else {
            return json_decode($result);
        }

        curl_close($ch);

    }

    public static function createVA($params)
    {
        $result = Clientbtn::post(
            Authbtn::getSnapBaseUrl() . '/transfer-va/create-va',
            Authbtn::getSnapAuthorize(),
            Authbtn::$clientKey,
            Authbtn::$partnerId,
            Authbtn::$date,
            $params
        );
        return $result;
    }
    public static function getVA($params)
    {
        $ref='96718930201907001';
        $result = Clientbtn::post(
            Clientbtn::getVaUrl() . "/bayar/$ref",
            Clientbtn::$clientKey,
            Clientbtn::$partnerId,
            Clientbtn::$date,
            $params
        );
        return $result;
    }

}