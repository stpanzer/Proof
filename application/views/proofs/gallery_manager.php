<link href="<?php echo site_url();?>css/gal_manage.css" rel="stylesheet">
<h1 class="title">Galleries</h1>
<div class="optionbar">
	<div class = "newgal button">
			<a href="<?php echo site_url();?>admin/galleries/new"><span class = "emptyspan"></span>New Gallery</a>		
	</div>
	<div class = "pricelist button">
			<a href="<?php echo site_url();?>admin/settings"><span class ="emptyspan"></span>Default price list</a>
	</div>
</div>
<div class = "clearfix"></div>
<?php
foreach($galleries as $gal):?>
<div class = "gallery">
	<div class = "header">
	<h2 class="subtitle"><?php echo $gal['gallery_name'];?></h2>
	</div>
	
	<div class = "buttons styledtext">
		<div class = "galbutton button">
			<a href= "<?php echo site_url()?>admin/upload/<?php echo $gal['gal_id'];?>"><span class = "emptyspan"></span>&uArr;<br>Upload</a>
		</div>
		<div class = "button galbutton">
			<a href= "<?php echo site_url()?>admin/organize/<?php echo $gal['gal_id'];?>"><span class = "emptyspan"></span>&#8634;<br>Organize</a>
		</div>
		<div class = "button galbutton">
			<a href = "<?php echo site_url()?><?php echo ($gal['gallery_type'] == "proof") ? 'proofs/' : 'open/';?><?php echo $gal['gal_id'];?>">
				<span class = "emptyspan"></span><img src="<?php echo site_url()?>img/proof_nav/eye.png" alt=""><br>View</a>				
		</div>
		<div class = "button galbutton">
			<a href = "<?php echo site_url()?>admin/publish/<?php echo $gal['gal_id']?>"><span class = "emptyspan"></span>&#128214;<br>Publish</a>
		</div>
		<?php if($gal['gallery_type'] == "proof"):?>
			<div class = "button galbutton">
				<a href = "<?php echo site_url()?>admin/add_user/<?php echo $gal['gal_id']?>"><span class = "emptyspan"></span>+<br>Add user</a>
			</div>
			<div class = "button galbutton">
				<a href= "<?php echo site_url()?>admin/galleries/<?php echo $gal['gal_id'];?>"><span class = "emptyspan"></span>&#9998;<br>Price list</a>
			</div>
		<?php endif;?>
	</div>
	<div class="clearfix"></div>
	
	<div class="images">
	<?php foreach($gal['sample_imgs'] as $img):?>
		<div class = "sample_image">
			<span class="table">
				<img src = "<?php echo $img['thumb']?>">
			</span>
		</div>
	<?php endforeach;?>
	</div>
	
</div>
	
<?php endforeach;?>

