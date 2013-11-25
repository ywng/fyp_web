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

		// assume table is there

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
}

/* End of file Passenger.php */
/* Location: ./application/controllers/Passenger.php */