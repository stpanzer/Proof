<link href="<?php echo site_url()?>css/proof_gallery.css" rel="stylesheet"/>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/black-tie/jquery-ui.min.css" rel = "stylesheet"/>
<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="<?php echo site_url()?>scripts/jquery.scrollTo-1.4.3.1-min.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/jquery.localscroll-1.2.7-min.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/jquery.serialScroll-1.2.2-min.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/pure.js" type="text/javascript"></script>

<div class = "title clearfix"><h1><?php echo $gallery_name?></h1></div><br>

<div class = "thumbnails">
<div class = "scrollable">
<div class = "scroller">
<div class = "panel">
<ul class = "grid">
<?php

for($i = 0; $i < count($imgs); $i++){
	if($i % 12 == 0 && $i != 0){
		echo '</ul></div><div class = "panel"> <ul class ="grid">';
	}
	echo '<li><a data-img-id="'.$imgs[$i]['id'].'" href="'.$imgs[$i]['url'].'"><img src="'.$imgs[$i]['thumb'].'" alt = ""></a></li>';
	
}?>
</ul>
</div>
</div>

</div>
<img src = "<?php echo site_url()?>img/proof_nav/prev.png" alt = "" class = "scrollButtons prev">
<img src = "<?php echo site_url()?>img/proof_nav/next.png" alt = "" class = "scrollButtons next">
<div class = "submitButton submit">
Review and submit requests
</div>
</div>
<div id = "imagepane">
<div class = "imgwrap">
<div id = "imagefull">
<img src = "<?php echo site_url()?>img/proof_nav/ajax-loader.gif" alt = "" id = "ajaxindicator" class = "hidden">

</div>
<div id = "infopane">

</div>
</div>
</div>

<div style = "display:none">
	<form class = "template">
	<table class="sizes">
		<thead><tr><td>Print type</td><td>Price per print</td><td>Quantity</td></tr></thead>
		<tr class = "tsizeline">
			<td><label class = "checkboxlabel"><input class="tsizebox" type="checkbox"></label></td>
			<td class = "tprice"></td>
			<td><label class = "textboxlabel"><input size="5" class = "tamount" type="number"> </label></td>
		</tr>
	</table>
	</form>
</div>
<div id="dialog" title="Confirmation Required">
  Are you sure you want to submit your print requests? <br><br>
  You will have an opportunity to review your order before submitting them.
</div>

<script type="application/javascript">
function empty_form(){
	var $form = $('.sizeform');
	$form.find('input[type=number]').val('');
	$form.find(':checked').prop('checked', false);
		
	
	
}
function submit_print_reqs(){
	$.post('<?php echo site_url();?>ajaxreq/submit_print_reqs', {"gallery_name":"proof", "usr_id":<?php echo $usrid ? $usrid : 0;?>}, function(data, status, xhr){
		console.log('submitted');
	});
	
}
function clear_print_req(imgid, sizeid){
	$.post('<?php echo site_url();?>ajaxreq/clear_print_req',
			{
				"img_id":imgid,
				"size_id":sizeid
			}, function (data, status, xhr){
				console.log('cleared'); 
			});
	
}

function set_print_req(imgid, sizeid, amount){
	//send a post request for this size/image
	$.post('<?php echo site_url();?>ajaxreq/set_print_req',
			{
				"img_id":imgid,
				"num_req":amount,
				"size_id":sizeid
			},function(data, status, xhr){
				console.log("whoa nelly");
			});	
}


function init_size_form(){
	//If the textbox is empty upon losing focus, uncheck the line's checkbox
	$('.amount').blur(function(){
		var $this = $(this);
		if($this.val() === ''){
			$this.parents('.sizeline').find(':checkbox').prop('checked', false);
			
		}
	});

	//check the line's checkbox if starting to edit the line's textbox
	$('.amount').on('click', function(){
		var $this = $(this);
		var $lineitem = $this.parents('.sizeline');
		if($lineitem.has(":checked").length == 0){
			var $checkbox = $lineitem.find(":checkbox");
			$checkbox.prop('checked', true);
		}

	});
	
	//handle an amount being input or removed. clears any previous request if the line's textbox is empty
	$('.amount').change(function(){
		//todo: validate changes
		var $this = $(this);
		var $lineitem = $this.parents('.sizeline');
		
		var amount = $this.val();
		var sizeid = $lineitem.attr('data-size-id');
		var imgid = $('#imagefull').attr('data-img-id');
		if(! (amount === '') ){
			set_print_req(imgid, sizeid, amount);
		}else{
			clear_print_req(imgid, sizeid);
		}
		
	});


	//handle a checkbox click
	$('.sizebox').change(function(){
		$this = $(this);
		var $lineitem = $this.parents('.sizeline');
		var inputbox = $lineitem.find('.amount')
		var sizeid = $this.parents('.sizeline').attr('data-size-id');
		var imgid = $('#imagefull').attr('data-img-id');

		if(!($this.prop("checked"))){
			//Box unchecked, clear the request and empty the amount box
			inputbox.val("");
			
			clear_print_req(imgid, sizeid);
		}else if ($lineitem.data('no-input') == 1){
			set_print_req(imgid, sizeid, 0);
		}

	});
	
}

