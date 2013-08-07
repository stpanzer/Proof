<?php
class Admin extends AuthController{
	
	function __construct(){
		parent::__construct();
		if(! ($this->data['admin']) )
		{
			
			redirect('');
				
		}
	}
	
	
	function settings(){
		$this->load->model('proof_model');
		try{
			$defaultpsizes = $this->proof_model->get_default_print_sizes();
		}catch(Exception $e){
			$defaultpsizes = array();
			
		}
		$data['print_sizes'] = $defaultpsizes;
		$data['title'] = 'Default settings';
		$data['default'] = true;

		$this->load->view('proofs/gal_settings', $data);

		
	}
	
	function index(){
		
		$this->output->append_output("Admin index");
		
	}
	
	private function _show_error($data){

		$this->load->view('proofs/generic_error', $data);

	
	}
	
	public function orders($orderid = NULL){
		$this->load->model('proof_model');
		
		if(!isset($orderid)){
			//Get a full list of orders
			
			$orderlist = $this->proof_model->print_order_list(false);
			$filled = $this->proof_model->print_order_list(true);
			
			$data['orders'] = $orderlist;
			$data['filled_orders'] = $filled;
			$data['title'] = 'Order list';
	
			$this->load->view('proofs/orderlist', $data);

		}else if(is_numeric($orderid)){
			$data['orderid'] = $orderid;
			$data['filled'] = $this->proof_model->order_filled($orderid);
			//Show a particular order
			$userorders = $this->proof_model->get_print_order($orderid);
			$data['order'] = $userorders;
			$data['title'] = 'Order list';

			$this->load->view('proofs/order', $data);

		}else{
			//non numeric orderid, show error
			$data['error'] = "Invalid order number";
			$data['title'] = "Invalid order number";
			$this->_show_error($data);

		}
		
		
		return;
	}
	/* Serves up the organizer view
	 * 
	 * 
	 */
	public function organize($galid = NULL){
		$this->load->model('proof_model');
		$this->load->model('gallery_model');
		
		$data['title'] = "Organize";
		
		//get the users' images
		try{
			$userimgs =  $this->proof_model->get_user_images($galid);
			$data['gallery_name'] = $this->gallery_model->get_gallery_name($galid);
		}catch (Exception $e){
			//Oops, something went wrong.
			$data['error'] = $e->getMessage();

			$this->load->view('proofs/generic_error', $data);

				
		}
		
		$data['imgs'] = $userimgs['imgs'];
		$data['thumbs'] = $userimgs['thumbs'];
		$data['imgids'] = array();
		$data['galid'] = $galid;
		foreach($userimgs['imgs'] as $img){
			array_push($data['imgids'], basename($img));
			
		}

		$this->load->view('proofs/img_organize', $data);
	
	
	}
	function _show_galleries(){
		$data['title'] = "Galleries";
		try{
			$data['galleries'] = $this->proof_model->get_galleries();
		}catch(Exception $e){
			$data['galleries'] = Array();
		}
		$this->load->view('proofs/gallery_manager', $data);
	}
	function upload($galid = NULL){
		if($galid == NULL){
			$this->_show_galleries();
		}else{
			$this->load->model('gallery_model');
			$data['title'] = "Upload Photos";
			$data['gallery_name'] = $this->gallery_model->get_gallery_name($galid);
			$data['galid'] = $galid;
			$this->load->view('proofs/upload_photos', $data);
		}
		
	}
	
	function publish($galid = NULL){
		if(is_null($galid)){
			redirect('admin/galleries');
			return;
		}
		$this->load->model('gallery_model');
		$data['title'] = "Publish gallery";
		$data['gallery'] = $this->gallery_model->get_gallery_info($galid);
		$this->load->view('proofs/publish', $data);
		
		
	}
	
