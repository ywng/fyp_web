<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Handle all transcations for the Active_order & inactive_order table 
 *
 * @author hpchan
 */

class Order_model extends CI_Model {

	var $Table_name_active = 'Active_Order';
	var $KEY_oid = 'oid';
	var $KEY_pid = 'pid';
	var $KEY_did = 'did';
	var $KEY_location_from = 'location_from';
	var $KEY_gps_from = 'gps_from';
	var $KEY_location_to = 'location_to';
	var $KEY_gps_to = 'gps_to';
	var $KEY_order_time = 'order_time';
	var $KEY_special_note = 'special_note';
	var $KEY_status_id = 'status_id';
	var $KEY_estimated_price = 'estimated_price';
	var $KEY_estimated_duration = 'estimated_duration';
	var $KEY_estimated_pickuptime = 'estimated_pickuptime';
	var $KEY_post_time = 'post_time';

	var $Table_name_inactive = 'Inactive_Order';
	var $KEY_actual_price = 'actual_price';

	var $Status_KEY_pending = 0;
	var $Status_KEY_bidded = 1;
	var $Status_KEY_customer_confirmed = 2;
	var $Status_KEY_driver_coming = 3;
	var $Status_KEY_driver_waiting = 4;
	var $Status_KEY_driver_picked_up = 5;
	var $Status_KEY_trip_finished = 6;


	function move_order_from_active_to_inactive($oid, $new_status_id, $actual_price = NULL) {

		$active_order = $this->get_active_order_by_oid($oid);
		if (count($active_order) == 0) {
			return FALSE;
		} else {
			$active_order[$this->KEY_status_id] = $new_status_id;
			if (!is_null($actual_price) && is_numeric($actual_price)) {
				$this->db->insert($this->Table_name_inactive, $active_order);
				return TRUE;
			} else {
				return FALSE;
			}
		}

	}


	function get_active_order_by_oid($oid) {

		$result = $this->db->from($this->Table_name_active)
						->where($this->KEY_oid, $oid)
						->get();

		if ($result->num_rows() > 0) {
			return $result->row_array(1);
		} else {
			return array();
		}

	}

	function get_inactive_order_by_oid($oid) {

		$result = $this->db->from($this->Table_name_inactive)
						->where($this->KEY_oid, $oid)
						->get();

		if ($result->num_rows() > 0) {
			return $result->row_array(1);
		} else {
			return array();
		}
	}

	function get_all_active_orders_by_did($did, $limit, $offset) {
		return $this->get_all_active_orders_by_key($this->KEY_did, $did, $limit, $offset);
	}

	function get_all_active_orders_by_pid($pid, $limit, $offset) {
		return $this->get_all_active_orders_by_key($this->KEY_pid, $pid, $limit, $offset);
	}

	function get_all_inactive_orders_by_did($did, $limit, $offset) {
		return $this->get_all_inactive_orders_by_key($this->KEY_did, $did, $limit, $offset);
	}

	function get_all_inactive_orders_by_pid($pid, $limit, $offset) {
		return $this->get_all_inactive_orders_by_key($this->KEY_pid, $pid, $limit, $offset);
	}

	function create_new_order($pid, $latitude_from, $longitude_from, $latitude_to, $longitude_to, $other_detail) {

		$gps_from = $latitude_from.','.$longitude_from;
		$gps_to = $latitude_to.','.$longitude_to;
		$other_detail[$this->KEY_pid] = $pid;
		$other_detail[$this->KEY_gps_from] = $gps_from;
		$other_detail[$this->KEY_gps_to] = $gps_to;
		$other_detail[$this->KEY_post_time] = date('Y-m-d G:i:s');
		$this->db->insert($this->Table_name_active, $other_detail);

		if ($this->db->affected_rows() > 0) {
			return $this->db->insert_id();
		} else {
			return FALSE;
		}

	}
	
	function driver_confirm_order($oid, $did) {
		return $this->update_order($oid, array($this->KEY_status_id => $Status_KEY_bidded, $this->KEY_did => $did));
	}

	function update_order($oid, $detail) {
		$this->db->where($this->KEY_oid, $oid)
				->update($this->Table_name_active, $detail);

		if ($this->db->affected_rows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function change_status($oid, $new_status_id) {
		return $this->update_order($oid, array($this->KEY_status_id => $new_status_id));
	}

	// private functions


	private function get_all_orders_by_key($table_name, $key, $value, $limit, $offset) {
		$result = $this->db->from($table_name)
						->where($key, $value)
						->order_by($this->KEY_post_time, 'DESC')
						->limit($limit, $offset)->get();

		if ($result->num_rows() > 0) {
			return $result->result_array();
		} else {
			return array();
		}
	}

	private function get_all_active_orders_by_key($key, $value, $limit, $offset) {
		return $this->get_all_orders_by_key($this->Table_name_active, $key, $value, $limit, $offset);
	}

	private function get_all_inactive_orders_by_key($key, $value, $limit, $offset) {
		return $this->get_all_orders_by_key($this->Table_name_inactive, $key, $value, $limit, $offset);
	}

}

/* End of file order_model.php */
/* Location: ./application/models/order_model.php */