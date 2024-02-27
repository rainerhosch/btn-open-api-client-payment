<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	public static $Authorization;
	public function __construct()
	{
		parent::__construct();
		// Set zona waktu ke Waktu Indonesia Barat (WIB)
		date_default_timezone_set('Asia/Jakarta');
		// Format tanggal dan waktu
		$this->date = date('Y-m-d\TH:i:sP');
		$this->clientKey = "5ab937f1-0567-4014-add9-bba6cb8b3399"; // OAuth ID : sebagai value untuk field X-CLIENT-KEY
		$this->partnerId = "83e8c966-d90e-4133-9787-11b1b6766a81"; // Apikey ID : sebagai value untuk field X-PARTNER-ID pada layanan
		$this->clientSecret = "fe18c8e7-53ba-49f4-9ea8-456fb38f9dc4"; // ApikeySecret :sebagai komponen clientSecret membentuk X-SIGNATURE pada layanan selain Get Token


		$this->params = array('client_key' => $this->clientKey, 'partner_id' => $this->partnerId, 'api_secret' => $this->clientSecret, 'date' => $this->date, 'production' => false);
		$this->load->library('authbtn');
		$this->authbtn->config($this->params);

	}

	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function get_token()
	{
		// $data_param = array(
		//     "grantType" => "client_credentials",
		//     "additionalInfo" => (object) [] // Body request dalam format JSON
		// );
		$res = $this->authbtn->getSnapToken();
		// Welcome::$Authorization = $res->tokenType . ' ' . $res->accessToken;
		var_dump($res);
	}

	private function _getAuthorization()
	{
		$res = $this->authbtn->getSnapToken();
		return $res;
	}

	public function create_va()
	{
		$this->load->library('clientbtn');
		$this->clientbtn->config($this->params);
		// $res = $this->authbtn->getSnapToken();

		$data_param = (object) [
			"partnerServiceId" => " 93333",
			"customerNo" => "123456789012",
			"virtualAccountNo" => " 141351059",
			"virtualAccountName" => "Rizky Ardiansyah",
			"trxId" => "BTN0012345",
			"totalAmount" => [
				"value" => "200000.00",
				"currency" => "IDR"
			],
			"virtualAccountTrxType" => "F",
			"expiredDate" => "",
			"additionalInfo" => [
				"description" => "12345679237",
				"payment" => "FEE PETOJO",
				"paymentCode" => "330088",
				"currentAccountNo" => ""
			]
		];
		$data = $this->clientbtn->createVA($data_param);
		var_dump($data);
	}
}
