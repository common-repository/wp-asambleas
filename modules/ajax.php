<?php 
// verify show polls ajax checker
add_action('wp_ajax_verify_shop_vote_popup', 'wvp_verify_shop_vote_popup');
add_action('wp_ajax_nopriv_verify_shop_vote_popup', 'wvp_verify_shop_vote_popup');
function wvp_verify_shop_vote_popup(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
 

 

		echo json_encode( array( 'result' => 'success', 'status' => get_option('open_polls_popup' ) ) );
  
	}
	die();
}


// process checkboxes
add_action('wp_ajax_remove_noty_action', 'wvp_remove_noty_action');
add_action('wp_ajax_nopriv_remove_noty_action', 'wvp_remove_noty_action');
 
function wvp_remove_noty_action(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		$template_id = (int)$_POST['template_id'];
 
		update_post_meta( $template_id, 'hide_send_message_'.$template_id, '1');
		echo json_encode( array( 'result' => 'success' ) );
  
	}
	die();
}


// process checkboxes
add_action('wp_ajax_check_output_type', 'wvp_check_output_type');
add_action('wp_ajax_nopriv_check_output_type', 'wvp_check_output_type');
 
function wvp_check_output_type(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
	 
		if( $_POST['value'] == 'true' ){
			update_post_meta( $_POST['post_id'], $_POST['cf'], 'on' );

			

		}else{
			update_post_meta( $_POST['post_id'], $_POST['cf'], 'off' );

						
			// save voting timestamps
			//$user_closed_times = (array)get_post_meta( $_POST['post_id'], 'vote_close_time', true );
			if( $_POST['cf'] == 'vote_is_open' ){
				$user_closed_times = array( 'time' => current_time('timestamp'), 'shares' => wvp_get_total_quorum() );
				update_post_meta( $_POST['post_id'], 'vote_close_time', $user_closed_times );

				/*
				$fp = fopen( dirname(__FILE__).'/trace1.txt', 'w');
				fwrite($fp, var_export( $user_closed_times, true ));				
				fwrite($fp, var_export( wvp_get_total_quorum(), true ));				
				fclose($fp);
				*/

			}
		}
		echo json_encode( array( 'result' => 'success' ) );
  
	}
	die();
}

// update user data
add_action('wp_ajax_update_user_data', 'wvp_update_user_data');
 
 
function wvp_update_user_data(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
	 

		$user_id = (int)$_POST['user_id'];
		$field = sanitize_text_field( $_POST['field'] );
		$value = str_replace(',', '.', $_POST['value'] );
		$value = (float)$value;
	
		update_user_meta( $user_id, $field, $value   );
	 
		echo json_encode( array( 'result' => 'success', 'value' => $value ) );
  
	}
	die();
}


// remove poll date
add_action('wp_ajax_remove_poll_date', 'wvp_remove_poll_date');
add_action('wp_ajax_nopriv_remove_poll_date', 'wvp_remove_poll_date');
 
function wvp_remove_poll_date(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		$user_closed_times = (array)get_post_meta( $_POST['post_id'], 'vote_close_time', true );

		$new_array = [];
		foreach( $user_closed_times as $k => $v ){
			if( $k == $_POST['index'] ){ continue; }
			$new_array[] = $v;
		}
	 
		update_post_meta( $_POST['post_id'], 'vote_close_time', $new_array );
		/*
		$fp = fopen( dirname(__FILE__).'/trace2.txt', 'w');
				fwrite($fp, var_export( $user_closed_times, true ));				
				fclose($fp);
		*/

		echo json_encode( array( 'result' => 'success' ) );
  
	}
	die();
}



// process ajaxed shortcodes
add_action('wp_ajax_process_shortcode_action', 'wvp_process_shortcode_action');
add_action('wp_ajax_nopriv_process_shortcode_action', 'wvp_process_shortcode_action');
 
