<link href="<?php echo site_url()?>css/pricelist.css" rel="stylesheet"/>
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>
<div class = "settingswrap">

<h1 class="title">Print Types/Price List <?php if(!$default):?>for <?php echo $gallery_name;?><?php endif;?></h1>
<h2><?php if($default):?>Set the default sizes/print types here.<?php endif;?> Examples of request types include 8" x 10", 4" x 6", or 'Add to Slideshow,' etc...</h2>
<form>
<table class = 'sizes'>
<tr>
<td></td>
<td>Size Name</td>
<td>Price</td>
<td>No Input</td>
</tr>
<?php

foreach($print_sizes as $size){
	echo "<tr class = 'sizeline' data-size-id='".$size['size_id']."'><td><a class='removesize button'>Remove</a></td>";
	echo "<td><input type='text' class ='size_val' value='".$size['size_val']."'></td>";
	echo "<td><span class = 'currency'>$<input type= 'text' class='price' value='".$size['price']."'></span></td>";
	if($size['no_input']){
		echo "<td><input type='checkbox' class='no_input' checked=true></td></tr>";
	}else{
		echo "<td><input type='checkbox' class='no_input'></td></tr>";

	}
	
}
?>

</table>
</form>
<a class = 'button addsize'>New size</a>
</div>
<div id = "notes">
Select 'No Input' for requests such as 'Add to disk/slideshow' or 'Email me', for which the users have no need to specify a number they want.<br>
<?php if(!$default):?>
<br>Important: Altering print types or changing prices will reset any user's requests on this gallery.
<?php endif;?>
</div>
<script type="application/javascript">
function set_print_sizes(sizearray){
	<?php if($default): ?>
	$.post('<?php echo site_url();?>ajaxreq/set_print_sizes',
			{
				"print_sizes":sizearray,
				"default":true
			}, 
			function(data, status, xhr){
				$.each(data, function(index, val){
					/*Why index+2? 
					Well, nth-child is 1 indexed to begin with, and our table has a column name row. 
					So the first real line is index+2*/
					$('tr.sizeline:nth-child('+(index+2)+')').data('sizeId', val['size_id']);

						
				});
			}
	);
	<?php else:?>
	$.post('<?php echo site_url();?>ajaxreq/set_print_sizes',
			{
				"print_sizes":sizearray,
				"gal_id":<?php echo $galid?>
			}, 
			function(data, status, xhr){
				$.each(data, function(index, val){
					/*Why index+2? 
					Well, nth-child is 1 indexed to begin with, and our table has a column name row. 
					So the first real line is index+2*/
					$('tr.sizeline:nth-child('+(index+2)+')').data('sizeId', val['size_id']);

						
				});
			}
	);
	<?php endif;?>	
}
function sizes_from_dom(){

	var printsizes = {};
	$.each($('.size_val'), function(index, value){
		printsizes[index] = {};
		$value = $(value);
		printsizes[index]['value'] = $value.val();

		$parent = $value.parents('tr');
		//short circuit evaluation to make sure that all elements have both value and a size_id
		printsizes[index]['size_id'] = $parent.data('sizeId') || "";
		printsizes[index]['price'] = $parent.find('.price').val();
		printsizes[index]['no_input'] = $parent.find('.no_input').is(':checked');
	});
	return JSON.stringify(printsizes);
}
function update_print_sizes(){
	printsizes = sizes_from_dom();
	set_print_sizes(printsizes);
	
}
$(document).ready(function(){
	$('.sizes').on('click', '.removesize', function(){
		var $parenttr = $(this).parents('tr');
		var size_id = $parenttr.data('sizeId');
		
		$.post('<?php echo site_url();?>ajaxreq/clear_print_size',
				{
					"size_id":size_id,
					"default":<?php if($default):?>
						1,
					<?php else: ?>
						0,
					"gal_id":<?php echo $galid || 0?>,
					<?php endif;?>
				},
				function(data, status, xhr){
					$parenttr.remove();
				}
		);
		
	});
	$('.sizes').on('blur', 'input', function(){
		update_print_sizes();

	});
	$('.settingswrap').on('click', '.addsize', function(){
		$('.sizes tr:last').after(
				'<tr class="sizeline"><td><a class="removesize button">Remove</a></td><td><input class="size_val" type="text"></td><td><span class = "currency">$<input type="text" class="price"></span></td><td><input class ="no_input" type="checkbox"></td></tr>');
		update_print_sizes();		
	});
	if($('.sizeline').length < 1){
		$('.addsize').click();
	}

	
});
</script>