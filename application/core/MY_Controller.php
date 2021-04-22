<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');

class MY_Controller extends CI_Controller {

	function __construct() {
		parent::__construct();

		// load model
		$this->load->model('CoreModel');
		$this->load->model('M_Transaction');
		
		$this->setupMenus();
	}

	function setupMenus() {
		$GLOBALS['menus'] = $this->CoreModel->getMenus();
	}

	function getBoolean($value) {
		if ($value == "0") {
			return false;
		} else {
			return true;
		}
	}

	function getResponseUrl() {
		$streamClean = $this->security->xss_clean($this->input->raw_input_stream);
		return json_decode($streamClean);
	}

	function getHeaderFromUrl($headerName) {
		return $this->input->get_request_header($headerName, true);
	}

	function isValidTransactionId($transactionIdentify, $accountKey) {
		$transaction = $this->M_Transaction->getTransactionById($transactionIdentify, $accountKey)->row();
		if (isset($transaction)) {
			return ($transaction->account_key == $accountKey);
		} else {
			return false;
		}
	}
}
