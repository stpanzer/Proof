<?php
/*Config information for the Proof application*/


/*
 * admin_username = the name of the user account to allow into the admin panel.
 * admin_pass = the password for the admin user. can be deleted after running the setup script (/first_time_setup)
 * image_folder = the full path to the place you want to store images.
 */
$config['admin_username'] = "";
$config['admin_pass'] = "";
$config['admin_email'] = "";
/*
*	Install folder requires trailing slash
*/
$config['install_folder'] = "";
$config['user_table'] = 'users';

/* Do not edit unless you know what you are doing*/

$config['image_folder'] = $config['install_folder'].'img';
$config['rel_image_folder'] = "img";
