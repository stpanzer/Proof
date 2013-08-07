<?php
class MY_Form_validation extends CI_Form_validation{
	function __construct(){
		parent::__construct();
	}
	
	/**
	 *    Method is used to validate strings to allow alpha
	 *    numeric spaces underscores and dashes ONLY.
	 *    @param $str    String    The item to be validated.
	 *    @return BOOLEAN   True if passed validation false if otherwise.
	 */
	function alpha_dash_space($str_in = '')
	{
		if (! preg_match("/^([-a-z0-9_ ])+$/i", $str_in))
		{
			$this->set_message('alpha_dash_space', 'The %s field may only contain alpha-numeric characters, spaces, underscores, and dashes.');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
}