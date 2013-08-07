<link href="<?php echo site_url();?>/css/orders.css" rel="stylesheet"/>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/black-tie/jquery-ui.min.css" rel = "stylesheet"/>
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<h2 class = "title">Orders</h2>
<div id = "tabs">
	<ul>
		<li><a href="#wrap">Unfilled</a></li>
		<li><a href="#filled">Filled</a></li>
	</ul>
	
	<div id = "wrap">
	<?php
		if(count($orders) > 0):?>
		<table class = "orders money">
		<thead>
			<tr>
				<td></td>
				<td>Gallery</td>
				<td>User</td>
				<td>Time</td>
			</tr>
		</thead>
			<?php foreach($orders as $order):?>
				<tr class = "order" data-href = "<?php echo site_url().'admin/orders/'.$order['order_id']?>" >
					<td class = "samplepic">
						<img src="<?php echo $order['sample_image']['thumb']?>">
					</td>	
					<td class = "ord_title"> <?php echo $order['gallery_name']?></td>
					<td><?php echo $order['username'];?></td>
					<td><?php echo date('M j Y g:i A', strtotime($order['timestamp']));?></td>
				</tr>
			
			<?php endforeach;?>

		</table>
		<?php else:?>
		<div class = "message">
			No unfilled orders.
		</div>
		<?php endif;?>	
	</div>
	
	<div id = "filled">
		
			<?php
			if(count($filled_orders) > 0):?>
				<table class = "orders money">
				<thead>
					<tr>
						<td></td>
						<td>Gallery</td>
						<td>User</td>
						<td>Time</td>
					</tr>
				</thead>
				<?php foreach($filled_orders as $order):?>
					<tr class = "order" data-href = "<?php echo site_url().'admin/orders/'.$order['order_id']?>" >
						<td class = "samplepic">
							<img src="<?php echo $order['sample_image']['thumb']?>">
						</td>	
						<td class = "ord_title"> <?php echo $order['gallery_name']?></td>
						<td><?php echo $order['username'];?></td>
						<td><?php echo date('M j Y g:i A', strtotime($order['timestamp']));?></td>
					</tr>
				
				<?php endforeach;?>
		</table>
		<?php else:?>
		<div class = "message">
			No orders have been filled.
		</div>
		<?php endif;?>	
	</div>
</div>
<script type="application/javascript">
$(document).ready(function(){
	$('.order').click(function(){
		window.location = $(this).data('href');
	});
	$('#tabs').tabs();
	
});

</script>