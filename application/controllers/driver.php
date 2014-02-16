<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Drivers related function are put here
 *
 * @author FYP luo1
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Driver extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	var $user_type = 'driver';

	public function index()
	{
		echo 'Hello World!';
	}

	/**
	*  This can be accessed by /driver/register with POST method
	*
	*/
	public function register_post()
	{

		// load up the validation file
		$this->load->library('form_validation');

		/*
		*	first_name, last_name, phone, password, email, license_no, licence_photo
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
			array('field' => 'license_no', 'label' => 'license number plate', 'rules' => 'trim|required|xss_clean'),
		);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			// call fail response with error code (we can predefine a set of this) 
			// and the error message will be auto set (can be overriden by adding second parameter of string type)
			$this->core_controller->fail_response(2, validation_errors());
		}

		// check if the phone, email is already associated with other accounts

        $this->load->model('driver_model');

        $existance = $this->driver_model->check_if_driver_exists_by_phone($this->input->post('phone'));
        if ($existance) {
                $this->core_controller->fail_response(3);
        }

        $existance = $this->driver_model->check_if_driver_exists_by_email($this->input->post('email'));
        if ($existance) {
                $this->core_controller->fail_response(4);
        }

        $existance = $this->driver_model->check_if_driver_exists_by_license_no($this->input->post('license_no'));
        if ($existance) {
                $this->core_controller->fail_response(4);
        }

        // passed the validation process, then we add the passenger into the database
        $data = array(
                $this->driver_model->KEY_first_name => $this->input->post('first_name'),
                $this->driver_model->KEY_last_name => $this->input->post('last_name'),                        
                $this->driver_model->KEY_password => $this->input->post('password'),
                $this->driver_model->KEY_email => $this->input->post('email'),
                $this->driver_model->KEY_phone_no => $this->input->post('phone'),
                $this->driver_model->KEY_license_no => $this->input->post('license_no'),
                $this->driver_model->KEY_license_photo => $this->input->post('license_photo'),

        );
        $driver_id = $this->driver_model->add_driver($data);
        if ($driver_id < 0) {
                $this->core_controller->fail_response(5);
        }

        // probably we would like add some data before we end our process, use add_return_data('__key__', __value__)
        // After adding all required return data, call successfully_processed() from core_controller
        // Note: add_return_data is chainable
        // Example: $this->core_controller->add_return_data('key1', 'value1')->add_return_data('key2', 'value2')->add_return_data('key3', 'value3')->...
        $this->core_controller->add_return_data('did', $driver_id)->successfully_processed();		
		
	}

	/**
	*  This can be accessed by /driver/viewProfile with GET method
	*  View Profile
	*
	*/
	public function viewProfile_get()
	{

		
	}

	/**
	*  This can be accessed by /driver/editProfile with POST method
	*  Edit Profile
	*
	*/
	public function editProfile_post()
	{

		
	}


	/**
	*  This can be accessed by /driver/setStatus with POST method
	*  Set Status 
	*
	*/
	public function setStatus_post()
	{

		
	}

	/**
	*  This can be accessed by /driver/getTripHistory with GET method
	*  Get Trip History
	*
	*/
	public function getTripHistory_get()
	{

		
	}

	/**
	*  This can be accessed by /driver/getActiveTrip with GET method
	*  Get Active Trip
	*
	*/
	public function getActiveTrip_get()
	{

		
	}

	/**
	*  This can be accessed by /driver/login with POST method
	*  Login
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


		$this->load->model('driver_model');

		$user_data = $this->driver_model->get_driver_by_email($this->input->post('email'));

		if (count($user_data) == 0) {
			// email does not exist
			$this->core_controller->fail_response(6);
		}

		if ($user_data[$this->driver_model->KEY_password] != $this->input->post('password')) {
			$this->core_controller->fail_response(6);
		}

		$new_session_token = $this->get_valid_session_token_for_driver($user_data[$this->driver_model->KEY_did]);

		$this->core_controller->add_return_data('session_token', $new_session_token['session_token'])
							->add_return_data('expire_time', $new_session_token['expire_time']);

		foreach ($this->hide_driver_data($user_data) as $key => $value) {
			$this->core_controller->add_return_data($key, $value);
		}
		$this->core_controller->successfully_processed();

		
	}

	/**
	*  This can be accessed by /driver/logout with POST method
	*  Logout
	*
	*/
	public function logout_post()
	{

		
	}

	/* helper function */

	private function get_valid_session_token_for_driver($did) {
		$this->load->model('session_model');
		$result = $this->session_model->session_token_based_on_id($did, $this->user_type);
        if (!is_null($result) && is_array($result) && count($result) > 0) {
            // has session token, check
            
       		if (!$result['expired']) {
		    	return $this->session_model->get_session_by_id($did, $this->user_type);
		    }
        }
        $this->session_model->generate_new_session_token($did, $this->user_type);
        return $this->session_model->get_session_by_id($did, $this->user_type);
	}

	private function hide_driver_data($driver_data_array) {
		$this->load->model('driver_model');
		if (array_key_exists($this->driver_model->KEY_password, $driver_data_array)) {
			unset($driver_data_array[$this->driver_model->KEY_password]);
		}

		return $driver_data_array;
	}


}

/* End of file driver.php */
/* Location: ./application/controllers/driver.php */