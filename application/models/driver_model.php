<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Handle all transcations for the driver-related tables 
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
	
	var $Table_name_assigned_drivers = 'Assigned_Drivers';
	var $KEY_oid = 'oid';
	var $KEY_assigned_time = 'assigned_time';

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
			return $result->row_array(1);
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
		return $this->check_if_driver_exists($this->KEY_license_no,$license_no);
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

	function get_driver_by_did($did) {
		return $this->get_driver_by_key($this->KEY_did, $did);
	}

	function get_driver_by_phone_no($phone_no) {
		return $this->get_driver_by_key($this->KEY_phone_no, $phone_no);
	}

	function get_driver_by_license_no($license_no) {
		return $this->get_driver_by_key($this->KEY_license_no, $license_no);
	}

	function get_all_driver() {
		return $this->db->get($this->Table_name_driver)->result();
	}




	/* status related */

	function get_avail($did) {

		$result = $this->db->select($this->KEY_is_available)
						->from($this->Table_name_driver)
						->where($this->KEY_did, $did)
						->get();
		
		return $result->row(1)->{$this->KEY_is_available};

	}

	function update_avail($did, $avail) {	
		// update
		$this->db->where($this->KEY_did, $did)
				->update($this->Table_name_driver, array(
						$this->KEY_is_available => $avail
					));

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	function approve_driver($did, $approval) {	
		// update
		$this->db->where($this->KEY_did, $did)
				->update($this->Table_name_driver, array(
						$this->KEY_member_status_id => $approval
					));

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	/* assigned_drivers */
	function assigned_drivers($oid, $gps_longitude,$gps_latitude, $max_driver){	//param: max_driver -> max number of drivers to send notification
		
		$drivers = $this->get_list_of_nearby_drivers($oid,$gps_longitude, $gps_latitude, $max_driver);
		$this->insert_assigned_drivers($drivers,$oid);
		// (TODO) send notification here
		
	}

	//manage db storage of assigned drivers
	function insert_assigned_drivers($drivers,$oid){
		
		foreach ($drivers as $did){
			$entry[$this->KEY_did] = $did;
			$entry[$this->KEY_oid] = $oid;
			$entry[$this->KEY_assigned_time] = date('Y-m-d G:i:s');
			$this->db->insert($this->Table_name_assigned_drivers, $entry);
		}
		
	}
	
	function get_list_of_nearby_drivers($oid,$longitude,$latitude,$max_driver){
		$query = $this->db->query("SELECT dl.$this->KEY_did, ( 3959 * acos( cos( radians($latitude) ) * cos( radians( SUBSTRING_INDEX($this->KEY_gps, ',', 1) ) ) * 
												cos( radians( SUBSTRING_INDEX($this->KEY_gps, ',', -1) ) - radians($longitude) ) + sin( radians($latitude) ) * 
												sin( radians( SUBSTRING_INDEX($this->KEY_gps, ',', 1) ) ) ) ) 
											AS distance 
											FROM $this->Table_name_driver_location  dl
											JOIN $this->Table_name_driver d ON d.$this->KEY_did=dl.$this->KEY_did
											WHERE d.$this->KEY_is_available=1 
												AND d.$this->KEY_member_status_id=1
												AND d.$this->KEY_did NOT IN (SELECT $this->KEY_did FROM $this->Table_name_assigned_drivers WHERE $this->KEY_oid=$oid)
											ORDER BY distance ASC LIMIT 0, $max_driver");
											//exclude drivers with: 1) already in [Assigned_Driver] 2) member_status_id!=1    3) is_available!=1
		
		$dids = array();
		foreach ($query->result() as $row)
		{
		   array_push($dids,$row->{"$this->KEY_did"});
		}
		return $dids;
	}
	
	/* gps related */
	function get_driver_location($did) {

		$result = $this->db->select($this->KEY_gps)
						->from($this->Table_name_driver_location)
						->where($this->KEY_did, $did)
						->get();
		if ($result->num_rows() > 0) {
			$gps = $result->row(1)->{$this->KEY_gps};
			$loc = explode(",", $gps);
			return array('latitude' => $loc[0], 'longitude' => $loc[1]);
		} else {
			return array();
		}

	}

	function update_driver_location($did, $latitude, $longitude) {

		$driver_previous_loc = $this->get_driver_location($did);

		$data = array( $this->KEY_did => $did, $this->KEY_gps => $latitude . ';' . $longitude );

		if (count($driver_previous_loc) == 0) {
			
			// insert
			$this->db->insert($this->Table_name_driver_location, $data);

			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}

		} else {
			
			// update
			$this->db->where($this->KEY_did, $did)
					->update($this->Table_name_driver_location, array(
							$this->KEY_gps => $data[$this->KEY_gps]
						));

			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}

		}
	}
	
	function update_driver($did, $data) {
		$this->db->where($this->KEY_did, $did)
				->update($this->Table_name_driver, $data);

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

/* End of file driver_model.php */
/* Location: ./application/models/driver_model.php */