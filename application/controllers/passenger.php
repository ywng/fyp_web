<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Passengers related function are put here
 *
 * @author hpchan
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Passenger extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	var $user_type = 'passenger';

	/**
	*  This can be accessed by /passenger/register with POST method
	*
	*/
	public function register_post()
	{
		// load up the validation file
		$this->load->library('form_validation');

		/*
		*	first_name, last_name, phone, password, email
		*
		*/

		$validation_config = array(
			// array('field' => 'username', 'label' => 'username', 'rules' => 'trim|required|xss_clean|min_length[6]'),
			array('field' => 'first_name', 'label' => 'First Name', 'rules' => 'trim|required|xss_clean'),
			array('field' => 'last_name', 'label' => 'Last Name', 'rules' => 'trim|required|xss_clean'),
			array('field' => 'password', 'label' => 'password', 'rules' => 'trim|required|xss_clean|min_length[6]|md5'), 
			// use md5 to hash the password
			array('field' => 'phone', 'label' => 'phone number', 'rules' => 'trim|required|xss_clean|min_length[8]|max_length[8]|numeric'),
			array('field' => 'email', 'label' => 'email address', 'rules' => 'trim|required|xss_clean|valid_email'),
		);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			// call fail response with error code (we can predefine a set of this) 
			// and the error message will be auto set (can be overriden by adding second parameter of string type)
			$this->core_controller->fail_response(2, validation_errors());
		}

		// check if the phone, email is already associated with other accounts

        $this->load->model('passenger_model');

        $existance = $this->passenger_model->check_if_passenger_exists_by_phone($this->input->post('phone'));
        if ($existance) {
                $this->core_controller->fail_response(3);
        }

        $existance = $this->passenger_model->check_if_passenger_exists_by_email($this->input->post('email'));
        if ($existance) {
                $this->core_controller->fail_response(4);
        }

        // passed the validation process, then we add the passenger into the database
        $data = array(
                $this->passenger_model->KEY_first_name => $this->input->post('first_name'),
                $this->passenger_model->KEY_last_name => $this->input->post('last_name'),                        
                $this->passenger_model->KEY_password => $this->input->post('password'),
                $this->passenger_model->KEY_email => $this->input->post('email'),
                $this->passenger_model->KEY_phone_no => $this->input->post('phone'),
        );
        $passenger_id = $this->passenger_model->add_Passenger($data);
        if ($passenger_id < 0) {
                $this->core_controller->fail_response(5);
        }

        // probably we would like add some data before we end our process, use add_return_data('__key__', __value__)
        // After adding all required return data, call successfully_processed() from core_controller
        // Note: add_return_data is chainable
        // Example: $this->core_controller->add_return_data('key1', 'value1')->add_return_data('key2', 'value2')->add_return_data('key3', 'value3')->...
        $this->core_controller->add_return_data('pid', $passenger_id)->successfully_processed();		

		
	}

	/**
	*  This can be accessed by /passenger/view_profile with GET method
	*
	*/
	public function view_profile_get()
	{

		// passenger can only see his/her own profile

        $this->core_controller->add_return_data('passenger', 
        	$this->hide_passenger_data($this->core_controller->get_current_user())) // hide password
        		->successfully_processed();
		
	}

	/**
	*  This can be accessed by /passenger/edit_profile with POST method
	*
	*/
	public function edit_profile_post()
	{
		// only phone number and password can be changed
		$this->load->library('form_validation');

		$validation_config = array(
			array('field' => 'last_name_flag' , 'label' => 'last name flag', 'rules' => 'trim|xss_clean|min_length[1]|max_length[1]'),
			array('field' => 'last_name' , 'label' => 'last name', 'rules' => 'trim|xss_clean|min_length[1]'),
			array('field' => 'first_name_flag' , 'label' => 'first name flag', 'rules' => 'trim|xss_clean|min_length[1]|max_length[1]'),
			array('field' => 'first_name' , 'label' => 'first name', 'rules' => 'trim|xss_clean|min_length[1]'),
			array('field' => 'password_flag', 'label' => 'change password flag', 'rules' => 'trim|xss_clean|min_length[1]|max_length[1]|numeric'),
			array('field' => 'password', 'label' => 'password', 'rules' => 'trim|xss_clean|min_length[6]|md5'), 
			// use md5 to hash the password
			array('field' => 'phone_flag', 'label' => 'change phone flag', 'rules' => 'trim|xss_clean|min_length[1]|max_length[1]|numeric'),
			array('field' => 'phone', 'label' => 'phone number', 'rules' => 'trim|xss_clean|min_length[8]|max_length[8]|numeric')
		);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);
		
		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}

		$this->load->model('passenger_model');
		$update_data = array();

		if ($this->input->post('password_flag') == 1) {
			$update_data[$this->passenger_model->KEY_password] = $this->input->post('password');
		}
		if ($this->input->post('phone_flag') == 1) {
			$update_data[$this->passenger_model->KEY_phone_no] = $this->input->post('phone');
		}
		if ($this->input->post('last_name_flag') == 1) {
			$update_data[$this->passenger_model->KEY_last_name] = $this->input->post('last_name');
		}
		if ($this->input->post('first_name_flag') == 1) {
			$update_data[$this->passenger_model->KEY_first_name] = $this->input->post('first_name');
		}
		

		if (count($update_data) == 0) {
			$this->core_controller->fail_response(8); // nothing to update
		}

		$current_user = $this->core_controller->get_current_user();

		$update_status = $this->passenger_model->update_passenger($current_user[$this->passenger_model->KEY_pid], $update_data);

		$this->core_controller->successfully_processed();

	}

	/**
	*  This can be accessed by /passenger/trip_history with GET method
	*
	*/
	public function trip_history_get()
	{
		
		
	}


	/**
	*  This can be accessed by /passenger/active_trip with GET method
	*
	*/
	public function active_trip_get($limit = NULL, $offset = NULL)
	{

		if (is_null($limit) || empty($limit) || !is_numeric($limit)) {
			$limit = 20;
		}
		if (is_null($offset) || empty($offset) || !is_numeric($offset)) {
			$offset = 0;
		}

		$current_passenger = $this->core_controller->get_current_user();

		$this->load->model('order_model');
		$results = $this->order_model->get_all_active_orders_by_pid($current_passenger[$this->order_model->KEY_pid], $limit, $offset);

		$results_with_separated_gps = array();

		foreach ($results as $row) {

			$trip_detail = $this->split_latitude_longitude($row, $this->order_model->KEY_gps_from, 
				$this->order_model->KEY_gps_from.'_latitude', $this->order_model->KEY_gps_from.'_longitude');

			$trip_detail = $this->split_latitude_longitude($trip_detail, $this->order_model->KEY_gps_to, 
				$this->order_model->KEY_gps_to.'_latitude', $this->order_model->KEY_gps_to.'_longitude');
			$results_with_separated_gps[] = $trip_detail;
		}

		$this->core_controller->add_return_data('order', $results_with_separated_gps)->successfully_processed();

	}

	/**
	*  This can be accessed by /passenger/login with POST method
	*
	*/
	public function login_post()
	{
		// load up the validation file
		$this->load->library('form_validation');

		/*
		*	first_name, last_name, phone, password, email
		*
		*/

		$validation_config = array(
			array('field' => 'password', 'label' => 'password', 'rules' => 'trim|required|xss_clean|min_length[6]|md5'), 
			// use md5 to hash the password
			array('field' => 'email', 'label' => 'email address', 'rules' => 'trim|required|xss_clean|valid_email'),
		);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}


		$this->load->model('passenger_model');

		$user_data = $this->passenger_model->get_passenger_by_email($this->input->post('email'));

		if (count($user_data) == 0) {
			// email does not exist
			$this->core_controller->fail_response(6);
		}

		if ($user_data[$this->passenger_model->KEY_password] != $this->input->post('password')) {
			$this->core_controller->fail_response(6);
		}

		$new_session_token = $this->get_valid_session_token_for_passenger($user_data[$this->passenger_model->KEY_pid]);

		$this->core_controller->add_return_data('session_token', $new_session_token['session_token'])
							->add_return_data('expire_time', $new_session_token['expire_time']);

		foreach ($this->hide_passenger_data($user_data) as $key => $value) {
			$this->core_controller->add_return_data($key, $value);
		}
		$this->core_controller->successfully_processed();
		
	}

	/**
	*  This can be accessed by /passenger/logout with GET method
	*
	*/
	public function logout_get()
	{
		// expire current passenger session token
		$this->load->model('session_model');
		$this->load->model('passenger_model');
		$current_user = $this->core_controller->get_current_user();

		$this->session_model->expire_session($current_user[$this->passenger_model->KEY_pid], $this->user_type);

		$this->core_controller->successfully_processed();
	}

	/* helper function */

	private function get_valid_session_token_for_passenger($pid) {
		$this->load->model('session_model');
		$result = $this->session_model->session_token_based_on_id($pid, $this->user_type);
        if (!is_null($result) && is_array($result) && count($result) > 0) {
            // has session token, check
            
       		if (!$result['expired']) {
		    	return $this->session_model->get_session_by_id($pid, $this->user_type);
		    }
        }
        $this->session_model->generate_new_session_token($pid, $this->user_type);
        return $this->session_model->get_session_by_id($pid, $this->user_type);
	}

	private function hide_passenger_data($passenger_data_array) {
		$this->load->model('passenger_model');
		if (array_key_exists($this->passenger_model->KEY_password, $passenger_data_array)) {
			unset($passenger_data_array[$this->passenger_model->KEY_password]);
		}

		return $passenger_data_array;
	}

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

}


/* End of file passenger.php */
/* Location: ./application/controllers/passenger.php */