	function galleries($galid = NULL){
		$this->load->model('proof_model');
		$this->load->model('gallery_model');
		if($galid == NULL){
			//Display all galleries
			$this->_show_galleries();
		}else if($galid === "new"){
			//New gallery
			$this->load->library('form_validation');
			$data['title'] = "New Gallery";
			
			//validation rules
			$galtype = $this->input->post('galtype');
			$this->form_validation->set_rules('galname','gallery name', 'trim|xss|required|alpha_dash_space');
			$this->form_validation->set_rules('galtype', 'gallery type', 'trim|xss|required');
		
					
			if($this->form_validation->run() == FALSE){
				//show errors in form
				$this->load->view('proofs/new_gallery', $data);
			}else{
				//get data, insert into temporary user table and gallery table
				$galname = $this->input->post('galname');
				$galtype = $this->input->post('galtype');
				$username = $this->input->post('username');
				$email = $this->input->post('email');
				$this->load->model('gallery_model');
				$newgalid = $this->gallery_model->new_gallery($galname, $galtype);
				
				redirect('admin/upload/'.$newgalid);
			}
			
		}else if(is_numeric($galid)){
			//Show specific gallery's price list
			$data['title'] = "Edit print and price list";
			//Edit price list
			$data['galid'] = $galid;
			$data['default'] = false;
			$data['gallery_name'] = $this->gallery_model->get_gallery_name($galid); 
			$data['print_sizes'] =  $this->proof_model->get_sizes($galid);
			$this->load->view('proofs/gal_settings', $data);
			
		}
		
	}
	
	function add_user($galid = NULL){
		$this->load->model('gallery_model');
		$this->load->model('proof_model');
		if($galid == NULL){
			redirect('admin/galleries');
		}
		
		$data['title'] = "Add user";
		
		if($this->proof_model->gallery_has_user($galid)){
			$data['error'] = "Gallery already has user.";
			$data['title'] = "Gallery error";
			$this->_show_error($data);
			return;
		}
		
		//form rules
		$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean|min_length['.$this->config->item('username_min_length', 'tank_auth').']|max_length['.$this->config->item('username_max_length', 'tank_auth').']|alpha_dash');		
		$this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|min_length['.$this->config->item('password_min_length', 'tank_auth').']|max_length['.$this->config->item('password_max_length', 'tank_auth').']|alpha_dash');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|xss_clean|matches[password]');

		if ($this->form_validation->run()) {								// validation ok
			if (!is_null($data = $this->tank_auth->create_user(
					$this->form_validation->set_value('username'),
					$this->form_validation->set_value('email'),
					$this->form_validation->set_value('password'),
					false))) {									// success
				
				$this->load->model('gallery_model');
				$this->gallery_model->attach_user($galid, $data['user_id']);
				
				$data['site_name'] = $this->config->item('website_name', 'tank_auth');
				$email_activation = $this->config->item('email_activation', 'tank_auth');
				
				if ($this->config->item('email_account_details', 'tank_auth')) {	// send "welcome" email
	
					$this->_send_email('welcome', $data['email'], $data);
				}
				unset($data['password']); // Clear password (just for any case)
				
				$this->_show_message($this->lang->line('auth_message_registration_completed_2').' '.anchor('/auth/login/', 'Login'), "Registration successful");
				return;
				
			} else {
				$errors = $this->tank_auth->get_error_message();
				foreach ($errors as $k => $v)	$data['errors'][$k] = $this->lang->line($v);
			}
		}
		$data['gallery_name'] = $this->gallery_model->get_gallery_name($galid);
		$this->load->view('proofs/add_user', $data);		
		
	}
	
	/**
	 * Serves up a list of users, allowing the admin to delete them.
	 * 
	 * @return void
	 */
	
	function users(){
		$this->load->model('proof_model');
		$data['title'] = 'Admin Panel';
		$query = $this->proof_model->get_n_users(20, 0);
		$data['query'] = $query;

		$this->load->view('proofs/user_list', $data);

		
	}
	
	function del_gallery($galid = NULL){
		if(is_null($galid)){
			redirect('admin/galleries');
		}
		$this->load->model('gallery_model');
		$this->gallery_model->delete_gallery($galid);
		redirect('admin/galleries');
	}
	
	
	/**
	 * Send email message of given type (activate, forgot_password, etc.)
	 *
	 * @param	string
	 * @param	string
	 * @param	array
	 * @return	void
	 */
	function _send_email($type, $email, &$data)
	{
		$this->load->library('email');
		$this->email->from($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
		$this->email->reply_to($this->config->item('webmaster_email', 'tank_auth'), $this->config->item('website_name', 'tank_auth'));
		$this->email->to($email);
		$this->email->subject(sprintf($this->lang->line('auth_subject_'.$type), $this->config->item('website_name', 'tank_auth')));
		$this->email->message($this->load->view('email/'.$type.'-html', $data, TRUE));
		$this->email->set_alt_message($this->load->view('email/'.$type.'-txt', $data, TRUE));
		$this->email->send();
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
}
