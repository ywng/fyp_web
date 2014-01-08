<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Handle all transcations for the driver table 
 *
 * @author hpchan
 */

class Driver_model extends CI_Model {

	var $Table_name_driver = 'Driver';
	var $KEY_did = 'did';
	var $KEY_first_name = 'first_name';
	var $KEY_last_name = 'last_name';
	var $KEY_email = 'email';
	var $KEY_phone_no = 'phone_no';
	var $KEY_password = 'password';
	var $KEY_license_no = 'license_no';
	var $KEY_license_photo = 'license_photo';
	var $KEY_member_status_id = 'member_status_id';
	var $KEY_is_available = 'is_available';

	var $Table_name_driver_location = 'Driver_Location';
	var $KEY_gps = 'gps';



	/* this is used for internal mapping, normally do not call this outside this file */

	private function check_if_driver_exists($key, $value) {
		$number_of_result = $this->db->from($this->Table_name_driver)
							->where($key, $value)
							->count_all_results();
		if ($number_of_result > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	private function get_driver_by_key($key, $value) {
		$result = $this->db->from($this->Table_name_driver)
							->where($key, $value)
							->get();

		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}

	}

	// function check_if_driver_exists_by_username($username) {
	// 	return $this->check_if_driver_exists($this->KEY_username, $username);
	// }

	function check_if_driver_exists_by_email($email) {
		return $this->check_if_driver_exists($this->KEY_email, $email);
	}

	function check_if_driver_exists_by_phone($phone) {
		return $this->check_if_driver_exists($this->KEY_phone_no, $phone);
	}

	function check_if_driver_exists_by_license_no($license_no) {
		return $this->check_if_driver_exists($this->KEY_license_no, $KEY_license_no);
	}

	function add_driver($data) {
		$this->db->insert($this->Table_name_driver, $data);
		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return -1;
		}
	}

	function get_driver_by_email($email) {
		return $this->get_driver_by_key($this->KEY_email, $email);
	}

	function get_driver_by_id($pid) {
		return $this->get_driver_by_key($this->KEY_pid, $pid);
	}

	function get_driver_by_phone_no($phone_no) {
		return $this->get_driver_by_key($this->KEY_phone_no, $phone_no);
	}

	function get_driver_by_license_no($license_no) {
		return $this->get_driver_by_key($this->KEY_license_no, $license_no);
	}
}

/* End of file driver_model.php */
/* Location: ./application/models/driver_model.php */