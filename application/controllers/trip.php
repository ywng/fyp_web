<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Trips related functions are put here
 *
 * @author FYP luo1
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Trip extends REST_Controller {

	var $Status_KEY_pending = 0;
	var $Status_KEY_bidded = 1;
	var $Status_KEY_customer_confirmed = 2;
	var $Status_KEY_driver_coming = 3;
	var $Status_KEY_driver_waiting = 4;
	var $Status_KEY_driver_picked_up = 5;
	var $Status_KEY_trip_finished = 6;

	var $CONST_MAX_DRIVERS_NEARBY = 5;

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}


	///////////////////
	////
	////     Common function
	////
	///////////////////


	/**
	*  This can be accessed by /trip/trip_details with GET method
	*
	*/
	public function trip_details_get()
	{
		$oid = $this->input->get('oid', TRUE);
		if ($oid == FALSE || !is_numeric($oid)) {
			$this->core_controller->fail_response(101);
		}

		$this->load->model('order_model');

		$trip_detail = $this->order_model->get_active_order_by_oid($oid);

		if (count($trip_detail) == 0) {
			$this->core_controller->fail_response(101);
		}

		$trip_detail = $this->split_latitude_longitude($trip_detail, $this->order_model->KEY_gps_from, 
			$this->order_model->KEY_gps_from.'_latitude', $this->order_model->KEY_gps_from.'_longitude');

		$trip_detail = $this->split_latitude_longitude($trip_detail, $this->order_model->KEY_gps_to, 
			$this->order_model->KEY_gps_to.'_latitude', $this->order_model->KEY_gps_to.'_longitude');

		if ($trip_detail[$this->order_model->KEY_pid]) {
			$this->load->model('passenger_model');
			$passenger_data = $this->passenger_model->get_passenger_by_pid($trip_detail[$this->order_model->KEY_pid]);
			$passenger_data = $this->hide_passenger_data($passenger_data); // hide the password before sending back
			$this->core_controller->add_return_data('passenger', $passenger_data);
		}

		if ($trip_detail[$this->order_model->KEY_did]) {
			$this->load->model('driver_model');
			$driver_data = $this->driver_model->get_driver_by_did($trip_detail[$this->order_model->KEY_did]);
			$driver_data = $this->hide_driver_data($driver_data); // hide the password before sending back
			$this->core_controller->add_return_data('driver', $driver_data);

			$location_data = $this->driver_model->get_driver_location($trip_detail[$this->order_model->KEY_did]);
			if (count($location_data) > 0) {
				$this->core_controller->add_return_data('driver_location', $location_data);
			}
		}


		$this->core_controller->add_return_data('order', $trip_detail)->successfully_processed();
	}

	/**
	*  This can be accessed by /trip/price_estimate with POST method
	*
	*/
	public function price_estimate_post()
	{
		// what is this function for?!
	}


	///////////////////
	////
	////     Passenger related
	////
	///////////////////


	/**
	*  This can be accessed by /trip/create_trip with POST method
	*  create order, and assign orders to drivers
	*/
	public function create_trip_post()
	{
		$this->load->library('form_validation');

/*
	
	var $KEY_special_note = 'special_note';
	var $KEY_status_id = 'status_id';
	var $KEY_estimated_price = 'estimated_price';
	var $KEY_estimated_duration = 'estimated_duration';
	*/


		$validation_config = array(
				array('field' => 'gps_from_latitude', 'label' => 'from gps latitude', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
				array('field' => 'gps_from_longitude', 'label' => 'from gps longitude', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
				array('field' => 'from_gps_name', 'label' => 'from gps name', 'rules' => 'trim|xss_clean|max_length[100]'),
				array('field' => 'gps_to_latitude', 'label' => 'to gps latitude', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'),
				array('field' => 'gps_to_longitude', 'label' => 'to gps longitude', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'),
				array('field' => 'to_gps_name', 'label' => 'to gps name', 'rules' => 'trim|xss_clean|max_length[100]'),
				array('field' => 'pickup_time', 'label' => 'pick up time', 'rules' => 'trim|required|xss_clean|max_length[50]'),
				array('field' => 'special_note', 'label' => 'special note', 'rules' => 'trim|xss_clean'),
				array('field' => 'estimated_price', 'label' => 'estimated price', 'rules' => 'trim|required|xss_clean|numeric|min_length[1]'),
				array('field' => 'estimated_duration', 'label' => 'estimated duration', 'rules' => 'trim|required|xss_clean|numeric|min_length[1]'),
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}

		$this->load->model('order_model');

		$current_user = $this->core_controller->get_current_user();

		$detail = array();

		if ($this->input->post('from_gps_name', TRUE) !== FALSE) {
			$detail[$this->order_model->KEY_location_from] = $this->input->post('from_gps_name');
		}
		if ($this->input->post('to_gps_name', TRUE) !== FALSE) {
			$detail[$this->order_model->KEY_location_to] = $this->input->post('to_gps_name');
		}
		if ($this->input->post('special_note', TRUE) !== FALSE) {
			$detail[$this->order_model->KEY_special_note] = $this->input->post('special_note');
		}
		if ($this->input->post('estimated_price', TRUE) !== FALSE) {
			$detail[$this->order_model->KEY_estimated_price] = $this->input->post('estimated_price');
		}
		if ($this->input->post('estimated_duration', TRUE) !== FALSE) {
			$detail[$this->order_model->KEY_estimated_duration] = $this->input->post('estimated_duration');
		}
		if ($this->input->post('pickup_time', TRUE) !== FALSE) {
			$detail[$this->order_model->KEY_order_time] = date('Y-m-d G:i:s', $this->input->post('pickup_time'));
		}
		$detail[$this->order_model->KEY_status_id] = $this->order_model->Status_KEY_pending;


		$order_id = $this->order_model->create_new_order($current_user['pid'], 
			$this->input->post('gps_from_latitude', TRUE), $this->input->post('gps_from_longitude', TRUE), 
			$this->input->post('gps_to_latitude', TRUE), $this->input->post('gps_to_longitude', TRUE), 
			$detail);

		
		if ($order_id === FALSE) {
			$this->core_controller->fail_response(102);
		}
		
		//assign drivers
		$this->load->model('driver_model');
		$this->driver_model->assigned_drivers(
			$order_id,
			$this->input->post('gps_from_longitude', TRUE),
			$this->input->post('gps_from_latitude', TRUE), 
			$this->CONST_MAX_DRIVERS_NEARBY
		);
		
		$this->core_controller->add_return_data('oid', $order_id)->successfully_processed();

	}

	/*public function assign_drivers_post()
	{
	
		//POST
		if ($this->input->post('p_gps', TRUE) !== FALSE) {
			$p_gps = $this->input->post('p_gps');
		}
		if ($this->input->post('oid', TRUE) !== FALSE) {
			$oid = $this->input->post('oid');
		}
		if ($this->input->post('max_driver', TRUE) !== FALSE) {
			$oid = $this->input->post('max_driver');
		}
	
		//validation
		$this->load->library('form_validation');
		$validation_config = array(
			array('field' => 'p_gps', 'label' => 'p_gps (passenger gps)', 'rules' => 'trim|required|xss_clean'), 
		);
		$validation_config = array(
			array('field' => 'oid', 'label' => 'oid', 'rules' => 'trim|required|xss_clean|numeric'), 
		);
		$validation_config = array(
			array('field' => 'max_driver', 'label' => 'max_driver (max no. of drivers)', 'rules' => 'trim|required|xss_clean|numeric'), 
		);
		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);
		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}
	
		
		$this->load->model('driver_model');
		$nearby_dids = $this->driver_model->get_list_of_nearby_drivers($p_gps,$oid,$max_driver);	//retrive a sorted array of dids based distance (top 5)
		$this->driver_model->insert_assigned_drivers($nearby_dids,$oid);	//add entry to [Assigned_Drivers]
			
		foreach ($nearby_dids as $key => $did){
			$this->core_controller->add_return_data("driver$key", $did);	
		}
		$this->core_controller->add_return_data('count', count($nearby_dids));	
		$this->core_controller->successfully_processed();	
	}*/

	
	/**
	*  This can be accessed by /trip/edit_trip_detail with POST method
	*
	*/
	public function edit_trip_detail_post()
	{
		$this->core_controller->fail_response(100000001);
	}

	/**
	*  This can be accessed by /trip/confirm_driver with POST method
	*
	*/
	public function confirm_driver_post()
	{
		$this->load->library('form_validation');
		$validation_config = array(
			array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}
		$this->load->model('order_model');
		$current_passenger = $this->core_controller->get_current_user();

		$status = $this->confirm_new_status($this->input->post('oid', TRUE), $this->order_model->Status_KEY_customer_confirmed, 
			$this->order_model->KEY_pid, $current_passenger[$this->order_model->KEY_pid]);

		if ($status == FALSE) {
			$this->core_controller->fail_response(100000002);
		} else {
			$this->core_controller->successfully_processed();
		}
	}

	/**
	*  This can be accessed by /trip/passenger_cancel_trip with POST method
	*
	*/
	public function passenger_cancel_trip_post()
	{
		$this->core_controller->fail_response(100000001);
	}

	/**
	*  This can be accessed by /trip/rate_driver with POST method
	*
	*/
	public function rate_driver_post()
	{
		$this->core_controller->fail_response(100000001);
	}


	///////////////////
	////
	////     Driver related
	////
	///////////////////

	/**
	*  This can be accessed by /trip/bid_trip with POST method
	*  Bid Trip
	*
	*/
	public function bid_trip_post()
	{
		// check if any did in the order
		$this->load->library('form_validation');
		$validation_config = array(
			array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}
		$this->load->model('order_model');
		$current_driver = $this->core_controller->get_current_user();

		$order = $this->core_controller->get_active_order_by_oid($this->input->post('oid', TRUE));
		if (count($order) == 0) {
			$this->core_controller->fail_response(101);
		}
		if (array_key_exists($this->order_model->KEY_did, $order) && !is_null($order[$this->order_model->KEY_did])) {
			$this->core_controller->fail_response(104);
		}
		$status = $this->order_model->driver_confirm_order($this->input->post('oid', TRUE), $current_driver[$this->order_model->KEY_did]);

		if ($status == FALSE) {
			$this->core_controller->fail_response(104);	
		}
		$this->core_controller->successfully_processed();

	}

	/**
	*  This can be accessed by /trip/driver_cancel_trip with POST method
	*  Cancel Trip
	*
	*/
	public function driver_cancel_trip_post()
	{
		$this->core_controller->fail_response(100000001);
	}



	/**
	*  This can be accessed by /trip/confirm_coming with POST method
	*  Confirm Coming
	*
	*/
	public function confirm_coming_post()
	{
		$this->load->library('form_validation');
		$validation_config = array(
			array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}
		$this->load->model('order_model');
		$current_driver = $this->core_controller->get_current_user();

		$status = $this->confirm_new_status($this->input->post('oid', TRUE), $Status_KEY_driver_coming, 
			$this->order_model->KEY_did, $current_driver[$this->order_model->KEY_did]);

		if ($status == FALSE) {
			$this->core_controller->fail_response(100000002);
		} else {
			$this->core_controller->successfully_processed();
		}
	}

	/**
	*  This can be accessed by /trip/confirm_waiting with POST method
	*  Confirm Waiting
	*
	*/
	public function confirm_waiting_post()
	{
		$this->load->library('form_validation');
		$validation_config = array(
			array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}
		$this->load->model('order_model');
		$current_driver = $this->core_controller->get_current_user();

		$status = $this->confirm_new_status($this->input->post('oid', TRUE), $Status_KEY_driver_waiting, 
			$this->order_model->KEY_did, $current_driver[$this->order_model->KEY_did]);

		if ($status == FALSE) {
			$this->core_controller->fail_response(100000002);
		} else {
			$this->core_controller->successfully_processed();
		}
		
	}	

	/**
	*  This can be accessed by /trip/confirm_pickup with POST method
	*  Confirm Pick-up
	*
	*/
	public function confirm_pickup_post()
	{
		$this->load->library('form_validation');
		$validation_config = array(
			array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}
		$this->load->model('order_model');
		$current_driver = $this->core_controller->get_current_user();


		$status = $this->confirm_new_status($this->input->post('oid', TRUE), $Status_KEY_driver_picked_up, 
			$this->order_model->KEY_did, $current_driver[$this->order_model->KEY_did]);

		if ($status == FALSE) {
			$this->core_controller->fail_response(100000002);
		} else {
			$this->core_controller->successfully_processed();
		}
	}

	/**
	*  This can be accessed by /trip/confirm_finish with POST method
	*  Confirm Finish
	*
	*/
	public function confirm_finish_post()
	{
		// need actual price and will move to inactive order
		$this->load->library('form_validation');
		$validation_config = array(
			array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
			array('field' => 'actual_price', 'label' => 'actual price', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'),
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}
		$this->load->model('order_model');
		$current_driver = $this->core_controller->get_current_user();


		$order = $this->order_model->get_active_order_by_oid($oid);
		if (count($order) == 0) {
			$this->core_controller->fail_response(101);
		}
		if ($order[$this->order_model->KEY_did] != $current_driver[$this->order_model->KEY_did]) {
			$this->core_controller->fail_response(103);
		}

		$status = $this->order_model->move_order_from_active_to_inactive($oid, $this->Status_KEY_trip_finished, 
			$this->input->post('actual_price', TRUE));

		if ($status == FALSE) {
			$this->core_controller->fail_response(100000002);
		} else {
			$this->core_controller->successfully_processed();
		}
	}

	/**
	*  This can be accessed by /trip/rate_passenger with POST method
	*  Rate Passenger
	*
	*/
	public function rate_passenger_post()
	{
		$this->core_controller->fail_response(100000001);		
	}
	

	// helper functions


	private function split_latitude_longitude($data, $key, $latitude_key, $longitude_key) {
		if (array_key_exists($key, $data)) {
			$loc = explode(",", $data[$key]);
			if (count($loc) == 2) {
				$data[$latitude_key] = $loc[0];
				$data[$longitude_key] = $loc[1];
				unset($data[$key]);
			}
		}
		return $data;
	}

	private function confirm_new_status($oid, $new_status_id, $check_key, $check_value) {

		$this->load->model('order_model');
		$order = $this->order_model->get_active_order_by_oid($oid);
		if (count($order) == 0) {
			$this->core_controller->fail_response(101);
		}
		if ($order[$check_key] != $check_value) {
			$this->core_controller->fail_response(103);
		}

		if ($order[$this->order_model->KEY_status_id] == $new_status_id) {
			// nth to change
			return TRUE;
		}

		return $this->order_model->change_status($oid, $new_status_id);

	}

	private function hide_passenger_data($passenger_data_array) {
		$this->load->model('passenger_model');
		if (array_key_exists($this->passenger_model->KEY_password, $passenger_data_array)) {
			unset($passenger_data_array[$this->passenger_model->KEY_password]);
		}

		return $passenger_data_array;
	}

	private function hide_driver_data($driver_data_array) {
		$this->load->model('driver_model');
		if (array_key_exists($this->driver_model->KEY_password, $driver_data_array)) {
			unset($driver_data_array[$this->driver_model->KEY_password]);
		}

		return $driver_data_array;
	}
}

/* End of file trip.php */
/* Location: ./application/controllers/trip.php */