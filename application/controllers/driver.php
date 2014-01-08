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

	/**
	*  This can be accessed by /driver/register with POST method
	*
	*/
	public function register_post()
	{

		
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
	*  This can be accessed by /driver/bidTrip with POST method
	*  Bid Trip
	*
	*/
	public function bidTrip_post()
	{

		
	}

	/**
	*  This can be accessed by /driver/cancelTrip with POST method
	*  Cancel Trip
	*
	*/
	public function cancelTrip_post()
	{

		
	}

	/**
	*  This can be accessed by /driver/confirmPickUp with POST method
	*  Confirm Pick-up
	*
	*/
	public function confirmPickUp_post()
	{

		
	}

	/**
	*  This can be accessed by /driver/confirmFinish with POST method
	*  Confirm Finish
	*
	*/
	public function confirmFinish_post()
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
	*  This can be accessed by /driver/ratePassenger with POST method
	*  Rate Passenger
	*
	*/
	public function ratePassenger_post()
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

		
	}

	/**
	*  This can be accessed by /driver/logout with POST method
	*  Logout
	*
	*/
	public function logout_post()
	{

		
	}


}

/* End of file driver.php */
/* Location: ./application/controllers/driver.php */