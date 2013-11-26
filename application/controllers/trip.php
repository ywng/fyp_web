<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Trips related functions are put here
 *
 * @author FYP luo1
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Trip extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	/**
	*  This can be accessed by /trip/createTrip with POST method
	*
	*/
	public function createTrip_post()
	{
		
	}

	/**
	*  This can be accessed by /trip/getTripDetails with GET method
	*
	*/
	public function getTripDetails_get()
	{
		
	}

	/**
	*  This can be accessed by /trip/getTripStatus with GET method
	*
	*/
	public function getTripStatus_get()
	{
		
	}

	/**
	*  This can be accessed by /trip/editTripDetails with POST method
	*
	*/
	public function editTripDetails_post()
	{
		
	}

	/**
	*  This can be accessed by /trip/priceEstimate with POST method
	*
	*/
	public function priceEstimate_post()
	{
		
	}


}

/* End of file trip.php */
/* Location: ./application/controllers/trip.php */