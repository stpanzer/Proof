<link href="<?php echo site_url()?>css/users.css" rel="stylesheet"/>
<link href="<?php echo site_url()?>css/basic.css" rel="stylesheet"/>

<script src="<?php echo site_url()?>scripts/jquery-latest.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/jquery.scrollTo-1.4.3.1-min.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/jquery.localscroll-1.2.7-min.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/jquery.serialScroll-1.2.2-min.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/dropzone.js" type="text/javascript"></script>
<script src="<?php echo site_url()?>scripts/pure.js" type="text/javascript"></script>

<div style = 'display:none'>
	<div id = "template">
		<table>
			<thead>
				<tr class = "altcolor">
					<td> </td>
					<td> Username </td>
					<td> Email </td>
					<td> Gallery name </td>
				</tr>
			</thead>
			<tbody>
				<tr class = "user">
					<td class = "delete"><form><input type="checkbox"></form></td>
					<td class = "username"></td>
					<td class = "email"></td>
					<td class = "galname"></td>
					
				</tr>
			</tbody>		
		</table>
	</div>
</div>
<h1 class = "title">Users</h1><br>
<h2 class = "subtitle">Deleting a user will clear any orders and active requests.</h2>

<div id = "userlist">
	<div class = "wrap" style="display:none">
		<div id = "target"><table></table></div>
		<div class = "button"><a id = "deleteselected"><span class = "emptyspan"></span>Delete selected</a></div>
	</div>
	<img src = "<?php echo site_url()?>img/proof_nav/ajax-loader.gif" id = "ajaxindicator">
	
</div>

<a class="prev">Previous</a><a class = "next">Next</a>

<script type="text/javascript">
function userList(){
	this.compiledTemplate = $('#template').compile({
		'tr.user' : {
			'user<-users':{
				'td.username':'user.username',
				'td.email':'user.email',
				'@data-id':'user.id',
				'td.galname':'user.gallery_name'		
			}					
		},
		'.@id':function(a){
			return 'users';
		}		
	});
	
			
}

userList.prototype.set_container_width = function(){
	var width = 0;
	$.each($('.panel'), function(){
		width += this.offsetWidth;
	});

	//addback one extra
	this.$container.css('width', width + $('.panel')[0].offsetWidth);
	
};
userList.prototype.updateOffset = function(next){
	if(next){
		//we're grabbing the next offset users.
		this.offset += 10;
	}else{
		//we're grabbing the previous users
		this.offset -= 10;
	}
	//hide the previous button if we are on the first page
	if(this.offset <= 0){
		$('.prev').hide();	
	}
	if(this.offset > 0){
		$('.prev').show();
	}
	this.update_user_table();
	
};
function bindscope(scope, fn) {
    return function () {
        fn.apply(scope, arguments);
    };
}
userList.prototype.update_user_table = function(){
	/*	Uses ajax get to update the usertable. Uses a function closure for the success callbacks so that the reference to
	*   this persists.
	*/
	$('#userlist').children().fadeTo(400, 0).promise().done(bindscope(this, function(){
		
		//remove current userlist
		$('#userlist #users').remove();
		$('#userlist .wrap').css('opacity', '0').prepend('<div id = "target"></div>');
		$('#ajaxindicator').show().fadeTo(400, 1);


		//Get offset number of users
		$.get('<?php echo site_url()?>ajaxreq/get_n_users',
			{'num' : '10', 'offset' : this.offset},
			bindscope(this, function(data, textStatus, jqXHR)
			{	
				if(!data['error']){
					if(data['users'].length === 0){
						$('#target').html('No users found');
					}else{
						$('#target').render(data, this.compiledTemplate);
						$('#userlist .wrap').hide();
						$('#userlist tr:nth-child(even)').addClass('altcolor');
						if( data.message == 'no_more_users' ){
							$('.next').hide();
						}else if(data.message == 'more'){
							$('.next').show();
						}
						//hide ajax loading spinner
						$('#ajaxindicator').hide();
						
						
						//show contents
						$('#userlist .wrap').show().fadeTo(400, 1);
					}
				}else{
					$('#target').html('No users found');
					$('#ajaxindicator').hide();
					$('.wrap > .button').hide();
					$('#userlist .wrap').show().fadeTo(400, 1);
					$('.next').hide();
				}
				
				
			}), "json"
		);	
	}));
	
	
};

function get_checked_users(){
	var checked_input = $('input:checked');
	var users = new Array();
	$.each(checked_input, function(index, val){
		users.push($(val).parents('.user').attr('data-id'));
	});
	return users;
	
}
userList.prototype.setup_buttons = function(){
	var delbutton = $('#deleteselected');
	delbutton.css('cursor', 'pointer');
	
	var usrlistref = this;
	
	delbutton.on('click', function(){
		var usrs_to_delete = get_checked_users();
		if(usrs_to_delete.length < 1){
			return;
		}
		$.post("<?php echo site_url()?>ajaxreq/delete_user", 
				{'usr_id':JSON.stringify(usrs_to_delete)}, 
				bindscope(this, function(){
					
					usrlistref.update_user_table();
				})
		);
	});

	$('.prev').click(function(){
		userlist.updateOffset(false);
	}).css('cursor','pointer').hide();

	$('.next').click(function(){
		userlist.updateOffset(true);
	}).css('cursor','pointer');
	
	
	

	
};



$(document).ready(function () {
	
	
	//init dropzone
	Dropzone.options.myDropzone = {
		url:'<?php echo site_url()?>ajaxreq/fileupload',
		maxFilesize: 35,
		paramName: 'image'
	};
	userlist = new userList();
	userlist.setup_buttons();
	userlist.update_user_table();
	
	
});

</script>