function wvp_process_shortcode_action(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		$shortcode =  $_POST['shortcode'];
		$iframe_url =  $_POST['iframe_url'];
 
		if( $shortcode == '' ){
			echo json_encode( array( 'result' => 'success', 'html' => '<iframe src="'.$iframe_url.'" style="width:100%; height:400px;"></iframe>' ) );
		}else{
			echo json_encode( array( 'result' => 'success', 'html' => do_shortcode('['.$shortcode.']') ) );
		}
		
  
	}
	die();
}


// Send Test Emails Ajax
add_action('wp_ajax_send_test_emails', 'wvp_send_test_emails');
add_action('wp_ajax_nopriv_send_test_emails', 'wvp_send_test_emails');
 
function wvp_send_test_emails(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){

		$template_id = (int)$_POST['template_id'];

		//if initial start
		if( $_POST['is_start'] == '1' ){
			update_option('email_sending_report', [] );
			update_option('email_test_processed_items', 0 );
		}
		


		$processed_items = (int)get_option('email_test_processed_items');
		$email_sending_report = get_option('email_sending_report');
		$settings = get_option('wvp_email_options');

		/**
		 * patch rebuilt from single sending to multiple templates v2.0.60
		 * restructurize settings to use user meta
		 */
		$settings['users_amount'] = get_post_meta( $template_id, 'users_amount', true);
		$settings['from_name'] = get_post_meta( $template_id, 'from_name', true);
		$settings['from_email'] = get_post_meta( $template_id, 'from_email', true);
		$settings['email_subject'] = get_post_meta( $template_id, 'email_subject', true);
		$settings['recipients'] = get_post_meta( $template_id, 'recipients', true);
		$settings['tempalte_type'] = 'global'; //get_post_meta( $template_id, 'tempalte_type', true);
		$settings['global_template'] = get_post_meta( $template_id, 'global_template', true);
		$settings['custom_template'] = get_post_meta( $template_id, 'custom_template', true);
		$settings['send_mail_to_all'] = get_post_meta( $template_id, 'send_mail_to_all', true);
		 /** END */

		$email_amount_to_send = (int)$settings['users_amount'];
		if( $email_amount_to_send == 0 ){
			$email_amount_to_send = 20;
		}

		// init variables
		$from_name = $settings['from_name'];
		$from_email = $settings['from_email'];
		$email_subject = $settings['email_subject'];

		

		$recipients = explode( ',', $settings['emails_to_sent_test'] );
		$recipients = array_filter( $recipients );
		$recipients = array_map('trim', $recipients);

 

		$tempalte_type = $settings['tempalte_type'];
		$global_template = stripslashes( $settings['global_template'] );

		

		$custom_template = stripslashes( $settings['custom_template'] );


		// template to use
		if( $tempalte_type == 'global' ){
			$template_content = $global_template;
		}
		if( $tempalte_type == 'custom' ){
			$template_content = $custom_template;
		}

		// nl patch
		$template_content_bkp = $template_content;
		//$template_content = nl2br( $template_content );
		$template_content = $template_content;

		//$email_sending_report = [];

		//  getting users
		if( $recipients[0] == 'all'){
			global $wpdb;
			$user_list = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );						
		}else{
			$user_list = $recipients;
		}

		// full sending amount 
		$full_sending_amount = count($user_list);

		// sending process
		$users_count = count($user_list);


		// sening filter
		$portion_to_run = array_slice ( $user_list , $processed_items, $email_amount_to_send);
		$user_list = $portion_to_run;


		foreach( $user_list as $s_email ){
		 		 
			$template_content_inner = str_replace('{first_name}', '', $template_content);
			$template_content_inner = str_replace('{last_name}', '', $template_content_inner);
			$template_content_inner = str_replace('{password}', '', $template_content_inner);
			$template_content_inner = str_replace('{username}', '', $template_content_inner);

			$to = $s_email;
			$subject = $email_subject;
			$body = $template_content_inner;
			$headers = [];
			$headers[] =  'Content-Type: text/html;';
			$headers[] =  ' charset=UTF-8';
			$headers[] =  'From: '.$from_name.' <'.$from_email.'>';


			/**email log title patch */
			$all_emails_titles = get_option('email_sent_subjects');
			$all_emails_titles[md5( $subject )] = $subject;
			update_option('email_sent_subjects', $all_emails_titles);

			$body = $body.'<img src="'.get_option('home').'?email_user_id='.''.'&email_user_subject='.md5( $subject ).'" />';

			/**email log title patch END */
		

			$res = wp_mail( $to, $subject, $body, $headers );

			$email_sending_report[ $s_email ] = $res;

			$processed_items++;
		}

		// last email sending	
		//update_option('email_sending_report', $email_sending_report);	
		//update_option('email_test_processed_items', $processed_items);
		update_post_meta($template_id, 'total_emails', $full_sending_amount );
		update_post_meta($template_id, 'email_sending_report_'.$template_id, $email_sending_report );
		update_post_meta($template_id, 'email_full_processed_items_'.$template_id, $processed_items );

		
		//if( $_POST['is_start'] == '0' || $email_amount_to_send == $users_count ){

		$emails_amount_left = $full_sending_amount - $processed_items;

		if( /*  $_POST['is_start'] == '0' || */ $emails_amount_left <= 0  ){
			// big email log
			$current_log = get_option('big_email_log' );
			$current_log[] = array( 'date' => current_time('timestamp'), 'body' => $template_content_bkp, 'usercount' => $users_count, 'template_type' => $template_id, 'content' =>  $body, 'is_test' => true, 'send_report' => $email_sending_report );
			update_option('big_email_log', $current_log );


			update_post_meta( $template_id, 'hide_send_message_'.$template_id, '0');
			update_post_meta($template_id, 'sending_finished_at',  current_time('timestamp') );
		}
		

		echo json_encode( array( 'result' => 'success', 'total_users' => $users_count, 'processed_users' =>  $processed_items  ) );
	}
	die();
}

 

