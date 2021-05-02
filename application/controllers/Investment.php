<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investment extends MY_Controller {

    function __construct() {
        parent::__construct();
		$this->load->model('CoreModel');
		$this->load->model('M_Investment');

		$accountKey = $this->getHeaderFromUrl('currentUser');
        if (!$this->M_Investment->checkHeaders($accountKey)) {
			header("location: ".base_url('account/logoutUserSettings'));
			exit;
		}
    }

	// ------------ MANAGE ------------- //

	function insertInvestment() {
		$response = $this->getResponseUrl();
		
		$data = $this->getDataInvestment($response);
		$data['account_key'] = $this->input->get_request_header('currentUser', true);

		// set identify
		$timestamp = time();
		$data["investment_identify"] = "FMIV".$timestamp;

		$investmentId = $this->CoreModel->addData("transaction_investment", $data);
		echo json_encode(Array("data" => $investmentId));
	}

	function updateInvestment() {
		$response = $this->getResponseUrl();

		$data = $this->getDataInvestment($response);
		$data['account_key'] = $this->input->get_request_header('currentUser', true);
		$data["value"] = $this->setNullIsEmpty($response->value);

		// $data["investment_identify"] = $response->identify;

		echo json_encode(Array("data" => $response));
	}

	private function getDataInvestment($response) {
		$data["transaction_date"] = $response->date;
		$data["category_id"] = $response->instrumentId;
		$data["manager"] = $response->manager;
		$data["description"] = $response->description;
		$data["type"] = $response->status;
		$data["amount"] = $response->amount;

		return $data;
	}

	// ------------- FETCH ------------- //

    function portfolio() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$result = $this->M_Investment->getInvestment($accountKey)->result_array();
		$portfolios = array();
		foreach ($result as $portfolio) {
			$portfolio["amount_text"] = number_format($portfolio["amount"]);

			$arr = array();
			$arr["id"] = $portfolio["transaction_investment_id"];
			$arr["date"] = $portfolio["transaction_date"];
			$arr["state_text"] = "Progress";
			$arr["description"] = $portfolio["description"];
			$arr["instrument"] = ucwords($portfolio["category_name"]);
			$arr["manager"] = $portfolio["manager"];
			$arr["amount"] = (int)$portfolio["amount"];
			$arr["amount_text"] = number_format($arr["amount"]);
			$arr["value"] = (float)$portfolio["value"];
			$arr["value_text"] = $portfolio["unit"] != "" ? $portfolio["value"] ." ". $portfolio["unit"] : null;
			$arr["outcome"] = $arr["amount"];

			// set child array
			$arr["child"] = array($portfolio);
			if (array_key_exists($portfolio["description"], $portfolios)) {
				$portfolios[$portfolio["description"]]["value"] += (float)$portfolio["value"];
				if ($portfolio["unit"] != "") {
					$portfolios[$portfolio["description"]]["value_text"] = $portfolios[$portfolio["description"]]["value"] ." ". $portfolio["unit"];
				}
				$addProfitText = '';

				$amount = $portfolios[$portfolio["description"]]["amount"];
				if ($portfolio["type"] == "income" || $portfolio["type"] == "done") {
					$amount -= $portfolio["amount"];
					if ($portfolio["type"] == "done") {
						$amount *= -1;
						$addProfitText .= " (".number_format($amount/$portfolios[$portfolio["description"]]["outcome"]*100, 2)."%)";
						$portfolios[$portfolio["description"]]["state_text"] = "Done";
					}
				} else if ($portfolio["type"] == "outcome") {
					$amount += $portfolio["amount"];
					$portfolios[$portfolio["description"]]["outcome"] += $portfolio["amount"];
				}
				$portfolios[$portfolio["description"]]["amount"] = (int)$amount;
				$portfolios[$portfolio["description"]]["amount_text"] = number_format($amount) . $addProfitText;

				array_push($portfolios[$portfolio["description"]]["child"], $portfolio);
			} else {
				$arr["amount_text"] = number_format($arr["amount"]);
				$portfolios[$portfolio["description"]] = $arr;
			}
		}
		$portfolios = array_values($portfolios);
		echo json_encode(Array("data" => $portfolios));
    }

	function getOneInvestment() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
		$investmentIdentify = $this->input->get('investmentIdentify');
		$response = $this->M_Investment->getOneInvestmentById($investmentIdentify, $accountKey)->row();
		$data["id"] = $response->transaction_investment_id;
		$data["date"] = $response->transaction_date;
		$data["instrument"]["id"] = (int)$response->category_id;
		$data["instrument"]["name"] = $response->category_name;
		$data["manager"] = $response->manager;
		$data["type"] = $response->type;
		$data["description"] = $response->description;
		$data["amount"]["value"] = (int)$response->amount;
		$data["amount"]["text"] = number_format($response->amount);
		$data["isDone"] = $response->is_done;
		echo json_encode(array("data" => $data));
	}
}
?>