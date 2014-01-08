<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Passengers related function are put here
 *
 * @author FYP luo1
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
	*  This can be accessed by /passenger/viewProfile with GET method
	*
	*/
	public function viewProfile_get()
	{
		$this->load->model('passenger_model');
		$pid=$this->input->get('pid');
		$passenger_info = $this->passenger_model->get_passenger_by_pid($pid);
        if (sizeof($passenger_info) !=1) {
                $this->core_controller->fail_response(5);
        }

        $this->core_controller->add_return_data('Passenger Info:', $passenger_info)->successfully_processed();
		
	}

	/**
	*  This can be accessed by /passenger/editProfile with POST method
	*
	*/
	public function editProfile_post()
	{
		
		
	}

	/**
	*  This can be accessed by /passenger/confirmDriver with POST method
	*
	*/
	public function confirmDriver_post()
	{
		
		
	}

	/**
	*  This can be accessed by /passenger/cancelTrip with POST method
	*
	*/
	public function cancelTrip_post()
	{
		
		
	}

	/**
	*  This can be accessed by /passenger/rateDriver with POST method
	*
	*/
	public function rateDriver_post()
	{
		
		
	}

	/**
	*  This can be accessed by /passenger/getTripHistory with GET method
	*
	*/
	public function getTripHistory_get()
	{
		
		
	}


	/**
	*  This can be accessed by /passenger/getActiveTrip with GET method
	*
	*/
	public function getActiveTrip_get()
	{
		

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

		$users_data = $this->passenger_model->get_passenger_by_email($this->input->post('email'));

		if (count($users_data) == 0) {
			// email does not exist
			$this->core_controller->fail_response(6);
		}


		$user_data = $users_data[0];

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
	*  This can be accessed by /passenger/logout with POST method
	*
	*/
	public function logout_post()
	{
		
		
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

}


/* End of file passenger.php */
/* Location: ./application/controllers/passenger.php */
