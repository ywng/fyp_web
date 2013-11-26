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

	/**
	*  This can be accessed by /passenger/register with POST method
	*
	*/
	public function register_post()
	{

		
	}

	/**
	*  This can be accessed by /passenger/viewProfile with GET method
	*
	*/
	public function viewProfile_get()
	{
		
		
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
		
		
	}

	/**
	*  This can be accessed by /passenger/logout with POST method
	*
	*/
	public function logout_post()
	{
		
		
	}


}


/* End of file passenger.php */
/* Location: ./application/controllers/passenger.php */
=======
/* End of file Passenger.php */
/* Location: ./application/controllers/Passenger.php */

