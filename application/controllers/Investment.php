<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Investment extends MY_Controller {

    function __construct() {
        parent::__construct();
		$this->load->model('M_Investment');

		$accountKey = $this->getHeaderFromUrl('currentUser');
        if (!$this->M_Investment->checkHeaders($accountKey)) {
			header("location: ".base_url('account/logoutUserSettings'));
			exit;
		}
    }

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
}
?>