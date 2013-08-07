<link href = "<?php echo site_url()?>css/review.css" rel="stylesheet">
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/black-tie/jquery-ui.min.css" rel = "stylesheet"/>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<h1 class = "title">Print Requests</h1>
<div class = "wrap">

<table class="sizes money"><thead><tr><td></td><td>Print Type</td><td>Quantity</td><td>Unit Price</td><td>Total</td></tr></thead>
<?php
$pricetot = 0;


	foreach($imgs as $img):
		$fileurl = site_url().'img/'.$galid.'/'.$img['img_id'];
		$thumburl = site_url().'img/'.$galid.'/'.$img['thumb'];?>
		<tr class="<?php echo (count($img['reqs']) == 1 ? "last" : "");?> "><td class="imgtd" rowspan = "<?php echo count($img['reqs'])?>"><a href = "<?php echo $fileurl;?>"><img src="<?php echo $thumburl;?>"> </a></td>
		<?php for($i = 0; $i < count($img['reqs']); $i++): 
			if(($i == (count($img['reqs']) - 1)) && ($i != 0)):?>
				<tr class = "last">
			<?php elseif($i != 0):?>
				<tr>
			<?php endif;
			$thisreq = $img['reqs'][$i];?>		
			<?php if($thisreq['no_input']): ?>
				<td><?php echo $thisreq['size'];?></td><td></td>
				<td>$<?php echo $thisreq['price']?> </td><td> $<?php echo $thisreq['price'];?></td>
				<?php $pricetot += $thisreq['price'];?>
			<?php else:?>
				<td><?php echo $thisreq['size']?></td><td><?php echo $thisreq['num_req']?></td>
				<td>$<?php echo $thisreq['price']?> </td><td> $<?php echo number_format($thisreq['price']*$thisreq['num_req'], 2)?></td>
				<?php $pricetot += $thisreq['price'] * $thisreq['num_req'];?>
			<?php endif;?>
			</tr>
		<?php endfor;?>		

	<?php endforeach;?>
	<tr><td></td><td></td><td></td><td>Total :</td><td> $<?php  echo number_format($pricetot, 2);?></td></tr>
</table>
</div>

<div class = "buttons">
<a class="goback button" href = "<?php echo site_url()?>proofs/<?php echo $galid?>">Go back to edit</a>
<div class = "submitorder button">Submit</div>
</div>
<form id = "submitform" method="post" action="<?php echo site_url();?>proofs/submitreq">
	
</form>
<div id="dialog" title="Confirmation Required">
  Are you sure you wish to submit this order?<br><br>
  
</div>

<script type="application/javascript">
$(document).ready(function(){
	$("#dialog").dialog({
	      autoOpen: false,
	      modal: true,
	      resizable: false,
	      draggable: false,
	      width: 450,
	      height: 250
	    });
	
	$(".submitorder").click(function(e) {
	    e.preventDefault();
	    var targetUrl = $(this).attr("href");

	    $("#dialog").dialog({
	   	open: function(event, ui) { 
	    	//hide close button.
	    	$(this).parent().children().children('.ui-dialog-titlebar-close').hide();
	    }, buttons : {
	        "Confirm" : function() {
	        	var input = $("<input>").attr("type", "hidden").attr("name", "galid").val("<?php echo $galid;?>");
		    	var $form = $('#submitform');
		    	$form.append($(input));
		    	$form.submit();
				
	        },
	        "Cancel" : function() {
	          $(this).dialog("close");
	        }
	      }
	    });

	    $("#dialog").dialog("open");
	  });

	
});

</script>