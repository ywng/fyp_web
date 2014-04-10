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
    
    
    /*
     * 
     */
    
   /* gps related */
    function get_all_order_gps_location() {

        $result1 = $this->db->select($this->KEY_GPS_location_from)
                        ->from($this->Table_name_Active_Order)
                        ->get();


        $result2 = $this->db->select($this->KEY_GPS_location_from)
                        ->from($this->Table_name_Inactive_Order)
                        ->get();

        // Merge both query results
        $result_combined = array_merge($result1,$result2);
        if ($result_combined->num_rows() > 0) {
           return $result_combined->result_array();
        } else {
            return array();
        }

    }
    
}

/* end of file statistics_model.php */