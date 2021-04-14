<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Summary extends MY_Controller {

    function __construct() {
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        parent::__construct();
		$this->load->model('M_Transaction');

		$accountKey = $this->getHeaderFromUrl('currentUser');
        if (!$this->M_Transaction->checkHeaders($accountKey)) {
			header("location: ".base_url('account/logoutUserSettings'));
			exit;
		}
    }

	function yoy() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$arrTrans = $this->M_Transaction->getDashboardTransaction($accountKey)->result();
		$all = array();
		foreach($arrTrans as $trans) {
			$response["year"] = (int)$trans->year;
			$response["month"] = (int)$trans->month;
			$response["totalTransaction"] = (int)$trans->total_transaction;
			$response["totalInvestment"] = (int)$trans->total_investment;
			array_push($all, $response);
		}
		echo json_encode(array("data" => $all));
	}

	function cardInfo() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$investment = $this->M_Transaction->getTotalInvestment($accountKey)->row();
		$response = array(
			"investment" => array(
				"title" => "Investment",
				"backgroundColor" => "bg-green",
				"value" => (int)$investment->total_investment, 
				"text" => number_format($investment->total_investment),
				"link" => "investment/portfolio"
			), "upcoming" => array(
				"title" => "Upcoming",
				"backgroundColor" => "bg-blue",
				"value" => 0,
				"text" => "0",
				"link" => ""
			)
		);
		echo json_encode($response);
	}
}
?>