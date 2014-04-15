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
	*  This can be accessed by /statistics/getAllTrip_TimeOfDay with GET method
	*/
	public function getAllOrderDayOfWeek_get()
	{
		$this->load->model('statistics_model');
		$results= $this->statistics_model->get_all_order_DayOfWeek();

		foreach($results as $result)
			$this->core_controller->add_return_data($result->{"weekday"},$result->{"freq"});
		$this->successfully_processed();
	}
	
	/**
	*  This can be accessed by /statistics/getAllTrip_TimeOfDay with GET method
	*/
	public function getAllOrderTimeOfDay_get()
	{
		$this->load->model('statistics_model');
		$results= $this->statistics_model->get_all_order_TimeOfDay();

		foreach($results as $result)
			$this->core_controller->add_return_data($result->{"hr"},$result->{"freq"});
		$this->successfully_processed();
	}
	
	/**
	*  This can be accessed by /statistics/getAllOrderCumulative with GET method
	*/
	public function getAllOrderCumulative_get()
	{
		$this->load->model('statistics_model');
		
		/*$results= $this->statistics_model->get_all_order_cumulative();
		
		foreach($results as $result)
			$this->core_controller->add_return_data($result["order_date"],$result["freq"]);*/	//hard-coded field name here
			
		$this->core_controller->add_return_data('a','b')->successfully_processed();
			
		//$this->successfully_processed();
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

	public function getAllOrdersGPSLocation_get(){

		$this->load->model('statistics_model');
		$result= $this->statistics_model->get_all_order_gps_location();

		$this->core_controller->add_return_data('gps_loca',$result)->successfully_processed();
		
	}

}

/* End of file statistics.php */
/* Location: ./application/controllers/statistics.php */