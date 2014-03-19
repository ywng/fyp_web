<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Handle all transcations for the user table 
 *
 * @author hpchan
 */

class User_model extends CI_Model {

	var $Table_name = 'User';
	var $KEY_user_id = 'user_id';
	var $KEY_username = 'username';
	var $KEY_email = 'email';
	var $KEY_phone = 'phone';

	function check_if_user_exists($key, $value) {
		$number_of_result = $this->db->from($this->Table_name)
							->where($key, $value)
							->count_all_results();
		if ($number_of_result > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function check_if_user_exists_by_username($username) {
		return $this->check_if_user_exists($this->KEY_username, $username);
	}

	function check_if_user_exists_by_email($email) {
		return $this->check_if_user_exists($this->KEY_email, $email);
	}

	function check_if_user_exists_by_phone($phone) {
		return $this->check_if_user_exists($this->KEY_phone, $phone);
	}

	function add_users($data) {
		$this->db->insert($this->Table_name, $data);
		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return -1;
		}
	}
}

/* End of file user_model.php */
/* Location: ./application/models/user_model.php */