<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends MY_Controller {

    function __construct() {
        parent::__construct();
		$this->load->model('M_Transaction');
		$this->load->model('M_Investment');
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

	function transaction() {
		$responses = $this->M_Transaction->getCategories()->result_array();
		$datas = array();
		foreach ($responses as $response) {
			$data = $this->getCategoryItem($response);
			array_push($datas, $data);
		}
		$datas = array_values($datas);
		echo json_encode(array("data" => $datas));
	}

	private function getCategoryItem($data) {
		$category["id"] = (int)$data["category_id"];
		$category["name"] = $data["category_name"];
		$category["icon"] = $data["icon"];
		$category["position"] = (int)$data["position"];
		$category["parentId"] = (int)$data["parent_id"];
		return $category;
	}

	function investment() {
		$responses = $this->M_Investment->getCategories()->result_array();
		$datas = array();
		foreach($responses as $response) {
			$data["id"] = $response["category_id"];
			$data["name"] = $response["category_name"];
			$data["valueUnit"] = $response["unit"];
			array_push($datas, $data);
		}
		$datas = array_values($datas);
		echo json_encode(array("data" => $datas));
	}
}
?>