<link rel = "stylesheet" href = "<?php echo site_url()?>css/publish.css">
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/black-tie/jquery-ui.min.css" rel = "stylesheet"/>
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<h1 class="title"><?php echo $gallery['gallery_name'] ?> : Publishing options</h1>

<div class = "options">
		<div class = "galleryopts">
			<label for="name" class="subtitle">Gallery name:<br><input type="text" id = "name" size="50" value="<?php echo $gallery['gallery_name']?>"/></label>
			<img class = "ajax dispnone" src = "<?php echo site_url()?>img/proof_nav/ajax-loader.gif"><span class = "check dispnone">&#x2713;</span>
		</div>
		<div class = "togglewrap">
		<?php if($gallery['open']):?>
			<div class = "open button">
				Close Gallery
			</div>
		<?php else:?>
			<div class = "closed button">
				Open Gallery
			</div>
		<?php endif;?>
			
		</div>
		<div class = "delwrap">	
			<div class = "delete button">
				<span class = "emptyspan"></span>
				Delete gallery
			</div>
		</div>
		

</div>
<div id="dialog" title="Confirmation Required">
  Deleting this gallery will remove all users, orders, and images associated with it. Are you sure you want to delete '<?php echo $gallery['gallery_name'];?>'?
</div>
<script type="application/javascript">
$(document).ready(function(){
	$('#name').change(function(){
		if(!$('.check').hasClass('dispnone')){
			$('.check').addClass('dispnone');
		}
		$('.message').remove();
		$('.ajax').removeClass('dispnone');
		$.post("<?php echo site_url()?>ajaxreq/set_gallery_name",
				{'gal_id':<?php echo $gallery['gal_id'];?>,
				'gallery_name': $(this).val()},
				function(data, status, xhr){
					if(data.error){
						$('.galleryopts').append('<div class = "message">Gallery name may only contain alpha-numeric characters, spaces, underscores, and dashes.</div>');
					}else{
						$('.check').removeClass('dispnone');
					}
					$('.ajax').addClass('dispnone');

				}, "json");

	});
	
	$('.options').on('click', '.closed', function(ev){
		console.log(this);
		(function(inthis){
		$.post("<?php echo site_url()?>ajaxreq/toggle_gallery_open",
				{'gal_id':<?php echo $gallery['gal_id']?>},
				function(data, status, xhr){
					$(inthis).remove();
					$('.togglewrap').prepend('<div class = "open">Close Gallery</div>');
				});
		})(this);
	});

	$('.options').on('click', '.open', function(ev){
		console.log(this);
		(function(inthis){
		$.post("<?php echo site_url()?>ajaxreq/toggle_gallery_open",
				{'gal_id':<?php echo $gallery['gal_id']?>},
				function(data, status, xhr){
					$(inthis).remove();
					$('.togglewrap').prepend('<div class = "closed">Open Gallery</div>');
				});
		})(this);
	});
	$("#dialog").dialog({
	      autoOpen: false,
	      modal: true,
	      resizable: false,
	      draggable: false,
	      width: 450,
	      height: 250
	    });
	$('.delete').on('click', function(ev){
		buttons = {"Confirm" : function() {
	          window.location.href = "<?php echo site_url()?>admin/del_gallery/<?php echo $gallery['gal_id'];?>";
	        },
	        "Cancel" : function() {
	          $(this).dialog("close");
	        }
	      };
		$("#dialog").dialog({
		   	open: function(event, ui) { 
		    	//hide close button.
		    	$(this).parent().children().children('.ui-dialog-titlebar-close').hide();
		    }, buttons : buttons   });
	    $("#dialog").dialog("open");
	});
	

});

</script>
