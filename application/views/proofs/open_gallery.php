<link href="<?php echo site_url();?>css/open_gallery.css" rel="stylesheet"/>
<script src="<?php echo site_url();?>scripts/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo site_url();?>scripts/jquery.scrollTo-1.4.3.1-min.js" type="text/javascript"></script>
<script src="<?php echo site_url();?>scripts/jquery.localscroll-1.2.7-min.js" type="text/javascript"></script>
<script src="<?php echo site_url();?>scripts/jquery.serialScroll-1.2.2.js" type="text/javascript"></script>
<div class = "outer">
<div class = "wrap">
	<div class = "centerme">
	<div class = "fullsize">
		<img>
		<div class = "ajaxloader">
			<img src = "<?php echo site_url()?>img/proof_nav/ajax-loader.gif">
		</div>
	</div>
	</div>
</div>
<div class = "images">
	
	<div class = "prev"><img src = "<?php echo site_url()?>img/proof_nav/prev.png" alt=""></div>
	
	<div class = "scrollable">
		<div class = "scroller">
			<div class = "panel">
			<?php 
			$i = 0;
			
			foreach($images as $img):
			
				if($i % 7 == 0 && ($i != 0)):?>
				</div><div class = "panel">
				<?php endif;?>
			
				<div class = "imagebox">
					<div class = "thumb"> 
					<a href="<?php echo $img['url']?>"><img src = "<?php echo $img['thumb']?>"></a>
					</div>
					
				</div>
			<?php 
			$i += 1;
			endforeach;?>
			</div>
		</div>
	</div>
	
	<div class = "next"><img src = "<?php echo site_url()?>img/proof_nav/next.png" alt=""></div>
	</div>
</div>


<script type="application/javascript">

			
$(document).ready(function(){
	var load_image = function(){
		var url = $(this).attr('href');
		var $img = $('.fullsize > img');
		$.when( $('.ajaxloader').fadeTo(250,1),
				$img.fadeTo(250,0)).done(function(){
					$img.remove();
					var html = $(document.createElement('img')).addClass('hidden displaynone').attr('id', 'new').attr('src', url).css('opacity', 0);
					$('.fullsize').append(html);
	
					$('#new').load(function(){
						var height = $('.fullsize').height();			
						var width = $('.fullsize').width();
						$full = $('.fullsize');
						$full.css('height', 'auto').css('width', 'auto');
								
						$newimg = $('#new');
						$newimg.removeClass('displaynone');
						
						var autoheight = $full.height();
						var autowidth = $full.width();
						$newimg.addClass('displaynone');
						$full.height(height).css('width', width);
						
						$full.animate({
							height:autoheight,
							width:autowidth
						}, function(){
							$('.ajaxloader').fadeTo(300, 0);
							$('#new').removeAttr('id').removeClass('hidden').removeClass('displaynone').fadeTo(300, 1);
							console.log(callbacks);
							if(callbacks.length > 0){
								load_image.call(callbacks.splice(0,1));
							}else{
								ignore = false;
							}		
						});
						
						
					});
			
		});

	}
	
	var ignore = false;
	var callbacks = [];
	$('.thumb a').click(function(ev){
		ev.preventDefault();
		if(ignore === false){
			ignore = true;
			load_image.call(this);
		}else if(callbacks.length > 0){
			callbacks.splice(0,1,this);
		}else{
			callbacks.push(this);
		}
	});
	$('.thumb a')[0].click();
	//scroller
	var $container = $('.scrollable');
	var sc_offset = parseInt($container.css('paddingLeft')) * -1;
	var scrollOpts = {
		target:$('.scrollable'),
		items:$('.panel'),
		next:$('.next'),
		prev:$('.prev'),
		offset:sc_offset,
		duration:350,
		axis:'xy',
		easing:'swing'
	}
	$('.images').serialScroll(scrollOpts);

});

</script>

