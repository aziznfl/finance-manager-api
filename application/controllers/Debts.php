<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Debts extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('M_Transaction');
		$this->load->model('M_Debts');
    }

    function list() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
        $results = $this->M_Debts->getList($accountKey)->result_array();
        $data = [];
        foreach ($results as $result) {
            $item['to'] = $result['to_who'];
            $item['balance'] = $result['balance'];
            array_push($data, $item);
        }
        echo json_encode(Array("data" => $data));
    }

    function manage() {
		$accountKey = $this->getHeaderFromUrl('currentUser');
        $data['account_key'] = $accountKey;

		$response = $this->getResponseUrl();
        print_r($response);
        // $data['amount'] = $response->amount;
        // $data['type'] = $response->type;
        // $data['to_who'] = $response->toWho;
        // $data['added_date'] = $response->addedData;
        // $this->M_Transaction->addData("debts", $data);
        
        // echo json_encode(Array("data" => 1));
    }
}
?>