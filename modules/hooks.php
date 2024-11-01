<?php 




// adding post button
add_action('restrict_manage_posts', 'add_poll_section_filter', 1000000);
function add_poll_section_filter( $which ) {

   // if( $which == 'top' ){
	if( isset( $_GET['post_type'] ) ){
		if( $_GET['post_type'] == 'poll' ){
			$login_suspended  = get_option('login_suspended');
			if( $login_suspended == '1'){
				$out = '
				<a href="'.admin_url('edit.php?post_type=poll&suspend_login=0').'" style="float:right;"  class="button-primary" >'.__('Allow login', 'wvp').'</a>
				'; 
			}else{
				$out = '
				<a href="'.admin_url('edit.php?post_type=poll&suspend_login=1').'" style="float:right;"  class="button-primary" >'.__('Suspend Logins', 'wvp').'</a>
				'; 
			}
	
			$out .= '
				&nbsp;<a href="'.admin_url('/edit.php?post_type=poll&page=wvp_reloginrelogin_settings').'" style="float:right; margin:0px 15px;"  class="button-primary" >'.__('Allow only re-login', 'wvp').'</a>&nbsp;
				'; 
		 
	
			// output <select> and submit button
			echo  $out;
		}
	}
		
	//}
	
}


//export user data
add_action('restrict_manage_users', 'wvp_restrict_manage_users', 1000000);
function wvp_restrict_manage_users( $which ) {
 
		$out = '
			&nbsp; <a href="'.admin_url('users.php?export_type=userdata').'" style="float:right;"  class="button-primary" >'.__('Export Users', 'wvp').'</a>
			'; 
	
		// output <select> and submit button
		echo  $out;
	//}
	
}


//show error if not allowed to login
add_action('wp_authenticate_user', 'wvp_verify_login_user', 30, 2);
function wvp_verify_login_user($user  ) {
	global $wpdb;
 
	/** login error patch */
	if( is_wp_error( $user ) ){
		return $user;
	}

	if( $user   ){
  
		if( in_array( 'subscriber', $user->roles ) ){
			$login_suspended  = get_option('login_suspended');
		 
			if( $login_suspended == '1'){
				return new WP_Error( 'user_expired', __('Access to the site to logged out users is suspended at this moment.', 'wvp') );
			}
		}
 
	}


	// do not login if can vote = 0
	$settings = get_option('wvp_options');
	$disable_login_can_vote_0 = $settings['disable_login_can_vote_0'];


	if( !in_array( 'administrator', $user->roles ) ){
		if( $disable_login_can_vote_0 == 'yes' ){
			$can_vote = get_user_meta( $user->ID, 'can_vote', true );
			if( $can_vote == '0' ){
				return new WP_Error( 'not_allowed_to_login', __('Your login to the  website has been disabled. If you have any doubt, please contact the administrator', 'wvp') );
			}	
		}
	}

	// disable_login
	$disable_login = get_user_meta( $user->ID, 'disable_login', true );
	if( $disable_login == 'on' ){
		return new WP_Error( 'not_allowed_to_login', __('Your login to the  website has been disabled. If you have any doubt, please contact the administrator', 'wvp') );
	}

	// do not lalow to login users that didnt login in this day
	$wvp_relogin_options = get_option('wvp_relogin_options');
	if( $wvp_relogin_options['allow_only_relogin'] == 'yes' && !in_array( 'administrator', $user->roles )  ){
		$last_login = get_user_meta( $user->ID, 'last_login', true );
		$event_Date = $wvp_relogin_options['event_start_time'];
		$beginOfDay = strtotime("today", strtotime( $event_Date ) );
		if( $last_login < $beginOfDay ){
			return new WP_Error( 'user_expired', __('Sorry, you can\'t access this site at this moment.', 'wvp') );
		}else{
			$login_suspended = '0';
		}
		
	}
	

	return $user;
}

/* debugging  */
add_Action('init1', function(){
	global $current_user;

	$wvp_relogin_options = get_option('wvp_relogin_options');
	if( $wvp_relogin_options['allow_only_relogin'] == 'yes' ){
		$last_login = get_user_meta( $current_user->ID, 'last_login', true );
		
		$event_Date = $wvp_relogin_options['event_start_time'];
	
		$beginOfDay = strtotime("today", strtotime( $event_Date ) );
		if( $last_login < $beginOfDay ){
			$login_suspended = '1';
		}else{
			$login_suspended = '0';
		}
		die();
	}
});
  

