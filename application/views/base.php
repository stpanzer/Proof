<!DOCTYPE html>
<html lang="en-US">

	<head>
		<link href="<?php echo site_url()?>/style.css" rel="stylesheet"/>
		<link href='http://fonts.googleapis.com/css?family=Dosis:500' rel='stylesheet' type='text/css'>
		<title><?php echo ($title ? $title : "") ?></title>
	</head>
	<body>
		
		<div id = "contentbox">
			<div id = "nav" class="clearfix">
			
			<?php if($this->data['admin']):?>					
					<div class = "button"><a href = "<?php echo site_url();?>admin/galleries/"><span class='emptyspan'></span>Galleries</a></div>
					<div class = "button"><a href = "<?php echo site_url();?>admin/users/"><span class='emptyspan'></span>Users</a></div>
					<div class = "button"><a href = "<?php echo site_url();?>admin/orders/"><span class='emptyspan'></span>Orders</a></div>
			<?php endif;?>
			<?php if($this->tank_auth->is_logged_in()):?>
					<div class = "button"><a href = "<?php echo site_url()?>auth/logout">Log out</a></div>
			<?php endif; ?>
		
			</div>
			<?php echo $content;?>
		</div>
	
	<div class="footer"> 
				
	</div>
		
</body>
</html>
		
	
