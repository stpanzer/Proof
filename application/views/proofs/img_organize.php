<link rel= "stylesheet" href = "<?php echo site_url();?>scripts/jquery-ui-1.10.3/css/ui-lightness/jquery-ui-1.10.3.custom.min.css"/>
<link rel= "stylesheet" href = "<?php echo site_url();?>css/organize.css"/>
<script src="<?php echo site_url();?>scripts/jquery-latest.js"></script>
<script src="<?php echo site_url();?>scripts/jquery-ui-1.10.3/js/jquery-ui-1.10.3.custom.js"></script>
<h1 class = "title">Sorting <?php echo $gallery_name;?></h1>
<div class = "sortcontainer">
<ul id = "grid">
<?php

for($i = 0; $i < count($thumbs); $i++):?>
	<li class="ui-state-default" id="<?php echo $imgids[$i];?>">
		<div class = "imgcontainer">
			<a href="<?php echo $imgs[$i]?>;">
				<img src="<?php echo $thumbs[$i];?>" alt = "" class = "org_img">
			</a>
		</div>
	</li>
	
<?php endfor;?>
</ul>
</div>
<script type="application/javascript">
$(document).ready(function(){
	$("#grid").sortable({
			start: function(e, ui){
	            ui.placeholder.height(ui.item.height());
	        },
	        stop: function(e, ui){
				var order = $(this).sortable('toArray');
				$.post("<?php echo site_url();?>ajaxreq/reorder_gallery",
						{"new_order" : JSON.stringify(order),
						 "galid" : <?php echo $galid;?>}
				);
	        }
		});
	$("#grid").disableSelection();
});
</script>
