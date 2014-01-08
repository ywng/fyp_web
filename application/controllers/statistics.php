<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Statistics related function are put here
 *
 * @author luo1
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Statistics extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	/**
	*  This can be accessed by /statistics/getAllTripStat with GET method
	*
	*/
	public function getAllTripStat_get()
	{
		
	}

	/**
	*  This can be accessed by /statistics/getDriverStat with GET method
	*
	*/
	public function getDriverStat_get()
	{
		
	}

	/**
	*  This can be accessed by /statistics/getPassenStat with GET method
	*
	*/
	public function getPassenStat_get()
	{
		
	}

	/**
	*  This can be accessed by /statistics/getPopularPlace with GET method
	*
	*/
	public function getPopularPlace_get()
	{
		
	}

	/**
	*  This can be accessed by /statistics/getNumTaxiNearBy with GET method
	*
	*/
	public function getNumTaxiNearBy_get()
	{
		
	}

}

/* End of file statistics.php */
/* Location: ./application/controllers/statistics.php */