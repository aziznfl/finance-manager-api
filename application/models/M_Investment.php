<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Investment extends CoreModel {

	function __construct() {
		parent::__construct();
	}

	function getCategories() {
		$query = "
			SELECT * 
			FROM category_investment
		";
		return $this->db->query($query);
	}

	function getWhereTransaction($accountKey) {
		return "(account_key = '".$accountKey."')";
	}

	function getInvestment($accountKey) {
		$this->db->join("category_investment", "transaction_investment.category_id = category_investment.category_id", "left");
		$this->db->where($this->getWhereTransaction($accountKey));
		$this->db->order_by("transaction_date", "ASC");
		return $this->db->get('transaction_investment');
	}

	function getOneInvestmentById($investmentIdentify, $accountKey) {
		$query = "
			SELECT i.*, c.category_name
			FROM transaction_investment i
			JOIN category_investment c ON i.category_id = c.category_id
			WHERE i.transaction_investment_id = " . $investmentIdentify . " and " . $this->getWhereTransaction($accountKey) ."
		";
		return $this->db->query($query);
	}
}
?>