// process user voting
add_action('init', function(){
	global $current_user, $wpdb;
	
	$wvp_extra_options = get_option('wvp_extra_options');

	error_reporting(0);


	/**
	 * logout user link
	 */
	if ( isset( $_GET['logout_user'] ) ){
		if( wp_verify_nonce( $_GET['logout_user'], basename( __FILE__ ) ) ){
			if(class_exists('WP_Session_Tokens')){
				$coder_sessions = WP_Session_Tokens::get_instance( $_GET['user_id'] );
	 			$coder_sessions->destroy_all();
				 update_user_meta( $_GET['user_id'], 'online_activity', 0 );
			}
		}
	} 

	/**
	 * login URL
	 */
	if( isset( $_GET['user'] ) && isset( $_GET['pass'] ) ){
		$res = wp_authenticate( $_GET['user'], $_GET['pass'] );
		if( is_wp_error( $res ) ){
			wp_redirect( get_option('home'), 302 );
			die();
		}else{
			wp_clear_auth_cookie();
			wp_set_current_user ( $res->ID );
			wp_set_auth_cookie  ( $res->ID );

			wp_redirect( get_permalink( $_GET['page'] ), 302 );
			die();
		}
	}

	/**
	 * switch back accopunt
	 */
	if( isset( $_GET['return_to_user_field'] ) ){
	 
		if( wp_verify_nonce( $_GET['return_to_user_field'], 'return_to_user_action' ) ){
			$switch_back_to = (int)$_GET['user_id'];
		 
			$switch_back_user = $wpdb->get_var("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'switched_to' AND meta_value = {$switch_back_to}");
		 
			// temporary timestamp
			delete_user_meta( $switch_back_user, 'switched_to'  );
			delete_user_meta( $switch_back_user, 'switched_to_time'  );

			wvp_auto_login( $switch_back_user );

		}
	}
	
	/**
	 * switch to accopunt
	 */
	if( isset( $_POST['switch_to_account_field'] ) ){
		if( wp_verify_nonce( $_POST['switch_to_account_field'], 'switch_to_account_action' ) ){
			$switch_to = (int)$_POST['login_to'];
			$all_who_gived = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'donate_recipient' AND meta_value = {$current_user->ID}" );
			if( in_array( $switch_to, $all_who_gived ) ){
				
				// temporary timestamp
				update_user_meta( $current_user->ID, 'switched_to', $switch_to );
				update_user_meta( $current_user->ID, 'switched_to_time', time() );

				wvp_auto_login( $switch_to );

			}

			//verify if we can to switch
		}
	}
	

	// upload csv to database
	if( isset( $_FILES['csv_file_for_users'] ) ){
		if(   $_FILES['csv_file_for_users']['name'] != '' ){
			$table_name = 'imported_csv_users';
			$table_name =  $wpdb->prefix.$table_name;

			$wpdb->query("TRUNCATE {$table_name}");
/*
			$upload_dir = wp_upload_dir();
			$tmp_name = $_FILES["csv_file_for_users"]["name"];
			$tmp_import_dir = $upload_dir['basedir'].'/tmp_import/';
			$tmp_import_file = $upload_dir['basedir'].'/tmp_import/'.$tmp_name;
			unlink( $tmp_import );

			wp_mkdir_p( $tmp_import_dir );

			$tmp_name = $_FILES["csv_file_for_users"]["tmp_name"];
			move_uploaded_file( $tmp_name, $tmp_import_file);

 
		 
			$query = "
			LOAD DATA  INFILE '".$tmp_import_file."' IGNORE 
			INTO TABLE {$table_name}
			FIELDS TERMINATED BY ',' 
			LINES TERMINATED BY '\\n\\r'
			(login,email,own_shares)
			";
			var_dump( $query );
			$res = $wpdb->query( $query );
			var_dump( $res );
			$wpdb->show_errors( true ); 
			$wpdb->print_error();
			die();
			*/
		 
			$import_string = [];
			$row = 1;
			if (($handle = fopen( $_FILES['csv_file_for_users']['tmp_name'] , "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
					
		 

					$import_string[] = $wpdb->prepare( "( '%s', '%s', '%s')", $data[0], $data[1], $data[2] );
					$row++;

					if( $row >= 500 ){
						$row=0;
						$wpdb->query("INSERT INTO {$table_name}
						( login, email, own_shares )
						VALUES
						".implode( ',', $import_string )." " );
						$import_string = [];
					}
				}
				$wpdb->query("INSERT INTO {$table_name}
						( login, email, own_shares )
						VALUES
						".implode( ',', $import_string )." " );
				fclose($handle);
				$res = true;

				//$wpdb->query("DELETE FROM {$table_name} WHERE `id` = 1");
			}
		

			if( $res === false  ){
				wp_Redirect( admin_url( 'edit.php?post_type=poll&page=wvp_regformsettings&msg=svg_import_error'  ) , 302 );
				exit;
			}else{		
				$count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
				wp_Redirect( admin_url( 'edit.php?post_type=poll&page=wvp_regformsettings&msg=svg_import_success&count='.$count ) , 302 );
				exit;
			}
			

		}
	}

	/**
	 * drop csv userts
	 */
	if( isset( $_GET['wvp_action'] ) ){
		if( $_GET['wvp_action'] == 'drop_csv_users_files' ){
			/*
			$table_name = 'imported_csv_users';
			$table_name =  $wpdb->prefix.$table_name;

			$wpdb->query("TRUNCATE {$table_name}");
			*/
			$all_users = get_users([
				'meta_key' => 'is_imported',
				'meta_value' => '1'
			]);
			$upload_dir = wp_upload_dir();
			
			foreach( $all_users as $s_user ){
				$files_path = $upload_dir['basedir'].'/regform/'.$s_user->ID;
				$files_url = $upload_dir['baseurl'].'/regform/'.$s_user->ID;

				wvp_rrmdir( $files_path );
				wp_mkdir_p( $files_path );

			

				for( $i=1; $i<=5; $i++ ){
					delete_user_meta( $s_user->ID, 'attach_'.$i );
				}
			}

		}
	}

	/** reg form handler */
	if( isset( $_POST['reg_user_name'] ) )
	if( wp_verify_nonce( $_POST['reg_user_name'], 'reg_user_action') ){
		$out_user_info = [];

		$wvp_extra_options = get_option('wvp_regform_options');
		$max_size_allowed = (int)$wvp_extra_options['max_size_allowed'];

		$attach_extensions = explode(',', str_replace( ' ', '', trim( $wvp_extra_options['attach_extensions'] ) ) );

		$input_email = sanitize_text_field( $_POST['input_email'] );
		$out_user_info[] = __('Email: ', 'wvp').$input_email;

		$input_name = sanitize_text_field( $_POST['input_name'] );
		$out_user_info[] = __('Name: ', 'wvp').$input_name;

		$input_password = sanitize_text_field( $_POST['input_password'] );
		$out_user_info[] = __('Password: ', 'wvp').$input_password;

		if( email_exists( $input_email ) ){
			$page_url = get_permalink( votingClassCoreHelper::get_shortcode_page('registration_form') );
			wp_redirect( $page_url.'?message=email_exists', 302 );
			exit;
		}

		if( isset($_POST['input_username'] ) ){
			$input_username = sanitize_text_field( $_POST['input_username'] );
			

			if( username_exists( $input_username ) ){
				$page_url = get_permalink( votingClassCoreHelper::get_shortcode_page('registration_form') );
				wp_redirect( $page_url.'?message=user_exists', 302 );
				exit;
			}
		}else{
			$input_username = $input_email;
		}


		// verify if user in database
		$table_name = 'imported_csv_users';
		$table_name =  $wpdb->prefix.$table_name;

		if( $wvp_extra_options['validate_email_and_username'] == 'yes' ){
			$count_entries = $wpdb->get_var(  $wpdb->prepare( "SELECT COUNT(*) FROM  {$table_name} WHERE login = %s AND email = %s", $input_username, $input_email ));
			$user_db_info = $wpdb->get_row(  $wpdb->prepare( "SELECT  * FROM  {$table_name} WHERE login = %s AND email = %s", $input_username, $input_email ));
		}
		if( $wvp_extra_options['validate_email_and_username'] == 'no' ){
			$count_entries = $wpdb->get_var(  $wpdb->prepare( "SELECT COUNT(*) FROM  {$table_name} WHERE login = %s", $input_username ));
			$user_db_info = $wpdb->get_row(  $wpdb->prepare( "SELECT  * FROM  {$table_name} WHERE login = %s", $input_username ) );

			if( $count_entries == 0 ){
				$count_entries = $wpdb->get_var(  $wpdb->prepare( "SELECT COUNT(*) FROM  {$table_name} WHERE email = %s", $input_email ));
				$user_db_info = $wpdb->get_row(  $wpdb->prepare( "SELECT  * FROM  {$table_name} WHERE email = %s", $input_email ));

			
			}
			
		}

		// if user not verified in base
		if( $count_entries == 0 ){
			$page_url = get_permalink( votingClassCoreHelper::get_shortcode_page('registration_form') );
			wp_redirect( $page_url.'?message=not_found', 302 );
			exit;
		}


		// if( username)
		if( isset($_POST['input_username'] ) ){
			

			$input_username = sanitize_text_field( $_POST['input_username'] );
			$user_login = $input_username;

			$out_user_info[] = __('Username: ', 'wvp').$input_username;

			$userdata = array(
				'user_login' =>  $input_username,
				'user_email'   =>  $input_email,
				'user_pass'  =>  $input_password
			);

			if( isset( $_POST['input_name'] ) ){
				$userdata['first_name'] = sanitize_text_field( $_POST['input_name'] );
			}
			if( isset( $_POST['input_lastname'] ) ){
				$userdata['last_name'] = sanitize_text_field( $_POST['input_lastname'] );
			}
	
			// files verification
			for( $i=1; $i<=5; $i++ ){
				if( isset( $_FILES['attach_'.$i] ) ){

					if( isset( $_FILES['attach_'.$i]['name'] ) ){
						if(  $_FILES['attach_'.$i]['name'] != '' ){
							//verify format
							$name = $_FILES['attach_'.$i]["name"];
							$ext = end((explode(".", $name)));

							//verify size
							if( !in_array( $ext, $attach_extensions ) ){
								$page_url = get_permalink( votingClassCoreHelper::get_shortcode_page('registration_form') );
								wp_redirect( $page_url.'?message=attach_'.$i.'_error', 302 );
								exit;
							}

							// verify size
							$size = $_FILES['attach_'.$i]['size'];
							$size_mb = $size / 1000000;

							if( $size_mb > $max_size_allowed ){
								$page_url = get_permalink( votingClassCoreHelper::get_shortcode_page('registration_form') );
								wp_redirect( $page_url.'?message=size_'.$i.'_error', 302 );
								exit;
							}

							// file is ok, upload file
						}
						
					}

				}
			}

			$user_id = wp_insert_user( $userdata ) ;
			update_user_meta( $user_id, 'input_message',  sanitize_text_field( $_POST['input_message'] ) );
			update_user_meta( $user_id, 'input_check1',  sanitize_text_field( $_POST['input_check1'] ) );
			update_user_meta( $user_id, 'input_check2',  sanitize_text_field( $_POST['input_check2'] ) );


			update_user_meta( $user_id, 'input_select1',  sanitize_text_field( $_POST['input_select1'] ) );
			update_user_meta( $user_id, 'input_select2',  sanitize_text_field( $_POST['input_select2'] ) );
			$out_user_info[] = __('Select 1: ', 'wvp').sanitize_text_field( $_POST['input_select1'] );
			$out_user_info[] = __('Select 2: ', 'wvp').sanitize_text_field( $_POST['input_select2'] );


			update_user_meta( $user_id, 'is_imported',  '1' );

			$out_user_info[] = __('Message: ', 'wvp').sanitize_text_field( $_POST['input_message'] );

			// update user shares
			$user_shares = $user_db_info->own_shares;
			/** patch */
			$user_shares = (int)$user_shares;
			if( $user_shares == 0 ){
				$user_shares = 1;
			}

			update_user_meta( $user_id, 'own_shares', $user_shares);
			update_user_meta( $user_id, 'can_vote', 1 );

			/** update extra fields */
			update_user_meta( $user_id, 'own_shares', $user_shares);

			// files uploading
			$upload_dir = wp_upload_dir();
			$files_path = $upload_dir['basedir'].'/regform/'.$user_id;
			$files_url = $upload_dir['baseurl'].'/regform/'.$user_id;


			$files_url = $upload_dir['baseurl'].'/regform/'.$user_id;


			$list_of_files_urls = [];
			for( $i=1; $i<=5; $i++ ){
				echo '<li><a download href="'.$files_url.'/'.get_user_meta( $user_id, 'attach_'.$i,  true ).'">'.get_user_meta( $user_id, 'attach_'.$i,  true ).'</a></li>';
				//$list_of_files_urls[] = '<a download href="'.$files_url.'/'.get_user_meta( $user->ID, 'attach_'.$i,  true ).'">'.$files_url.'/'.get_user_meta( $user->ID, 'attach_'.$i,  true ).'</a>'
			}
			

			wp_mkdir_p( $files_path );

			for( $i=1; $i<=5; $i++ ){
				if( isset( $_FILES['attach_'.$i] ) ){
					if( isset( $_FILES['attach_'.$i]['name'] ) ){
						if(  $_FILES['attach_'.$i]['name'] != '' ){
							$tmp_name = $_FILES['attach_'.$i]["tmp_name"];
							$name = basename( $_FILES['attach_'.$i]["name"] );
							$name = sanitize_file_name( $name );
							// file is ok, upload file
							move_uploaded_file($tmp_name, "$files_path/$name");
							update_user_meta( $user_id, 'attach_'.$i,    $name  );

							$out_user_info[] = str_replace( '%', $i, __('Attach %: ', 'wvp') ).$files_url.'/'.$name;
						}
					}
				}
			}

			// main sending
			$emails_list = explode( ',', $wvp_extra_options['form_submission_emails']  );
			$emails_list = array_map('trim', $emails_list);

			$to = $emails_list;
			$subject = str_replace( '{username}', $input_username, __('New preapproved user has registered in the site – {username}', 'wvp') );
			$body = implode( '<br/>', $out_user_info );
			$headers = array('Content-Type: text/html; charset=UTF-8');
			
			wp_mail( $to, $subject, $body, $headers );


			/* user submission email */
			if( $wvp_extra_options['send_confirmation_email'] == 'yes' ){
				$to = $input_email;
				$subject = esc_html( $wvp_extra_options['email_subject'] );
				
				$body = $wvp_extra_options['message_to_send'];
				$body = str_replace( '{username}', $input_username, $body);
				$body = str_replace( '{first_name}', sanitize_text_field( $_POST['input_name'] ), $body);
				$body = str_replace( '{last_name}', sanitize_text_field( $_POST['input_lastname'] ), $body);
		
				$headers = [];
				$headers[] = 'From: '.esc_html( $wvp_extra_options['email_from_name'] ).' <'.sanitize_email( $wvp_extra_options['email_from_email'] ).'>';
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				
				wp_mail( $to, $subject, $body, $headers );
			}


			/*
			$fp = fopen( dirname(__FILE__).'/email.txt', 'w');
			fwrite($fp, var_export( $body, true ));
			fclose($fp);
			*/

			// success final redirec
			$page_url = get_permalink( votingClassCoreHelper::get_shortcode_page('registration_form') );
			wp_redirect( $page_url.'?message=reg_success', 302 );
			exit;
			
		 
		}

	}

	/** email tracking Opener */
	if( isset( $_GET['test_user_email'] ) ){

		$to = 'dobrzhanskiy@gmail.com';
		$subject = 'The subject';
		$body = 'The email body content<img src="https://plugin.voodoopress.net/?email_user_id=1&email_user_subject=12121212121" /> asdad';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		
		wp_mail( $to, $subject, $body, $headers );
	}
	if( isset( $_GET['email_user_id'] ) ){

		$table_name = 'email_view_log';
		$table_name =  $wpdb->prefix.$table_name;
		//$wpdb->query("DROP TABLE ".$table_name );

	 
		$email_user = (int)$_GET['email_user_id'];
		$email_subject = sanitize_text_field( $_GET['email_user_subject'] );
		$email_date = current_time('timestamp');
		$email_ip = $_SERVER['REMOTE_ADDR'];

		$current_result = $wpdb->get_var(  $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}email_view_log WHERE `user_id` = %s AND `subject` = %s", $email_user, $email_subject  ) );
	
		if( $current_result == 0 ){
			$wpdb->insert(
				$table_name,
				[
					'user_id' => $email_user,
					'date' => $email_date,
					'ip' => $email_ip,
					'subject' => $email_subject,
				]
			);
		}
	 
		$graphic_http = plugins_url( '/1.png', __FILE__ );

		$filesize = filesize( '1.png' );

		header( 'Pragma: public' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false );
		header( 'Content-Disposition: attachment; filename="a_unique_image_name_' . $email_user  . '.png"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: '.$filesize );
		readfile( $graphic_http );
		exit;
	}
	/** email tracking Opener END */

	// remove email log
	if( isset( $_GET['delete_email_log'] ) ){
	 
		delete_option('big_email_log' );

		wp_redirect( admin_url( 'edit.php?post_type=poll&page=wvp_dataemail_log' ), 302);
		die();
	}

	// remove email log
	if( isset( $_GET['remove_email_log'] ) ){
		$current_log = get_option('big_email_log' );
		$tmp_storage = [];
		foreach( $current_log as $s_row ){
			if( $s_row['date'] == $_GET['remove_email_log'] ){ continue; }
			$tmp_storage[] = $s_row;
		}
		update_option('big_email_log', $tmp_storage);
		wp_redirect( admin_url('edit.php?post_type=poll&page=wvp_dataemail_log&remove=1'), 302);
		die();
	}

	// use email cliche
	if( isset( $_GET['reuse_email_log'] ) ){
		$current_log = get_option('big_email_log' );
		$tmp_storage = [];
		foreach( $current_log as $s_row ){
		 
			if( $s_row['date'] == $_GET['reuse_email_log'] ){ 
				$tempalte_type = $s_row['template_type'];
	 
				$email_settings = get_option('wvp_email_options');

				if( $tempalte_type == 'global' ){
					$email_settings['global_template'] = $s_row['body'];
				}
				if( $tempalte_type == 'custom' ){
					$email_settings['custom_template'] = $s_row['body'];
				}
				update_option('wvp_email_options', $email_settings);
			}
	 
		}		
		wp_redirect( admin_url('edit.php?post_type=poll&page=wvp_emailsend_email&cliche_used=1'), 302);
		die();
	}

	// drop email log
	if( isset( $_GET['droplog'] ) ){
		delete_option('email_sending_report');
		wp_redirect(admin_url('edit.php?post_type=poll&page=wvp_emailsend_email'), 302);
		exit;
	}

	// login suspension processing
	if( isset( $_GET['suspend_login'] ) ){
		if( $_GET['suspend_login'] == '1' ){
			update_option('login_suspended', '1');
		}
		if( $_GET['suspend_login'] == '0' ){
			update_option('login_suspended', '0');
		}
	}

	// logout all users
	if( isset( $_GET['logoutall'] ) )
	if( $_GET['logoutall'] == '1' ){
		
		global $current_user;

		$all_users = get_users();
		
		foreach($all_users as $s_user ){
			if( $current_user->ID == $s_user->ID ){ continue; }
			$sessions = WP_Session_Tokens::get_instance( $s_user->ID );
			$sessions->destroy_all();

			/**
			 * logout all patch
			 */
			update_user_meta( $s_user->ID, 'online_activity', 0 );
		}
		
	}

	// delete all answers
	if( isset( $_GET['delete_answers'] ) )
	if( $_GET['delete_answers'] == '1' ){
		$post_id = (int)$_GET['post'];

		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'user\\_%' ", $post_id ) );
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'uservotetime\\_%' ", $post_id ) );
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'browser\\_%' ", $post_id ) );
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE   `post_id` = %d AND meta_key LIKE 'usermessage\\_%' ", $post_id ) );

	 

		wp_redirect( admin_url('post.php?post='.$post_id.'&action=edit&message=1'), 302);
		die();
	}

	// http://localhost/wordpress/show_poll-id41687/?is_test=1&user_selection=1&parent_vote=41687&current_page=1&user_id=1

	//error_reporting(E_ALL);
	//ini_set('display_errors', 'On');

	$user_can_vote = get_user_meta( $current_user->ID, 'can_vote', true );
	if( ( is_user_logged_in() && !current_user_can('administrator') && $user_can_vote == '1' ) || $_REQUEST['is_test'] == '1'  ){
	
		if( isset($_REQUEST['user_selection']) ){
		

			// check if already voted

			//patch for multivariant
			if( is_array($_REQUEST['user_selection']) ){
				$user_selection =  $_REQUEST['user_selection'];
			}else{
				$user_selection = (int)$_REQUEST['user_selection'];
			}

			/*** v 1.15 patch	*/
			$parent_vote = (int)$_REQUEST['parent_vote'];
			$current_page = (int)$_REQUEST['current_page'];

			$poll_variants_check = (array) get_post_meta( $parent_vote, 'poll_variants_check', true );

			$we_have_nullers = 0;
			$nullers_count = 0;
			$we_have_not_nullers = 0;
			$inner_cnt = 0;

			// check for nullers
			foreach( $user_selection as $single_selection ){
				// variant selected
				if( $inner_cnt == $single_selection ){
					$checkbox_value = $poll_variants_check[$inner_cnt];
					if( $checkbox_value == 'on' ){
						$we_have_nullers = 1;
						$nullers_count++;
					}
				}
				$inner_cnt++;
			}

			// check if no nullers
			foreach( $user_selection as $single_selection ){
				// variant selected
				if( $inner_cnt == $single_selection ){
					$checkbox_value = $poll_variants_check[$inner_cnt];
					if( $checkbox_value == 'off' ){
						$we_have_not_nullers = 1;
					}
				}
				$inner_cnt++;
			}

			// verify issues
			if( $we_have_nullers == 1 &&  $we_have_not_nullers == 1 ){
				// return error
				wp_redirect( get_permalink( $current_page ), 302 );
				exit;
			}
			if( $we_have_nullers == 1 && $nullers_count > 1 ){
				// return error
				wp_redirect( get_permalink( $current_page ), 302 );
				exit;
			}
	 
			/*** v 1.15 patch	END */
		
			/* V14 changes #4
			// check if voting user accepted proxies from current AVOID CHEAT
			//var_dump( (int)$_POST['donor_selector'] );
			//var_dump( $current_user->ID );
			if( (int)$_POST['donor_selector'] != $current_user->ID ){
				$allow_make_vote = 0;
				$all_donors_list = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM {$wpdb->usermeta} WHERE meta_key = 'donate_recipient' AND meta_value = {$current_user->ID} AND user_id = %d ", $_POST['donor_selector']) );
		 
				if( count($all_donors_list) > 0 ){
	
					foreach( $all_donors_list as $s_donor ){
	
						// filter posll by allow_only_assigned_users_to_vote
						$user_associated_category = get_user_meta( $s_donor->user_id, USER_CATEGORY_META_KEY, true );
						//var_dump( $user_associated_category );
						// get  poll assiciation value	
					
							$poll_cats = wp_get_post_terms( (int)$_POST['parent_vote'], USER_CATEGORY_NAME );
						 
							$all_cat_ids = [];
							foreach( $poll_cats as $single_cat ){
								$all_cat_ids[] = $single_cat->term_id;
							}
							
							$allow_only_assigned_users_to_vote = get_post_meta( (int)$_POST['parent_vote'], 'allow_only_assigned_users_to_vote', true  );

							if( $allow_only_assigned_users_to_vote == 'yes' ){
								if( in_array( $user_associated_category, $all_cat_ids ) &&  (int)$_POST['donor_selector'] == $s_donor->user_id ){
									// if in category and recieved proxy
									$allow_make_vote = 1;
									$current_user = get_user_by( 'ID', (int)$_POST['donor_selector'] );
								}
							}else{
								$allow_make_vote = 1;
								$current_user = get_user_by( 'ID', (int)$_POST['donor_selector'] );
							}
							
					}
				}
			}
			*/
			/*
			die();
			if( $allow_make_vote == 0 ){
				wp_redirect( get_permalink( $current_page ).'?error=not_allowed', 302 );
				exit;
			}
			*/
			
		


			// tesing patch for user
			if( $_REQUEST['is_test'] == '1' ){
				$current_user = get_user_by( 'ID', $_REQUEST['user_id'] );
			}
			
			$parent_vote = (int)$_REQUEST['parent_vote'];
			$current_page = (int)$_REQUEST['current_page'];

			// check if voted
			$current_vote = get_post_meta( $parent_vote, 'user_'.$current_user->ID,  true );
	 
			if( isset( $current_vote ) && $current_vote != '' ){
				wp_redirect( get_permalink( $current_page ), 302 );
				exit;
			}
			
			// patching number of votes
			$votes_amount = (int)get_post_meta( $parent_vote, 'max_variants', true );
			$poll_type =  get_post_meta( $parent_vote, 'poll_type', true );
	 
			if( $poll_type == 'multi' ){
				if( count($user_selection) > $votes_amount ){
					wp_redirect( get_permalink( $current_page ).'?submit=false&id='.$parent_vote, 302 );
					exit;
				}
			}
			

			$user_message = sanitize_text_field( $_REQUEST['user_message'] );

			// getting user browser
			$user_browser = get_browser_name( $_SERVER['HTTP_USER_AGENT'] );

			// if litemode dont save data
			if( $wvp_extra_options['lite_mode'] != 'yes' ){
				update_post_meta( $parent_vote, 'user_'.$current_user->ID.'_ip'.$_SERVER['REMOTE_ADDR'],  $user_selection );
				update_post_meta( $parent_vote, 'browser_'.$current_user->ID,  $user_browser );
				update_post_meta( $parent_vote, 'uservotetime_'.$current_user->ID,  current_time('timestamp') );
			}
			
			update_post_meta( $parent_vote, 'usermessage_'.$current_user->ID,  $user_message );

			// multivariant patch
			if( is_array($_REQUEST['user_selection']) ){
				

				foreach( $user_selection as $s_answer ){
					add_post_meta( $parent_vote, 'user_'.$current_user->ID,  $s_answer );
				}
			}else{
				update_post_meta( $parent_vote, 'user_'.$current_user->ID,  $user_selection );
			}
			

			// process sending emails
			$emails = get_post_meta( $parent_vote, 'emails', true );
			$emails = explode(',', $emails);
			$emails = array_filter( $emails );

			// settings to check if send answers
			$settings = get_option('wvp_extra_options');

			$own_shares = get_own_shares( $current_user->ID  ); 
			$proxys_amount = get_proxys_amount( $current_user->ID ); 
			$total_shares = get_total_shares( $current_user->ID );

			$poll_variants =  get_post_meta( $parent_vote, 'poll_variants' , true );

			// multivariant patch
			if( is_array($_REQUEST['user_selection']) ){
				foreach( $user_selection as $s_answer ){
					$user_selected_string .= $poll_variants[$s_answer];
				}
			}else{
				$user_selected_string = $poll_variants[$user_selection];
			}

			$email_title = __('Vote for ', 'wvp').get_post( $parent_vote )->post_title;
			$email_subject = '
			<ul>
		 
			<li>'.__('Vote name: ', 'wvp').get_post( $parent_vote )->post_title.'</li>';
			if( $settings['include_answers'] == 'yes' ){
				$email_subject .= '  
				<li>'.__('Vote answer:', 'wvp').$user_selected_string.' </li>';
			}
			
			$own_shares = get_own_shares( $current_user->ID  );  
			$proxys_amount = get_proxys_amount( $current_user->ID );
			$total_shares = get_total_shares( $current_user->ID );  

			$email_subject .= '
			<li>'.__('Date – Time of vote: ', 'wvp').date('Y/m/d H:i', current_time('timestamp')).' </li>  
			<li>'.__('Username: ', 'wvp').$current_user->user_login.'</li>  
			<li>'.__('User:', 'wvp').' '.$current_user->first_name.'   '.$current_user->last_name.' </li>  
			<li>'.__('User Email: ', 'wvp').' '.$current_user->user_email.'</li>  
			<li>'.__('User IP:', 'wvp').'  '.$_SERVER['REMOTE_ADDR'].'</li>  
			<li>'.__('Own Shares:', 'wvp').' '.$own_shares.'</li>  
			<li>'.__('Proxys amount:', 'wvp').' '.$proxys_amount.'</li>  
			<li>'.__('Total Shares:', 'wvp').': '.$total_shares.'</li>

			'.( isset($_REQUEST['user_message']) ? '<li>'.__('User Message:', 'wvp').': '.$user_message.'</li>' : '' ).'

			</ul>
';


			if( count($emails) > 0 ){
				foreach( $emails as $s_email ){
					$to = $s_email;
					$subject = $email_title;
					$body = $email_subject;
					$headers = array('Content-Type: text/html; charset=UTF-8');
					 
					wp_mail( $to, $subject, $body, $headers );
				}
			}

			wp_redirect( get_permalink( $current_page ).'?submit=true&id='.$parent_vote, 302 );
			exit;
		}
	}
	
	/**
	 * show list of predefined users
	 */
	if( isset( $_GET['action'] ) )
	if( $_GET['action'] == 'check_preexisted_users' ){
		$table_name = 'imported_csv_users';
		$table_name =  $wpdb->prefix.$table_name;
		$list = $wpdb->get_results("SELECT * FROM {$table_name}");

		$out = '
		<table>
			<thead>
				<tr>
					<th>'.__('Login', 'wvp').'</th>
					<th>'.__('Email', 'wvp').'</th>
					<th>'.__('Own Shares', 'wvp').'</th>
				</tr>
			</thead>
			<tbody>';
				foreach( $list as $s_row ){
					$out .= '
					<tr>
						<td>'.$s_row->login.'</td>
						<td>'.$s_row->email.'</td>
						<td>'.$s_row->own_shares.'</td>
						
						
					</tr>
					';
				}
			$out .= '
			</tbody>
		</table>
		';

		echo $out;
		die();
	}

	// drop database
	if( isset( $_GET['action'] ) )
	if( $_GET['action'] == 'drop_database' ){
	 
			global $wpdb;
			$table_name = 'online_users_log';
			$table_name =  $wpdb->prefix.$table_name;
			$wpdb->query("TRUNCATE {$table_name}");
		 
			global $wpdb;
			$table_name = 'online_log';
			$table_name =  $wpdb->prefix.$table_name;
			$wpdb->query("TRUNCATE {$table_name}");
 
	}

	if( isset( $_GET['action'] ) )
	if( $_GET['action'] == 'drop_email_opened' ){
		global $wpdb;
		$table_name = 'email_view_log';
		$table_name =  $wpdb->prefix.$table_name;
		$wpdb->query("TRUNCATE {$table_name}");

		wp_redirect( admin_url('edit.php?post_type=poll&page=wvp_dataemail_open_report') , 302);
		exit;
	}

	// export csv rev 13a
	if( isset( $_GET['action'] ) )
	if( $_GET['action'] == 'save_csv_data_block' ){

		$output_options = get_option('wvp_extra_options');
		$columns_data = $output_options['poll_settings']['show_item'];
		$labels_data = $output_options['poll_settings']['block_value'];


		$columns_poll_open_stats_data = $output_options['poll_open_stats']['show_item'];
		$labels_poll_open_stats_data = $output_options['poll_open_stats']['block_value'];

		header("Content-type: text/csv");		
		header("Content-Disposition: attachment; filename=poll_results_report_rev13.csv");
	 	header("Pragma: no-cache");
		header("Expires: 0");

 
		$output = fopen("php://output", "w");

		fputcsv($output, array( 
			__('Poll Title', 'wvp'), 
			__('Amount of votes received in this answer', 'wvp'),   
			__('Total shares in this answer', 'wvp'),    
			__('%', 'wvp'),   
			__('% of answers of the A+B shares over the quorum value that was present when the poll was closed', 'wvp'),   
			) );


		$all_polls = get_posts([
			'post_type' => 'poll',
			'showposts' => -1
		]);

		foreach( $all_polls as $single_poll ){

			$col_5_val_cal_total = 0;

			// get amount of votes recieved
			$all_votes = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = {$single_poll->ID} AND meta_key LIKE '%user\\_%'");
				if( count($all_votes) == 0 ){ continue;  }

			// add title
			fputcsv($output, array( 
				$single_poll->post_title, 
				'',   
				'',   
				'',
				) );

				$poll_variants = (array) get_post_meta( $single_poll->ID, 'poll_variants', true );

				$pols_count = count( $poll_variants );

				
				$all_results = [];
				$all_votes = [];
				$total_votes_count = 0;
				$totla_count = 0;

				for( $i=0; $i<$pols_count; $i++ ){
					$total_votes = $wpdb->get_col("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$single_poll->ID} AND meta_key LIKE 'user\\_%' AND  meta_value = {$i} AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$single_poll->ID} AND meta_key LIKE 'user_%_ip%' AND  meta_value = {$i}  )");

					$total_votes = array_unique( $total_votes );
					$all_votes[$i] = count( $total_votes );

					$total_votes_count = $total_votes_count + count( $total_votes );
				
					if( count( $total_votes ) > 0 )
					foreach( $total_votes as $s_vote ){
						$tmp = explode('_', $s_vote);
						$user_shares = get_own_shares( $tmp[1]  ); 
						$total_shares = get_total_shares( $tmp[1] );  
						$all_results[$i] = $all_results[$i] + $user_shares + $total_shares;
					}
					$totla_count = $totla_count + $all_results[$i];
				}

				if( count($poll_variants) > 0 ){
					$cnt = 0;
					foreach( $poll_variants as $s_var ){

						// count percentage 
						if( !$totla_count || $totla_count  == 0 ){
							$percent = 0;
						}else{
							$percent = $all_results[$cnt]*100 / $totla_count;
						}
						
						$out .= '
						<tr>';
						if( $columns_data['col_1'] == 'on' ){
						$out .= '
							<td class=""><h6>'.$s_var.'</h6>
							<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="'.$percent.'" style="width: '.$percent.'%"></div>
							</div>
							</td>';
						}
						if( $columns_data['col_2'] == 'on' ){
						$out .= '
							<td class="text-center">'.$all_votes[$cnt].'</td>';
						}
						if( $columns_data['col_3'] == 'on' ){
							$out .= '
							<td class="text-center">'.$all_results[$cnt].'</td>';
						}
						if( $columns_data['col_4'] == 'on' ){
							$out .= '
							<td class="text-center">'.number_format( $percent, 2 ).'</td>';
						}
						if( $columns_data['col_5'] == 'on' ){
							$user_closed_times = get_post_meta( $single_poll->ID, 'vote_close_time', true );
							$last_quorum = 0;
							//foreach( $user_closed_times as $index => $value ){
								$last_quorum = $user_closed_times['shares'];
							//}

							
							if( $last_quorum == 0 ){
								$col_5_val_cal = 0;
							}else{
								$col_5_val_cal = ($all_results[$cnt]*100) / $last_quorum;
							}
							$col_5_val_cal_total = $col_5_val_cal_total + $col_5_val_cal;
						}
							$out .= '
						</tr>';
						fputcsv($output, array( 
							$s_var, 
							$all_votes[$cnt],   
							$all_results[$cnt],   
							number_format( $percent, 2 ),
							$col_5_val_cal
							) );
						$cnt++;
					}
				}

				fputcsv($output, array( 
					__('Total','wvp'), 
				
					$total_votes_count,
					number_format( $totla_count, 2 ),
					'100%',
					number_format( $col_5_val_cal_total, 2 )
				));
				
			$all_times  = (array)get_post_meta( $single_poll->ID,  'vote_close_time', true );
			if( $columns_poll_open_stats_data['col_1'] == 'on' ){
				fputcsv($output, array( 
					__('Poll was closed at:','wvp').' '.date( 'Y/m/d H:i:s', (int)$all_times['time'] ), 
					
					'',
					'',
					''
				));
			}

			// add totals
			

			if( $columns_poll_open_stats_data['col_2'] == 'on' ){
			fputcsv($output, array( 
				__('Quorum at the closing time','wvp').' '.$all_times['shares'], 
			 
				'',
				'',
				''
			));
			}


			$all_users = get_users();	
			$total_shares_count = 0; 
			$bodycount = 0;
			foreach( $all_users as $s_user ){
				$user_vote = get_post_meta( $single_poll->ID, 'user_'.$s_user->ID, true );

				$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
				
				$val_a = get_own_shares( $s_user->ID  ); 
				$val_b = get_total_shares( $s_user->ID );

				$val_a_b = $val_a + $val_b;

				$user_answer =  get_post_meta( $single_poll->ID, 'user_'.$s_user->ID, true );
				$poll_type =  get_post_meta( $single_poll->ID, 'poll_type', true );
			 
				if( $poll_type == 'multi' ){
					$all_votes = get_post_meta( $single_poll->ID, 'user_'.$s_user->ID );
					$out_votes = [];
					foreach( $all_votes as $s_vote ){
						if( $s_vote == '' ){ continue; }
						$total_results[$s_vote] = (float)$total_results[$s_vote] + $val_a_b;
						$total_shares_count = $total_shares_count + $val_a_b;
					}
				}else{
					if( $user_answer == '' ){ continue; }
					$total_results[$user_answer] = (float)$total_results[$user_answer] + $val_a_b;
					$total_shares_count = $total_shares_count + $val_a_b;
				}
				
				//prefind if user voted

				$user_vote = get_post_meta( $single_poll->ID, 'user_'.$s_user->ID, true );
		 
				if(  $user_vote != '' ){
					$bodycount++;
				}else{
					continue;
				}
			}

			if( $columns_poll_open_stats_data['col_3'] == 'on' ){
				fputcsv($output, array( 
					__('Total users that answered this poll','wvp').' '.$bodycount,
					'',
					'',
					''
				));
			}

			if( $columns_poll_open_stats_data['col_4'] == 'on' ){
				fputcsv($output, array( 
					__('Total own shares + shares from proxies of users that voted in this poll:','wvp').' '.$total_shares_count, 
				
					'',
					'',
					''
				));
			}

			/*
			$out .= '
			<tr>';
			if( $columns_data['col_1'] == 'on' || $all ){
				$out .= '<td class="" '.$align_patch.'>'.__('Total', 'wvp').'</td>';
			}
			if( $columns_data['col_2'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>'.$total_votes_count.'</td>';
			}
			if( $columns_data['col_3'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>'.number_format( $totla_count, 2 ).'</td>';
			}
			if( $columns_data['col_4'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>100.00%</td>';
			}
			if( $columns_data['col_5'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>'.number_format( $col_5_val_cal_total, 2 ).'</td>';
			}
			$out .= '
			</tr>';
			*/

			


			$not_voted_cont = 0;
			$all_users = get_users();
			$total_results = [];
			$not_voted_shares = 0;
			foreach( $all_users as $s_user ){

				// show only can vote users
				$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
				if( $user_can_vote != '1' ){ continue; }

		

				$val_a = get_own_shares( $s_user->ID  ); 
				$val_b = get_total_shares( $s_user->ID );

				$val_a_b = $val_a + $val_b;

				$user_answer =  get_post_meta( $single_poll->ID, 'user_'.$s_user->ID, true );
				$poll_type =  get_post_meta( $single_poll->ID, 'poll_type', true );
			
				if( $poll_type == 'multi' ){
					$all_votes = get_post_meta( $single_poll->ID, 'user_'.$s_user->ID );
					$out_votes = [];
					foreach( $all_votes as $s_vote ){
						$total_results[$s_vote] = (float)$total_results[$s_vote] + $val_a_b;
					}

					
				}else{
					$total_results[$user_answer] = (float)$total_results[$user_answer] + $val_a_b;
				}

				//prefind if user voted

				$user_vote = get_post_meta( $single_poll->ID, 'user_'.$s_user->ID, true );


				if(  $user_vote != '' ){
					//$visible_class = ' ';
					continue;
				}else{
					//$visible_class = 'd-none not-voted';
					
				}
				$not_voted_cont++;
				
				$not_voted_shares = $not_voted_shares + $val_a_b;
			}

			if( $columns_poll_open_stats_data['col_5'] == 'on' ){
				fputcsv($output, array( 
					__("Shares that didn't vote",'wvp').' '.$not_voted_shares, 
					'', 
					'', 
					''
				));
			}
			fputcsv($output, array( 
				'', 
				'', 
				'', 
				''
			));
		}
		fputcsv($output, array( 
			'', 
			'', 
			'', 
			''
		));
		

		foreach( $all_login_users as $s_user ){
			$userdata = get_user_by( 'ID', $s_user );
			$total_shares = get_own_shares( $s_user ) + get_total_shares( $s_user );
		 
			fputcsv($output, array( 
				$userdata->first_name, 
				$userdata->last_name,   
				$userdata->user_login,   
				$total_shares  
				) );
		}

		fclose($output);
		die();	

	}


	if( isset( $_GET['action'] ) )
	if( $_GET['action'] == 'save_pdf_data_block' ){
		$settings = get_option( 'wsp_options' );
 
		// reference the Dompdf namespace
		$upload_dir = wp_upload_dir();

		$dompdf = new DOMPDF();


		$args = array(
			'post_type' => 'poll',
			//'fields' => 'ids',
			'showposts' => -1
		);
		$all_polls = get_posts( $args );

		$ids2use = [];
		foreach( $all_polls as $single_poll ){
			// get amount of votes recieved
			$all_votes = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = {$single_poll->ID} AND meta_key LIKE '%user\\_%'");
			if( count($all_votes) == 0 ){ continue;  }

			$ids2use[] = $single_poll->ID;
		}
	
		 

		//$html = do_shortcode('[showallpollsanswers all="1"]');
		$html = do_shortcode('[showallpollsanswers ispdf="1"  ids="'.implode(',', $ids2use).'"]');

		$b_path = $upload_dir['basedir'];
		$b_url = $upload_dir['baseurl'];

	 
		$html = str_replace($b_url, $b_path, $html);

		$dompdf->load_html($html);
		$dompdf->render();

		$output = $dompdf->output();
    

		
		$file_path = $upload_dir['basedir'].'/report.pdf';
		$file_url = $upload_dir['baseurl'].'/report.pdf';
 
		file_put_contents($file_path, $output);

 
		header('Content-Type: application/pdf');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=report.pdf" );
		readfile($file_url);
	}
});


// add user profile settings
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );
add_action( 'user_new_form', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { 
	global $wpdb;
  ?>

	<h3><?php echo __('Extra profile information', 'wvp'); ?></h3>

	<table class="form-table">

		<tr>
			<th><label for="twitter"><?php echo __('Can Vote', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="can_vote" id="can_vote" value="<?php echo esc_attr( get_the_author_meta( 'can_vote', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>

		<tr>
			<th><label for="twitter"><?php echo __('Own Shares (For decimals use . )', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="own_shares" id="own_shares" value="<?php echo esc_attr( get_the_author_meta( 'own_shares', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Proxys amount (For decimals use . )', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="proxys_amount" id="proxys_amount" value="<?php echo esc_attr( get_the_author_meta( 'proxys_amount', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Total shares of received Proxys (For decimals use . )', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="total_shares" id="cantotal_shares_vote" value="<?php echo esc_attr( get_the_author_meta( 'total_shares', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Password to send ', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="password_to_send" id="password_to_send" value="<?php echo esc_attr( get_the_author_meta( 'password_to_send', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Additional emails for sending messages', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="additional_emails" id="additional_emails" value="<?php echo esc_attr( get_the_author_meta( 'additional_emails', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>

		<!-- v1.15 patch -->
		<tr>
			<th><label for="twitter"><?php echo __('Custom 1', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="custom1" id="custom1" value="<?php echo esc_attr( get_the_author_meta( 'custom1', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Custom 2', 'wvp'); ?></label></th>
			<td>
				<input type="text" name="custom2" id="custom2" value="<?php echo esc_attr( get_the_author_meta( 'custom2', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Disable login in this website to this user', 'wvp'); ?></label></th>
			<td>
				<input type="checkbox" name="disable_login" id="disable_login" value="on" <?php if( get_the_author_meta( 'disable_login', $user->ID )  == 'on' ) echo ' checked ';  ?> class="regular-text" /><br />
			</td>
		</tr>

		

		<tr>
			<th><label for="twitter"><?php echo __('User Files', 'wvp'); ?></label></th>
			<td>
				<ul>
				<?php 
				$upload_dir = wp_upload_dir();
				$files_path = $upload_dir['basedir'].'/regform/'.$user->ID;
				$files_url = $upload_dir['baseurl'].'/regform/'.$user->ID;

				for( $i=1; $i<=5; $i++ ){
					echo '<li><a download href="'.$files_url.'/'.get_user_meta( $user->ID, 'attach_'.$i,  true ).'">'.get_user_meta( $user->ID, 'attach_'.$i,  true ).'</a></li>';
				}
				
				?>


				</ul>
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('User Message', 'wvp'); ?></label></th>
			<td>
				<textarea rows="10" name="input_message" id="input_message"   class="regular-text" ><?php echo esc_attr( get_user_meta( $user->ID, 'input_message', true  ) ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Checkbox 1', 'wvp'); ?></label></th>
			<td>
				<?php 
				echo get_user_meta( $user->ID, 'input_check1', true  );
				?>
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Checkbox 2', 'wvp'); ?></label></th>
			<td>
				<?php 
				echo get_user_meta( $user->ID, 'input_check2', true  );
				?>
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Select 1', 'wvp'); ?></label></th>
			<td>
				<?php 
				echo get_user_meta( $user->ID, 'input_select1', true  );
				?>
			</td>
		</tr>
		<tr>
			<th><label for="twitter"><?php echo __('Select 2', 'wvp'); ?></label></th>
			<td>
				<?php 
				echo get_user_meta( $user->ID, 'input_select2', true  );
				?>
			</td>
		</tr>

		<!-- v1.15 patch END -->

	</table>

	<h3><?php echo __('User polls info', 'wvp'); ?></h3>

	<table class="form-table">

		<tr>
		 
			<td>
				<table>
					<thead>
		 				<tr>
						<th><?php _e('Poll Name', 'wvp'); ?></th>
						<th><?php _e('Poll Category', 'wvp'); ?></th>
						<th><?php _e('User Answer', 'wvp'); ?></th>
						<th><?php _e('Date/time of the answer', 'wvp'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
				 
						$all_polls = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'user_{$user->ID}' ");

						foreach( $all_polls as $s_poll ){
							$poll_info = get_post( $s_poll->post_id );
							$all_cats = [];
							$poll_cats = wp_get_post_terms( $s_poll->post_id, 'poll_category' );

							$poll_variants = get_post_meta( $s_poll->post_id, 'poll_variants', true );
						
							$poll_type = get_post_meta( $s_poll->post_id, 'poll_type', true );

							$out_votes = [];
							
							if( $poll_type == 'multi' ){
								$all_votes = get_post_meta( $s_poll->post_id, 'user_'.$user->ID );
								
								foreach( $all_votes as $s_vote ){
									$out_votes[] = $poll_variants[$s_vote];
								}
							}else{
								$user_vote = get_post_meta( $s_poll->post_id, 'user_'.$user->ID, true );
								$out_votes[] = $poll_variants[$user_vote];
							}

							foreach( $poll_cats as $s_cat ){
								$all_cats[] = $s_cat->name;
							}
						 
							$answer_date = get_post_meta( $s_poll->post_id, 'uservotetime_'.$user->ID, true );
						 
							?>
							<tr>
								<td><?php echo $poll_info->post_title; ?></td>
								<td><?php echo implode(',', $all_cats);?></td>
								<td><?php echo implode(',', $out_votes); ?></td>
								<td><?php echo ( $answer_date ?  date('Y-m-d H:i:s', (int)$answer_date) : '' ); ?></td>
							</tr>
							<?php
						}
						?>
						
					</tbody>
				</table>
			</td>
		</tr>

	 

	</table>


	<h3><?php echo __('Send polls plugin email to only this user', 'wvp'); ?></h3>
	<p><?php echo str_replace( '%s', admin_url('edit.php?post_type=poll&page=wvp_emailsend_email'), __('Use this option to send email to this user with content written at SEND EMAIL page. <a href="%s">Click here to see it</a> / edit it.', 'wvp') ); ?></p>

	<table class="form-table">

	 

		<!-- v1.15 patch -->
		<tr>
			<th><label for="twitter"><?php echo __('Choose template', 'wvp'); ?></label></th>
			<td>
				<input type="hidden" id="current_page_user" value="<?php echo $user->ID; ?>" />
				<select name="template_id" id="template_id">
						<?php 
							$all_email_templates = get_posts([
								'showposts' => -1,
								'post_type' => 'email_template'
							]);
							foreach( $all_email_templates as $s_template ){
								?>
								<option value="<?php echo $s_template->ID; ?>" ><?php echo $s_template->post_title; ?>
								<?php
							}
						?>
						
						
				</select>
				
			</td>
			
			
		</tr>
		<tr>
			<td>
				<button type="button"  class="preview_user_email button button-primary"><?php echo __('Preview email', 'wvp'); ?></button>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="email_status_block" class="" style="display:none;">
					<div id="text_part" class=""><b><?php echo __('The following is a preview of the email that will be sent. Please review it and if it is OK, click on the SEND button below', 'wvp'); ?></b></div><br/>
					<div id="content_part" class=""></div>	
					<div id="send_part" class=""><button  type="button" class="send_user_email button button-primary"><?php echo __('Send email', 'wvp'); ?></button></div>	
				</div>
			</td>
		</tr>
		 
		<!-- v1.15 patch END -->

	</table>

<?php }
 

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );
add_action( 'user_register', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {
 
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	 
	update_user_meta( $user_id, 'can_vote', (int)$_POST['can_vote'] );
	update_user_meta( $user_id, 'own_shares', str_replace(',', '.', $_POST['own_shares'] ) );
	update_user_meta( $user_id, 'proxys_amount', str_replace(',', '.', $_POST['proxys_amount'] ) );
	update_user_meta( $user_id, 'total_shares', str_replace(',', '.', $_POST['total_shares'] ) );
	update_user_meta( $user_id, 'password_to_send', str_replace(',', '.', $_POST['password_to_send'] ) );
	update_user_meta( $user_id, 'additional_emails',  $_POST['additional_emails']   );
	update_user_meta( $user_id, 'custom1',  sanitize_text_field( $_POST['custom1'] ) );
	update_user_meta( $user_id, 'custom2',  sanitize_text_field( $_POST['custom2'] ) );
	update_user_meta( $user_id, 'disable_login',  sanitize_text_field( $_POST['disable_login'] ) );
	update_user_meta( $user_id, 'user_message',  sanitize_text_field( $_POST['user_message'] ) );
}




///###############  Duplicate FN
add_action( 'admin_action_rd_duplicate_post_as_draft', 'wvp_duplicate_post_as_draft' );
function wvp_duplicate_post_as_draft(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
	}
 
	/*
	 * Nonce verification
	 */
	if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
		return;
 
	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );
 
	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
 
	/*
	 * if post data exists, create the post duplicate
	 */
	if (isset( $post ) && $post != null) {
 
		/*
		 * new post data array
		 */
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'draft',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
 
		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );
 
		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		update_post_meta( $new_post_id, 'poll_variants', get_post_meta( $post_id, 'poll_variants', true ) );
		update_post_meta( $new_post_id, 'after_vote_message', get_post_meta( $post_id, 'after_vote_message', true ) );
		update_post_meta( $new_post_id, 'emails', get_post_meta( $post_id, 'emails', true ) );

		update_post_meta( $new_post_id, 'post_type', get_post_meta( $post_id, 'post_type', true ) );
		update_post_meta( $new_post_id, 'min_variants', get_post_meta( $post_id, 'min_variants', true ) );
		update_post_meta( $new_post_id, 'max_variants', get_post_meta( $post_id, 'max_variants', true ) );

		/*
		 * duplicate all post meta just in two SQL queries
		 */
		/*
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
	 
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
		 
				$meta_key = $meta_info->meta_key;
				if( $meta_key == '_wp_old_slug' ) continue;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			  
			$wpdb->query($sql_query);
			
			$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_value IS NULL AND post_id =  $new_post_id ");
		}
 
		// drop custom fields
		delete_post_meta( $new_post_id, 'poll_variants' );
		*/
 
		/*
		 * finally, redirect to the edit post screen for the new draft
		 */
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}

 
 
add_filter( 'post_row_actions', 'wvp_duplicate_post_link', 10, 2 );
function wvp_duplicate_post_link( $actions, $post ) {
	if( $post ->post_type == 'poll' ){
		if (current_user_can('edit_posts')) {
			$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=rd_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
		}
	}
	return $actions;
}
 

// table csv export
add_action( 'init', function(){


	// import polls from csv

	if( isset( $_FILES['csv_questions_import'] ) && $_FILES['csv_questions_import']["name"] != '' ){
	 
		if( isset( $_FILES['csv_questions_import']["tmp_name"] ) ){
			$row = 1;
			if (($handle = fopen( $_FILES['csv_questions_import']["tmp_name"], "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					 
					$row++;
					
					//insert poll
					$new_id = wp_insert_post([
						'post_type' => 'poll',
						'post_status' => 'publish',
						'post_title' => $data[0]
					]);

					if( $data[1] == '1' ){
						update_post_meta( $new_id, 'poll_type', 'single' );
					}
					if( $data[1] == '2' ){
						update_post_meta( $new_id, 'poll_type', 'multi' );
					}
					if( $data[2] != '' ){
						update_post_meta( $new_id, 'min_variants', (int)$data[2] );
					}
					if( $data[3] != '' ){
						update_post_meta( $new_id, 'max_variants', (int)$data[3] );
					}
					$all_answers = [];
					for( $z=4; $z<= 13; $z++ ){
						if( $data[$z] != '' ){
							$all_answers[] = $data[$z];
						}
					}
					update_post_meta( $new_id, 'poll_variants', $all_answers );
				}
				fclose($handle);
			}	
		}
		delete_option('wvp_import_options');
		wp_redirect( admin_url( 'edit.php?post_type=poll&page=wvp_importimport_polls&msg=22' ), 302 );
		exit();
	}

	// import polls questions
	if( isset( $_POST['add_questions'] ) && $_POST['add_questions'] != '' ){
		$all_questions = explode( "\n", stripslashes( $_POST['add_questions'] ) );
		$all_questions = array_filter( $all_questions );
 
		if( count($all_questions) > 0 ){
			foreach( $all_questions as $s_question ){
				$settings = get_option('wvp_variants_options');
				$default_answer_variants = $settings['default_answer_variants'];
						
				$rows = explode("\n", $default_answer_variants);
				$poll_variants = array_filter( $rows );

			 
					$new_id = wp_insert_post([
						'post_type' => 'poll',
						'post_status' => 'publish',
						'post_title' => $s_question
					]);
					update_post_meta( $new_id, 'poll_variants', $poll_variants );
					update_post_meta( $new_id, 'poll_type', 'single' );

					update_post_meta( $new_id, 'default_submit_message', stripslashes( $settings['default_submit_message'] ) );
				 
					update_post_meta( $new_id, 'emails', $settings['wordpress_default_emails'] );
			 
				
				
			}
		}

		delete_option('wvp_import_options');
		wp_redirect( admin_url( 'edit.php?post_type=poll&page=wvp_importimport_polls&msg=11' ), 302 );
		exit();
	}

	// export assignation of proxies
	if( isset( $_GET['action'] ) )
	if( $_GET['action'] == 'export_proxy_data' ){
		global $wpdb;
		
		header("Content-type: text/csv");		
		header("Content-Disposition: attachment; filename=proxy_assign_data_".sanitize_title(  date('Y-m-d-H-i') ).".csv");
	 	header("Pragma: no-cache");
		header("Expires: 0");

		// check if tmp dir exists
		$all_users = get_users();
		 
		$output = fopen("php://output", "w");

		fputcsv($output, array( 
			__('User that gave proxy', 'wvp'),
			__('User that received the proxy', 'wvp'),
			__('Date of action', 'wvp'),
			__('Given shares', 'wvp'),
			
			__('Proxy was given by user or admin?', 'wvp'),
			__('Message', 'wvp'),
			__('Is external', 'wvp'),
			) );
 
		$all_users_that_have_assigns = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'assign_date' AND meta_value != '1' " );
		foreach( $all_users_that_have_assigns as $s_user ){
			$sender_data = get_user_by('ID', $s_user);
			$acceptor_data = get_user_by('ID', get_user_meta( $s_user, 'donate_recipient', true )  );
			fputcsv($output, array( 
				$sender_data->first_name.' - '.$sender_data->last_name.' - '.$sender_data->user_login, 
				$acceptor_data->first_name.' - '.$acceptor_data->last_name.' - '.$acceptor_data->user_login,   
				date( 'Y/m/d H:i', (int)get_user_meta( $s_user, 'assign_date', true ) ),
				get_user_meta( $s_user, 'own_shares_old', true ),
				get_user_meta( $s_user, 'assign_type', true ),
				stripslashes( get_user_meta( $s_user, 'sendproxy_comment', true ) ),
				( get_user_meta( $s_user, 'is_external', true ) == '1' ? __('Yes', 'wvp') : __('No', 'wvp') )

			));
		}		

		fclose($output);	 
		die();
		###########
		 
	}

	// export all users
	if( isset( $_GET['export_type'] ) )
	if( $_GET['export_type'] == 'userdata' ){
		global $wpdb;
		
		header("Content-type: text/csv");		
		header("Content-Disposition: attachment; filename=users_list_".sanitize_title(  date('Y-m-d-H-i') ).".csv");
	 	header("Pragma: no-cache");
		header("Expires: 0");

		// check if tmp dir exists
		$all_users = get_users();
		 
		$output = fopen("php://output", "w");

		fputcsv($output, array( 
			__('Username', 'wvp'), 
			__('Name', 'wvp'),   
			__('Last Name', 'wvp'),   
			__('Email', 'wvp'),    
			__('password_to_send', 'wvp'),   
			__('Can Vote', 'wvp'),   
			__('Own Shares', 'wvp'),   
			__('Proxys amount', 'wvp'),   
			__('Total shares of received Proxys', 'wvp'),   
			__('Poll Names', 'wvp'),   
			__('Proxies received names', 'wvp'),   
			__('Proxies received last names', 'wvp'),   
			__('Additional emails', 'wvp'),   

			__('User category', 'wvp'),   
			__('Custom field 1', 'wvp'),   
			__('Custom field 2', 'wvp'),   
			__('Message', 'wvp'),   
			__('select menu 1', 'wvp'),   
			__('select menu 2', 'wvp'),   
			__('attachement 1 url', 'wvp'),   
			__('attachement 2 url', 'wvp'),   
			__('attachement 3 url', 'wvp'),   
			__('attachement 4 url', 'wvp'),   
			__('attachement 5 url', 'wvp'),   
			
			) );
	 
		foreach( $all_users as $s_user ){

			/*  1.18 patch */
			$user_first_name = [];
				$user_last_name = [];
				$all_rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}usermeta WHERE meta_key = 'donate_recipient' AND meta_value = {$s_user->ID}");
 
				foreach( $all_rows as $s_row ){
					$user_data = get_user_by('ID', $s_row->user_id);
					$user_first_name[] = $user_data->first_name;
					$user_last_name[] = $user_data->last_name;
				}
				$user_first_name = array_filter( $user_first_name );
				$user_last_name = array_filter( $user_last_name );
			/*  1.18 patch END */

			// getting all polls
			$all_polls = $wpdb->get_col("SELECT DISTINCT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'user_".$s_user->ID."'");

			$all_polls_name = [];
			foreach( $all_polls as $s_poll ){
				$all_polls_name[] = get_post( $s_poll )->post_title;
			}


			/** v2.0.34  */

	 
			$upload_dir = wp_upload_dir();
			$files_path = $upload_dir['basedir'].'/regform/'.$s_user->ID;
			$files_url = $upload_dir['baseurl'].'/regform/'.$s_user->ID;

			$selected_cats = get_user_meta( $s_user->ID, USER_CATEGORY_NAME_META_KEY, true );
			$custom1 = get_user_meta( $s_user->ID, 'custom1', true );
			$custom2 = get_user_meta( $s_user->ID, 'custom2', true );
			$message = get_user_meta( $s_user->ID, 'input_message', true );
			$input_select1 = get_user_meta( $s_user->ID, 'input_select1', true );
			$input_select2 = get_user_meta( $s_user->ID, 'input_select2', true );

			if( get_user_meta( $s_user->ID, 'attach_1',  true ) ){
				$attach_1 = $files_url.'/'.get_user_meta( $s_user->ID, 'attach_1',  true );
			}else{
				$attach_1 = '';
			}
			if( get_user_meta( $s_user->ID, 'attach_2',  true ) ){
				$attach_2 = $files_url.'/'.get_user_meta( $s_user->ID, 'attach_2',  true );
			}else{
				$attach_2 = '';
			}
			if( get_user_meta( $s_user->ID, 'attach_3',  true ) ){
				$attach_3 = $files_url.'/'.get_user_meta( $s_user->ID, 'attach_3',  true );
			}else{
				$attach_3 = '';
			}
			if( get_user_meta( $s_user->ID, 'attach_4',  true ) ){
				$attach_4 = $files_url.'/'.get_user_meta( $s_user->ID, 'attach_4',  true );
			}else{
				$attach_4 = '';
			}
			if( get_user_meta( $s_user->ID, 'attach_5',  true ) ){
				$attach_5 = $files_url.'/'.get_user_meta( $s_user->ID, 'attach_5',  true );
			}else{
				$attach_5 = '';
			}
		 
			/** v2.0.34  END */

			fputcsv($output, array( 
				$s_user->user_login, 
				$s_user->first_name,   
				$s_user->last_name,   
				$s_user->user_email,    
				get_user_meta( $s_user->ID, 'password_to_send', true ),   
				get_user_meta( $s_user->ID, 'can_vote', true ),   
				get_own_shares($s_user->ID),   
				get_proxys_amount($s_user->ID),   
				get_total_shares($s_user->ID),  
				implode( ', ', $all_polls_name ),   
				implode( ', ', $user_first_name ),   
				implode( ', ', $user_last_name ),
				get_the_author_meta( 'additional_emails', $s_user->ID ),

				$selected_cats,
				$custom1,
				$custom2,
				$message,
				$input_select1,
				$input_select2,
				$attach_1,
				$attach_2,
				$attach_3,
				$attach_4,
				$attach_5,

				) );
		}

		fclose($output);	 
		die();
		###########
		 
	}

	// export all answers
	if( isset( $_GET['export_type'] ) )
	if( $_GET['export_type'] == 'full_answers' ){
		global $wpdb;
 
		// check if tmp dir exists

		$upload_dir = wp_upload_dir();
		$tmp_path = $upload_dir['basedir'].'/mass_poll_export';
		if( !is_dir( $tmp_path ) ){
			wp_mkdir_p( $tmp_path );
		}

		$zip_path = $upload_dir['basedir'].'/zip';
		if( !is_dir( $zip_path ) ){
			wp_mkdir_p( $zip_path );
		}

		// delete all fiels
		$files = glob( $tmp_path.'/*'); // get all file names
		foreach($files as $file){ // iterate files
		if(is_file($file))
			unlink($file); // delete file
		}

		// getting all posts
		$args = array(
			'showposts' => -1,
			'post_type' => 'poll'
		);

		// categories
		if( isset( $_GET['poll_category'] ) &&  $_GET['poll_category'] != 'all' ){
			 
			$args['tax_query'] = array(
			array(
				'taxonomy' => 'poll_category',
				'field' => 'term_id',
				'terms' => $_GET['poll_category']
				)
			);
		}

		$all_polls = get_posts( $args );
		

		foreach( $all_polls as $s_poll ){
			$post_id = $s_poll->ID;

			
			$all_votes = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE '%user\\_%'");
	 
			if( count( $all_votes ) == 0){ continue; }

			

			$fields_array = array( 
				__('Username', 'wvp'), 
				__('First name', 'wvp'),   
				__('Last Name', 'wvp'),   
				__('Own Shares (A)', 'wvp'),   
				__('Proxys amount received by user', 'wvp'),   
				__('Total shares of received proxys (B)', 'wvp'),   
				__('Total Shares (A + B)', 'wvp'),   
				__('Answer given by user', 'wvp'),   
				__('Date-time of answer', 'wvp'),   
				__('User IP', 'wvp'),   
				__('User Browser', 'wvp'),   
				__('User Message', 'wvp'),   
				__('User Category', 'wvp'),   
			);

			$fields_eng = array( 
				'Username', 
				'First name', 
				'Last Name',
				'Own Shares (A)',
				'Proxys amount received by user',
				'Total shares of received proxys (B)',
				'Total Shares (A + B)', 
				'Answer given by user', 
				'Date-time of answer', 
				'User IP',
				'User Browser', 
				'User Message', 
				'User Category', 
		   );
	 

			/*
			var_dump( $post_id );
			var_dump( $all_votes );
			var_dump( count( $all_votes) );
			*/
		 
			$fielname = $tmp_path.'/poll_'.sanitize_title( get_post( $post_id )->post_title ).'.csv';

			$output = fopen( $fielname, "w");
	 
			// generate title
			$title_array = [];
			$inner_cnt = 0;
			foreach( $fields_array as $s_feild ){
				$patched_name = sanitize_title( $fields_eng[$inner_cnt] );
		 
				if( in_array( $patched_name, (array)$_GET['include_field'] ) ){
					$title_array[] = $s_feild;
				}
				$inner_cnt++;
			}

			fputcsv($output, $title_array );

			/* 
			if( $_GET['include_answers'] == 'yes' ){
				fputcsv($output, array( 
					__('Username', 'wvp'), 
					__('First name', 'wvp'),   
					__('Last Name', 'wvp'),   
					__('Own Shares (A)', 'wvp'),   
					__('Proxys amount received by user', 'wvp'),   
					__('Total shares of received proxys (B)', 'wvp'),   
					__('Total Shares (A + B)', 'wvp'),   
					__('Answer given by user', 'wvp'),   
					__('Date-time of answer', 'wvp'),   
					__('User IP', 'wvp'),   
					__('User Browser', 'wvp'),   
					__('User Message', 'wvp'),   
				));
			}else{
				fputcsv($output, array( 
					__('Username', 'wvp'), 
					__('First name', 'wvp'),   
					__('Last Name', 'wvp'),   
					__('Own Shares (A)', 'wvp'),   
					__('Proxys amount received by user', 'wvp'),   
					__('Total shares of received proxys (B)', 'wvp'),   
					__('Total Shares (A + B)', 'wvp'),   
			 
					__('Date-time of answer', 'wvp'),   
					__('User IP', 'wvp'),   
					__('User Browser', 'wvp'),   
					__('User Message', 'wvp'),   
				));
			}
			*/
			
			$added_entries = 0;
			$all_users = get_users();
			foreach( $all_users as $s_user ){
				
				$user_vote = get_post_meta( $post_id, 'user_'.$s_user->ID, true );
				$selected_cats = get_user_meta( $s_user->ID, USER_CATEGORY_NAME_META_KEY, true );
				
							if(  $user_vote != '' ){
								//$visible_class = ' ';
								
							}else{
								//$visible_class = 'd-none not-voted';
								continue;
							}

				// show only can vote users
				$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
				if( $user_can_vote != '1' ){ continue; }

				$own_shares = get_own_shares( $s_user->ID  );  
				$total_shares = get_total_shares( $s_user->ID );  

				$val_a = $own_shares;
				$val_b = $total_shares;

				$val_a_b = $val_a + $val_b;

				$user_answer =  get_post_meta( $post_id, 'user_'.$s_user->ID, true );
				$poll_variants =  get_post_meta( $post_id, 'poll_variants' , true );
				$poll_type =  get_post_meta( $post_id, 'poll_type' , true );
			
				$user_browser = get_post_meta( $post_id, 'browser_'.$s_user->ID, true );

				$vote_date = date( 'Y/m/d H:i:s', (int)get_post_meta( $post_id, 'uservotetime_'.$s_user->ID, true ) );
	

				// patch for multipol
				if( $poll_type == 'multi' ){
			 
					$all_votes = get_post_meta( $post_id, 'user_'.$s_user->ID );
					$out_votes = [];
					foreach( $all_votes as $s_vote ){
						$out_votes[] = $poll_variants[$s_vote];

					}
				
					$vote_text =  implode( ', ', $out_votes);
				}else{
					$vote_text =  ( isset($poll_variants[$user_answer]) && $poll_variants[$user_answer] != ''  ? $poll_variants[$user_answer] : ' - ' ) ;
				}

				$user_ip = $wpdb->get_var("SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'user\\_{$s_user->ID}\\_ip%'");
				$user_ip = explode("ip", $user_ip);
				$user_ip = $user_ip[1];
				$message = get_post_meta( $post_id, 'usermessage_'.$s_user->ID, true );

				$added_entries++;
				
				/*
				if( $_GET['include_answers'] == 'yes' ){
				fputcsv($output, 
				array( 
					$s_user->user_login, 
					$s_user->first_name, 
					$s_user->last_name, 
					$val_a, 
					get_proxys_amount( $s_user->ID ),
					$val_b, 
					$val_a_b, 
					$vote_text,
					$vote_date,
					$user_ip,
					$user_browser,
					$message
					));
				}else{
					fputcsv($output, 
				array( 
					$s_user->user_login, 
					$s_user->first_name, 
					$s_user->last_name, 
					$val_a, 
					get_proxys_amount( $s_user->ID ),
					$val_b, 
					$val_a_b, 
				 
					$vote_date,
					$user_ip,
					$user_browser,
					$message
					));
				}
				*/
				
				$value_array = [];
				$inner_cnt = 0;
				foreach( $fields_array as $s_feild ){
					$patched_name = sanitize_title( $fields_eng[$inner_cnt] );
					$inner_cnt++;

					if( in_array( $patched_name, (array)$_GET['include_field'] )   ){
						
						switch(  $patched_name ){
							case "username":
								$value_array[] = $s_user->user_login;
							break;
							case "first-name":
								$value_array[] = $s_user->first_name;
							break;
							case "last-name":
								$value_array[] = $s_user->last_name;
							break;
							case "own-shares-a":
								$value_array[] = $val_a;
							break;
							case "proxys-amount-received-by-user":
								$value_array[] = get_proxys_amount( $s_user->ID );
							break;
							case "total-shares-of-received-proxys-b":
								$value_array[] = $val_b;
							break;
							case "total-shares-a-b":
								$value_array[] = $val_a_b;
							break;
							case "answer-given-by-user":
								$value_array[] = $vote_text;
							break;
							case "date-time-of-answer":
								$value_array[] = $vote_date;
							break;
							case "user-ip":
								$value_array[] = $user_ip;
							break;
							case "user-browser":
								$value_array[] = $user_browser;
							break;
							case "user-message":
								$value_array[] = $message;
							break;
							case "user-category":
								$value_array[] = $selected_cats;
							break;
						}

						
					}

				}
				
				fputcsv($output,  $value_array );
			}

			

			
			fclose($output);

			if( $added_entries == 0 ){
				unlink($fielname);
			}

			// putput user answers


			


		}// polls llop
		// make zip
		$zip = new ZipArchive;

		// Get real path for our folder
		$rootPath = realpath( $tmp_path  );
	 
		unlink( $zip_path.'/poll_archive.zip' );

		$zip->open( $zip_path.'/poll_archive.zip', ZipArchive::CREATE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($rootPath),
		RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file){
		// Skip directories (they would be added automatically)
		if (!$file->isDir()){
			// Get real and relative path for current file
			$filePath = $file->getRealPath();
			$relativePath = substr($filePath, strlen($rootPath) + 1);
			// Add current file to archive
			$zip->addFile($filePath, $relativePath);
		}
		}

		// Zip archive will be created only after closing object
		$zip->close();
 
		$file_zip_path = $zip_path.'/poll_archive.zip';
	 
		header("Content-Type: application/zip");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize( $poll_archive ));
		header("Content-Disposition: attachment; filename=\"".basename( $file_zip_path )."\"");
		readfile($file_zip_path);
	 
		exit;
		

		
				 


		###########
		 
	}


	// export cuser quorum
	if( isset( $_GET['export_stats'] ) )
	if( $_GET['export_stats'] == 'true' ){
		global $wpdb;

		$start_date = strtotime( $_GET['user_start_date'] );
		$end_date = strtotime( $_GET['user_end_date'] );

		header("Content-type: text/csv");		
		header("Content-Disposition: attachment; filename=users_".sanitize_file_name(  $start_date )."-".sanitize_file_name(  $end_date ).".csv");
	 	header("Pragma: no-cache");
		header("Expires: 0");

		$table_name = 'user_logins';
		$table_name =  $wpdb->prefix.$table_name;

		$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
		$start_date = strtotime( $_GET['user_start_date'] );
		$end_date = strtotime( $_GET['user_end_date'] );



		$all_login_users = $wpdb->get_col("SELECT DISTINCT user_id   FROM {$table_name} WHERE  date BETWEEN   '{$start_date}' AND '{$end_date}'  AND user_id IN (".implode( ',', $all_users_that_caN_vote ).") " );
		//$all_login_users = $wpdb->get_col("SELECT DISTINCT user_id   FROM {$table_name} WHERE  date BETWEEN   '{$start_date}' AND '{$end_date}'  " );
		//$all_login_users = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'last_login' AND meta_value BETWEEN   {$start_date} AND {$end_date}  AND user_id IN (".implode( ',', $all_users_that_caN_vote ).") " );

		$output = fopen("php://output", "w");

		fputcsv($output, array( 
			__('Name', 'wvp'), 
			__('Last Name', 'wvp'),   
			__('Username', 'wvp'),    
			__('Total Sshares (A+B)', 'wvp'),   
			) );

		foreach( $all_login_users as $s_user ){
			$userdata = get_user_by( 'ID', $s_user );
 
			$total_shares = get_own_shares( $s_user ) + get_total_shares( $s_user );

		 
			fputcsv($output, array( 
				$userdata->first_name, 
				$userdata->last_name,   
				$userdata->user_login,   
				$total_shares  
				) );
		}

		fclose($output);
		die();	
	}

	// export currentusers shortcode
	if( $_GET['exportcsv'] == 'currentusers' ){
		global $wpdb;
		header("Content-type: text/csv");		
		header("Content-Disposition: attachment; filename=user_".sanitize_title(  date('Y-m-d-H-i') ).".csv");
	 	header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "w");

		fputcsv($output, array( 
			__('Name', 'wvp'), 
			__('Last Name', 'wvp'),   
			__('Username', 'wvp'),   
			__('Own Shares (A)', 'wvp'),   
			__('Proxies Received', 'wvp'),   
			__('Shares of proxies received (B)', 'wvp'),   
			__('Total Sshares (A+B)', 'wvp'),   
			__('Current Online Status', 'wvp'),   
			) );
	 

			$settings = get_option('wvp_options');
			if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
				$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
			}
			if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
				$time_limit = strtotime( $settings['from_timestamp'] );
			}
	
			// all who can vote
			$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
	
			// all online users
			$all_online_usrs = $wpdb->get_col("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id  IN ( ".implode( ",", $all_users_that_caN_vote )." )");
		 
	
			$all_offline_users = array_diff( $all_users_that_caN_vote, $all_online_usrs );
		 
			$all_users = get_users();
			$total_results = [];	 
			foreach( $all_users as $s_user ){
				// check if user is online or offline
				if( in_array( $s_user->ID, $all_online_usrs ) ){
					$user_status = 'online';
				}
				if( in_array( $s_user->ID, $all_offline_users ) ){
					$user_status = 'offline';
				}


				// show only can vote users
				$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
				if( $user_can_vote != '1' ){ continue; }

				$own_shares = get_own_shares( $s_user->ID  ); 
				$total_shares = get_total_shares( $s_user->ID ); 
				$proxys_amount = get_proxys_amount( $s_user->ID ); 

				$val_a = $own_shares;
				$val_b = $total_shares;

				$val_a_b = $val_a + $val_b;

				$s_user = get_user_by( 'ID', $s_user->ID );
			 

				fputcsv($output, array( 
					$s_user->first_name, 
					$s_user->last_name,   
					$s_user->user_login,   
					$val_a,   
					$proxys_amount,   
					$val_b,   
					$val_a_b,     
					$user_status,   
					) );
			}
		 
			fclose($output);
			die();	
	}

	if( isset( $_GET['export'] ) )
	if( $_GET['export'] == 'csv_voted' ){
		global $wpdb;
		$post_id = (int)$_GET['post'];
		
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=vote_date_".sanitize_title( get_post( $post_id )->post_title ).".csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		

		$output = fopen("php://output", "w");


		fputcsv($output, array( 
			__('Username', 'wvp'), 
			__('First name', 'wvp'),   
			__('Last Name', 'wvp'),   
			__('Own Shares (A)', 'wvp'),   
			__('Proxys amount received by user', 'wvp'),   
			__('Total shares of received proxys (B)', 'wvp'),   
			__('Total Shares (A + B)', 'wvp'),   
			__('Answer given by user', 'wvp'),   
			__('Date-time of answer', 'wvp'),   
			__('User IP', 'wvp'),   
			__('User Browser', 'wvp'),   
			__('User Message', 'wvp'),   
			__('User Category', 'wvp'),   
			 
			
			) );

		$all_users = get_users();
		foreach( $all_users as $s_user ){
			
			$user_vote = get_post_meta( $post_id, 'user_'.$s_user->ID, true );

			
						if(  $user_vote != '' ){
							//$visible_class = ' ';
							
						}else{
							//$visible_class = 'd-none not-voted';
							continue;
						}

			// show only can vote users
			$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
			//if( $user_can_vote != '1' ){ continue; }

			$own_shares = get_own_shares( $s_user->ID  );  
			$total_shares = get_total_shares( $s_user->ID );  

			$val_a = $own_shares;
			$val_b = $total_shares;

			$val_a_b = $val_a + $val_b;

			$user_answer =  get_post_meta( $post_id, 'user_'.$s_user->ID, true );
			$poll_variants =  get_post_meta( $post_id, 'poll_variants' , true );
			$poll_type =  get_post_meta( $post_id, 'poll_type' , true );
		 
			$user_browser = get_post_meta( $post_id, 'browser_'.$s_user->ID, true );

			$vote_date = date( 'Y/m/d H:i:s', get_post_meta( $post_id, 'uservotetime_'.$s_user->ID, true ) );
 

			// patch for multipol
			if( $poll_type == 'multi' ){
				$all_votes = get_post_meta( $post_id, 'user_'.$s_user->ID );
				$out_votes = [];
				foreach( $all_votes as $s_vote ){
					$out_votes[] = $poll_variants[$s_vote];

				}
			 
				$vote_text =  implode( ', ', $out_votes);
			}else{
				$vote_text =  ( isset($poll_variants[$user_answer]) && $poll_variants[$user_answer] != ''  ? $poll_variants[$user_answer] : ' - ' ) ;
			}

			$user_ip = $wpdb->get_var("SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'user\\_{$s_user->ID}\\_ip%'");
			$user_ip = explode("ip", $user_ip);
			$user_ip = $user_ip[1];
			$message = get_post_meta( $post_id, 'usermessage_'.$s_user->ID, true );

			$selected_cats = get_user_meta( $s_user->ID, USER_CATEGORY_NAME_META_KEY, true );

			fputcsv($output, 
			array( 
				$s_user->user_login, 
				$s_user->first_name, 
				$s_user->last_name, 
				$val_a, 
				get_proxys_amount( $s_user->ID ),
				$val_b, 
				$val_a_b, 
				$vote_text,
				$vote_date,
				$user_ip,
				$user_browser,
				$message,
				$selected_cats
				));
		}

		fclose($output);
		die();				
 
	}
	// export not voted
	if( isset( $_GET['export'] ) )
	if( $_GET['export'] == 'csv_not_voted' ){
		global $wpdb;
		$post_id = (int)$_GET['post'];
		
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=vote_date_".sanitize_title( get_post( $post_id )->post_title ).".csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		

		$output = fopen("php://output", "w");


		fputcsv($output, array( 
			__('Username', 'wvp'), 
			__('First name', 'wvp'),   
			__('Last Name', 'wvp'),   
			__('Own Shares (A)', 'wvp'),   
			__('Proxys amount received by user', 'wvp'),   
			__('Total shares of received proxys (B)', 'wvp'),   
			__('Total Shares (A + B)', 'wvp'),   
			__('Answer given by user', 'wvp'),   
			__('Date-time of answer', 'wvp'),   
			__('User Category', 'wvp'),   
			 
			
			) );

		$all_users = get_users();
		foreach( $all_users as $s_user ){
			
						$user_vote = get_post_meta( $post_id, 'user_'.$s_user->ID, true );

			
						if(  $user_vote != '' ){
							//$visible_class = ' ';
							continue;
						}else{
							//$visible_class = 'd-none not-voted';
						}

			// show only can vote users
			$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
			if( $user_can_vote != '1' ){ continue; }

			$own_shares = get_own_shares( $s_user->ID  ); 
			$total_shares = get_total_shares( $s_user->ID ); 

			$val_a = $own_shares;
			$val_b = $total_shares;

			$val_a_b = $val_a + $val_b;

			$user_answer =  get_post_meta( $post_id, 'user_'.$s_user->ID, true );
			$poll_variants =  get_post_meta( $post_id, 'poll_variants' , true );
			$poll_type =  get_post_meta( $post_id, 'poll_type' , true );
		 

		
			$timestamp = (int)get_post_meta( $post_id, 'uservotetime_'.$s_user->ID, true );
			if( $timestamp !== 0 ){
				$vote_date = date( 'Y/m/d H:i:s',  $timestamp);
			}else{
				$vote_date = ' - ';
			}

			// patch for multipol
			if( $poll_type == 'multi' ){
				$all_votes = get_post_meta( $post_id, 'user_'.$s_user->ID );
				$out_votes = [];
				foreach( $all_votes as $s_vote ){
					$out_votes[] = $poll_variants[$s_vote];

				}
			 
				$vote_text =  implode( ', ', $out_votes);
			}else{
				$vote_text =  ( isset($poll_variants[$user_answer]) && $poll_variants[$user_answer] != ''  ? $poll_variants[$user_answer] : ' - ' ) ;
			}


			$selected_cats = get_user_meta( $s_user->ID, USER_CATEGORY_NAME_META_KEY, true );
			fputcsv($output, 
			array( 
				$s_user->user_login, 
				$s_user->first_name, 
				$s_user->last_name, 
				$val_a, 
				get_proxys_amount( $s_user->ID ), 
				$val_b, 
				$val_a_b, 
				$vote_text,
				$vote_date ,
				$selected_cats
				));
		}

		fclose($output);
		die();				
 
	}

	// delete user vote
	if( isset( $_GET['delete_vote'] ) )
	if( $_GET['delete_vote'] ){
 
		global $wpdb;

		$user_id = (int)$_GET['delete_vote'];
		$post_id = (int)$_GET['post'];


		delete_post_meta( $post_id, 'user_'.$user_id );
		delete_post_meta( $post_id, 'uservotetime_'.$user_id );
		delete_post_meta( $post_id, 'browser_'.$user_id );
		delete_post_meta( $post_id, 'usermessage_'.$user_id );

		$ip_placeholder = "\"user_".$user_id."_ip%\"";
		$res = $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key LIKE $ip_placeholder ", $post_id));
 
		 
	}
	
	// change vote
	if( isset($_GET['ans_variant']) ){

		// check if more one
		$exploded_ans = explode(',', urldecode( $_GET['ans_variant']) );

	

		$ans_variant = (int)$_GET['ans_variant'];
		$vote_user = (int)$_GET['vote_user'];
		$post_id = (int)$_GET['post'];


		delete_post_meta( $post_id, 'user_'.$vote_user  );

		foreach( $exploded_ans as $s_ans ){
			add_post_meta( $post_id, 'user_'.$vote_user, (int)$s_ans  );
		}
		
	}

	// drop table
	if( $_GET['drop_name_log'] == 'all' ){
		global $wpdb;
		$table_name = 'online_users_log';
		$table_name =  $wpdb->prefix.$table_name;
		$wpdb->query("TRUNCATE {$table_name}");
	}

	// drop name log table
	if( $_GET['drop'] == 'all' ){
		global $wpdb;
		$table_name = 'online_log';
		$table_name =  $wpdb->prefix.$table_name;
		$wpdb->query("TRUNCATE {$table_name}");
	}

	// export email_opened log
	if( $_GET['export_log'] == 'true' ){
		global $wpdb;
		$table_name = 'email_view_log';
		$table_name =  $wpdb->prefix.$table_name;
		
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=email_view_log.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		

		$output = fopen("php://output", "w");

		$all_email_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}email_view_log LEFT JOIN {$wpdb->prefix}users ON {$wpdb->prefix}email_view_log.user_id = {$wpdb->prefix}users.ID ".$search_query );

		fputcsv(
			$output, array(
		__('Username', 'wvp'), 
		__('Email', 'wvp'), 
		__('First name', 'wvp'), 
		__('Last Name', 'wvp'), 
		__('Email open date and time', 'wvp') ,
		__('IP', 'wvp'),
		__('Email Subject', 'wvp') 
		) 
	);


 
		foreach( $all_email_entries as $s_row ){
			$userdata = get_user_by( 'ID', $s_row->user_id );
			$email_subjects = get_option('email_sent_subjects');
			if( !isset( $email_subjects[$s_row->subject] ) ){
				$s_row->subject = __('Not defined', 'wvp');
			}

			fputcsv($output, 
			array( 
				$userdata->user_login,
				$userdata->user_email,
				$userdata->first_name,
				$userdata->last_name,
				date( 'Y-m-d H:i', $s_row->date ),
				$s_row->ip,
				$s_row->subject
			) );

		}

		fclose($output);
		die();		

	}

	// export log as csv
	if( $_GET['export_log'] == 'true' ){
		global $wpdb;

		$table_name = 'online_log';
		$table_name =  $wpdb->prefix.$table_name;
		
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=log_export_.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		

		$output = fopen("php://output", "w");

		$all_entries = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY date DESC", ARRAY_N);

		fputcsv($output, array( __('Date', 'wvp'), __('Time', 'wvp'), __('Total current users online at this moment', 'wvp'), __('Total online shares at this moment', 'wvp'), __('% of shares out of the total for our organization, online at this moment', 'wvp') ) );

		foreach( $all_entries as $s_row ){
			fputcsv($output, array( date( 'Y/m/d', (int)strtotime( $s_row[1] ) ), date( 'H:i:s', (int)strtotime( $s_row[1] ) ), $s_row[2], $s_row[3], $s_row[4] ) );
		}

		fclose($output);
		die();		

	}

	// export users log as csv
	if( $_GET['export_user_log'] == 'true' ){
		global $wpdb;

		$table_name = 'online_users_log';
		$table_name =  $wpdb->prefix.$table_name;
		
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=online_users_log.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		

		$output = fopen("php://output", "w");

		$all_entries = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY date DESC");

		fputcsv($output, 
			array( 
			__('Date', 'wvp'), 
			__('Name', 'wvp'), 
			__('Last Name', 'wvp'), 
			__('Username', 'wvp'),
			__('Own Shares', 'wvp'),
			__('Recieved Proxies', 'wvp'),
			__('Shares in proxies', 'wvp'),
			//__('Total shares', 'wvp'),
			)
		);


	 

		foreach( $all_entries as $s_row ){
		 
			$userdata = get_user_by('ID', $s_row->user);
			fputcsv($output, array( 
				date( 'Y/m/d H:i',  (int)$s_row->date ), 
				$userdata->first_name,
				$userdata->last_name,
				$userdata->user_login,

				$s_row->own_shares,
				$s_row->recieved_proxies,
				$s_row->shares_in_proxies,
				) 
			);
	
		}

		fclose($output);
		die();		

	}
	

	// re assign accepor
	
	if(  $_POST['_admin_change_acceptor'] == '1' ){
		
		$share_sender = (int)$_POST['_user_from'];
		$shares_acceptor = (int)$_POST['_user_to'];
		$user_switch_to = (int)$_POST['_user_switch_to'];

		// remove existed

		##############################################
		// aceptor data
		$donator_own_shares = (float)get_user_meta( $share_sender, 'own_shares', true );
		$donator_proxys_amount = (float)get_user_meta( $share_sender, 'proxys_amount', true );
		$donator_total_shares = (float)get_user_meta( $share_sender, 'total_shares', true );
 

		// aceptor data
		$acceptor_own_shares = (float)get_user_meta( $shares_acceptor, 'own_shares', true );
		$acceptor_proxys_amount = (float)get_user_meta( $shares_acceptor, 'proxys_amount', true );
		$acceptor_total_shares = (float)get_user_meta( $shares_acceptor, 'total_shares', true );

 
		// get donated_amount
		$donated_amount = get_user_meta( $share_sender, 'own_shares_old', true );
 
		// add donated amount to donater
		$donator_own_shares = $donator_own_shares + $donated_amount;
	 
		//update own shares
		update_user_meta( $share_sender, 'own_shares', $donator_own_shares );
		update_user_meta( $share_sender, 'can_vote', 1 );


		// remove shares from acceptor total
		$acceptor_total_shares = $acceptor_total_shares - $donated_amount;
		

		// update acceptor total
		update_user_meta( $shares_acceptor, 'total_shares', $acceptor_total_shares );

		// decrese proxies and update
		$acceptor_proxys_amount--;
		update_user_meta( $shares_acceptor, 'proxys_amount', $acceptor_proxys_amount );
	 

		// remove marks
		delete_user_meta( $share_sender, 'already_donated'  );
		delete_user_meta( $share_sender, 'donate_recipient'  );
		delete_user_meta( $share_sender, 'own_shares_old' );
		delete_user_meta( $share_sender, 'assign_date' );
		delete_user_meta( $share_sender, 'assign_type' );

		###############################################


		// assign new
		$user_from = (int)$_POST['_user_from'];
		$shares_acceptor = (int)$_POST['_user_switch_to'];
	 

		###################################################
		$current_user_share = (float)get_user_meta( $user_from, 'own_shares', true );

		

		// aceptor data
		$acceptor_own_shares = (float)get_user_meta( $shares_acceptor, 'own_shares', true );
		$acceptor_proxys_amount = (float)get_user_meta( $shares_acceptor, 'proxys_amount', true );
		$acceptor_total_shares = (float)get_user_meta( $shares_acceptor, 'total_shares', true );

		// drop current user shares
		update_user_meta( $user_from, 'own_shares', 0 );
		update_user_meta( $user_from, 'can_vote', 0 );
		update_user_meta( $user_from, 'own_shares_old', $current_user_share );
		update_user_meta( $user_from, 'assign_date', current_time('timestamp') );

		//increase acceptor shares
		$acceptor_total_shares = $acceptor_total_shares + $current_user_share;
		update_user_meta( $shares_acceptor, 'total_shares', $acceptor_total_shares );

		// update acceptors proxies
		$acceptor_proxys_amount++;
		update_user_meta( $shares_acceptor, 'proxys_amount', $acceptor_proxys_amount  );

		// marks
		update_user_meta( $user_from, 'already_donated', 'yes' );
		update_user_meta( $user_from, 'donate_recipient', $shares_acceptor );
		
		// assign type
 		update_user_meta( $user_from, 'assign_type', 'admin' );
		 

		$redirect_url = admin_url( 'edit.php?post_type=poll&page=wvp_dataproxy_admin' );
		wp_redirect( $redirect_url, 302 );
		 exit;

		###################################################

	}

	// assign proxies
	if( isset( $_POST['assign_proxies'] ) ||   $_POST['_admin_assign_share'] == '1' ){
		global $current_user;

		/*
		$own_shares = get_user_meta( $current_user->ID, 'own_shares', true );
		$proxys_amount = get_user_meta( $current_user->ID, 'proxys_amount', true );
		$total_shares = get_user_meta( $current_user->ID, 'total_shares', true );
		*/
		//error_reporting( E_ALL );
		$is_external = 0;
		// verify if remote or local user
		if( $_POST['assign_proxies_to_external'] == 'yes' ){

			

			$external_name = sanitize_text_field( $_POST['external_name'] );
			$external_email = sanitize_text_field( $_POST['external_email'] );

			if( email_exists( $external_email ) ){
				$redirect_url = get_permalink( $_POST['parent_page'] );
				wp_redirect( $redirect_url.'?email_exists=1', 302 );
				exit;
			}else{
				$password = rand( 1000, 9999 );
				$user_id = wp_create_user( $external_email, $password, $external_email );
				wp_update_user( [ 
					'ID' => $user_id,
					'first_name' => $external_name
				] );

				// hack to use next code
				$_POST['assign_proxies'] = $user_id;

				// log external users
				$is_external = 1;

				//emails 
				$to = get_option('admin_email');
				$subject = __('Proxy assigned to external user', 'wvp');
				$body = __('User %u1 assigned proxy to user %u2', 'wvp' );

				$body = str_replace( '%u1', $current_user->user_login, $body);
				$body = str_replace( '%u2', $external_email, $body);

				$headers = array('Content-Type: text/html; charset=UTF-8');
				
				wp_mail( $to, $subject, $body, $headers );

			 
			}
		}


		$user_from = $current_user->ID;
		$shares_acceptor = (int)$_POST['assign_proxies'];

		// check max proxy amount
		$wvp_data_options = get_option('wvp_data_options');
		$max_proxy_amount = (int)$wvp_data_options['max_proxy_amount'];

	

		if( $max_proxy_amount > 0 ){
			$current_proxy_amount = get_proxys_amount( $shares_acceptor );
			if( $current_proxy_amount >= $max_proxy_amount   ){

				$redirect_url = get_permalink( $_POST['parent_page'] );
				wp_redirect( $redirect_url.'?max_amount_reached=1', 302 );
				exit;
			}
		}

		// upload fiels processing
		$urls_stack = [];
		if( isset( $_FILES['attached_documents'] ) ){
			if( count($_FILES['attached_documents']["name"]) > 0 && $_FILES['attached_documents']["name"][0] != '' ){
				
			 
	 
				$upload_dir = wp_upload_dir();
				$items_count = count( $_FILES['attached_documents']["name"] );
				for( $i=0; $i<$items_count; $i++ ){
					$name =   time().'_'.$_FILES['attached_documents']["name"][$i];
					$tmp_name =  $_FILES['attached_documents']["tmp_name"][$i];
					if( !is_dir( $upload_dir['path'] ) ){
						wp_mkdir_p($upload_dir['path']);
					}
					$file_path = $upload_dir['path'].'/'.$name;
					$file_url = $upload_dir['url'].'/'.$name;
					move_uploaded_file ( $tmp_name, $file_path );
	
					$urls_stack[] = $file_url;
				}
			}
		}
		
 
		

		// patch for user ban
		$wvp_data_options =  get_option('wvp_data_options');
		if( in_array( $shares_acceptor, (array)$wvp_data_options['users_cant_recieve_proxies'] ) ){ 
			$redirect_url = admin_url( 'edit.php?post_type=poll&page=wvp_dataproxy_admin' );
			wp_redirect( $redirect_url, 302 );
			exit;
		}


		$redirect_url = get_permalink( $_POST['parent_page'] );

		// patch to assign by admin
		if( isset( $_POST['_user_from'] ) && isset( $_POST['_user_to'] ) ){
			$user_from = (int)$_POST['_user_from'];
			$shares_acceptor = (int)$_POST['_user_to'];

			if( $max_proxy_amount > 0 ){
				$current_proxy_amount = get_proxys_amount( $shares_acceptor );
				if( $current_proxy_amount >= $max_proxy_amount   ){
					$redirect_url = get_permalink( $_POST['parent_page'] );
					wp_redirect( admin_url( 'edit.php?post_type=poll&page=wvp_dataproxy_admin' ).'&max_amount_reached=1', 302 );
					exit;
				}
			}

			$redirect_url = admin_url( 'edit.php?post_type=poll&page=wvp_dataproxy_admin' );
			if( !current_user_can('administrator') ){
				wp_redirect( $redirect_url, 302 );
				exit;
			}
 
		}


		$current_user_share = (float)get_user_meta( $user_from, 'own_shares', true );

		

		// aceptor data
		$acceptor_own_shares = (float)get_user_meta( $shares_acceptor, 'own_shares', true );
		$acceptor_proxys_amount = (float)get_user_meta( $shares_acceptor, 'proxys_amount', true );
		$acceptor_total_shares = (float)get_user_meta( $shares_acceptor, 'total_shares', true );

		// drop current user shares
		update_user_meta( $user_from, 'own_shares', 0 );
		update_user_meta( $user_from, 'can_vote', 0 );
		update_user_meta( $user_from, 'own_shares_old', $current_user_share );
		update_user_meta( $user_from, 'assign_date', current_time('timestamp') );

		// v1.5 assign moderation
		update_user_meta( $user_from, 'assign_approved', 0 );

		//increase acceptor shares
		$acceptor_total_shares = $acceptor_total_shares + $current_user_share;
		update_user_meta( $shares_acceptor, 'total_shares', $acceptor_total_shares );

		// update acceptors proxies
		$acceptor_proxys_amount++;
		update_user_meta( $shares_acceptor, 'proxys_amount', $acceptor_proxys_amount  );

		// marks
		update_user_meta( $user_from, 'already_donated', 'yes' );
		update_user_meta( $user_from, 'donate_recipient', $shares_acceptor );
		update_user_meta( $user_from, 'sendproxy_comment', sanitize_text_field( $_POST['_assign_message'] ) );
		update_user_meta( $user_from, 'sendproxy_files', implode( ', ', $urls_stack ) );
		update_user_meta( $user_from, 'is_external', $is_external );
		
		// assign type
		update_user_meta( $user_from, 'assign_type', 'user' );
		if( isset( $_POST['_user_from'] ) && isset( $_POST['_user_to'] ) ){
			update_user_meta( $user_from, 'assign_type', 'admin' );
		}

		wp_redirect( $redirect_url, 302 );
		exit;
	}


	// remove proxy
	
	if( isset( $_POST['remove_proxy'] )  || $_POST['_admin_revoke_share'] == '1'  ){
		global $current_user;

		//error_reporting(E_ALL);
		//ini_set('display_errors', 'On');

		/*
		$own_shares = get_user_meta( $current_user->ID, 'own_shares', true );
		$proxys_amount = get_user_meta( $current_user->ID, 'proxys_amount', true );
		$total_shares = get_user_meta( $current_user->ID, 'total_shares', true );
		*/

		$share_sender = $current_user->ID;
 
		// guy who get shares
		$shares_acceptor = get_user_meta( $share_sender, 'donate_recipient', true );

		$redirect_url = get_permalink( $_POST['parent_page'] );

		

		if( isset( $_POST['_admin_revoke_share'] )  ){
	 
			$share_sender = (int)$_POST['_user_from'];
			$shares_acceptor = (int)$_POST['_user_to'];
			
			$redirect_url = admin_url( 'edit.php?post_type=poll&page=wvp_dataproxy_admin' );
			if( !current_user_can('administrator') ){
				wp_redirect( $redirect_url, 302 );
				exit;
			}
		}
 
		// aceptor data
		$donator_own_shares = (float)get_user_meta( $share_sender, 'own_shares', true );
		$donator_proxys_amount = (float)get_user_meta( $share_sender, 'proxys_amount', true );
		$donator_total_shares = (float)get_user_meta( $share_sender, 'total_shares', true );
 

		// aceptor data
		$acceptor_own_shares = (float)get_user_meta( $shares_acceptor, 'own_shares', true );
		$acceptor_proxys_amount = (float)get_user_meta( $shares_acceptor, 'proxys_amount', true );
		$acceptor_total_shares = (float)get_user_meta( $shares_acceptor, 'total_shares', true );

 
		// get donated_amount
		$donated_amount = get_user_meta( $share_sender, 'own_shares_old', true );
 
		// add donated amount to donater
		$donator_own_shares = $donator_own_shares + $donated_amount;
	 
		//update own shares
		update_user_meta( $share_sender, 'own_shares', $donator_own_shares );
		update_user_meta( $share_sender, 'can_vote', 1 );

		// remove shares from acceptor total
		$acceptor_total_shares = $acceptor_total_shares - $donated_amount;
		

		// update acceptor total
		update_user_meta( $shares_acceptor, 'total_shares', $acceptor_total_shares );

		// decrese proxies and update
		$acceptor_proxys_amount--;
		update_user_meta( $shares_acceptor, 'proxys_amount', $acceptor_proxys_amount );
	 

		// remove marks
		delete_user_meta( $share_sender, 'already_donated'  );
		delete_user_meta( $share_sender, 'donate_recipient'  );
		delete_user_meta( $share_sender, 'own_shares_old' );
		delete_user_meta( $share_sender, 'assign_date' );
		delete_user_meta( $share_sender, 'assign_type' );


		// v1.5 assign moderation
		//update_user_meta( $user_from, 'assign_approved'  );
	 

		##################
		wp_redirect( $redirect_url, 302 );
		exit;

		
		
	}


	
	
}) ;

add_action( 'current_screen', 'wpdocs_this_screen_' );
 
/**
 * Run code on the admin widgets page
 */
function wpdocs_this_screen_() {
	$currentScreen = get_current_screen();

    if( ($currentScreen->action === "add" &&  $currentScreen->post_type === "poll") || ( $_GET['action'] === "rd_duplicate_post_as_draft" ) ) {
        $all_posts = get_posts(array(
			'post_type' => 'poll',
			'post_status' => 'any',
			'showposts' => -1
		));
		$polls_num = 1;
		if( count($all_posts) > $polls_num ){
			wp_die( sprintf( __( 'Sorry, you allowed to create only %d polls.', 'wvp'), $polls_num, admin_url('edit.php?post_type=poll') ) );
		}
    }
}


// email sending
add_action('init', function(){
 

	// email sending
	if( isset($_POST['from_name1']) ){

		$settings = get_option('wvp_email_options');

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
		$template_content = nl2br( $template_content );

		$email_sending_report = [];

		//  getting users
		if( $recipients[0] == 'all'){
			global $wpdb;
			$user_list = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
			
			
		}else{
			$user_list = $recipients;
		}


		// sending process
		$users_count = count($user_list);
		foreach( $user_list as $s_id ){
		 
			$template_content_inner = wvp_get_user_email_content( $s_id, $template_content );

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

			 /*
			var_dump( $to );
			var_dump( $subject );
			var_dump( $body );
			*/
			$fp = fopen( dirname(__FILE__).'/data.txt', 'w');
			fwrite($fp,  var_export( $body, true ) );	
			fclose($fp);

			$res = wp_mail( $to, $subject, $body, $headers );

			$email_sending_report[$s_id] = $res;
		}

		update_option('email_sending_report', $email_sending_report);

		$current_log = get_option('big_email_log' );
		$current_log[] = array( 'date' => current_time('timestamp'), 'body' => $template_content_bkp, 'usercount' => $users_count, 'template_type' => $tempalte_type, 'content' =>  $body );
		update_option('big_email_log', $current_log );

		//{first_name} {last_name} {password} {username}

	}
}, 100);


// check user live time
add_Action('init', function(){
	global $current_user;

	if( is_user_logged_in() ){
		update_user_meta( $current_user->ID, 'online_activity', current_time('timestamp') );
	}
});

// data logging
add_Action('init', function(){
	global $wpdb;

 
	$output_options = get_option('wvp_extra_options');


	$table_name = 'online_log';
	$table_name =  $wpdb->prefix.$table_name;

	$settings = get_option('wvp_options');

	$cron_delay = (int)$settings['minutes_to_save_quorum_data'];

	$org_total_shares = (int)$settings['organization_total_shares'];
	
	$output_blocks =  '';
	if( isset($settings['quorum_table']) ){
		$output_blocks =  $settings['quorum_table'];
	}
	
	$show_online_users =  $settings['show_online_users'];

	

	$last_cron_run = (int)get_option('proxy_cron_time');
	$offset =  current_time('timestamp') - $last_cron_run;
	 
	if( $offset > $cron_delay*60 && $output_options['lite_mode'] != 'yes'  ){

		if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
			$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
		}
		if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
			$time_limit = strtotime( $settings['from_timestamp'] );
		}
		
		//$all_online_usrs = $wpdb->get_results("SELECT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} ");

		$all_online_usrs = $wpdb->get_results("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id IN( SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1'  )");


		$total_shares = 0;
		$own_shares = 0;
		$total_proxies = 0;

		if(  count($all_online_usrs) > 0 )
		foreach( $all_online_usrs as $s_user ){
			$own_shares = get_own_shares( $s_user->ID  );  
			$total_shares = get_total_shares( $s_user->ID ); 
			$total_proxies = $total_proxies + $own_shares + $total_shares;
			
		}

		if(  $org_total_shares > 0 ){
			$val_c = $total_proxies*100 / $org_total_shares;
		}else{
			$val_c = 0;
		}
		
	 
		if( count($all_online_usrs) > 0 ){
			$res = $wpdb->insert(
				$table_name,
				array(
					'date' => current_time('mysql'),
					'total_users' => count($all_online_usrs),
					'total_online_shares' => $total_proxies,
					'percent_of_shares' => $val_c,
				)
			);
		}
		
		//var_dump( $res );
		//var_dump(  $wpdb->last_error );
		//$wpdb->show_error( true );
		//$wpdb->print_error();
	 
		update_option('proxy_cron_time', current_time('timestamp'));
	}

	

});


// loggin of user names
//add_Action('init', 'wvp_online_users_log' );
//add_Action('admin_footer', 'wvp_online_users_log' );
add_Action('wp_footer', 'wvp_online_users_log' );
 
function wvp_online_users_log(){
	global $wpdb;

	remove_Action('init', 'wvp_online_users_log' );
	remove_Action('wp_footer', 'wvp_online_users_log' );
 


	$table_name = 'online_users_log';
	$table_name =  $wpdb->prefix.$table_name;

	$settings = get_option('wvp_options');

	$cron_delay = (int)$settings['minutes_to_save_names'];

	$last_cron_run = (int)get_option('proxy_name_cron_time');

	$user_log_currently_running = (int)get_option('name_log_runing');

	$offset =  current_time('timestamp') - $last_cron_run;
	
	if( $offset > $cron_delay*60 && $user_log_currently_running == 0  ){
		update_option('name_log_runing', 1);
		update_option('proxy_name_cron_time', current_time('timestamp'));

		//$time_limit = current_time('timestamp') - (int)$settings['user_online_lifetime']*60;
		if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
			$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
		}
		if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
			$time_limit = strtotime( $settings['from_timestamp'] );
		}

		$all_online_users = $wpdb->get_col("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id IN( SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1'  )");
 

		$date_stamp = current_time('timestamp');


		$wpdb->delete( $table_name, array(
			'date' => $date_stamp
		) );

		// 00:xx patch
		$current_mins = date( 'i', current_time('timestamp') );
		
		foreach( $all_online_users as $s_user){
			$res = $wpdb->insert(
				$table_name,
				array(
					'date' => $date_stamp,
					'user' => $s_user,
					'own_shares' => get_own_shares( $s_user ),
					'recieved_proxies' => get_proxys_amount( $s_user ),
					'shares_in_proxies' => get_total_shares( $s_user ),
	 
				)
			);
		}

		update_option('name_log_runing', 0);
	}

	

}


// patch to drop time for logged out users
add_action( 'clear_auth_cookie', function() {
	global $current_user;
	
 
    delete_user_meta( $current_user->ID, 'online_activity' );
  
});

// poll catgory temrs
add_filter( 'manage_edit-poll_category_columns', 'wvp_poll_category_columns' );
function wvp_poll_category_columns( $columns ) {
    $columns['foo'] = __('Category ID', 'wvp');
    return $columns;
}


add_filter('manage_poll_category_custom_column', 'wvp_poll_category_column_content',10,3);
function wvp_poll_category_column_content($content,$column_name,$term_id){
    $term= get_term($term_id, 'poll_category');
    switch ($column_name) {
        case 'foo':
            //do your stuff here with $term or $term_id
            $content = $term_id;
            break;
        default:
            break;
    }
    return $content;
}


// outoput of poll data
add_filter( 'manage_edit-poll_columns', 'wvp_poll_columns' ) ;
function wvp_poll_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		
		'title' => __( 'Title', 'wvp' ),
		'taxonomy-poll_category' => __( 'Poll Category', 'wvp' ),
		//'poll_category_id' => __( 'Poll Cat ID', 'wvp' ),
		'total_users' => __( 'Total users that have voted', 'wvp' ),
		'summ_of_shares' => __( 'Sum of total shares', 'wvp' ), 

		// show all shortocdes
		//'all_polls_shortcode' => __( 'Show polls at poll page?', 'wvp' ), 
		//'all_results_shortcode' => __( 'Show results  at results page?', 'wvp' ), 
		//'vote_is_open' => __( 'Vote is open?', 'wvp' ), 

		//'show_only_voters' => __( 'Show only voters lists in poll results?', 'wvp' ), 


		'actions' => __( 'Actions', 'wvp' ), 
	);

	return $columns;
}

