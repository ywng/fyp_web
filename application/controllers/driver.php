<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Users related function are put here
 *
 * @author hpchan
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Driver extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	/**
	*  This can be accessed by /driver/register with POST method
	*
	*/
	public function register_post()
	{

		
	}

	/**
	*  This can be accessed by /driver/profile with GET method
	*  View Profile
	*
	*/
	public function profile_get()
	{

		$this->core_controller->add_return_data('successful')->successfully_process();
	}

}

/* End of file user.php */
/* Location: ./application/controllers/user.php */