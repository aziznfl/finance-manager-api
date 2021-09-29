<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Debts extends CoreModel {

	function __construct() {
		parent::__construct();
	}

    function getList($accountKey) {
		$query = "
			SELECT *
			FROM (
			    SELECT to_who, SUM(
			        IF (type = 'debts' OR type = 'transfer_from', -amount, amount)
			    ) AS balance
			    FROM debts
			    WHERE ".$this->getWhereTransaction($accountKey)."
			    GROUP BY to_who
			    ORDER BY transaction_date ASC
			) AS debts_view
			WHERE debts_view.balance != 0
		";
		return $this->db->query($query);
	}
}
?>