add_action( 'manage_poll_posts_custom_column', 'wvp_poll_posts_custom_column', 10, 2 );

function wvp_poll_posts_custom_column( $column, $post_id ) {
	global $post, $wpdb;

	$poll_type = get_post_meta( $post_id, 'poll_type', true );
	switch( $column ) {
		/* If displaying the 'duration' column. */
		case 'total_users' :
			if( $poll_type == 'single' ){
				$all_voted = $wpdb->get_var("SELECT  count( DISTINCT ( meta_key) ) FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\\_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'    )");
				echo $all_voted;
			}
			if( $poll_type == 'multi' ){
				$all_voted = $wpdb->get_var("SELECT   count(DISTINCT(meta_key)) FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\\_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'    )");
				echo $all_voted;
			}
			
 

			break;

		case 'summ_of_shares' :
			if( $poll_type == 'single' ){
			$total_votes = $wpdb->get_col("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\\_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'   )");
			$total_votes = array_unique( $total_votes );

			$total = 0;

			if( count( $total_votes ) > 0 )
			foreach( $total_votes as $s_vote ){
				$tmp = explode('_', $s_vote);
				$user_shares = get_own_shares( $tmp[1]  ); 
				$total_shares = get_total_shares( $tmp[1] ); 
				$total = $total + $user_shares + $total_shares;
			}
			 
			echo $total;
			}

			if( $poll_type == 'multi' ){
				$total_votes = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\\_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'   )");
				$total_votes = array_unique( $total_votes );
				$total = 0;
	
				if( count( $total_votes ) > 0 )
				foreach( $total_votes as $s_vote ){
					$tmp = explode('_', $s_vote);
					$user_shares = get_own_shares( $tmp[1]  ); 
					$total_shares = get_total_shares( $tmp[1] ); 
					$total = $total + $user_shares + $total_shares;
				}
				 
				echo $total;
				}
			break;	
		
		case 'all_polls_shortcode' :
			$current_value = get_post_meta( $post_id, 'show_polls_shortcode', true );
			echo '
			<div class="tw-bs4">
			<div class="text-center">
			<input type="checkbox" data-id="'.$post_id.'" class="show_polls_shortcode" data-cf="show_polls_shortcode"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" />
			</div>
			</div>
			';
		break;

		case 'all_results_shortcode' :
			$current_value = get_post_meta( $post_id, 'all_results_shortcode', true );
			echo '
			<div class="tw-bs4">
			<div class="text-center">
			<input type="checkbox" class="all_results_shortcode" data-cf="all_results_shortcode" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" />
			</div>
			</div>
			';
		break;

		case 'vote_is_open' :
			$current_value = get_post_meta( $post_id, 'vote_is_open', true );
			echo '
			<div class="tw-bs4">
			<div class="text-center">
			<input type="checkbox" class="vote_is_open" data-cf="vote_is_open" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" />
			</div>
			</div>
			';
		break;

		case 'show_only_voters' :
			$current_value = get_post_meta( $post_id, 'show_only_voters', true );
			echo '
			<div class="tw-bs4">
			<div class="text-center">
			<input type="checkbox" class="show_only_voters" data-cf="show_only_voters" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" />
			</div>
			</div>
			';
		break;
		case 'poll_category_id' :
			$get_post_terms = wp_get_post_terms( $post_id, 'poll_category');
			
			$ids = [];
			foreach( $get_post_terms as $s_term ){
				$ids[] = $s_term->term_id;
			}
			echo implode( ', ', $ids );
		break;
		case 'actions' :
			$out = '<ul class="actions_list">';

			$current_value = get_post_meta( $post_id, 'show_polls_shortcode', true );
			$out .=  '<li>
			<div class="tw-bs4">
			 
			<input type="checkbox" data-id="'.$post_id.'" class="show_polls_shortcode" data-cf="show_polls_shortcode"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" /> '.__( 'Show poll at poll page?', 'wvp' ).'
		 
			</div></li>
			';

			$current_value = get_post_meta( $post_id, 'vote_is_open', true );

			$closing_time = get_post_meta( $post_id, 'vote_close_time', true );
		 
			$out .='<li>
			<div class="tw-bs4">
			 
			<input type="checkbox" class="vote_is_open" data-cf="vote_is_open" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" /> '.__( 'Vote is open?', 'wvp' ).' '.( isset($closing_time['time']) ? __('Latest closing date/time:', 'wvp').' '.date( 'Y-m-d H:i', (int)$closing_time['time'] ) : '' ).'
	 
			</div></li>
			';

			$out .='<li>
			<b>'.__('Global results page:','wvp').'</b>
	 
			</div></li>
			';
 
			

			$current_value = get_post_meta( $post_id, 'show_data_table', true );
			$out .= '<li>
			<div class="tw-bs4">
			 
			<input type="checkbox" class="show_data_table" data-cf="show_data_table" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" /> '.__( 'Show answers data top table?', 'wvp' ).'
	 
			</div></li>
			';

		 

			$current_value = get_post_meta( $post_id, 'list_not_voted', true );
			$out .= '<li>
			<div class="tw-bs4">
			 
			<input type="checkbox" class="list_not_voted" data-cf="list_not_voted" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" /> '.__( 'Show list of users that have not voted', 'wvp' ).'
	 
			</div></li>
			';

			$current_value = get_post_meta( $post_id, 'list_that_voted', true );
			$out .= '<li>
			<div class="tw-bs4">
			 
			<input type="checkbox" class="list_that_voted" data-cf="list_that_voted" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" /> '.__( 'Show list of users that have voted', 'wvp' ).'
	 
			</div></li>
			';

			$current_value = get_post_meta( $post_id, 'show_user_answers', true );
			$out .= '<li>
			<div class="tw-bs4">
			 
			<input type="checkbox" class="show_user_answers" data-cf="show_user_answers" data-id="'.$post_id.'"  '.( $current_value == 'on' ? '  checked ' : '  ' ).' value="on" /> '.__( 'Show users answer', 'wvp' ).'
	 
			</div></li>
			';


			$out .= '</ul>';
			echo $out;
		break;

 
		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}


/**
 * email template columns
 */
add_filter( 'manage_edit-email_template_columns', 'wvp_email_template' ) ;
function wvp_email_template( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		
		'title' => __( 'Title', WVP_LOCALE ),
		'schedule_date' => __( 'Scheduled date/time', WVP_LOCALE ),
		
		'status' => __( 'Status', WVP_LOCALE ),
	 
	);

	return $columns;
}

