<link href="<?php echo site_url();?>css/new_gallery.css" rel="stylesheet">
<link href="<?php echo site_url();?>css/basic.css" rel="stylesheet">
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo site_url();?>scripts/dropzone.js" type="text/javascript"></script>
<h1>Uploading to <?php echo $gallery_name;?></h1>
<form class ="dropzone" id="my-dropzone"></form>

<script type = "application/javascript">
	$(document).ready(function(){
		Dropzone.options.myDropzone = {
				url:'<?php echo site_url()?>ajaxreq/gal_fileupload',
				maxFilesize: 4,
				paramName: 'image',
				init: function(){
					var dz = this;
					dz.off('sending');
					dz.off('complete');
					dz.on('sending', function(file, xhr, formData){
						formData.append('galid', <?php echo $galid?>);
					});
				}
			};
		

	});

</script>
