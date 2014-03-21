<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Admin related function are put here
 *
 * @author luo1
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Admin extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	var $user_type = 'admin';

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


		$this->load->model('admin_model');

		$user_data = $this->admin_model->get_admin_by_email($this->input->post('email'));

		if (count($user_data) == 0) {
			// email does not exist
			$this->core_controller->fail_response(6);
		}

		if ($user_data[$this->admin_model->KEY_password] != $this->input->post('password')) {
			$this->core_controller->fail_response(6);
		}

		$new_session_token = $this->get_valid_session_token_for_admin($user_data[$this->admin_model->KEY_id]);

		$this->core_controller->add_return_data('session_token', $new_session_token['session_token'])
							->add_return_data('expire_time', $new_session_token['expire_time']);

		foreach ($this->hide_admin_data($user_data) as $key => $value) {
			$this->core_controller->add_return_data($key, $value);
		}
		$this->core_controller->successfully_processed();
		
	}

	/**
	*  This can be accessed by /admin/logout with GET method
	*  Logout
	*
	*/
	public function logout_get()
	{
		// expire current passenger session token
		$this->load->model('session_model');
		$this->load->model('admin_model');
		$current_user = $this->core_controller->get_current_user();

		$this->session_model->expire_session($current_user[$this->admin_model->KEY_id], $this->user_type);

		$this->core_controller->successfully_processed();
		
	}

	/**
	*  This can be accessed by /admin/suspend_drivers with GET method
	*
	*/
	public function suspend_drivers_get()
	{
		
	}

	/**
	*  This can be accessed by /admin/activate_driver with POST method
	*
	*/
	public function activate_driver_post()
	{
		
	}

	/**
	*  This can be accessed by /admin/allDriver with GET method
	*  
	*/
	public function allDriver_get()
	{
		$this->load->model('driver_model');
		$driver_array = $this->driver_model->get_all_driver();
		$this->core_controller->add_return_data('aaData', $driver_array)->successfully_processed();	
	}

	/**
	*  This can be accessed by /admin/pending_approval_drivers with GET method
	*
	*/
	public function pending_approval_drivers_get()
	{
		
	}

	/**
	*  This can be accessed by /admin/approve_driver with POST method
	*
	*/
	public function approve_driver_post()
	{
		$this->load->model('driver_model');
		

		 if ($this->driver_model->approve_driver($this->input->post('did'),$this->input->post('approval'))) {
               $this->core_controller->successfully_processed(); 
        }else{
        	   $this->core_controller->fail_response(2);
        }
		
	}

	/* helper function */

	private function get_valid_session_token_for_admin($id) {
		$this->load->model('session_model');
		$result = $this->session_model->session_token_based_on_id($id, $this->user_type);
        if (!is_null($result) && is_array($result) && count($result) > 0) {
            // has session token, check
            
       		if (!$result['expired']) {
		    	return $this->session_model->get_session_by_id($id, $this->user_type);
		    }
        }
        $this->session_model->generate_new_session_token($id, $this->user_type);
        return $this->session_model->get_session_by_id($id, $this->user_type);
	}

	private function hide_admin_data($admin_data_array) {
		$this->load->model('admin_model');
		if (array_key_exists($this->admin_model->KEY_password, $admin_data_array)) {
			unset($admin_data_array[$this->admin_model->KEY_password]);
		}

		return $admin_data_array;
	}

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */