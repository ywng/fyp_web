<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Users related function are put here
 *
 * @author hpchan
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class User extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	/**
	*  This can be accessed by /user/register with POST method
	*
	*/
	public function register_post()
	{
		// load up the validation file
		$this->load->library('form_validation');

		$validation_config = array(
			array('field' => 'username', 'label' => 'username', 'rules' => 'trim|required|xss_clean|min_length[6]'),
			array('field' => 'password', 'label' => 'password', 'rules' => 'trim|requried|md5|xss_clean|min_length[6]'), // use md5 to hash the password
			array('field' => 'phone', 'label' => 'phone number', 'rules' => 'trim|required|xss_clean|min_length[8]|numeric'),
			array('field' => 'email', 'label' => 'email address', 'rules' => 'trim|required|xss_clean|valid_email'),
		);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			// call fail response with error code (we can predefinite a set of this) 
			// and the error message will be auto set (can be overriden by adding second parameter of string type)
			$this->core_controller->fail_response(2, validation_errors());
		}

		// assume table is there

		// check if the username, phone, email is already associated with other accounts
		$this->load->model('user_model');
		$existance = $this->user_model->check_if_user_exists_by_username($this->input->post('username'));
		if ($existance) {
			$this->core_controller->fail_response(3);
		}

		$existance = $this->user_model->check_if_user_exists_by_email($this->input->post('email'));
		if ($existance) {
			$this->core_controller->fail_response(4);
		}

		$existance = $this->user_model->check_if_user_exists_by_phone($this->input->post('phone'));
		if ($existance) {
			$this->core_controller->fail_response(5);
		}

		// assume there is no other fields, i.e. passed the validation process, then we add the user into the database
		$data = array(
			$this->user_model->KEY_username => $this->input->post('username'),
			$this->user_model->KEY_email => $this->input->post('email'),
			$this->user_model->KEY_phone => $this->input->post('phone'),
		);
		$user_id = $this->user_model->add_user($data);
		if ($user_id < 0) {
			$this->core_controller->fail_response(6);
		}

		// probably we would like add some data before we end our process, use add_return_data('__key__', __value__)
		// After adding all required return data, call successfully_process() from core_controller
		// Note: add_return_data is chainable
		// Example: $this->core_controller->add_return_data('key1', 'value1')->add_return_data('key2', 'value2')->add_return_data('key3', 'value3')->...
		$this->core_controller->add_return_data('user_id', $user_id)->successfully_process();
	}
}

/* End of file user.php */
/* Location: ./application/controllers/user.php */