add_action( 'manage_email_template_posts_custom_column', 'wvp_email_template_posts_custom_column', 10, 2 );

function wvp_email_template_posts_custom_column( $column, $post_id ) {
	global $post, $wpdb;
	switch( $column ) {
		/* If displaying the 'duration' column. */
		case 'schedule_date' :
			$when_send_email = get_post_meta( $post_id, 'when_send_email', true );
			if( $when_send_email == 'schedule' ){
				echo  get_post_meta( $post_id, 'schedule_email_sending', true );				
			}
			if( $when_send_email == 'now' ){
				echo __('Now', WVP_LOCALE );
			}
			if( $when_send_email == 'draft' ){
				echo __('Draft', WVP_LOCALE );
			}
			
			break;

		case 'status' :
		 
		$total_recipients =  (int)get_post_meta( $post_id, 'total_emails', true );			
		$items_sent = (int)get_post_meta( $post_id, 'email_full_processed_items_'.$post_id, true);
		$sending_finished_at = get_post_meta($post_id, 'sending_finished_at', true );
	
		if( $sending_finished_at   && $sending_finished_at != '' ){
			echo __('Finished at ', WVP_LOCALE ).date('Y-m-d H:i', $sending_finished_at );;
		}else if( $total_recipients != $items_sent && $items_sent > 0 ){
			echo __('Sending', WVP_LOCALE );
		}elseif( $items_sent == 0 ){
			echo __('Pending', WVP_LOCALE );
		}
			break;	
		
 
		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

// adding user filter
add_action('admin_footer', function(){
	global $wpdb;
	remove_action("pre_get_users", 'wvp_get_users_list');
	$cant_vote = get_users(

		array(
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'can_vote',
					'value' => '1',
					'compare' =>  '!='
				),
				array(
					'key' => 'can_vote',
					'value' => '1',
					'compare' =>  'NOT EXISTS'
				)
			),
			'fields' => 'ids'
		)
	);
	$all_users = get_users();
	

	
	// user vote block
	$all_users_that_cant_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value != '1' " );
	$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );

	$diff = count($all_users) - count( $all_users_that_caN_vote );

	echo '<div class="" style="display:none;" id="type_filter">
	<li class="can_vote">
		 <b>'.__('Can vote?', 'wvp').'</b>'; 

		 if( $_GET['vote'] == 'yes' ){
			echo '
			<b>'.__('Yes', 'wvp').'</b> <span class="count">('.count($all_users_that_caN_vote).')</span>';
		 }else{
			echo '
			<a href="users.php?vote=yes">'.__('Yes', 'wvp').' <span class="count">('.count($all_users_that_caN_vote).')</span></a>';
		 }
		 

		 echo ' | ';

		 if( $_GET['vote'] == 'no' ){
			echo '<b>'.__('No', 'wvp').'</b> <span class="count">('.$diff.')</span></a>';
		 }else{
			echo '<a href="users.php?vote=no">'.__('No', 'wvp').' <span class="count">('.$diff.')</span></a>';
		 }
		
		echo '
	</li> 
	 
	</div>';

	// user category block
	$user_category_terms = get_terms( array(
		'taxonomy' => USER_CATEGORY_NAME,
		'hide_empty' => 0
	) );
	echo '<div class="" style="display:none;" id="category_filter">
	<li class="can_vote">';

		$out_items_cats = [];
	 
		foreach( $user_category_terms as $s_cat ){

			$items_count = get_users([
				'meta_query' => [
					[
						'key' => USER_CATEGORY_META_KEY,
						'value' => $s_cat->term_id,
					]
				]
			]);
 
			if( $_GET['usercat'] == $s_cat->term_id ){
			   $out_cat_data =  '
			   <b>'.$s_cat->name.'</b> <span class="count">('.count($items_count).')</span>';
			}else{
			   $out_cat_data =  '
			   <a href="users.php?usercat='.$s_cat->term_id.'">'.$s_cat->name.' <span class="count">('.count($items_count).')</span></a>';
			}

			$out_items_cats[] = $out_cat_data;
		}

		
		 

		 echo  '<b>'.__('Poll categories:', 'wvp').'</b>'.implode(' | ', $out_items_cats);

	 
		
		echo '
	</li> 
	 
	</div>';


	// footer insertion of poll mass editor block
	if( !isset( $_GET['page'] ) ){
		if( isset( $_GET['post_type'] ) ){
			global $pagenow, $typenow;
			if( $_GET['post_type'] == 'poll' && $pagenow == 'edit.php' && $typenow == 'poll' ){
				
				echo '
				<div class="footer_mass_editor_block" style="display:none;">


				<div id="" class="notice"> 
				<p>
				<b>'.__('MASSIVE ACTIONS: Select action to apply to all created polls at the same time with one click:', 'wvp').'</b>
				

				<ul class="actions_list">
						<li>
							<div class="tw-bs4">
								 
								'.__('Show poll at poll page?', 'wvp').'
								<select class="mass_checkbox_processing" data-cf="show_polls_shortcode">
									 <option value="">'.__('Select action', 'wvp').'
									 <option value="on">'.__('On', 'wvp').'
									 <option value="off">'.__('Off', 'wvp').'
								 </select>
							</div>
						</li>
						<li>
							<div class="tw-bs4">
								'.__('Vote is open?', 'wvp').'
								<select class="mass_checkbox_processing" data-cf="vote_is_open">
									 <option value="">'.__('Select action', 'wvp').'
									 <option value="on">'.__('On', 'wvp').'
									 <option value="off">'.__('Off', 'wvp').'
								 </select>
							</div>
						</li>
						<li>
							<div class="tw-bs4">
								'.__('Open popup in user’s screens', 'wvp').'
								<select  class="mass_checkbox_processing" data-cf="open_polls_popup">
									 <option value="">'.__('Select action', 'wvp').'
									 <option value="on" '.( get_option('open_polls_popup') == 'on' ? ' selected ' : '' ).' >'.__('On', 'wvp').'
									 <option value="off" '.( get_option('open_polls_popup') == 'off' ? ' selected ' : '' ).' >'.__('Off', 'wvp').'
								 </select>
							</div>
						</li>
						<li>
							<b>'.__('Global results page:', 'wvp').'</b>
						</li>
						<li>
							<div class="tw-bs4">
								'.__('Show answers data top table?', 'wvp').'
								<select class="mass_checkbox_processing" data-cf="show_data_table">
									 <option value="">'.__('Select action', 'wvp').'
									 <option value="on">'.__('On', 'wvp').'
									 <option value="off">'.__('Off', 'wvp').'
								 </select>
							</div>
						</li>
						<li>
							<div class="tw-bs4">
								'.__('Show list of users that have not voted', 'wvp').'
								<select class="mass_checkbox_processing" data-cf="list_not_voted">
									 <option value="">'.__('Select action', 'wvp').'
									 <option value="on">'.__('On', 'wvp').'
									 <option value="off">'.__('Off', 'wvp').'
								 </select>
							</div>
						</li>
						<li>
							<div class="tw-bs4">
								'.__('Show list of users that have voted', 'wvp').'
								<select  class="mass_checkbox_processing" data-cf="list_that_voted">
									 <option value="">'.__('Select action', 'wvp').'
									 <option value="on">'.__('On', 'wvp').'
									 <option value="off">'.__('Off', 'wvp').'
								 </select>
							</div>
						</li>
						<li>
							<div class="tw-bs4">
								'.__('Show users answer', 'wvp').'
								<select  class="mass_checkbox_processing" data-cf="show_user_answers">
									 <option value="">'.__('Select action', 'wvp').'
									 <option value="on">'.__('On', 'wvp').'
									 <option value="off">'.__('Off', 'wvp').'
								 </select>
							</div>
						</li>
						
						</ul>

						</p>
						</div>
				</div>
				';
			}
		}
		
	}
	

});