// drop email queue
add_action('wp_ajax_drop_email_queue', 'wvp_drop_email_queue');
add_action('wp_ajax_nopriv_drop_email_queue', 'wvp_drop_email_queue');
function wvp_drop_email_queue(){
	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		$template_id = (int)$_POST['template_id'];

		update_post_meta($template_id, 'total_emails', false );
		update_post_meta($template_id, 'email_sending_report_'.$template_id, [] );
		update_post_meta($template_id, 'email_full_processed_items_'.$template_id, 0 );
		update_post_meta($template_id, 'hide_send_message_'.$template_id, '1');
		update_post_meta($template_id, 'sending_finished_at',  false );
		echo json_encode( array( 'result' => 'success'   ) );
	}
	die();
}

// Send Full Emails Log
add_action('wp_ajax_send_full_emails', 'wvp_send_full_emails');
add_action('wp_ajax_nopriv_send_full_emails', 'wvp_send_full_emails');
 
function wvp_send_full_emails(){
	global $current_user, $wpdb;

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		
	 	$template_id = (int)$_POST['template_id'];

		//if initial start
		if( $_POST['is_start'] == '1' ){
			update_post_meta($template_id, 'total_emails', 0 );
			update_post_meta($template_id, 'email_sending_report_'.$template_id, [] );
			update_post_meta($template_id, 'email_full_processed_items_'.$template_id, 0 );
			update_post_meta($template_id, 'sending_finished_at',  false );
		}
		


		$processed_items = (int)get_post_meta($template_id, 'email_full_processed_items_'.$template_id, true);
		$email_sending_report = get_post_meta($template_id, 'email_sending_report_'.$template_id, true);
		$settings = get_option('wvp_email_options');
		/**
		 * patch rebuilt from single sending to multiple templates v2.0.60
		 * restructurize settings to use user meta
		 */
		$settings['users_amount'] = get_post_meta( $template_id, 'users_amount', true);
		$settings['from_name'] = get_post_meta( $template_id, 'from_name', true);
		$settings['from_email'] = get_post_meta( $template_id, 'from_email', true);
		$settings['email_subject'] = get_post_meta( $template_id, 'email_subject', true);
		$settings['recipients'] = get_post_meta( $template_id, 'recipients', true);
		$settings['tempalte_type'] = 'global'; //get_post_meta( $template_id, 'tempalte_type', true);
		$settings['global_template'] = get_post_meta( $template_id, 'global_template', true);
		$settings['custom_template'] = get_post_meta( $template_id, 'custom_template', true);
		$settings['send_mail_to_all'] = get_post_meta( $template_id, 'send_mail_to_all', true);


		 /** END */

		$email_amount_to_send = (int)$settings['users_amount'];
		if( $email_amount_to_send == 0 ){
			$email_amount_to_send = 20;
		}

		// init variables
		$from_name = $settings['from_name'];
		$from_email = $settings['from_email'];
		$email_subject = $settings['email_subject'];

		$recipients = $settings['recipients'];
		$tempalte_type = $settings['tempalte_type'];
		$global_template = stripslashes( $settings['global_template'] );

		

		$custom_template = stripslashes( $settings['custom_template'] );


		// template to use
		if( $tempalte_type == 'global' ){
			$template_content = $global_template;
		}
		if( $tempalte_type == 'custom' ){
			$template_content = $custom_template;
		}

		// nl patch
		$template_content_bkp = $template_content;
		//$template_content = nl2br( $template_content );
		$template_content = $template_content;

		//$email_sending_report = [];

		//  getting users
		if( $settings['send_mail_to_all'] == 'all'){	
			$user_list = $wpdb->get_col("SELECT ID   FROM {$wpdb->users} " ); /* WHERE meta_key = 'can_vote' AND meta_value = '1' */ 
		}elseif( $settings['send_mail_to_all'] == 'selected' ){
			$user_list = $recipients;
		}elseif( $settings['send_mail_to_all'] == 'can_vote' ){
			$user_list = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1'" ); /* WHERE meta_key = 'can_vote' AND meta_value = '1' */ 
		}elseif( $settings['send_mail_to_all'] == 'cant_vote' ){
			$user_list = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '0'" );
		}
	 
		/** Patch 2.0.18 */
		if( substr_count( $settings['send_mail_to_all'], 'user_cat_') > 0 ){
			$terms_id_array = explode( '_', $settings['send_mail_to_all'] );
			$terms_id2use = $terms_id_array[2];
			$user_list = $wpdb->get_col( $wpdb->prepare( "SELECT user_id   FROM {$wpdb->usermeta} WHERE ( ( meta_key = 'can_vote' AND meta_value = '0' ) OR ( meta_key = 'can_vote' AND meta_value = '1' ) ) AND user_id IN (  SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_user_category' AND meta_value = '%d' )", $terms_id2use ) );
			 
		}
		/** Patch 2.0.18 END */

		// full sending amount 
		$full_sending_amount = count($user_list);

		// sending process
		$users_count = count($user_list);


		// sening filter
		$portion_to_run = array_slice ( $user_list , $processed_items, $email_amount_to_send);
		$user_list = $portion_to_run;


		foreach( $user_list as $s_id ){
		 
			$template_content_inner = wvp_get_user_email_content( $s_id, $template_content );
			
			$userdata = get_user_by( 'ID', $s_id );

			$to = $userdata->user_email;
			$subject = $email_subject;
			$body = $template_content_inner;
			$headers = [];
			$headers[] =  'Content-Type: text/html;';
			$headers[] =  ' charset=UTF-8';
			$headers[] =  'From: '.$from_name.' <'.$from_email.'>';


			
			$extra_emails = get_user_meta( $s_id, 'additional_emails', true);
			$extra_emails_bkp = $extra_emails;

			$extra_emails = explode(',', $extra_emails);
			$extra_emails = array_filter( $extra_emails );
			$extra_emails = array_map('trim', $extra_emails);
			foreach( $extra_emails as $s_email ){
				$headers[] = 'Cc: '.$s_email;
				$users_count++;
			}

			/**email log title patch */
			$all_emails_titles = get_option('email_sent_subjects');
			$all_emails_titles[md5( $subject )] = $subject;
			update_option('email_sent_subjects', $all_emails_titles);

			$body = $body.'<img src="'.get_option('home').'?email_user_id='.$s_id.'&email_user_subject='.md5( $subject ).'" />';

			/**email log title patch END */
			

			$res = wp_mail( $to, $subject, $body, $headers );

			/*
			$fp = fopen( dirname(__FILE__).'/data_ajax_new.txt', 'a');
			//fwrite($fp,  var_export( $body, true ) );		
			fwrite($fp,  var_export( $to, true ) );		
			fwrite($fp,  var_export( $subject, true ) );		
			fclose($fp);
			*/

			$email_sending_report[$s_id] = $res;

			$processed_items++;
		}

		// last email sending
		update_post_meta($template_id, 'total_emails', $full_sending_amount );
		update_post_meta($template_id, 'email_sending_report_'.$template_id, $email_sending_report);		
		update_post_meta($template_id, 'email_full_processed_items_'.$template_id, $processed_items);
 
		$emails_amount_left = $full_sending_amount - $processed_items;
 

		if( /*  $_POST['is_start'] == '0' || */ $emails_amount_left <= 0 ){
			// big email log
			$current_log = get_option('big_email_log' );
			$current_log[] = array( 'date' => current_time('timestamp'), 'body' => $template_content_bkp, 'usercount' => $users_count, 'template_type' => $template_id, 'content' =>  $body, 'is_test' => false, 'send_report' => $email_sending_report );
			update_option('big_email_log', $current_log );

			update_post_meta($template_id, 'hide_send_message_'.$template_id, '0');
			update_post_meta($template_id, 'sending_finished_at',  current_time('timestamp') );
 
		}
 
		echo json_encode( array( 'result' => 'success', 'total_users' => $users_count, 'processed_users' =>  $processed_items  ) );
  
	}
	die();
}

