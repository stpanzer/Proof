<?php
/* This controller handles all ajaxreqs for the proofs application
 * 
 */
class AjaxReq extends AjaxController{
	function __construct(){
		parent::__construct();
		$this->config->load('proof', TRUE);
		
		
	}
	/*
	 * Deletes the users by userid
	 * 
	 */
	function delete_user(){
		$userid = $this->input->post('usr_id');
		$this->load->model('proof_model');
		$this->proof_model->remove_users(json_decode($userid));
	}
	
	/*
	 * Returns a json representation of the print sizes in the gallery name provided.
	 * Uses the HTTP get method
	 */
	function print_sizes(){
		$galid = $this->input->post('gal_id');
		$this->load->model('proof_model');
		$result = $this->proof_model->get_sizes($galid);
		$this->output->set_content_type('application/json')
				->set_output(json_encode(array('sizes'=>$result)));
		
	}
	
	/*
	 * Sets a print request for a particular image in a gallery
	 * Uses the HTTP post method
	 */
	
	function set_print_req(){
		$imgid = $this->input->post('img_id');
		$numreq = $this->input->post('num_req');
		$size_id = $this->input->post('size_id');
		
		$this->load->model('proof_model');
		$this->proof_model->set_print_request($imgid, $size_id, $numreq);
		
		
	}
	
	function clear_print_req(){
		$imgid = $this->input->post('img_id');
		$size_id = $this->input->post('size_id');
		
		$this->load->model('proof_model');
		$this->proof_model->clear_print_request($imgid, $size_id);
	}
	
	function get_print_reqs(){
		$imgid = $this->input->post('img_id');
		
		$this->load->model('proof_model');
		$printreqs = $this->proof_model->get_print_reqs($imgid);
		
		$this->output->set_content_type('application/json')
			->set_output(json_encode(Array("reqs"=>$printreqs)));
		
	}
	/*All current print reqs on images in gallery are submitted. Clears previously submitted reqs.
	 * Generates an email to send to the admin.
	 * 
	 */
	function submit_print_reqs(){
		$usrid = $this->input->post('usr_id');
		$galname = $this->input->post('gal_name');
		
		
		$this->load->model('proof_model');
		$galid = $this->proof_model->get_gallery_id($usrid, $galname);
		$this->proof_model->submit_reqs($galid);
		
	}
	
	/*
	 * Takes a galname and userid from post, as well as a json array which contains the new order.
	 * Uses the HTTP post method
	 */
	function reorder_gallery(){
		$galname = $this->input->post('gallery_name');
		$galid = $this->input->post('galid');
		$order = json_decode($this->input->post('new_order'));
		if(is_null($order)){
			$this->output->append_output("Error, invalid/undecodeable json, ".$this->input->post('new_order'));
			return;
			
		}
		$this->load->model('proof_model');
		$this->proof_model->change_gallery_order($galid, $order);		
		
	}
	/*
	 * Returns a row from the user profile db corresponding to the id provided in the query string
	 * Uses the HTTP get method
	 */
	function userInfo(){
		$id = $this->input->get('id');
		
		$this->load->model('proof_model');
		$query = $this->proof_model->get_profile($id);
		if($query->num_rows() == 1){
			$resrow = $query->row();
			$this->output->set_content_type('application/json')
				->set_output(json_encode($resrow));
		
		}else{
			$this->output->append_output("Error, man");
			
		}
	}
	function toggle_gallery_open(){
		$galid = $this->input->post('gal_id');
		
		$this->load->model('proof_model');
				
		$this->proof_model->toggle_gallery_open($galid);
		
	}
	/*
	 * Returns a list of n users from offset. Id number, username, and email.
	 * Uses the HTTP get method
	 */
	function get_n_users(){
		$n = $this->input->get('num');
		$offset = $this->input->get('offset');
		
		$this->load->model('proof_model');
		$this->load->model('gallery_model');
		$query = $this->proof_model->get_n_users($n, $offset);
		$numusers = $this->proof_model->get_num_users();
		$numrows = $query->num_rows();
		if($numrows > 0){
			$result = array();
			$this->output->set_content_type('application/json');
			foreach($query->result() as $row){
				try{
					$gallery = $this->gallery_model->get_gallery_name($this->proof_model->get_gallery_id($row->id));
				}catch (Exception $e){
					$gallery = "No gallery";
				}
				
				$rowdata = array(array("username" => $row->username, "id" => $row->id, "email" => $row->email, "gallery_name" => $gallery));
				$result = array_merge($result, $rowdata); 
			}
			
			if($offset+$n >= $numusers){
				$result = array("users"=>$result, "message"=>"no_more_users");
				
			}else{
				$result = array("users"=>$result, "message" => "more");
				
			}
			$this->output->set_output(json_encode($result));
		}else{
			$this->output->set_output(json_encode(array("error" => "No more users")));
			
			
		}
		
	}
	