// filter by custom meta 
add_action("pre_get_users", 'wvp_get_users_list', 10, 1);
function wvp_get_users_list($WP_User_Query) {

    if ( isset( $_GET['vote'] )  ) {
		if( $_GET['vote'] == 'yes' ){
			$WP_User_Query->query_vars["meta_key"] = "can_vote";
			$WP_User_Query->query_vars["meta_value"] = "1";
		}
        if( $_GET['vote'] == 'no' ){
			$WP_User_Query->query_vars["meta_query"] = array(
				'relation' => 'OR',
				array(
					'key' => 'can_vote',
					'value' => '1',
					'compare' =>  '!='
				),
				array(
					'key' => 'can_vote',
					'value' => '1',
					'compare' =>  'NOT EXISTS'
				)
				);
		}
	}
	
	if ( isset( $_GET['usercat'] )  ) {
 
			$WP_User_Query->query_vars["meta_key"] = USER_CATEGORY_META_KEY;
			$WP_User_Query->query_vars["meta_value"] = (int)$_GET['usercat'] ;
 
    
    }

}

// output and edit user data
add_action('manage_users_columns', 'wvp_register_custom_user_column');
add_action('manage_users_custom_column', 'wvp_register_custom_user_column_view', 10, 3);

function wvp_register_custom_user_column($columns) {

	$tmp_columns = [];

	foreach( $columns as $key => $value ){
		$tmp_columns[$key] = $value;
		if( $key == 'name' ){
			$tmp_columns['user_category'] = __('User category', 'wvp');
		}
	}

	//$columns['user_category'] = __('User category', 'wvp');
    $tmp_columns['can_vote'] = __('Can vote?', 'wvp');
    $tmp_columns['own_shares'] = __('Own shares? (For decimals use . )', 'wvp');
    $tmp_columns['recieved_proxies'] = __('Received proxies (For decimals use . )', 'wvp');
    $tmp_columns['summ_of_proxies'] = __('Sum of received shares in proxies (For decimals use . )', 'wvp');
 
    return $tmp_columns;
}
function wvp_register_custom_user_column_view($value, $column_name, $user_id) {
	global $wpdb;

	if( $column_name == 'user_category' ){
		$selected_cats = get_user_meta( $user_id, USER_CATEGORY_NAME_META_KEY, true );
	 
		/*
		$selected_categories = [];
		$user_category_terms = get_terms( array(
			'taxonomy' => USER_CATEGORY_NAME,
			'hide_empty' => 0
		) );
 
		foreach( $user_category_terms as $single_cat ){
			if( in_array( $single_cat->term_id, $selected_cats ) ){
				$selected_categories[] = $single_cat->name;
			}
		}
	 
		$text =  implode(', ', $selected_categories);
		*/
		return $selected_cats;
	} 
    if( $column_name == 'can_vote' ){
		$text = '
		<div class="tw-bs4">
			<div class="text-center parent_cont">
				<div class="current_value">'.(int)get_user_meta( $user_id, 'can_vote', true ).'</div>
				<button type="button" class="btn btn-success btn-sm edit_value">'.__('Edit', 'wvp').'</button>
				<div class="editor_block">
					<input type="text" value="'.get_user_meta( $user_id, 'can_vote', true ).'" class="edited_value"  />
					<button type="button" class="btn btn-warning btn-sm save_value" data-user_id="'.$user_id.'" data-field="can_vote">'.__('Save', 'wvp').'</button>
				</div>
			</div>
		</div>';

		return $text;
	} 
    if( $column_name == 'own_shares' ){
	 
		$text = '
		<div class="tw-bs4">
			<div class="text-center parent_cont">
				<div class="current_value">'.get_own_shares( $user_id  ).'</div>
				<button type="button" class="btn btn-success btn-sm edit_value">'.__('Edit', 'wvp').'</button>
				<div class="editor_block">
					<input type="text" value="'.get_own_shares( $user_id  ).'" class="edited_value"  />
					<button type="button" class="btn btn-warning btn-sm save_value" data-user_id="'.$user_id.'" data-field="own_shares">'.__('Save', 'wvp').'</button>
				</div>
			</div>
		</div>';

		return $text;
	} 
    if( $column_name == 'recieved_proxies' ){
	
		$text = '
		<div class="tw-bs4">
			<div class="text-center parent_cont">
				<div class="current_value">'.get_proxys_amount( $user_id  ).'</div>
				<button type="button" class="btn btn-success btn-sm edit_value">'.__('Edit', 'wvp').'</button>
				<div class="editor_block">
					<input type="text" value="'.get_proxys_amount( $user_id  ).'" class="edited_value"  />
					<button type="button" class="btn btn-warning btn-sm save_value" data-user_id="'.$user_id.'" data-field="proxys_amount">'.__('Save', 'wvp').'</button>
				</div>
			</div>
		</div>';

		return $text;
	} 
    if( $column_name == 'summ_of_proxies' ){
	
		$text = '
		<div class="tw-bs4">
			<div class="text-center parent_cont">
				<div class="current_value">'.get_total_shares( $user_id  ).'</div>
				<button type="button" class="btn btn-success btn-sm edit_value">'.__('Edit', 'wvp').'</button>
				<div class="editor_block">
					<input type="text" value="'.get_total_shares( $user_id  ).'" class="edited_value"  />
					<button type="button" class="btn btn-warning btn-sm save_value" data-user_id="'.$user_id.'" data-field="total_shares">'.__('Save', 'wvp').'</button>
				</div>
			</div>
		</div>';

		return $text;
	} 
	 
	 
    return $value;

}