// mass checkboxes processing
// Send Full Emails Log
add_action('wp_ajax_mass_check_processing', 'wvp_mass_check_processing');
add_action('wp_ajax_nopriv_mass_check_processing', 'wvp_mass_check_processing');
function wvp_mass_check_processing(){

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		$all_polls = get_posts([
			'post_type' => 'poll',
			'showposts' =>  -1,
			'post_status' => 'any'
		]);
		foreach( $all_polls as $s_poll ){
			if( $_POST['value'] == 'on' ){
				update_post_meta( $s_poll->ID, $_POST['cf'], 'on' );
	
				
	
			}
			if( $_POST['value'] == 'off' ){
				update_post_meta( $s_poll->ID, $_POST['cf'], 'off' );
				// save voting timestamps
				if( $_POST['cf'] == 'vote_is_open' ){
					$user_closed_times = array( 'time' => current_time('timestamp'), 'shares' => wvp_get_total_quorum() );
					update_post_meta( $s_poll->ID, 'vote_close_time', $user_closed_times );
					/*
					$fp = fopen( dirname(__FILE__).'/trace3.txt', 'w');
					fwrite($fp, var_export( $user_closed_times, true ));				
					fclose($fp);
					*/
				}
			}
			
		}


		// show polls ot all users
		if( $_POST['cf'] == 'open_polls_popup' ){
			if( $_POST['value'] == 'on' ){
				update_option('open_polls_popup', 'on');
			}else{
				update_option('open_polls_popup', 'off');
			}
		}

		echo json_encode( array( 'result' => 'success' ) );
	}
	die();
}


