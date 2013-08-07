<?php
class first_time_setup extends CI_Controller{
	function __construct(){
		parent::__construct();
		$this->load->config('proof', TRUE);
	}
	function index(){
		$adminusername = $this->config->item('admin_username', 'proof');
		$adminpass = $this->config->item('admin_pass', 'proof');
		$adminemail = $this->config->item('admin_email', 'proof');
		$this->load->library('tank_auth');
		$this->load->helper('url');
		if(!is_null($this->tank_auth->create_user($adminusername, $adminemail, $adminpass, FALSE))){
			$this->output->append_output('Success, go to '.anchor('auth/login', 'login', 'login'.'.'));
		}else{
			$this->output->append_output('Something went wrong. Check your configuration.');
		}
	}
	
	
}