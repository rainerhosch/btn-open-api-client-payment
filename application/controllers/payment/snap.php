<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Snap extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */


	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		$this->load->model('Mpendaftaran');
		$this->load->model('Mmasterdata', 'masterdata');

		$this->load->model('MDataTables');
		$this->load->library('form_validation');
		if ($this->session->userdata('status') != 'login' and $this->session->userdata('level') != '1') {
			redirect(base_url("admin/start.html"));
		}


		$params = array('server_key' => 'SB-Mid-server-JvJFrSzEE436f8Xa8AGSDOxn', 'production' => false);
		// $params = array('server_key' => 'your_server_key', 'production' => false);
		$this->load->library('payment/midtrans', 'midtrans');
		$this->midtrans->config($params);
		$this->load->helper('url');
	}

	public function index()
	{
		// $this->load->view('payment/checkout_snap');
		$data['data_pendaftar'] = $this->Mpendaftaran->data_pendaftar();
		$data['biaya_register'] = [
			'S1' => 250000,
			'D3' => 150000
		];
		// echo '<pre>';
		// var_dump($data);
		// echo '</pre>';
		// die;
		$data['view'] = 'payment/checkout_snap';
		$this->load->view('layout/template', $data);


	}

	public function token()
	{

		// Required
		$transaction_details = array(
			'order_id' => rand(),
			'gross_amount' => 250000, // no decimal allowed for creditcard
		);

		// Optional
		$item1_details = array(
			'id' => 'a1',
			'price' => 18000,
			'quantity' => 3,
			'name' => "Apple"
		);

		// Optional
		$item2_details = array(
			'id' => 'a2',
			'price' => 20000,
			'quantity' => 2,
			'name' => "Orange"
		);

		// Optional
		// $item_details = array($item1_details, $item2_details);
		$item_details = array(
			'id' => 'pmb-000001',
			'price' => 250000,
			'quantity' => 1,
			'name' => "Pendaftaran PMB (S1)"
		);

		// Optional
		$billing_address = array(
			'first_name' => "Rizky",
			'last_name' => "Ardiansyah",
			'address' => "Jatiluhur",
			'city' => "Purwakarta",
			'postal_code' => "41152",
			'phone' => "087790001615",
			'country_code' => 'IDN'
		);

		// Optional
		$shipping_address = array(
			'first_name' => "Obet",
			'last_name' => "Supriadi",
			'address' => "Manggis 90",
			'city' => "Purwakarta",
			'postal_code' => "41152",
			'phone' => "08113366345",
			'country_code' => 'IDN'
		);

		// Optional
		$customer_details = array(
			'first_name' => "Rizky",
			'last_name' => "Ardiansyah",
			'email' => "rizky@wastukancana.ac.id",
			'phone' => "087790001615",
			'billing_address' => $billing_address,
			'shipping_address' => $shipping_address
		);

		// Data yang akan dikirim untuk request redirect_url.
		$credit_card['secure'] = true;
		//ser save_card true to enable oneclick or 2click
		//$credit_card['save_card'] = true;

		$time = time();
		$custom_expiry = array(
			'start_time' => date("Y-m-d H:i:s O", $time),
			'unit' => 'minute',
			'duration' => 2
		);

		$transaction_data = array(
			'transaction_details' => $transaction_details,
			'item_details' => $item_details,
			'customer_details' => $customer_details,
			'credit_card' => $credit_card,
			'expiry' => $custom_expiry
		);

		error_log(json_encode($transaction_data));
		$snapToken = $this->midtrans->getSnapToken($transaction_data);
		error_log($snapToken);
		echo $snapToken;
	}

	public function finish()
	{
		$result = json_decode($this->input->post('result_data'));
		echo 'RESULT <br><pre>';
		var_dump($result);
		echo '</pre>';

	}
}
