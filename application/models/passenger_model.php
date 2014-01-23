<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Handle all transcations for the passenger table 
 *
 * @author hpchan
 */

class Passenger_model extends CI_Model {

	var $Table_name = 'Passenger';
	var $KEY_pid = 'pid';
	var $KEY_first_name = 'first_name';
	var $KEY_last_name = 'last_name';
	var $KEY_email = 'email';
	var $KEY_phone_no = 'phone_no';
	var $KEY_password = 'password';


	/* this is used for internal mapping, normally do not call this outside this file */

	private function check_if_passenger_exists($key, $value) {
		$number_of_result = $this->db->from($this->Table_name)
							->where($key, $value)
							->count_all_results();
		if ($number_of_result > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	private function get_passenger_by_key($key, $value) {
		$result = $this->db->from($this->Table_name)
							->where($key, $value)
							->get();

		if ($result->num_rows() > 0) {
			return $result->row_array(1);
		} else {
			return array();
		}

	}

	// function check_if_passenger_exists_by_username($username) {
	// 	return $this->check_if_passenger_exists($this->KEY_username, $username);
	// }

	function check_if_passenger_exists_by_email($email) {
		return $this->check_if_passenger_exists($this->KEY_email, $email);
	}

	function check_if_passenger_exists_by_phone($phone) {
		return $this->check_if_passenger_exists($this->KEY_phone_no, $phone);
	}

	function add_passenger($data) {
		$this->db->insert($this->Table_name, $data);
		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return -1;
		}
	}

	function get_passenger_by_email($email) {
		return $this->get_passenger_by_key($this->KEY_email, $email);
	}

	function get_passenger_by_pid($pid) {
		return $this->get_passenger_by_key($this->KEY_pid, $pid);
	}

	function get_passenger_by_phone_no($phone_no) {
		return $this->get_passenger_by_key($this->KEY_phone_no, $phone_no);
	}

	function update_passenger($pid, $data) {
		$this->db->where($this->KEY_pid, $pid)
				->update($this->Table_name, $data);

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

/* End of file passenger_model.php */
/* Location: ./application/models/pasenger_model.php */