<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * GPS related function are put here
 *
 * @author luo1
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class GPS extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	/**
	*  This can be accessed by /GPS/updateLocation with POST method
	*
	*/
	public function updateLocation_post()
	{
		
	}
    
    /**
	*  This can be accessed by /GPS/getLocation with GET method
	*
	*/
	public function getLocation_get()
	{
		
	}
}

/* End of file GPS.php */
/* Location: ./application/controllers/GPS.php */