<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * statistics related db access
*
 * @author ywng
 */
class Statistics_model extends CI_Model{

    var $KEY_order_id = 'oid';
    var $KEY_GPS_location_from = 'gps_from';
 

    var $Table_name_Active_Order = 'Active_Order';
    var $Table_name_Inactive_Order = 'Inactive_Order';
    

    function __construct() {
        parent::__construct();
    }
    
	function get_all_order_cumulative() {
		$query_result = $this->db->query("SELECT date(order_time) as order_date, count(*) as freq FROM taxibook.Active_Order group by date(order_time) order by date(order_time)");
		
		/*$returned_result = array();
		
		foreach($query_result->result_array() as $row){
			array_push($returned_result,array("order_date" => $row->{"order_date"} , "freq" => $row->{"freq"}));
		}*/
		/*
		//data processing
		$cnt_result = count($returned_result);
		for($i=1;$i<$cnt_result;$i++){
			$returned_result[$i]["freq"] = $returned_result[$i]["freq"]+$returned_result[$i-1]["freq"];
		}
		*/
		
		return $query_result->result_array();
    }
    
	function get_all_order_DayOfWeek() {
		$query_result = $this->db->query("SELECT DAYNAME(order_time) as weekay, count(*) as freq FROM taxibook.Active_Order group by DAYNAME(order_time) order by DAYOFWEEK(order_time)");
		
		return $query_result->result();
    }
	
    function get_all_order_TimeOfDay() {
		$query_result = $this->db->query("SELECT HOUR(order_time) as hr, count(*) as freq FROM taxibook.Active_Order group by HOUR(order_time) order by HOUR(order_time)");
		
		return $query_result->result();
    }
    
   /* gps related */
    function get_all_order_gps_location() {

        $result1 = $this->db->select($this->KEY_GPS_location_from)
                        ->from($this->Table_name_Active_Order)
                        ->get()->result_array();


        $result2 = $this->db->select($this->KEY_GPS_location_from)
                        ->from($this->Table_name_Inactive_Order)
                        ->get()->result_array();

        // Merge both query results
        $result_combined = array_merge($result1,$result2);
        return $result_combined;

    }
    
}

/* end of file statistics_model.php */