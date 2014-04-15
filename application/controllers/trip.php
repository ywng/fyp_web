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
		$driver_ids = $this->driver_model->assigned_drivers(
			$order_id,
			$this->input->post('gps_from_longitude', TRUE),
			$this->input->post('gps_from_latitude', TRUE), 
			$this->CONST_MAX_DRIVERS_NEARBY
		);

		// Mark these drivers and send apns to them
		foreach ($driver_ids as $row => $driver_id) {
			$message = "Someone needs a taxi. Response?";
			$detail_array = array( 'oid' => $order_id );
			$this->send_apns_to_driver($driver_id, $message, $detail_array);
		}
		
		$this->core_controller->add_return_data('oid', $order_id)->successfully_processed();

	}
	
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

		$oid = $this->input->post('oid', TRUE);

		$apns_array = array(
				'message' => 'Passenger has just confirmed your bid.',
				'detail' => array('oid' => $oid),
			);

		$status = $this->confirm_new_status($oid, $this->order_model->Status_KEY_customer_confirmed, 
			$this->order_model->KEY_pid, $current_passenger[$this->order_model->KEY_pid], 'passenger', $apns_array);

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
		$this->load->model('order_model');
		$this->load->model('driver_model');	

		$oid =$this->input->post('oid');

		$criteria = array(
                $this->order_model->KEY_rating_session_key => md5($this->input->post('date_time')),
                $this->order_model->KEY_oid => $oid,
                $this->order_model->KEY_rating_score => "-1",
       	);

       	$update_data = array(
                $this->order_model->KEY_rating_score => $this->input->post('score'),
                $this->order_model->KEY_rating_comment => $this->input->post('comment'),
       
       	);

       	if ( !$this->order_model->search_and_update_rating_session($criteria,$update_data)){
       		$this->core_controller->add_return_data('message', "invalid link for rating!")->successfully_processed();

       	}else{
       		//as delete the rating session
   			$order = $this->order_model->get_inactive_order_by_oid($oid);
   			$driver=$this->driver_model->get_driver_by_did($order['did']);

   			$new_avg_score=($driver[$this->driver_model->KEY_average_rating]*$driver[$this->driver_model->KEY_rating_count]+$this->input->post('score'))/($driver[$this->driver_model->KEY_rating_count]+1);
       		$rating = array(
                $this->driver_model->KEY_average_rating => $new_avg_score,
                $this->driver_model->KEY_rating_count=> $driver[$this->driver_model->KEY_rating_count]+1,
      	 	);

       	
			$this->driver_model->update_driver($order['did'],$rating);

			$this->core_controller->add_return_data('message', "successfully rated!")->successfully_processed();


       	}

	
		

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

		// send apns to passenger


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
	// public function confirm_coming_post()
	// {
	// 	$this->load->library('form_validation');
	// 	$validation_config = array(
	// 		array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
	// 		);

	// 	$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

	// 	if ($this->form_validation->run() === FALSE) {
	// 		$this->core_controller->fail_response(2, validation_errors());
	// 	}
	// 	$this->load->model('order_model');
	// 	$current_driver = $this->core_controller->get_current_user();

	// 	$oid = $this->input->post('oid', TRUE);

	// 	$apns_array = array(
	// 			'message' => 'Your driver is coming now!',
	// 			'detail' => array('oid' => $oid),
	// 		);

	// 	$status = $this->confirm_new_status($oid, $Status_KEY_driver_coming, 
	// 		$this->order_model->KEY_did, $current_driver[$this->order_model->KEY_did], 'driver', $apns_array);

	// 	if ($status == FALSE) {
	// 		$this->core_controller->fail_response(100000002);
	// 	} else {
	// 		$this->core_controller->successfully_processed();
	// 	}
	// }

	/**
	*  This can be accessed by /trip/confirm_waiting with POST method
	*  Confirm Waiting
	*
	*/
	// public function confirm_waiting_post()
	// {
	// 	$this->load->library('form_validation');
	// 	$validation_config = array(
	// 		array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
	// 		);

	// 	$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

	// 	if ($this->form_validation->run() === FALSE) {
	// 		$this->core_controller->fail_response(2, validation_errors());
	// 	}
	// 	$this->load->model('order_model');
	// 	$current_driver = $this->core_controller->get_current_user();

	// 	$oid = $this->input->post('oid', TRUE);

	// 	$apns_array = array(
	// 			'message' => 'Your driver is waiting for you.',
	// 			'detail' => array('oid' => $oid),
	// 		);

	// 	$status = $this->confirm_new_status($oid, $Status_KEY_driver_waiting, 
	// 		$this->order_model->KEY_did, $current_driver[$this->order_model->KEY_did], 'driver', $apns_array);

	// 	if ($status == FALSE) {
	// 		$this->core_controller->fail_response(100000002);
	// 	} else {
	// 		$this->core_controller->successfully_processed();
	// 	}
		
	// }	

	/**
	*  This can be accessed by /trip/confirm_pickup with POST method
	*  Confirm Pick-up
	*
	*/
	// public function confirm_pickup_post()
	// {
	// 	$this->load->library('form_validation');
	// 	$validation_config = array(
	// 		array('field' => 'oid', 'label' => 'order id', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
	// 		);

	// 	$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

	// 	if ($this->form_validation->run() === FALSE) {
	// 		$this->core_controller->fail_response(2, validation_errors());
	// 	}
	// 	$this->load->model('order_model');
	// 	$current_driver = $this->core_controller->get_current_user();

	// 	$oid = $this->input->post('oid', TRUE);

	// 	$apns_array = array(
	// 			'message' => 'Your trip should be started now.',
	// 			'detail' => array('oid' => $oid),
	// 		);

	// 	$status = $this->confirm_new_status($oid, $Status_KEY_driver_picked_up, 
	// 		$this->order_model->KEY_did, $current_driver[$this->order_model->KEY_did], 'driver', $apns_array);

	// 	if ($status == FALSE) {
	// 		$this->core_controller->fail_response(100000002);
	// 	} else {
	// 		$this->core_controller->successfully_processed();
	// 	}
	// }

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
		$oid = $this->input->post('oid', TRUE);

		$order = $this->order_model->get_active_order_by_oid($oid);
		if (count($order) == 0) {
			$this->core_controller->fail_response(101);
		}
		if ($order[$this->order_model->KEY_did] != $current_driver[$this->order_model->KEY_did]) {
			$this->core_controller->fail_response(103);
		}

		$apns_array = array(
				'message' => 'Your trip is finished. Thank you for riding with TaxiBook.',
				'detail' => array('oid' => $oid),
			);

		$status = $this->order_model->move_order_from_active_to_inactive($oid, $this->Status_KEY_trip_finished, 
			$this->input->post('actual_price', TRUE), 'driver', $apns_array);

		if ($status == FALSE) {
			$this->core_controller->fail_response(100000002);
		} else {
			$data = array(
                $this->order_model->KEY_rating_session_key => md5($order['order_time']),
                $this->order_model->KEY_oid => $oid,
       	    );

		    $this->order_model->generate_rating_session($data);

	
			$this->load->model('driver_model');
			$driver=$this->driver_model-> get_driver_by_did($order['did']);

		    //generate email for rating 
		    $link="http://ec2-54-255-141-218.ap-southeast-1.compute.amazonaws.com/webpages/feedback.html?oid=".$oid.
		    '&date_time='.urlencode($order['order_time']).'&location_from='.urlencode($order['location_from']).'&location_to='.urlencode($order['location_to']).
		    '&driver='.urlencode($driver['first_name'].' '.$driver['last_name']);
	

		    $config = Array(		
		    'protocol' => 'smtp',
		    'smtp_host' => 'ssl://smtp.googlemail.com',
		    'smtp_port' => 465,
		    'smtp_user' => 'taxibook.no.reply@gmail.com',  //use our google ac to send the email
		    'smtp_pass' => 'taxibook123',
		    'smtp_timeout' => '4',
		    'mailtype'  => 'text', 
		    'charset'   => 'iso-8859-1'
		    );

		    $message = 
'Dear valued user,

Thank you for booking a taxi journey using our app!

The journey details:
Order id: '.$oid.'
Date & Time: '.$order['order_time'].'
From: '.$order['location_from'].'
To: '.$order['location_to'].'

You can rate the driver and comment by clicking the following link:
'.$link.'

Thank you!

Best regards,
Taxibook';

			$this->load->model('passenger_model');
			$passenger=$this->passenger_model-> get_passenger_by_pid($order['pid']);
 
			$this->load->library('email', $config);
			$this->email->set_newline("\r\n");
			$this->email->from('taxibook.no.reply@gmail.com', 'TaxiBook');
			$this->email->to($passenger['email']); 
			$this->email->subject('[non-reply]Please rate your driver.');
			$this->email->message($message);	

			$this->email->send();

			$this->core_controller->add_return_data('mail details', $this->email->print_debugger())->successfully_processed();
		

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

	private function confirm_new_status($oid, $new_status_id, $check_key, $check_value, $init_from, $details) {

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

		$new_status = $this->order_model->change_status($oid, $new_status_id);

		if ($new_status == TRUE) {
			if ( strtolower($init_from) == 'passenger') {
				// send to driver

				$did = $order[$this->order_model->KEY_did];
				if ($did != 0 && is_numeric($did)) {
					$this->send_apns_to_driver($did, $details['message'], $details['detail']);
				}
			} else if (strtolower($init_from) == 'driver') {
				// send to passenger

				$pid = $order[$this->order_model->KEY_pid];
				if ($pid != 0 && is_numeric($pid)) {
					$this->send_apns_to_passenger($pid, $details['message'], $details['detail']);
				}
			}
		}

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

	private function send_apns_to_driver($did, $message, $details) {

		$this->load->model('apns_model');
		$this->load->helper('sns');
		$this->load->config('amazon');

		$access_key = $this->config->item('amazonS3AccessKey');
		$secret_key = $this->config->item('amazonS3SecretKey');

		$devices = $this->apns_model->get_all_active_device_from_driver($did);

		foreach ($devices as $device) {
			if (!empty($device['sns_endpoint'])) {
				sns_apple_push_notification_message($access_key, $secret_key, $device['sns_endpoint'], $message, $details);	
			}
		}

	}

	private function send_apns_to_passenger($pid, $message, $details) {

		$this->load->model('apns_model');
		$this->load->helper('sns');
		$this->load->config('amazon');

		$access_key = $this->config->item('amazonS3AccessKey');
		$secret_key = $this->config->item('amazonS3SecretKey');

		$devices = $this->apns_model->get_all_active_device_from_passenger($pid);

		foreach ($devices as $device) {
			if (!empty($device['sns_endpoint'])) {
				sns_apple_push_notification_message($access_key, $secret_key, $device['sns_endpoint'], $message, $details);	
			}
		}

	}

	private function generate_rating_sessiom($Key, $oid) {

	

	}

}

/* End of file trip.php */
/* Location: ./application/controllers/trip.php */