function disable_form(){
	var $form = $('.sizeform');
	$form.find('input').attr("disabled", "disabled");
	$form.css('opacity', .4);
	$('.submit').off('click').css('opacity', .4);
}

$(document).ready(function (){
	var admin = <?php echo $admin ? 1 : 0?>;
	var userid = <?php echo $usrid ? $usrid : 0?>;
	//This sets up the thumbs to load in a new image ajaxy, using jQuery's nifty deffered object/promise system
	$('.grid li a').on('click', function(ev){
		ev.preventDefault();
		
		var blownup = $('#imagefull');
		
		
		var url = $(this).attr('href');
		if(url == $('#full').attr('src')){
			return;
		}
		empty_form();
		var imgid = $(this).attr('data-img-id');
		var $ajaxindicator = $('#ajaxindicator').removeClass('hidden');
		
		//remove old image
		blownup.children('a').remove();
		//create the image
		var $img = $(document.createElement('img')).addClass('hidden').attr('src', url+'?'+ new Date().getTime()).attr('id', 'full');
		var $link = $(document.createElement('a')).attr('href', url);
		blownup.append($img);
		$img.wrap($link);
		
		//set the data-img-id so we know which picture is currently blownup
		blownup.attr('data-img-id', imgid);
		$.post('<?php echo site_url();?>ajaxreq/get_print_reqs',
				{
					"img_id":imgid
				}, function(data, status, xhr){
					if(typeof(data['error']) == 'undefined'){
						$.each(data['reqs'], function(index, val){
							var $lineitem = $('.sizeline[data-size-id='+val['size_id']+']');
							var $amntinput = $lineitem.find('.amount');
							var $checkbox = $lineitem.find('.sizebox');
							$amntinput.val(val['num_req']);
							$checkbox.prop('checked', true);
						});
					}
					
				});
		
		$img.on('load', function(){
			$ajaxindicator.addClass('hidden');
			blownup.animate({
				height:$img.height(),
				width:$img.width()}, 'fast');
			$img.removeClass('hidden');
		})
		
	});
	
	/*
	* Get the sizes from the server via ajax (may change this later)
	*
	* Changes the classes because I don't want to select the template when working with the form this generates
	*/
	var comptemplate = $('.template').compile({
		'.tsizeline':{
			'size<-sizes':{
				'.tsizebox@value':'size.size_val',
				'.checkboxlabel+':'size.size_val',
				'.@data-size-id':'size.size_id',
				'.tsizebox@class':function(a){ return 'sizebox' },
				'.tamount@class':function(a){ 
					var item = a.item;
					
					if(item.no_input === "1"){
						return "hidden amount";
					}else{
						return "amount";
					}
				},
				'.@class':function(a){ return 'sizeline' },
				'.@data-no-input':function(a){
					return a.item.no_input;

				},
				'.tprice':'size.price',
				'.tprice@class':function(a){return 'price'}
			}
		},
		'.@class':function(a){
			return 'sizeform money';
		}
	});
	
	$.post('<?php echo site_url();?>ajaxreq/print_sizes', {"gallery_name":"proof", "gal_id":<?php echo $gal_id;?>}, function(data, status, xhr){
		var $infopane = $('#infopane');
		if(!$.isEmptyObject(data['sizes'])){
			
			$infopane.render(data, comptemplate);
			$infopane.show();
			init_size_form();
			if((admin) || !userid){
				disable_form();
			}
			//Load the first image
			$('.grid li a').first().click();
		}else{
			$infopane.html("<h1>No print types have been set</h1>");
			$infopane.show();
		}
	});

	$("#dialog").dialog({
	      autoOpen: false,
	      modal: true,
	      resizable: false,
	      draggable: false,
	      width: 450,
	      height: 250
	    });
	
	$(".submit").click(function(e) {
	    e.preventDefault();
	    var targetUrl = $(this).attr("href");
		$.post("<?php echo site_url()?>ajaxreq/user_has_reqs",
				{"usr_id":"<?php echo $usrid?>"},
				function(data, status, xhr){
					var tbuttons;
					if(data['has_req'] == true){
						$('#dialog').html("  Are you sure you want to submit your print/cd requests? <br><br> You will have an opportunity to review your order before submitting them.");
						tbuttons = {
						        "Confirm" : function() {
							          window.location.href = "<?php echo site_url()?>proofs/review/<?php echo $usrid?>";
							        },
							        "Cancel" : function() {
							          $(this).dialog("close");
							        }
							      };
					}else{
						$('#dialog').html("No print requests found.");
						tbuttons = { "Close" : function() {
							$(this).dialog("close");
						} };
					}
					$("#dialog").dialog({
					   	open: function(event, ui) { 
					    	//hide close button.
					    	$(this).parent().children().children('.ui-dialog-titlebar-close').hide();
					    }, buttons : tbuttons   });
				    $("#dialog").dialog("open");
					    
		}, "json");
	    

	  });
	/*
	* Setup the gallery scroll
	*/
	var $container = $('.scrollable');
	var sc_offset = parseInt($container.css('paddingLeft')) * -1;
	var scrollOpts = {
		target:$('.scrollable'),
		items:$('.panel'),
		next:$('.next'),
		prev:$('.prev'),
		offset:sc_offset,
		duration:500,
		axis:'xy',
		easing:'swing'
	}
	$('.thumbnails').serialScroll(scrollOpts);
	
});
</script>
