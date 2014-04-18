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
    
	var $weekday_name= array(
		'0'=>'Sunday',
		'1'=>'Monday',
		'2'=>'Tuesday',
		'3'=>'Wednesday',
		'4'=>'Thursday',
		'5'=>'Friday',
		'6'=>'Saturday'
	);

    function __construct() {
        parent::__construct();
    }
    
	function get_all_order_cumulative() {
		$query_result = $this->db->query("SELECT date(order_time) as order_date, count(*) as freq FROM (SELECT order_time FROM taxibook.Active_Order UNION ALL SELECT order_time FROM taxibook.Inactive_Order) T_Active_UNION_Inactive group by date(order_time) order by date(order_time)");
		
		$returned_result = $query_result->result_array();
		
		//data processing
		$cnt_result = count($returned_result);
		if($cnt_result>0)
			$returned_result[0]["freq"] = (int)$returned_result[0]["freq"];
		
		for($i=1;$i<$cnt_result;$i++){
			$returned_result[$i]["freq"] = $returned_result[$i]["freq"]+$returned_result[$i-1]["freq"];
		}
		
		return $returned_result;
    }
    
    function getAllOrderHourWeek() {
		$query = $this->db->query("SELECT DAYOFWEEK(order_time)-1 as weekday, HOUR(order_time) as hour, count(*) as freq FROM (SELECT order_time FROM taxibook.Active_Order UNION ALL SELECT order_time FROM taxibook.Inactive_Order) T_Active_UNION_Inactive group by DAYOFWEEK(order_time), HOUR(order_time) order by DAYOFWEEK(order_time)");

		$query_result = $query->result_array();
		
		$k = 0;
		$returned_result = array();
		for($i=0;$i<7;$i++){
			for($j=0;$j<24;$j++){
				$freq = 0;
				if($k<count($query_result)){
					$row = $query_result[$k];
					if($row['weekday']==$i && $row['hour']==$j){
						$freq = (int)$row['freq'];
						$k++;
					}
					else{
						$freq = 0;
					}
				}
				array_push($returned_result, array('weekay'=>$i,'hour'=>$j,'freq'=>$freq));
			}
		}
		
		return $returned_result;
    }
	
	function getAllOrderHourOfDay(){
		$query = $this->db->query("SELECT HOUR(order_time) as hour, count(*) as freq from (SELECT order_time FROM taxibook.Active_Order UNION ALL SELECT order_time FROM taxibook.Inactive_Order) T_Active_UNION_Inactive group by HOUR(order_time) order by HOUR(order_time)");
		
		
		return $query->result_array();
	}
	
	function getAllOrderWeekDay(){
		$query = $this->db->query("SELECT DAYNAME(order_time) as weekday, count(*) as freq FROM (SELECT order_time FROM taxibook.Active_Order  UNION ALL SELECT order_time FROM taxibook.Inactive_Order ) T_Active_UNION_Inactive group by DAYOFWEEK(order_time) order by DAYOFWEEK(order_time)");
		
		return $query->result_array();
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