// backend send user email
add_action('wp_ajax_backend_email_preview', 'wvp_backend_email_preview');
add_action('wp_ajax_nopriv_backend_email_preview', 'wvp_backend_email_preview');
function wvp_backend_email_preview(){

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		$template_id = (int)$_POST['template_id'];
	 
		$processed_items = (int)get_option('email_full_processed_items');
		$email_sending_report = get_option('email_sending_report');
		$settings = get_option('wvp_email_options');

		/**
		 * patch rebuilt from single sending to multiple templates v2.0.60
		 * restructurize settings to use user meta
		 */
		$settings['users_amount'] = get_post_meta( $template_id, 'users_amount', true);
		$settings['from_name'] = get_post_meta( $template_id, 'from_name', true);
		$settings['from_email'] = get_post_meta( $template_id, 'from_email', true);
		$settings['email_subject'] = get_post_meta( $template_id, 'email_subject', true);
		$settings['recipients'] = get_post_meta( $template_id, 'recipients', true);
		$settings['tempalte_type'] = 'global'; //get_post_meta( $template_id, 'tempalte_type', true);
		$settings['global_template'] = get_post_meta( $template_id, 'global_template', true);
		$settings['custom_template'] = get_post_meta( $template_id, 'custom_template', true);
		$settings['send_mail_to_all'] = get_post_meta( $template_id, 'send_mail_to_all', true);


		 /** END */

		// init variables
		$from_name = $settings['from_name'];
		$from_email = $settings['from_email'];
		$email_subject = $settings['email_subject'];
	 
		$tempalte_type = 'global';

		$global_template = stripslashes( $settings['global_template'] );
		$custom_template = stripslashes( $settings['custom_template'] );
 
		// template to use
		if( $tempalte_type == 'global' ){
			$template_content = $global_template;
		}
		if( $tempalte_type == 'custom' ){
			$template_content = $custom_template;
		}

		// nl patch
		$template_content_bkp = $template_content;
		//$template_content = nl2br( $template_content );
		$template_content = $template_content;

		$user_list = [];
		$user_list[] = (int)$_POST['current_page_user'];
		foreach( $user_list as $s_id ){		 
			$template_content_inner = wvp_get_user_email_content( $s_id, $template_content );
			$userdata = get_user_by( 'ID', $s_id );
			
		}
		

		$preview =  '<div class="tw-bs4"><div><b>'.__('Email subject:', 'wvp').'</b></div><div class="mb-4">'.$email_subject.'</div><div><b>'.__('Email body:', 'wvp').'</b></div><div class="mb-4">'.$template_content_inner.'</div>';
	 
		echo json_encode( array( 'result' => 'success', 'preview' => $preview ) );
	}
	die();
}


