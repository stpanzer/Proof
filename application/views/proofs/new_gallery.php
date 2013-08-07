<link href="<?php echo site_url();?>css/new_gallery.css" rel="stylesheet">
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>


<h1>New Gallery</h1>
<div class = "formbox">
	<?php echo form_open('admin/galleries/new');?>
	<div class = "clearfix">
		<div class ="formpart">
			<h3>Gallery Name</h3>
			<span class = "error"><?php echo form_error('galname');?></span>
			<input type="text" name="galname" value="<?php echo set_value('galname')?>" size="50"/>
		</div>
		<div class = "notes">
			The title of the gallery.
		</div>
	</div>
	<div class = "line clearfix">
		<div class ="formpart type">
			<h3>Gallery Type</h3>
			<span class = "error"><?php echo form_error('galtype');?></span>
			
			<input type="radio" name="galtype" value="proof" <?php echo set_radio('galtype', 'proof');?>/>Proof<br>
			<input type="radio" name="galtype" value="open" <?php echo set_radio('galtype', 'open');?>/>Open
		</div>
				
		<div class = "notes">
			Proof galleries are not public and are behind a username/password. Open galleries are public.
		</div>
	</div>
	
	<div class = "line clearfix">
		<div class = "formpart">
			<div class = "submit">
				<?php echo form_submit('submit', 'Submit');?>
			</div>
		</div>
	</div>
</div>
