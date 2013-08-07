<?php
class MY_Controller extends CI_Controller{
	function __construct(){
		parent::__construct();	
	
	}
	
	
}
class AuthController extends MY_Controller
{
	public $data;
	function _output($content)
	{
		// Load the base template with output content available as $content
		$data['content'] = &$content;
		echo($this->load->view('base', $data, true));
	}
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('security');
		$this->load->library('tank_auth');
		$this->lang->load('tank_auth');
		
		$this->config->load('proof', TRUE);
				
		if(! $this -> tank_auth->is_logged_in()){
			redirect(site_url('auth/login'));		
		
		}


		if($this->tank_auth->get_username() == $this->config->item('admin_username', 'proof')){
			$this->data['admin'] = true;
			
		}else{
			$this->data['admin'] = false;
			
		}
		$this->output->set_header('Content-Type: text/html; charset=utf-8');
	}


}

class AjaxController extends MY_Controller{
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('security');
		$this->load->library('tank_auth');
		$this->lang->load('tank_auth');
		if(! $this -> tank_auth->is_logged_in()){
			redirect(site_url('auth/login'));
				
	
		}
	}
	
}