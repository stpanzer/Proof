<link href="<?php echo site_url();?>/css/orders.css" rel="stylesheet"/>
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>

<h1 class="title">Order</h1>
<div id = "wrap">
	<table class = "requests money">
	<thead>
		<tr class="centertext">
			<td>Picture</td>
			<td>Size</td>
			<td class="quantity">Quantity</td>
			<td>Price</td>
			<td>Total</td>
		</tr>
	</thead>
	<?php
	$total = 0;
	$alt = false;
	foreach($order as $request):?>
		<?php if($alt):?>
			<tr class = "altcolor reqline">
		<?php else:?>
			<tr class = "reqline">
		<?php endif;?>
			<td class="samp_img lefttext" rowspan = <?php echo count($request['reqs'])?>>
				<a href="<?php echo $request['img_url']?>"><img src ="<?php echo $request['thumb']?>"></a>
				<div class = "origfile" title="<?php echo $request['original_filename'];?>">Filename: <?php echo $request['original_filename']?></div>
			</td>		
					<?php
					/*This is a bit complex, but its basically just to get the right coloring on the table. The rowspaned cell for the image messes everything up. 
					 * 
					 */				
					for($i = 0; $i < count($request['reqs']); $i++):
						$req = $request['reqs'][$i];
						$last = ($i == count($request['reqs'])-1);
						if($i != 0): 
							if($alt && $last):?>
								<tr class = "reqline last altcolor">
							<?php elseif(!$alt && !$last):?>
								<tr class = "reqline">
							<?php elseif(!$alt && $last):?>
								<tr class = "reqline last">						
							<?php elseif($alt && !$last):?>
								<tr class = "reqline altcolor">
							<?php endif;
						endif;?>	
						
						<td class="centertext">
							<?php echo $req['size_val'];?>
						</td>
						<td class="quantity"> 
							<?php echo $req['num_req'];?>					
						</td> 
						<td>
							$<?php echo number_format($req['price'], 2);
							$total += ($req['num_req']*$req['price']);?>
						</td>
						<td>
							$<?php echo number_format(($req['num_req']*$req['price']), 2);?>
						</td>
					</tr>
					<?php endfor;?>
				
			
		
		
	<?php 
	$alt = !$alt;
	endforeach;?>
	<tr><td></td><td></td><td></td><td>Approximate total:</td><td>$<?php echo number_format($total, 2);?></td></tr>
	</table>
	
	<div id = "fill" class="button">
	<?php if(!$filled):?>Fill order
	<?php else:?>Unfill order
	<?php endif;?>
	</div>
	
</div>
<script type="application/javascript">
	$(document).ready(function(){
		$('#fill').click(function(ev){
			$.post("<?php echo site_url()?>ajaxreq/toggle_order_filled",
					{"orderid":<?php echo $orderid;?>},
					function(data,status,xhr){
						window.location = "<?php echo site_url()?>admin/orders";
					});

		});

	});

</script>