	/*
	 * Handle image upload from dropzone
	 * Uses the HTTP post method
	 */
	
	
	function gal_fileupload(){
		$this->load->model('gallery_model');
		$tmpFile = $_FILES['image']['tmp_name'];
		$original_filename = $_FILES['image']['name'];
	
		//gallery id
		$galid = $this->input->post('galid');
		$ds = "/";
	    
		$path =  $this->config->item('image_folder', 'proof').$ds.$galid.$ds;
		
		if(!file_exists($path)){
			if(!mkdir($path, 0777, true)){
				$this->output->append_output("Error creating gallery or user folder.");
				return;
			}
		}
		//get the extension of the uploaded file so we can have that after the unique id
		$fileext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
		
		
		$config['upload_path'] = $path;
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size'] = 1024 * 4;
		$imgid = uniqid();
		$config['file_name'] = $imgid;
		$this->load->library('upload', $config);
		
		if ( ! $this->upload->do_upload('image'))
		{  
			$error = array('error' => $this->upload->display_errors());
			$this->output->set_status_header('413');
			$this->output->set_content_type('application/json')
				->set_output(json_encode($error));
			return;
				
		}
		
		
		//get image path from upload data
		$upload_data = $this->upload->data();
		
		//make thumbnail using the CI image library
		$config['source_image'] = $upload_data['full_path'];
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = 100;
		$config['height'] = 80;
	
		$this->load->library('image_lib', $config);
		if(!$this->image_lib->resize()){
			$error = array('error' => $this->image_lib->display_errors());
			$this->output->set_status_header('500');
			$this->output->set_content_type('application/json')
				->set_output(json_encode($error));
			return;
		}
		
		//add image to database
		$this->gallery_model->add_img_to_gallery($galid,  $imgid.'.'.$fileext, $imgid.'_thumb.'.$fileext, $original_filename);
	}
	
	function set_print_sizes(){
		$printsizes = json_decode($this->input->post('print_sizes'));
		$default = $this->input->post('default');
		$this->load->model('proof_model');
		
		if($default){
			$this->proof_model->specify_default_sizes($printsizes);
			
			
		}else{
			$galid = $this->input->post('gal_id');
			$this->proof_model->create_sizes($galid, $printsizes);
			//clear requests
			$this->proof_model->clear_all_print_requests($galid);
		}
		
		//return the new size list
		if($default){
			$newsizelist = $this->proof_model->get_default_print_sizes();
		}else{
			$newsizelist = $this->proof_model->get_sizes($galid);
		}
		
		$this->output->set_content_type('application/json')
			->set_output(json_encode($newsizelist));
		
	}
	function user_has_reqs(){
		$userid = $this->input->post('usr_id');
		
		$this->load->model('proof_model');
		$this->output->set_output(json_encode(Array("has_req" => $this->proof_model->has_reqs($userid))));
		
		
	}
	function clear_print_size(){
		$this->load->model('proof_model');
		$sizeid = $this->input->post('size_id');
		$default = $this->input->post('default');
		$galid = $this->input->post('gal_id');
		$this->proof_model->clear_print_size($sizeid);
		//if we aren't clearing a default print size, we need to remove any print requests on the gallery.
		if(!$default){
			$this->proof_model->clear_all_print_requests($galid);			
		}
		
	}
	function toggle_order_filled(){
		$orderid = $this->input->post('orderid');
		if(!is_null($orderid)){
			$this->load->model('proof_model');
			$this->proof_model->toggle_order_filled($orderid);
			
		}
		
	}
	function set_gallery_name(){
		$galid = $this->input->post('gal_id');
		$galname = $this->input->post('gallery_name');
		$this->load->library('form_validation');
		
		if(isset($galid) && isset($galname)){
			if($this->form_validation->alpha_dash_space($galname)){
				$this->load->model('gallery_model');
				$this->gallery_model->set_gallery_name($galid, $galname);	
				$this->output->set_output(json_encode(Array('success'=>'')));
			}else{
				$this->output->set_output(json_encode(Array('error'=>'non-alpha')));
			}
		}
		
	}
}