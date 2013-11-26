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
	*  This can be accessed by /admin/getSuspendList with GET method
	*
	*/
	public function getSuspendList_get()
	{
		
	}

	/**
	*  This can be accessed by /admin/activateUser with POST method
	*
	*/
	public function activateUser_post()
	{
		
	}

	/**
	*  This can be accessed by /admin/getUnapprovedDriverList with GET method
	*
	*/
	public function getUnapprovedDriverList_get()
	{
		
	}

	/**
	*  This can be accessed by /admin/approveDriver with POST method
	*
	*/
	public function approveDriver_post()
	{
		
	}

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */