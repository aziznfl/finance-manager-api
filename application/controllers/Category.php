<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends MY_Controller {

    function __construct() {
        parent::__construct();
		$this->load->model('M_Transaction');
		$this->load->model('M_Investment');

		$accountKey = $this->getHeaderFromUrl('currentUser');
        if (!$this->M_Transaction->checkHeaders($accountKey)) {
			header("location: ".base_url('account/logoutUserSettings'));
			exit;
		}
    }

	function listItem() {
		$responses = $this->M_Transaction->getCategories()->result_array();
		$datas = array();
		foreach ($responses as $response) {
			if($response["parent_id"] == 1) {
				$data = $this->getCategoryItem($response);
				$data["child"] = array();
				$datas[$data["id"]] = $data;
			} else {
				$data = $this->getCategoryItem($response);
				array_push($datas[$response["parent_id"]]["child"], $data);
			}
		}
		$datas = array_values($datas);
		echo json_encode(array("data" => $datas));
	}

	private function getCategoryItem($data) {
		$category["id"] = $data["category_id"];
		$category["name"] = $data["category_name"];
		$category["icon"] = $data["icon"];
		$category["position"] = $data["position"];
		$category["parentId"] = $data["parent_id"];
		return $category;
	}

	function investment() {
		$response = $this->M_Investment->getCategories()->result_array();
		echo json_encode(array("data" => $response));
	}
}
?>