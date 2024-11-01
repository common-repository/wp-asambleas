jQuery(document).ready(function($){

	// drop csv impoerted
	$('body').on( 'click', '#delete_all_submission_attachments', function( e ){
		if( !confirm( wvp_local_data.confirm_drop ) ){
			e.preventDefault();
		}
	})

	// close block
	$('body').on( 'click', '.close_message_button', function( e ){
		var data = {
			template_id : $('#post_ID').val(),
			security : wvp_local_data.nonce,
			action : 'remove_noty_action'
		}
		jQuery.ajax({url: wvp_local_data.ajaxurl,
			type: 'POST',
			data: data,            
			beforeSend: function(msg){
				$('.close_message_button').parents('.alert').replaceWith('');
			},
			success: function(msg){

			}, 
			error:  function(msg) {
				console.log( msg );		
			} 
		})
	});


	// view report body functionality
	$('body').on( 'click', '.view_message', function( e ){
		e.preventDefault();
		
		var id = $(this).attr('data-id');
		$('#mail_'+id).toggle();
	})
	

function count_str(main_str, sub_str){
    main_str += '';
    sub_str += '';

    if (sub_str.length <= 0) 
    {
        return main_str.length + 1;
    }

       subStr = sub_str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
       return (main_str.match(new RegExp(subStr, 'gi')) || []).length;
}
// select all recipients Log
$('.show_user_log').click(function(e){
	e.preventDefault();
	var parent = $(this).parents('tr');
	$('.user_log_list', parent).toggle();

})
// select all recipients
$('.select_all_users').click(function(e){
	e.preventDefault();
	$('.content_bar .single_user_row:visible .user_pick_check').attr('checked', true);
	$('.user_pick_check').change();

})
// unselect all recipients
$('.unselect_all_users').click(function(e){
	e.preventDefault();
	$('.content_bar .single_user_row:visible .user_pick_check').attr('checked', false);
	$('.user_pick_check').change();

})
// copunt selected users
$('.user_pick_check').change(function(){

	var count = 0;
	$('.content_bar .single_user_row .user_pick_check').each(function(){
		if( $(this).is(':checked') ){
			count++;
		}
	})
	$('.user_count').html( count );
})

// filter users emails
$('.user_filter').keyup(function(){
	

	var this_value = $('.user_filter').val();

	console.log( this_value );
	$('.content_bar .single_user_row').each(function(){
		var this_name = $('.user_pick_check', this).attr('data-username');
		
		var string_filter = '/'+this_value+'/g';

		var count = count_str(this_name, this_value );

		if( count > 0 ){
			$(this).show();
		}else{
			$(this).hide();
		}
		
	})

	if( this_value == '' ){
		$('.content_bar .user_pick_check').show();
	}
})

//delete_all_results_fake
$('body').on( 'click', '.delete_all_results_fake', function( e ){
	e.preventDefault();
	$('.delete_all_results_fake').hide();
	$('.delete_all_orig').show();
})

// adding patch to users list
if( $('.users-php .subsubsub').length > 0 ){
	$('.users-php #wpbody .subsubsub').append(  $('#type_filter').html() );
	console.log( $('#type_filter').html() );
}

// adding patch to users catgories
if( $('.users-php .subsubsub').length > 0 ){
	$('.users-php #wpbody .subsubsub').append(  $('#category_filter').html() );
}

// apply date time picker
if( $('.datepicker').length > 0 ){
	$('.datepicker').datetimepicker({
		dateFormat: 'yy/mm/dd'
	});
}
if( $('.date_time_picker').length > 0 ){
	$('.date_time_picker').datetimepicker({
		dateFormat: 'yy/mm/dd'
	});
}
	
	
 // edition of user params
 $('.edit_value').click(function(){
	 var pnt = $(this).parents('.parent_cont');
	 $('.editor_block', pnt).fadeIn();
 })
 

 // Send Test Email 
 var is_finished_ajax_test = 0;
 var interval_link_test;
 $('body').on( 'click', '.send_test_emails', function( e ){
	 e.preventDefault();
 
	 jQuery('body').append('<div class="big_loader"><div id="inner_counter_block"></div></div>');
 
 
	 // verify email
	 send_test_emails( 1 );
	 interval_link_test = setInterval(function(){
		 if( is_finished_ajax_test == 1 ){
			send_test_emails( 0 );
		 }
		 
	 }, 2000);
	 
	  
 })

function send_test_emails( is_start = 0 ){

	var data = {
		template_id: $('#post_ID').val(),
		is_start : is_start,
		security : wvp_local_data.nonce,
		action : 'send_test_emails'
	}
	jQuery.ajax({url: wvp_local_data.ajaxurl,
		type: 'POST',
		data: data,            
		beforeSend: function(msg){
			is_finished_ajax_test = 0;
				
			},
			success: function(msg){
				
				//jQuery('.big_loader').replaceWith('');
				
				var obj = jQuery.parseJSON( msg );
				if( obj.result == 'success' ){
					$('#inner_counter_block').html(  obj.processed_users+wvp_local_data.ofstring+obj.total_users+wvp_local_data.messages_sent);
				} 
				is_finished_ajax_test = 1;

				if( obj.processed_users == obj.total_users ){
					clearInterval( interval_link_test );
					jQuery('.big_loader').replaceWith('');
					location.reload();
				}

			} , 
			error:  function(msg) {
				console.log( msg );		
			} 
	})
}


 


// Send Full  Email List
var is_finished_ajax = 0;
var interval_link;
$('body').on( 'click', '.send_full_emails', function( e ){
	e.preventDefault();

	jQuery('body').append('<div class="big_loader"><div id="inner_counter_block"></div></div>');

	var data_continue = $(this).attr('data-continue');

	// verify email
	if( data_continue != '1' ){
		send_full_emails( 1 );
	}
	
	interval_link = setInterval(function(){
		if( is_finished_ajax == 1 ){
			send_full_emails( 0 );
		}
		if( data_continue == '1' ){
			send_full_emails( 0 );
		}
		
	}, 2000);
	
	 
})

$('body').on( 'click', '.drop_email_queue', function( e ){
	e.preventDefault();
	
	var data = {
		template_id : $('#post_ID').val(),
		security : wvp_local_data.nonce,
		action : 'drop_email_queue'
	}

	jQuery.ajax({url: wvp_local_data.ajaxurl,
			type: 'POST',
			data: data,            
			beforeSend: function(msg){
				jQuery('body').append('<div class="big_loader"></div>');
			},
			success: function(msg){
					console.log( msg );
					jQuery('.big_loader').replaceWith('');
					
					var obj = jQuery.parseJSON( msg );
					if( obj.result == 'success' ){
						$('.no_finished_query').fadeOut();
					} 
					

			} , 
			error:  function(msg) {
				console.log( msg );		
			}          
	});
})

function send_full_emails( is_start = 0 ){

	console.log( is_start );

	var data = {
		template_id: $('#post_ID').val(),
		is_start : is_start,
		security : wvp_local_data.nonce,
		action : 'send_full_emails'
	}

	jQuery.ajax({url: wvp_local_data.ajaxurl,
			type: 'POST',
			data: data,            
			beforeSend: function(msg){
					is_finished_ajax = 0;
					
				},
				success: function(msg){
					console.log( msg );
					//jQuery('.big_loader').replaceWith('');
					
					var obj = jQuery.parseJSON( msg );
					if( obj.result == 'success' ){
						$('#inner_counter_block').html(  obj.processed_users+wvp_local_data.ofstring+obj.total_users+wvp_local_data.messages_sent);
					} 
					is_finished_ajax = 1;

					if( obj.processed_users == obj.total_users ){
						clearInterval( interval_link );
						jQuery('.big_loader').replaceWith('');
						location.reload();
					}

				} , 
				error:  function(msg) {
					console.log( msg );		
				}          
	});
}


 // save value
 $('body').on( 'click', '.save_value', function( e ){
  
	// verify email
	var parent = $(this).parents('.parent_cont');
	var data = {
		user_id  : $(this).attr('data-user_id'),
		field  : $(this).attr('data-field'),		
		value  : $('.edited_value', parent).val(),	
		security : wvp_local_data.nonce,
		action : 'update_user_data'
	}
	console.log( data );
	jQuery.ajax({url: wvp_local_data.ajaxurl,
			type: 'POST',
			data: data,            
			beforeSend: function(msg){
					jQuery('body').append('<div class="big_loader"></div>');
				},
				success: function(msg){
					console.log( msg );
					
					jQuery('.big_loader').replaceWith('');
					
					var obj = jQuery.parseJSON( msg );
				 
					if( obj.result == 'success' ){
						$('.editor_block', parent).fadeOut();
						$('.current_value', parent).html( obj.value );
					} 
					 
				} , 
				error:  function(msg) {
					console.log( msg );		
				}          
		});
	 
})


// check if user entered correect default emails
$('#submit_default_settings').click(function( e ){

	// verify emails
	var all_items = $('#wordpress_default_emails').val();
	all_items = all_items.trim();
 
	if( all_items && all_items != '' ){
		var all_items_arr = all_items.split(",");
		var no_error = 1;
		$.each(all_items_arr, function( index, value ){
			console.log( value );
			if( !validateEmail(value) ){
				no_error = 0;
			}
		})
	
		if( no_error == 0 ){
			e.preventDefault();
			alert( wvp_local_data.invalid_email);
		}
	}
	
	
})

// posll type pickers
if( $('#poll_type').length > 0 ){
	setInterval(function(){
		if( $('#poll_type').val() == 'multi' ){
			$('.min_variants').fadeIn();
			$('.max_variants').fadeIn();
		}
		if( $('#poll_type').val() == 'single' ){
			$('.min_variants').fadeOut();
			$('.max_variants').fadeOut();
		}
	}, 500)
	
}

// Remove poll date

$('body').on( 'click', '.remove_poll_date', function( e ){
  
	// verify email
	var parent = $(this).parents('tr');
	var data = {
		post_id  : $(this).attr('data-id'),
		index  : $(this).attr('data-index'),		
		security : wvp_local_data.nonce,
		action : 'remove_poll_date'
	}
	console.log( data );
	jQuery.ajax({url: wvp_local_data.ajaxurl,
			type: 'POST',
			data: data,            
			beforeSend: function(msg){
					jQuery('body').append('<div class="big_loader"></div>');
				},
				success: function(msg){
					
					
					console.log( msg );
					
					jQuery('.big_loader').replaceWith('');
					
					var obj = jQuery.parseJSON( msg );
				 
					if( obj.result == 'success' ){
						parent.replaceWith('');
					} 
					 
				} , 
				error:  function(msg) {
					console.log( msg );		
				}          
		});
	 
})
// process show all checkbox

$('body').on( 'change', '.show_polls_shortcode, .all_results_shortcode, .vote_is_open, .show_only_voters, .show_user_answers, .list_that_voted, .list_not_voted, .show_data_table, .show_polls_shortcode, .vote_open_popup', function( e ){
  
	// verify email

	var data = {
		cf  : $(this).attr('data-cf'),
		post_id  : $(this).attr('data-id'),
		value  : $(this).is(':checked'),
		security : wvp_local_data.nonce,
		action : 'check_output_type'
	}
	console.log( data );
	jQuery.ajax({url: wvp_local_data.ajaxurl,
			type: 'POST',
			data: data,            
			beforeSend: function(msg){
					jQuery('body').append('<div class="big_loader"></div>');
				},
				success: function(msg){
					
					
					console.log( msg );
					
					jQuery('.big_loader').replaceWith('');
					
					var obj = jQuery.parseJSON( msg );
				 
					if( obj.result == 'success' ){
		  
					} 
					 
				} , 
				error:  function(msg) {
					console.log( msg );		
				}          
		});
	 
})

// addmin poll show unvoted users
$('body').on('click', '.show_unvoted', function(){
	$(this).addClass('clicked');
	var parent = $(this).parents('.table');
	$('.d-none', parent).addClass('are_visible').removeClass('d-none');;
})
$('body').on('click', '.show_unvoted.clicked', function(){
	$(this).removeClass('clicked');
	var parent = $(this).parents('.table');
	$('.are_visible', parent).addClass('d-none').removeClass('are_visible');;
})


if( $('.selectizer').length > 0 ){
	$('.selectizer').selectize({
		sortField: 'text'
	});
}
console.log( wvp_local_data );
$('#assign_proxy').submit(function( e ){
	if( $('#_user_from').val() == $('#_user_to').val() ){
		e.preventDefault();
		alert( wvp_local_data.same_user );
	}
})

$('body').on("click", ".add_variant", function(){
	var row = $('.vote_table tbody tr:first-child').clone();
	$('input', row ).val('');
	$('.vote_table tbody').append( row );
})

$('body').on("click", ".save_result_block", function(){
	var parent = $(this).parents('td');


	var answers = $('.answer_variant', parent).val()
	console.log( answers );
	
	var url = $('.answer_variant', parent).attr('data-url');
	if( answers.length > 1 ){
		url = url+'&ans_variant='+answers.join(',');
	}else{
		url = url+'&ans_variant='+answers;
	}
	
	window.location.href = url;
})
$('body').on("click", ".variant_line_remove", function(){
	$(this).parents('tr').replaceWith('');
})


//show edit form
$('body').on( 'click', '.edit_user_vote', function( e ){
	e.preventDefault();
	var parent = $(this).parents('tr');
	$('.edit_container', parent).fadeToggle();
})

$('body').on( 'click', '.delete_user_vote1', function( e ){
  
		// verify email

		var data = {
			user_id  : $(this).attr('data-user'),
			poll_id  : $(this).attr('data-poll'),
			action : 'delete_user_vote'
		}
		jQuery.ajax({url: wvp_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
						
						
						console.log( msg );
						
						jQuery('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
						
						console.log( obj );
						console.log( obj.success );
						if( obj.result == 'success' ){
			 
							is_error_captcha = 1;
							e.preventDefault();
							alert('Please, check captcha!');
						}else{
							if( is_error_image == 0 ){
								$('#hidden_submit').click();
							}
						}
						 
					} , 
					error:  function(msg) {
									
					}          
			});
		if( is_error_image == 1 ){
			e.preventDefault();
			alert('Please, select image!');
		}
		if( is_error_captcha == 1 ){
			
		}
	})

	
	// Uploading files	var file_frame;	
	// Uploading files
	

	jQuery('body').on('click', '.upload_image', function( event ){
	
	var file_frame;
	
		var parent = $(this).parents('.media_upload_block');
		var if_single = $(this).attr('data-single');
	  
		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
		  file_frame.open();
		  return;
		}

		// Create the media frame.
		if( if_single == 1 ){
			file_frame = wp.media.frames.file_frame = wp.media({
			  title: jQuery( this ).data( 'uploader_title' ),
			  button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			  },
			  multiple: false  // Set to true to allow multiple files to be selected
			});
		}else{
			file_frame = wp.media.frames.file_frame = wp.media({
			  title: jQuery( this ).data( 'uploader_title' ),
			  button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			  },
			  multiple: true  // Set to true to allow multiple files to be selected
			});
		}

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			if( if_single == 1 ){
				// We set multiple to false so only get one image from the uploader
				attachment = file_frame.state().get('selection').first().toJSON();
				$('.item_id', parent).val( attachment.id );
				$('.image_preview', parent).html( '<img src="'+attachment.url+'" />' );
				// Do something with attachment.id and/or attachment.url here
			}else{
				var selection = file_frame.state().get('selection');	
				
				selection.map( function( attachment ) {						
					attachment = attachment.toJSON();					
					console.log( attachment.id );
					console.log( attachment.url );
					
					var this_val = [];
					if( $('.item_id', parent).val() != '' ){
						
						var this_tmp = $('.item_id', parent).val();						
						this_val = this_tmp.split(',');
					}
					this_val.push( attachment.id );
					$('.item_id', parent).val( this_val.join(',') );
			 
					$('.image_preview', parent).append( '<img src="'+attachment.url+'" />' );
				})
			}
		});

		// Finally, open the modal
		file_frame.open();
	  });
	
	
	function validateEmail(email) { 
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}

	// add category picker
	if( $('#poll_category_cont').length > 0 ){
		$('ul.subsubsub').append( $('#poll_category_cont').html() );
	}


	// insert mass editor block on top of page
	if( $('.post-type-poll .wrap').length > 0 ){
		//$('.post-type-poll .wrap').prepend( $('.footer_mass_editor_block').html() );
	}

	// mass checkbox processing
	$('body').on('change', '.mass_checkbox_processing', function(){
		var selector = $(this).attr('data-cf');
		var value = $(this).val();
		var data = {
			cf  : $(this).attr('data-cf'),
			value  : $(this).val(),
			security : wvp_local_data.nonce,
			action : 'mass_check_processing'
		}
		console.log( data );
		jQuery.ajax({url: wvp_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
						if( value == 'on' ){
							$('.'+selector ).prop('checked', true );
						}
						
						if( value == 'off' ){
							$('.'+selector ).prop('checked', false );
						}

						console.log( msg );
						
						jQuery('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
					 
						if( obj.result == 'success' ){
			  
						} 
						 
					} , 
					error:  function(msg) {
						console.log( msg );		
					}          
			});
	})

	// sortable table
	if( $('.sortable_table').length > 0 ){
        $('table.sortable_table').tablesort();
        $('table.sortable_table').on('tablesort:complete', function(event, tablesort) {
           
            $('table.sortable_table').each(function(){
                var cnt = 1;
                $('tr td:first-child', this).each(function(){
                    $(this).html( cnt );
                    cnt++;
                })
            })
            
        });
    }

	// is multi output p[atch
	$('#poll_type').change(function(){
		if( $(this).val() == 'single' ){
			$('.vote_table .is_multi').fadeOut();
		}
		if( $(this).val() == 'multi' ){
			$('.vote_table .is_multi').fadeIn();
		}
	})
	$('#poll_type').change();


	//backend preview email
	$('body').on('click', '.preview_user_email', function(){
 
		var data = {
			template_id  : $('#template_id').val(),
			current_page_user  : $('#current_page_user').val(),
 
			security : wvp_local_data.nonce,
			action : 'backend_email_preview'
		}
		console.log( data );
		jQuery.ajax({url: wvp_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
		 
						console.log( msg );
						
						jQuery('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
					 
						if( obj.result == 'success' ){
							$('#content_part').html( obj.preview );
							$('#email_status_block').fadeIn();
						} 
						 
					} , 
					error:  function(msg) {
						console.log( msg );		
					}          
			});
	})

	//backend send email
	$('body').on('click', '.send_user_email', function(){
 
		var data = {
			template_id  : $('#template_id').val(),
			current_page_user  : $('#current_page_user').val(),
 
			security : wvp_local_data.nonce,
			action : 'backend_email_send'
		}
		console.log( data );
		jQuery.ajax({url: wvp_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').append('<div class="big_loader"></div>');
					},
					success: function(msg){
		 
						console.log( msg );
						
						jQuery('.big_loader').replaceWith('');
						
						var obj = jQuery.parseJSON( msg );
					 
						if( obj.result == 'success' ){
							
							$('#email_status_block').fadeOut();
							$('.message_sent_block_cont').replaceWith();
							$('#email_status_block').after( obj.msg );
						} 
						 
					} , 
					error:  function(msg) {
						console.log( msg );		
					}          
			});
	})
	
	// handler Choose the method you want to measure user online lifetime:
    if( $('#user_online_lifetime_type').length > 0 ){
        setInterval(function(){
			process_quote_time_logging();           
        }, 500)
		$('#user_online_lifetime_type').change(function(){
			process_quote_time_logging();
		})

		function process_quote_time_logging(){
			if( $('#user_online_lifetime_type').val() == 'prev_online_minutes' ){
                $('.prev_online_minutes').removeClass('d-none').show();
                $('.from_timestamp').hide();
            }
            if( $('#user_online_lifetime_type').val() == 'from_timestamp' ){
                $('.prev_online_minutes').hide();
                $('.from_timestamp').removeClass('d-none').show();
            }
		}
    }


	/** handler show hide send emails now - schedule */
	if( $('#when_send_email').length > 0 ){
		setInterval(function(){
			var when_send_email = $('#when_send_email').val();
			if( when_send_email == 'now' ){
				$('.ajax_send_email_cont').removeClass('d-none').show();
				$('.schedule_send_email_cont').hide();
			}
			if( when_send_email == 'schedule' ){
				$('.ajax_send_email_cont').hide();
				$('.schedule_send_email_cont').removeClass('d-none').show();
			}
			if( when_send_email == 'draft' ){
				$('.ajax_send_email_cont').hide();
				$('.schedule_send_email_cont').hide();
			}
		}, 500)
	}
	
	/** handler show hide send emails now - schedule END */
});