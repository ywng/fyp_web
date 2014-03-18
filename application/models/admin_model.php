<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Handle all transcations for the passenger table 
 *
 * @author hpchan
 */

class Admin_model extends CI_Model {

	var $Table_name = 'Admin';
	var $KEY_id = 'id';
	var $KEY_email = 'email';
	var $KEY_password = 'password';


	/* this is used for internal mapping, normally do not call this outside this file */


	private function get_admin_by_key($key, $value) {
		$result = $this->db->from($this->Table_name)
							->where($key, $value)
							->get();

		if ($result->num_rows() > 0) {
			return $result->row_array(1);
		} else {
			return array();
		}

	}

	

	function get_admin_by_id($id) {
		return $this->get_admin_by_key($this->KEY_id, $id);
	}

	function get_admin_by_email($email) {
		return $this->get_admin_by_key($this->KEY_email,$email);
	}


}

/* End of file passenger_model.php */
/* Location: ./application/models/pasenger_model.php */