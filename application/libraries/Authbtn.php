<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Authbtn
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
        Authbtn::$clientKey = $params['client_key'];
        Authbtn::$partnerId = $params['partner_id'];
        Authbtn::$clientSecret = $params['api_secret'];
        Authbtn::$date = $params['date'];
        Authbtn::$isProduction = $params['production'];
    }

    /**
     * @return string Veritrans API URL, depends on $isProduction
     */
    public static function getBaseUrl()
    {
        return Authbtn::$isProduction ?
            Authbtn::PRODUCTION_BASE_URL : Authbtn::SANDBOX_BASE_URL;
    }

    public static function getSnapBaseUrl()
    {
        return Authbtn::$isProduction ?
            Authbtn::SNAP_PRODUCTION_BASE_URL : Authbtn::SNAP_SANDBOX_BASE_URL;
    }
    /**
     * Send GET request
     * @param string  $url
     * @param string  $client_key
     * @param mixed[] $data_hash
     */
    public static function get($url, $client_key, $partner_id, $date, $data_hash)
    {
        return self::remoteCall($url, $client_key, $data_hash, false);
    }

    /**
     * Send POST request
     * @param string  $url
     * @param string  $client_key
     * @param string  $partner_id
     * @param string  $date
     * @param mixed[] $data_hash
     */
    public static function post($url, $client_key, $partner_id, $date, $data_hash)
    {
        return self::remoteCall($url, $client_key, $partner_id, $date, $data_hash, true);
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
    public static function remoteCall($url, $client_key, $partner_id, $date, $data_hash, $post = true)
    {
        $ch = curl_init();

        Authbtn::$privateKey = file_get_contents(dirname(__FILE__) . "/auth/h2h_btn.ppk");
        $stringToSign = $client_key . "|" . $date;
        $signature = null;
        openssl_sign($stringToSign, $signature, Authbtn::$privateKey, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);
        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "X-TIMESTAMP: $date",
                "X-SIGNATURE: $signatureBase64",
                "X-PARTNER-ID: $partner_id",
                "X-CLIENT-KEY: $client_key",
                // "X-EXTERNAL-ID: API84756KM0000",
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

        if ($result === false) {
            return 'Curl error: ' . curl_error($ch);
        } else {
            return json_decode($result);
        }

        curl_close($ch);

    }

    public static function getSnapToken()
    {
        $data_param = array(
            "grantType" => "client_credentials",
            "additionalInfo" => (object) [] // Body request dalam format JSON
        );
        $result = Authbtn::post(
            Authbtn::getSnapBaseUrl() . '/access-token/b2b',
            Authbtn::$clientKey,
            Authbtn::$partnerId,
            Authbtn::$date,
            $data_param
        );
        return $result;
    }
    public static function getSnapAuthorize()
    {
        $data_param = array(
            "grantType" => "client_credentials",
            "additionalInfo" => (object) [] // Body request dalam format JSON
        );
        $result = Authbtn::post(
            Authbtn::getSnapBaseUrl() . '/access-token/b2b',
            Authbtn::$clientKey,
            Authbtn::$partnerId,
            Authbtn::$date,
            $data_param
        );
        return $result->tokenType . ' ' . $result->accessToken;
    }
    public static function createVA($params)
    {
        $result = Authbtn::post(
            Authbtn::getSnapBaseUrl() . '/transfer-va/create-va',
            Authbtn::getSnapAuthorize(),
            Authbtn::$clientKey,
            Authbtn::$partnerId,
            Authbtn::$date,
            $params
        );
        return $result;
    }


}