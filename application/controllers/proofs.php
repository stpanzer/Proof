<?php

class Proofs extends AuthController {
	function __construct()
	{
		parent::__construct();
		$this->config->load('proof', TRUE);
		
	}
	private function _show_error($data){

		$this->load->view('proofs/generic_error', $data);

	
	}
	
	/**
	 * Show info message
	 *
	 * @param	string
	 * @return	void
	 */
	function _show_message($message, $title)
	{
		$data['message'] = $message;
		$data['title'] = $title;
		$this->load->view('proofs/generic_message', $data);
	}
	
	public function submitreq(){
		if($_SERVER['REQUEST_METHOD'] === 'POST'){
			$galid = $this->input->post('galid');
			$this->load->model('proof_model');
			
			
			if(isset($galid)){
				$data['title'] = 'Gallery submitted';
				if($this->proof_model->proof_gallery_open_by_galid($galid)){
					$this->proof_model->submit_reqs($galid);
				}
				$this->session->set_flashdata('message', 'Success');
				redirect('proofs/submitreq', 303);
				
				
			}else{
				$data['error'] = "Gallery ID number not found";
				$data['title'] = 'Error submiting print requests';
				$this->_show_error($data);
				
			}
		}else{
			$success = $this->session->flashdata('message');
			if($success){
				$this->_show_message("Success submitting print request.", "Success submitting");
			}
			
		}
	}
	
	public function review($userid = NULL){
		$this->load->model('proof_model');
		
		if(isset($userid) && $this->data['admin']){
			$data['admin'] = true;
				
		}else{
			$userid = $this->tank_auth->get_user_id();
				
		}
		
		$data['title'] = "Review order";
		$requests = Array();
		try{
			$galids = $this->proof_model->get_gallery_id($userid);
			$requests = $this->proof_model->get_req_images($galids);
			
		}catch(Exception $e){
			$data['error'] = $e->getMessage();
			$this->_show_error($data);
			return;
		}
		$data['galid'] = $galids;
		$data['usrid'] = $userid;
		$data['imgs']=$requests;

		$this->load->view('proofs/review', $data);

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
	
	public function index($galid = NULL){
		$this->load->model('gallery_model');
		$this->load->model('proof_model');
		$admin = false;
		if($this->tank_auth->get_username() == $this->config->item('admin_username', 'proof')){
			$admin = true;
			
		}
		if(is_null($galid) && $admin){
			redirect('admin/galleries');
			
		}else if(is_null($galid)){
			//if no galid is set, lets try and get the gallery of the currently logged in user.
			$galid = $this->proof_model->get_gallery_id($this->tank_auth->get_user_id());
		}
		$galtype = "";
		try{
			$galtype = $this->gallery_model->get_gallery_type($galid);
		}catch(Exception $e){
			$data['error'] = $e->getMessage();
			$this->_show_error($data);
			return;
		}
		$data['gallery_name'] = $this->gallery_model->get_gallery_name($galid);
		
		if($galtype == "proof"){
			try{
				$userid = $this->proof_model->get_user_id($galid);
			}catch(Exception $e){
				$userid = false;
				
			}
			$data['admin'] = $admin;  
			$data['title'] = "Gallery";
			try{
				if(!$this->gallery_model->gallery_open($galid)){			
					//gallery is closed
					$data['error'] = 'This gallery is now closed. It has either not been opened yet, or it has already expired.';
					$this->_show_error($data);
					return;
				}
			}catch(Exception $e){
				$this->_show_error($e->get_message());
				
			}
			//get the users' images
			try{
				$userimgs =  $this->gallery_model->get_gallery_images($galid);
			}catch (Exception $e){
				//Oops, something went wrong.
				$data['error'] = $e->getMessage();
				$this->_show_error($data);
				return;
			}
			
			$data['imgs'] = $userimgs;
			$data['usrid'] = $userid;
			$data['gal_id'] = $galid;
			$this->load->view('proofs/gallery', $data);
		}else{
			$data['error'] = "Gallery is not proof type.";
			$this->_show_error($data);
		}
	}
	
	
	
}
