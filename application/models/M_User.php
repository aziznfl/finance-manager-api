<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_User extends CI_Model {

	function __construct() {
		parent::__construct();
	}

	function login($email) {
		$this->db->where('email', $email);
		return $this->db->get('account');
	}
}
?>