// make category column sortable
add_filter( 'manage_users_sortable_columns', 'wvp_my_sortable_cat_column' );
function wvp_my_sortable_cat_column( $columns ) {
    $columns['user_category'] = __('User category', 'wvp');
    return $columns;
}

// add sorting functionality
add_action("pre_get_users", function ($WP_User_Query) {

    if (    isset($WP_User_Query->query_vars["orderby"])
        &&  ("user_category" === $WP_User_Query->query_vars["orderby"])
    ) {
        $WP_User_Query->query_vars["meta_key"] = USER_CATEGORY_NAME_META_KEY;
        $WP_User_Query->query_vars["orderby"] = "meta_value";
    }

}, 10, 1);


// login hook to log to table
add_action('wp_login', function( $user_login, $user ){
	global $wpdb;

	$table_name = 'user_logins';
	$table_name =  $wpdb->prefix.$table_name;

	$userdata = get_user_by('login', $user_login);

	$wpdb->insert(
		$table_name,
		array(
			'date' => current_time('timestamp'),
			'user_id' => $userdata->ID
		)
	);

	update_user_meta( $userdata->ID, 'last_login', time() );
}, 10, 2);


// poll category picker
add_action('admin_footer', function(){
	if( isset($_GET['post_type']) ){
		if( $_GET['post_type'] == 'poll' ){
			$all_terms = get_terms(array(
				'taxonomy' => 'poll_category',
				'count' => true

			));

			$out = '';
			foreach( $all_terms as $s_term ){
				//http://localhost/wordpress/wp-admin/edit.php?post_type=poll&poll_category=poll-category
				$out .= '<li><a href="edit.php?post_type=poll&poll_category='.$s_term->slug.'">'.$s_term->name.' <span class="count">('.$s_term->count.')</span></a> | </li>';
			}

			echo '<div id="poll_category_cont" style="display:none;"><br/><b>'.__('Poll Categories:', 'wvp').'</b> '.$out.'</div>';
		}
	}
});


