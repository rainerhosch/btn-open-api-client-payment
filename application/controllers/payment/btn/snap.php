<?php if (!defined('BASEPATH'))
exit('No direct script access allowed');


class Snap extends CI_Controller
{
    public function __construct()
	{
		parent::__construct();
        // Set zona waktu ke Waktu Indonesia Barat (WIB)
        date_default_timezone_set('Asia/Jakarta');
        // Mendefinisikan path ke folder asset
        $this->assetPath = FCPATH . 'assets/auth/';
        // Format tanggal dan waktu
        $this->date = date('Y-m-d\TH:i:sP');
        $this->clientKey = "5ab937f1-0567-4014-add9-bba6cb8b3399";
        $this->partnerId = "83e8c966-d90e-4133-9787-11b1b6766a81";
        $this->api  = "https://devapi.btn.co.id/snap/v1/";
    }

    public function Index(){
        //set production to true for production environment
        echo'Hello, from Api.';
        // // Data yang akan ditandatangani
        // $stringToSign = $this->clientKey."|".$this->date;
        
        
        // $fileName = 'h2h_btn.ppk';
        // // Baca kunci privat dari file
        // $privateKey = file_get_contents($this->assetPath.$fileName);
        
        // // Ubah kunci privat ke dalam bentuk resource
        // $privateKeyResource = openssl_pkey_get_private($privateKey);
        
        // // Tandatangani data dengan kunci privat
        // openssl_sign($stringToSign, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        
        // // Konversi tanda tangan menjadi format yang dapat digunakan dalam header HTTP
        // $base64Signature = base64_encode($signature);
        
        // // Buat header X-SIGNATURE
        // $xSignatureHeader = "X-SIGNATURE: " . $base64Signature;
        
        
        // // Buat request HTTP dengan header yang diperlukan
        // $ch = curl_init('https://devapi.btn.co.id/snap/v1/access-token/b2b');
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'X-CLIENT-KEY:'.$this->clientKey.'',
        //     'X-TIMESTAMP:'.$this->date.'',
        //     $xSignatureHeader // Tambahkan header tanda tangan
        // ]);
        
        // // Kirim request dan ambil responsenya
        // $response = curl_exec($ch);
        // // var_dump($response);die; 
        
        // // Periksa apakah terdapat kesalahan
        // if(curl_errno($ch)) {
        //     echo 'Error: ' . curl_error($ch);
        // }
        
        // // Tampilkan responsenya
        // echo $response;
        
        // // Tutup koneksi
        // curl_close($ch);
    }

    public function access_token()
    {
        $fileName = 'h2h_btn.ppk';
        $end_poin = "access-token/b2b";
        $url = $this->api.$end_poin;
        // Baca kunci privat dari file
        $privateKey = file_get_contents($this->assetPath.$fileName);

        $stringToSign = $this->clientKey . "|" . $this->date;

        $signature = null;
        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $signatureBase64 = base64_encode($signature);

        // var_dump($this->date);die;

        $headers = array(
            "Content-Type: application/json",
            "X-TIMESTAMP: $this->date",
            "X-SIGNATURE: $signatureBase64",
            "X-PARTNER-ID: $this->partnerId",
            "X-CLIENT-KEY: $this->clientKey",
            // "X-EXTERNAL-ID: API84756KM0000",
            "Origin: wastu.digital"
        );

        $data = array(
            "grantType" => "client_credentials",
            "additionalInfo" => (object) [] // Body request dalam format JSON
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Ubah data menjadi bentuk yang sesuai
        $response = curl_exec($ch);

        if($response === false) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            echo $response;
        }

        curl_close($ch);

    }

    
    
}

?>