<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Custom class to handle the request permission validation and response
 * This must be used for every controller that is created
 * Refer to /application/controllers/user.php for example
 * @author hpchan
 */

class CORE_Controller {
    
    protected $context = null;
    private $white_list_uri = array('user/register', );
    private $session = null;
    private $response_data = array();
    private $CI = null;
	private $response_helper = null;
    
    public function __construct() {
	
        $this->CI =& get_instance();
		
		//$this->pre_service_start_checking(); // this will not work until tables are set

    }
    
    public function pre_service_start_checking() {
        $pre_uri = str_replace(array(".json", ".xml"), array('',''), $this->CI->uri->uri_string());
        $uri_breakdown = explode('/', $pre_uri);

        if (count($uri_breakdown) >= 2) {
            $uri = implode('/', array( $uri_breakdown[0], $uri_breakdown[1] ));
        } else {
            $uri = $pre_uri;
        }

        if (in_array($uri, $this->white_list_uri)) {
            
        } else if ($this->credentials_check() === TRUE) {
            $this->response_data['session_token'] = $this->session['session_token'];
            $this->response_data['expire_time'] = $this->session['expire_time'];
        } else {
            $this->fail_response(1);
        }
    }

	public function set_response_helper($helper) {
		$this->response_helper = $helper;
	}

    public function successfully_processed() {
        $this->success_response($this->response_data);
    }
    
    public function fail_response($custom_status_code = 0, $msg = null ) {

        if (!is_null($msg)) {
            // get the message back
            if ($custom_status_code < 0) {
                $custom_status_code = $custom_status_code * -1;
            }

            $this->CI->load->config('error_message');
            $error_message_set = $this->CI->config->item('error');
            if (array_key_exists($custom_status_code, $error_message_set)) {
                $msg = $error_message_set[$custom_status_code];
            } else {
                $msg = $error_message_set[0];
            }
        }

        if ($this->response_helper) {
            $this->response_helper->response(array(
                'status_code' => $custom_status_code,
                'message' => $msg,
            ), 200);
        } else {
            $this->fallback_fail_response($msg, $custom_status_code);
        }
    }
    
    public function add_return_data($key, $value) {
        
        if ($key == 'expire_time') {
            $this->response_data[$key] = $value;
        } else if (array_key_exists($key, $this->response_data)) {
            throw new Exception('Key already exist', -1);
        }

        $this->response_data[$key] = $value;
		return $this;
    }
    
	public function get_context() {
		return $this->context;
	}

    public function new_user() {
        return new user();
    }
    
    private function credentials_check() {
	
		$this->CI->load->library('form_validation');
        $this->CI->form_validation->set_rules('id','username', 'trim|required|xss_clean|min_length[6]');
        $this->CI->form_validation->set_rules('session_token', 'session token', 'trim|required|xss_clean|min_length[30]');
        $this->CI->form_validation->set_rules('schema', 'app schema', 'trim|required|xss_clean|min_length[1]');
        $this->CI->form_validation->set_error_delimiters('', '');

        if ($this->CI->form_validation->run() === FALSE) {
            return FALSE;
        } else {
            $this->CI->load->model('user_model');
            $this->CI->load->model('session_model');
            $user_detail = $this->CI->user_model->get_user_by_username($this->CI->input->post('username'));
            
			// check if user does not exist
			if (count($user_detail) == 0) {
				return FALSE;
			}

            $current_user = user::db_record_to_object($user_detail[0]);
            
            $this->context->set_current_user_obj($current_user);
            
            $result = $this->CI->session_model->get_session_by_user_id($current_user->get_user_id());
            if (!is_null($result) && is_array($result) && count($result) > 0) {
                // has session token, check
                
                $received_session_token = $this->CI->input->post('session_token');
                
                if ($received_session_token && $received_session_token == $result['session_token']) {
                    if (time() - strtotime($result['expire_time']) >= 0) {
                        $this->CI->session_model->generate_new_session_token($current_user->get_user_id());
                        $this->session = $this->CI->session_model->get_session_by_user_id($current_user->get_user_id());
                    } else {
                        $this->session['session_token'] = $result['session_token'];
                        $this->session['expire_time'] = $result['expire_time'];
                    }
                    return TRUE;
                } else {
                    return FALSE;
                }
                
            } else {
                // db does not have record of the user session token, ask user to relogin
                // $this->session = $this->CI->session_model->generate_new_session_token($current_user->get_user_id());
                // return true;
                return FALSE;
            }
        }
        
        
    }
    
    protected function success_response($response_data) {
		
        if (is_array($response_data)) {
            $response_data['status_code'] = 1;
			$this->response_helper->response($response_data, 200);
        } else {
            $this->response_helper->response(array(
                'status_code' => 1,
                'data' => $response_data,
            ), 200);
        }
    }
    
    private function fallback_fail_response($msg, $custom_status_code, $http_code = 200) {
		
		header('HTTP/1.1: ' . $http_code);
		header('Status: ' . $http_code);
        header('Content-type: application/json');
		$response_array = array(
			'status_code' => $custom_status_code,
            'message' => $msg,
		);
		
		$encoded_string = json_encode($response_array);
		exit($encoded_string);
		
	}

}


/* end of file Core_Controller.php */