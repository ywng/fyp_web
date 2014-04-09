<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Handle all transcations for the apns_token table 
 *
 * @author hpchan
 */

class APNS_model extends CI_Model {

	var $Table_name_passenger = 'APNS_Token_Passenger';
	var $Table_name_driver = 'APNS_Token_Driver';
	var $KEY_pid = 'pid';
	var $KEY_did = 'did';
	var $KEY_device_token = 'device_token';
	var $KEY_active = 'active';
	var $KEY_sns_endpoint = 'sns_endpoint';


	function check_ios_device_token_exists_with_passenger_id($passenger_id, $device_token) {

		$result = $this->db->from($this->Table_name_passenger)
							->where($this->KEY_pid, $passenger_id)
							->where($this->KEY_device_token, $device_token)
							->get();

		if ($result->num_rows() > 0) {
			return $result->row_array(1);
		} else {
			return FALSE;
		}

	}

	function check_ios_device_token_exists_with_driver_id($driver_id, $device_token) {

		$result = $this->db->from($this->Table_name_driver)
							->where($this->KEY_did, $driver_id)
							->where($this->KEY_device_token, $device_token)
							->get();

		if ($result->num_rows() > 0) {
			return $result->row_array(1);
		} else {
			return FALSE;
		}

	}

	function get_active_device_token_by_passenger_id($passenger_id) {
		$result = $this->db->from($this->Table_name_passenger)
							->where($this->KEY_pid, $passenger_id)
							->where($this->KEY_active, 1)
							->get();
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	function get_active_device_token_by_driver_id($driver_id) {
		$result = $this->db->from($this->Table_name_driver)
							->where($this->KEY_did, $driver_id)
							->where($this->KEY_active, 1)
							->get();
		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	function assign_passenger_sns_endpoint($device_token, $sns_endpoint) {
		$this->db->where($this->KEY_device_token, $device_token)
				->update($this->Table_name_passenger, array(
						$this->KEY_sns_endpoint => $sns_endpoint,
					));

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	function assign_driver_sns_endpoint($device_token, $sns_endpoint) {
		$this->db->where($this->KEY_device_token, $device_token)
				->update($this->Table_name_driver, array(
						$this->KEY_sns_endpoint => $sns_endpoint,
					));

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function deactive_passenger_device($passenger_id, $device_token) {
		return $this->change_passenger_active_status($passenger_id, $device_token, 0);
	}

	function active_passenger_device($passenger_id, $device_token) {
		return $this->change_passenger_active_status($passenger_id, $device_token, 1);
	}

	private function change_passenger_active_status($pid, $device_token, $active_status) {

		$this->db->where($this->KEY_pid, $pid)
				->where($this->KEY_device_token, $device_token)
				->update($this->Table_name_passenger, array(
						$this->KEY_active => $active_status
					));

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	function deactive_driver_device($driver_id, $device_token) {
		return $this->change_driver_active_status($driver_id, $device_token, 0);
	}

	function active_driver_device($driver_id, $device_token) {
		return $this->change_driver_active_status($driver_id, $device_token, 1);
	}

	private function change_driver_active_status($did, $device_token, $active_status) {

		$this->db->where($this->KEY_did, $did)
				->where($this->KEY_device_token, $device_token)
				->update($this->Table_name_driver, array(
						$this->KEY_active => $active_status
					));

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	function register_passenger_device($pid, $device_token, $sns_endpoint) {

		$this->db->insert($this->Table_name_passenger, 
								array(
									$this->KEY_pid => $pid, 
									$this->KEY_device_token => $device_token,
									$this->KEY_sns_endpoint => $sns_endpoint,
									$this->KEY_active => 1,
								)
						);

		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return FALSE;
		}

	}

	function unregister_passenger_device($pid, $device_token) {

		$this->db->where($this->KEY_pid, $pid)
				->where($this->KEY_device_token, $device_token)
				->delete($this->Table_name_passenger);

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function unregister_driver_device($did, $device_token) {

		$this->db->where($this->KEY_did, $did)
				->where($this->KEY_device_token, $device_token)
				->delete($this->Table_name_driver);

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function register_driver_device($did, $device_token, $sns_endpoint) {

		$this->db->insert($this->Table_name_driver, 
								array(
									$this->KEY_did => $did, 
									$this->KEY_device_token => $device_token,
									$this->KEY_sns_endpoint => $sns_endpoint,
									$this->KEY_active => 1,
								)
						);

		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return FALSE;
		}

	}

	function get_all_active_device_from_passenger($pid) {

		$result = $this->db->from($this->Table_name_passenger)
						->where($this->KEY_pid, $pid)
						->where($this->KEY_active, 1)
						->get();

		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}

	}

	function get_all_active_device_from_driver($did) {

		$result = $this->db->from($this->Table_name_driver)
						->where($this->KEY_did, $did)
						->where($this->KEY_active, 1)
						->get();

		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}

	}

}

/* End of file passenger_model.php */
/* Location: ./application/models/pasenger_model.php */