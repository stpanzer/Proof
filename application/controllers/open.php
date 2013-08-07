<?php
class Open extends CI_Controller {
	public $data;
	function __construct()
	{
		parent::__construct();
		$this->load->helper('url');
		$this->config->load('proof', TRUE);
		$this->load->library('security');
		$this->load->library('tank_auth');
		if($this->tank_auth->is_logged_in() && ($this->tank_auth->get_username() == $this->config->item('admin_username', 'proof'))){
			$this->data['admin'] = true;
		}else{
			$this->data['admin'] = false;
		}
		
	}
		
	function _output($content)
	{
		// Load the base template with output content available as $content
		$data['content'] = &$content;
		echo($this->load->view('base', $data, true));
	}
	function _remap($method)
	{
		$param_offset = 2;
	
		// Default to index
		if ( ! method_exists($this, $method))
		{
			// We need one more param
			$param_offset = 1;
			$method = 'index';
		}
	
		// Since all we get is $method, load up everything else in the URI
		$params = array_slice($this->uri->rsegment_array(), $param_offset);
	
		// Call the determined method with all params
		call_user_func_array(array($this, $method), $params);
	}
	private function _show_error($data){

		$this->load->view('proofs/generic_error', $data);


	}

	private function _show_message($data){

		$this->load->view('proofs/generic_message', $data);


	}
	
	public function index($galid = NULL){
		$this->load->model('gallery_model');
		
		try{
			$galtype = $this->gallery_model->get_gallery_type($galid);
		}catch(Exception $e){
			$data['error'] = $e->getMessage();
			$this->_show_error($data);
			return;
		}
		$data['gallery_name'] = $this->gallery_model->get_gallery_name($galid);
		if($galtype == "open"){
			$data['images'] = $this->gallery_model->get_gallery_images($galid);
			$data['galid'] = $galid;
				
			$data['title'] = "Gallery";
			$this->load->view('proofs/open_gallery', $data);
				
		}else{
			$data['error'] = "Gallery is not of open type.";
			$this->_show_error($data);
		}
		
	}
}