// poll category picker
add_action('wp_footer', function(){
	echo '
	<a data-fancybox href="#dynamic_content_block" id="fk_link"></a>
	<div id="dynamic_content_block" style="display:none; width:96%;"></div>';
});

// add notices for poll page
add_action('admin_notices', 'wvp_general_admin_notice');
function wvp_general_admin_notice(){
	global $pagenow;
 
    if ( $pagenow == 'edit.php' && $_GET['post_type'] == 'poll' && !$_GET['page'] ) {
         echo '<div class="notice notice-warning is-dismissible">
             <p>'.sprintf( __('If you choose for any poll the option show users answer, remember to select the column USER ANSWER clicking <a href="%s">here</a>','wvp'), admin_url('edit.php?post_type=poll&page=wvp_extrasettings') ).'</p>
         </div>';
    }
}

/* 
remove old sessions
*/
add_action( 'wp_login', 'wvp_filter_other_devices', 10, 2);
function wvp_filter_other_devices( $user_login, $user ){
	$settings = get_option('wvp_options');
 
	
	if( $settings['allow_only_single_session'] == 'yes' ){
		if(class_exists('WP_Session_Tokens')){
			$coder_sessions = WP_Session_Tokens::get_instance( $user->ID );
			if ( $coder_user_id === get_current_user_id() ) {
				$coder_sessions->destroy_others( wp_get_session_token() );
			} else {
				$coder_sessions->destroy_all();
			}
	
			wp_clear_auth_cookie();
			wp_set_current_user ( $user->ID );
			wp_set_auth_cookie  ( $user->ID );
		}
	}
	
 
}
add_filter('user_row_actions', 'wvp_silocreativo_new_action', 10, 2);
function wvp_silocreativo_new_action( $actions, $user ) {
	$actions['new_action'] = "<a class='logout_user'  href='" . wp_nonce_url( admin_url( "users.php?&action=logout_user&amp;user_id=$user->ID"), basename(__FILE__), 'logout_user' ) . "'>" . __( 'Logout and remove from quorum', 'wvp' ) . "</a>";
	return $actions;
}