// backend send user email from user profile page
add_action('wp_ajax_backend_email_send', 'wvp_backend_email_send');
add_action('wp_ajax_nopriv_backend_email_send', 'wvp_backend_email_send');
function wvp_backend_email_send(){

	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
		//$template_type = sanitize_text_field( $_POST['template_type'] );
		$template_id = (int)$_POST['template_id'];

		//if initial start
		if( $_POST['is_start'] == '1' ){
			update_post_meta($template_id, 'email_sending_report_'.$template_id, [] );
			update_post_meta($template_id, 'email_full_processed_items_'.$template_id, 0 );
			//update_post_meta($template_id, 'sending_finished_at',  false );
		}
		


		$processed_items = (int)get_post_meta($template_id, 'email_full_processed_items_'.$template_id, true );
		$email_sending_report = get_post_meta($template_id, 'email_sending_report_'.$template_id, true);
		$settings = get_option('wvp_email_options');

		/**
		 * patch rebuilt from single sending to multiple templates v2.0.60
		 * restructurize settings to use user meta
		 */
		$settings['users_amount'] = get_post_meta( $template_id, 'users_amount', true);
		$settings['from_name'] = get_post_meta( $template_id, 'from_name', true);
		$settings['from_email'] = get_post_meta( $template_id, 'from_email', true);
		$settings['email_subject'] = get_post_meta( $template_id, 'email_subject', true);
		$settings['recipients'] = get_post_meta( $template_id, 'recipients', true);
		$settings['tempalte_type'] = 'global'; //get_post_meta( $template_id, 'tempalte_type', true);
		$settings['global_template'] = get_post_meta( $template_id, 'global_template', true);
		$settings['custom_template'] = get_post_meta( $template_id, 'custom_template', true);
		$settings['send_mail_to_all'] = get_post_meta( $template_id, 'send_mail_to_all', true);


		 /** END */


		$email_amount_to_send = (int)$settings['users_amount'];
		if( $email_amount_to_send == 0 ){
			$email_amount_to_send = 20;
		}

		// init variables
		$from_name = $settings['from_name'];
		$from_email = $settings['from_email'];
		$email_subject = $settings['email_subject'];

		$recipients = $settings['recipients'];
		$tempalte_type = sanitize_text_field( $_POST['template_type'] );
		$global_template = stripslashes( $settings['global_template'] );

		

		$custom_template = stripslashes( $settings['custom_template'] );

		$tempalte_type = 'global';

		// template to use
		if( $tempalte_type == 'global' ){
			$template_content = $global_template;
		}
		if( $tempalte_type == 'custom' ){
			$template_content = $custom_template;
		}

		// nl patch
		$template_content_bkp = $template_content;
		//$template_content = nl2br( $template_content );
		$template_content = $template_content;

		//$email_sending_report = [];

		//  getting users
		$user_list = [];
		$user_list[] = (int)$_POST['current_page_user'];
	 
		// full sending amount 
		$full_sending_amount = count($user_list);

		// sending process
		$users_count = count($user_list);


		// sening filter
		$portion_to_run = array_slice ( $user_list , $processed_items, $email_amount_to_send);
		$user_list = $portion_to_run;
		$user_list[] = (int)$_POST['current_page_user'];

	 
		foreach( $user_list as $s_id ){
		 
			$template_content_inner = wvp_get_user_email_content( $s_id, $template_content );
			$userdata = get_user_by( 'ID', $s_id );

			$to = $userdata->user_email;
			$subject = $email_subject;
			$body = $template_content_inner;
			$headers = [];
			$headers[] =  'Content-Type: text/html;';
			$headers[] =  ' charset=UTF-8';
			$headers[] =  'From: '.$from_name.' <'.$from_email.'>';


			
			$extra_emails = get_user_meta( $s_id, 'additional_emails', true);
			$extra_emails_bkp = $extra_emails;

			$extra_emails = explode(',', $extra_emails);
			$extra_emails = array_filter( $extra_emails );
			$extra_emails = array_map('trim', $extra_emails);
			foreach( $extra_emails as $s_email ){
				$headers[] = 'Cc: '.$s_email;
				$users_count++;
			}
	 
			/**email log title patch */
			$all_emails_titles = get_option('email_sent_subjects');
			$all_emails_titles[md5( $subject )] = $subject;
			update_option('email_sent_subjects', $all_emails_titles);

			$body = $body.'<img src="'.get_option('home').'?email_user_id='.$s_id.'&email_user_subject='.md5( $subject ).'" />';

			/**email log title patch END */
		 
			$res = wp_mail( $to, $subject, $body, $headers );

			$email_sending_report[$s_id] = $res;

			$processed_items++;
		}

		// last email sending
		update_post_meta($template_id, 'total_emails', $full_sending_amount );
		update_post_meta($template_id, 'email_sending_report_'.$template_id, $email_sending_report);		
		update_post_meta($template_id, 'email_full_processed_items_'.$template_id, $processed_items);
 
		$emails_amount_left = $full_sending_amount - $processed_items;
 

		if( /*  $_POST['is_start'] == '0' || */ $emails_amount_left <= 0 ){
			// big email log
			$current_log = get_option('big_email_log' );
			$current_log[] = array( 'date' => current_time('timestamp'), 'body' => $template_content_bkp, 'usercount' => $users_count, 'template_type' => $template_id, 'content' =>  $body, 'is_test' => false, 'send_report' => $email_sending_report );
			update_option('big_email_log', $current_log );

			update_option('hide_send_message', '0');
			//update_post_meta($template_id, 'sending_finished_at',  time() );
 
		}
 
		echo json_encode( array( 'result' => 'success', 'total_users' => $users_count, 'processed_users' =>  $processed_items, 'msg' => '<div class="tw-bs4 message_sent_block_cont"><div class="alert alert-success">'.__('Message sent', 'wvp').'</div></div>'  ) );
  
	}
	die();
}

?>