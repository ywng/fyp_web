<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * GPS related function are put here
 *
 * @author luo1
 */

require_once (APPPATH. 'libraries/REST_Controller.php');

class Gps extends REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('CORE_Controller');
		$this->core_controller->set_response_helper($this);
	}

	/**
	*  This can be accessed by /gps/update_location with POST method
	*
	*/
	public function update_location_post()
	{
		$this->load->library('form_validation');

		$validation_config = array(
				array('field' => 'latitude', 'label' => 'latitude', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
				array('field' => 'longitude', 'label' => 'longitude', 'rules' => 'trim|required|xss_clean|min_length[1]|numeric'), 
			);

		$this->form_validation->set_error_delimiters('', '')->set_rules($validation_config);

		if ($this->form_validation->run() === FALSE) {
			$this->core_controller->fail_response(2, validation_errors());
		}

		$current_driver = $this->core_controller->get_current_user();

		$this->load->model('driver_model');

		$status = $this->driver_model->update_driver_location($current_driver[$this->driver_model->KEY_did], 
			$this->input->post('latitude'), $this->input->post('longitude'));

		$this->core_controller->add_return_data('update_status', $status)->successfully_processed();
	}
    
    /**
	*  This can be accessed by /gps/driver_location with GET method
	*
	*/
	public function driver_location_get()
	{
		
		$did = $this->input->get('did', TRUE);

		if ($did === FALSE) {
			$this->core_controller->fail_response(1001);
		}

		$this->load->model('driver_model');
		$data = $this->driver_model->get_driver_location($did);

		if (count($data) == 0) {
			$this->core_controller->fail_response(1002);
		}

		$this->core_controller->add_return_data('latitude', $data['latitude'])
			->add_return_data('longitude', $data['longitude'])
			->successfully_processed();

	}
}

/* End of file gps.php */
/* Location: ./application/controllers/gps.php */