/**
 * CRON SCHEDULE SEND EMAILS
 */
add_action('template_redirect', function(){
	$all_scheduled_tempaltes = get_posts([
		'showposts' => -1,
		'post_type' => 'email_template',
		'meta_query' => [
			[
				'key' => 'when_send_email',
				'value' => 'schedule'
			]
		]
	]);
	

	foreach( $all_scheduled_tempaltes as $s_tempalte ){
		$make_run = 0;
		$template_date_timestamp = strtotime( get_post_meta( $s_tempalte->ID, 'schedule_email_sending', true ) );

		
		if( $template_date_timestamp < time() ){
			$last_run_hash = get_post_meta( $s_tempalte->ID, 'last_run_hash', true );
			if( $last_run_hash && $last_run_hash != '' ){
				if( $last_run_hash != md5($template_date_timestamp) ){
					$make_run = 1;
				}
			}else{
				$make_run = 1;
			}
		}

		if( $make_run == 1 ){

			//update_post_meta( $s_tempalte->ID, 'last_run_hash', md5($template_date_timestamp) );
			global $current_user, $wpdb;
					
				 	
			$template_id = $s_tempalte->ID;

			// get current sent amount
			$processed_items = (int)get_post_meta($template_id, 'email_full_processed_items_'.$template_id, true);
			$email_sending_report = get_post_meta($template_id, 'email_sending_report_'.$template_id, true);


		   $settings = [];
		   $settings['users_amount'] = get_post_meta( $template_id, 'users_amount', true);
		   $settings['from_name'] = get_post_meta( $template_id, 'from_name', true);
		   $settings['from_email'] = get_post_meta( $template_id, 'from_email', true);
		   $settings['email_subject'] = get_post_meta( $template_id, 'email_subject', true);
		   $settings['recipients'] = get_post_meta( $template_id, 'recipients', true);
		   $settings['tempalte_type'] = 'global'; //get_post_meta( $template_id, 'tempalte_type', true);
		   $settings['global_template'] = get_post_meta( $template_id, 'global_template', true);
		   $settings['custom_template'] = get_post_meta( $template_id, 'custom_template', true);
		   $settings['send_mail_to_all'] = get_post_meta( $template_id, 'send_mail_to_all', true);
   

		   // init variables
		   $from_name = $settings['from_name'];
		   $from_email = $settings['from_email'];
		   $email_subject = $settings['email_subject'];
   
		   $recipients = $settings['recipients'];
		   $tempalte_type = $settings['tempalte_type'];
		   $global_template = stripslashes( $settings['global_template'] );

		   $custom_template = stripslashes( $settings['custom_template'] );
		   $template_content = $global_template;
   
		   $email_amount_to_send = (int)$settings['users_amount'];
		   if( $email_amount_to_send == 0 ){
			   $email_amount_to_send = 20;
		   }


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
		
		   if( substr_count( $settings['send_mail_to_all'], 'user_cat_') > 0 ){
			   $terms_id_array = explode( '_', $settings['send_mail_to_all'] );
			   $terms_id2use = $terms_id_array[2];
			   $user_list = $wpdb->get_col( $wpdb->prepare( "SELECT user_id   FROM {$wpdb->usermeta} WHERE ( ( meta_key = 'can_vote' AND meta_value = '0' ) OR ( meta_key = 'can_vote' AND meta_value = '1' ) ) AND user_id IN (  SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_user_category' AND meta_value = '%d' )", $terms_id2use ) );
				
		   }

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
		   // big email log
		   $current_log = get_option('big_email_log' );
		   $current_log[] = array( 'date' => current_time('timestamp'), 'body' => $template_content_bkp, 'usercount' => $users_count, 'template_type' => $template_id, 'content' =>  $body, 'is_test' => false, 'send_report' => $email_sending_report );
		   update_option('big_email_log', $current_log );
   
		   update_option('hide_send_message_'.$template_id, '0');


	 
		   if(  $emails_amount_left <= 0 ){
	 
				$current_log = get_option('big_email_log' );
				$current_log[] = array( 'date' => current_time('timestamp'), 'body' => $template_content_bkp, 'usercount' => $users_count, 'template_type' => $template_id, 'content' =>  $body, 'is_test' => false, 'send_report' => $email_sending_report );
				update_option('big_email_log', $current_log );

				update_option('hide_send_message_'.$template_id, '0');
				update_post_meta( $template_id, 'last_run_hash', md5($template_date_timestamp) );

				update_post_meta($template_id, 'sending_finished_at',  current_time('timestamp') );

				 // last email sending
				 delete_post_meta($template_id, 'total_emails'  );
				 delete_post_meta($template_id, 'email_sending_report_'.$template_id );		
				 delete_post_meta($template_id, 'email_full_processed_items_'.$template_id );
			}
	

			// force cron run
			wp_remote_get( get_option('home') );

		}
	}
});

?>