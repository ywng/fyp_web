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
		
	}

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */