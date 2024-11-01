<?php 
/**
 * allow only from URL
 */

add_Action('template_redirect', function(){
	global $post;
	
	if( is_single() || is_page() ){
		$content = apply_filters('the_content', $post->post_content );
		if( substr_count( $content, '%BLOCKPAGE%' ) > 0 ){
			wp_die( __("Access to this page is not allowed", 'wvp') );
		}
 
	}
	
});
add_shortcode('reflink', 'wvp_reflink');
function wvp_reflink( $atts, $content ){
	global $post;
	$ref_url = $atts['allowifcomingfrom'];
	if( rtrim( $ref_url, '/')  != rtrim( $_SERVER['HTTP_REFERER'], '/') ){
		return '%BLOCKPAGE%';
	}
}


/**
 * back to main user
 */
add_shortcode('backtomainuser', 'wvp_backtomainuser');
function wvp_backtomainuser( $atts, $content ){
	global $post, $current_user, $wpdb;
	$label = '';
	if( isset($atts['label']) ){
		$label = $atts['label'];
	}
	$all_who_gived = $wpdb->get_var("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'switched_to'  AND meta_value = {$current_user->ID}");
 
	if( $all_who_gived )
		return '<a href="'.wp_nonce_url( get_permalink( $post->ID ).'?user_id='.$current_user->ID, 'return_to_user_action', 'return_to_user_field', ).'">'.esc_html( $label ).'</a>';
}

/**
 * current logged in user
 */
add_shortcode('loggednowas', 'wvp_loggednowas');
function wvp_loggednowas( $atts, $content ){
	global $current_user, $wpdb;

	$title = '';
	if( isset($atts['title']) ){
		$title = $atts['title'];
	}
	$fields = [];
	 
	if( isset( $atts['fields'] ) ){
		$fields = explode(',', $atts['fields']);
		$fields = array_map('trim', $fields);
	} 

	$user_name = [];
		if( in_array( 'username', $fields ) ){
			$user_name[] = $current_user->user_login;
		}
		if( in_array( 'first_name', $fields ) ){
			$user_name[] = $current_user->first_name;
		}
		if( in_array( 'last_name', $fields ) ){
			$user_name[] = $current_user->last_name;
		}

	// check if this user recieve any proxy
	$switch_back_user = $wpdb->get_var("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'switched_to' AND meta_value = {$current_user->ID}");
 
	//$switch_back_user = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'donate_recipient' AND meta_value = {$current_user->ID}" );

	if( $switch_back_user ){
		return esc_html( $title ).' '.implode(' - ', $user_name);
	}

}


/**
 * session swap shortcode
 */
add_shortcode('loginas', 'wvp_loginas');
function wvp_loginas( $atts, $content ){

 
	// get all users with associated proxies
	global $wpdb, $current_user;

	$fields = [];
	 
	if( isset( $atts['fields'] ) ){
		$fields = explode(',', $atts['fields']);
		$fields = array_map('trim', $fields);
	}
		
 
	$all_who_gived = $wpdb->get_col( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'donate_recipient' AND meta_value = {$current_user->ID}" );
 
	$out = '
	<div class="tw-bs4">
	<form method="POST" >
	'.wp_nonce_field( 'switch_to_account_action', 'switch_to_account_field', false, false ).'
		<div class="row">
		<div class="col">
			<select name="login_to">
	';
	foreach( $all_who_gived as $s_user ){
		//name filling
		$user_data = get_user_by('ID', $s_user);
		$user_name = [];
		if( in_array( 'username', $fields ) ){
			$user_name[] = $user_data->user_login;
		}
		if( in_array( 'first_name', $fields ) ){
			$user_name[] = $user_data->first_name;
		}
		if( in_array( 'last_name', $fields ) ){
			$user_name[] = $user_data->last_name;
		}
	
		$out .= '<option value="'.$s_user.'">'.implode(' - ', $user_name);
	}
	$out .= '
			</select>
			<button class="btn btn-info" >'.__('Go', 'wvp').'</button>
		</div><!-- /.col -->
		<div class="col">
			
		</div><!-- /.col -->
		</div><!-- /.row -->
	</form>
	</div><!-- /.tw-bs4 -->
	';

	if( count($all_who_gived) > 0 )
		return $out;
}

/**
 * [registration_form] 
 */
add_shortcode('registration_form', 'wvp_registration_form');
function wvp_registration_form( $atts, $content ){
	$settings_original = get_option('wvp_regform_options');
	$settings = $settings_original['reg_form_settings'];
 
	$message = '';
	if( isset( $_GET['message'] ) ){
		if( $_GET['message'] == 'email_exists' ){
			$message = '
			<div class="alert alert-warning mt-2 mb-2">'.__('Email already exists', 'wvp').'</div><!-- /.alert alert-warning -->
			';
		}
		if( $_GET['message'] == 'reg_success' ){
			$message = '
			<div class="alert alert-success mt-2 mb-2">'.$settings_original['confirmation_message'].'</div><!-- /.alert alert-warning -->
			';
		}
		if( $_GET['message'] == 'user_exists' ){
			$message = '
			<div class="alert alert-warning mt-2 mb-2">'.$settings_original['user_approved_message'].'</div><!-- /.alert alert-warning -->
			';
		}
		if( $_GET['message'] == 'not_found' ){
			$message = '
			<div class="alert alert-warning mt-2 mb-2">'.$settings_original['user_not_found_message'].'</div><!-- /.alert alert-warning -->
			';
		}

		if( substr_count( $_GET['message'], 'attach_' ) ){
			$exploded = explode('_', $_GET['message']);
			$index = $exploded[1];
			$message = '
			<div class="alert alert-warning mt-2 mb-2">'.str_replace( '%n', $index, __('Attach %n have wrong format.', 'wvp') ).'</div><!-- /.alert alert-warning -->
			';
		}
		if( substr_count( $_GET['message'], 'size_' ) ){
			$exploded = explode('_', $_GET['message']);
			$index = $exploded[1];
			$message = '
			<div class="alert alert-warning mt-2 mb-2">'.str_replace( '%n', $index, __('Attach %n is too big.', 'wvp') ).'</div><!-- /.alert alert-warning -->
			';
		}
	}

	/**
	 * presaved data
	 */
	if( isset( $_COOKIE['registration_form'] ) ){
		parse_str( $_COOKIE['registration_form'], $output);
	}

	$out = '
	<div class="tw-bs4">
	
	'.$message.'

	<form method="POST" enctype="multipart/form-data"  id="registration_form"	>
	'.wp_nonce_field( 'reg_user_action', 'reg_user_name', false, false ).'

	<div class="form-group">
		<label for="exampleInputEmail1">'.esc_html( $settings['_filed_email_label'] ).'</label>
		<input type="email" required class="form-control" required id="input_email"  name="input_email" value="'.esc_html( $output['input_email'] ).'">
	</div>';

	if( $settings['_filed_username'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleInputEmail1">'.esc_html( $settings['_filed_username_label'] ).'</label>
		<input type="text" class="form-control" id="input_username" name="input_username"  '.( $settings['_mandatory_username'] == 'on' ? ' required ' : '' ).'  value="'.esc_html( $output['input_username'] ).'" >
	</div>';


	$out .= '
	<div class="form-group">
		<label for="exampleInputEmail1">'.esc_html( $settings['_filed_name_label'] ).'</label>
		<input type="text" class="form-control" required id="input_name" name="input_name"  value="'.esc_html( $output['input_name'] ).'" >
	</div>';

	if( $settings['_filed_lastname'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleInputEmail1">'.esc_html( $settings['_filed_lastname_label'] ).'</label>
		<input type="text" class="form-control" id="input_lastname" name="input_lastname" '.( $settings['_mandatory_lastname'] == 'on' ? ' required ' : '' ).'  value="'.esc_html( $output['input_lastname'] ).'" >
	</div>';
 
	$out .= '
	<div class="form-group">
		<label for="exampleInputPassword1">'.esc_html( $settings['_filed_password_label'] ).'</label>
		<input type="password" required class="form-control" id="input_password" name="input_password"  >
	</div>';

	if( $settings['_filed_attach1'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleFormControlFile1">'.esc_html( $settings['_filed_attach1_label'] ).'</label>
		<input type="file" class="form-control-file" name="attach_1" '.( $settings['_mandatory_attach1'] == 'on' ? ' required ' : '' ).' >
	</div>';

	if( $settings['_filed_attach2'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleFormControlFile1">'.esc_html( $settings['_filed_attach2_label'] ).'</label>
		<input type="file" class="form-control-file" name="attach_2" '.( $settings['_mandatory_attach2'] == 'on' ? ' required ' : '' ).' >
	</div>';
 
	if( $settings['_filed_attach3'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleFormControlFile1">'.esc_html( $settings['_filed_attach3_label'] ).'</label>
		<input type="file" class="form-control-file" name="attach_3" '.( $settings['_mandatory_attach3'] == 'on' ? ' required ' : '' ).' >
	</div>';

	if( $settings['_filed_attach4'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleFormControlFile1">'.esc_html( $settings['_filed_attach4_label'] ).'</label>
		<input type="file" class="form-control-file" name="attach_4" '.( $settings['_mandatory_attach4'] == 'on' ? ' required ' : '' ).' >
	</div>';

	if( $settings['_filed_attach5'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleFormControlFile1">'.esc_html( $settings['_filed_attach5_label'] ).'</label>
		<input type="file" class="form-control-file" name="attach_5" '.( $settings['_mandatory_attach5'] == 'on' ? ' required ' : '' ).' >
	</div>';

	if( $settings['_filed_message'] == 'on' )
	$out .= '
	<div class="form-group">
		<label for="exampleInputPassword1">'.esc_html( $settings['_filed_message_label'] ).'</label>
		<textarea class="form-control" id="input_message" name="input_message" '.( $settings['_mandatory_message'] == 'on' ? ' required ' : '' ).'  >'.esc_html( $output['input_message'] ).'</textarea>
	</div>';

	if( $settings['_filed_check1'] == 'on' )
	$out .= '
	<div class="form-check mb-3">
		<input type="checkbox" class="form-check-input mt-2" id="input_check1" name="input_check1" '.( $settings['_mandatory_check1'] == 'on' ? ' required ' : '' ).' >
    	<label class="form-check-label" for="exampleCheck1">'.  $settings['_filed_check1_label']  .'</label>
	</div>';


	if( $settings['_filed_check2'] == 'on' )
	$out .= '
	<div class="form-check mb-3">
		<input type="checkbox" class="form-check-input mt-2" id="input_check2" name="input_check2" '.( $settings['_mandatory_check2'] == 'on' ? ' required ' : '' ).' >
    	<label class="form-check-label" for="exampleCheck1">'.  $settings['_filed_check2_label']  .'</label>
	</div>';
	
	
	if( $settings['_filed_select1'] == 'on' ){
		$select_options = explode( ',', esc_html( $settings['_filed_select1_options_label'] ) );
		$select_options = array_map('trim', $select_options);

		$out .= '
		<div class="form-group ">
		
			<label class="form-check-label" for="exampleCheck1">'.esc_html( $settings['_filed_select1_label'] ).'</label>
			<select id="input_select1"  class="form-control" name="input_select1" '.( $settings['_mandatory_select1'] == 'on' ? ' required ' : '' ).' >';

			$out .= '<option value="">'.$settings['_filed_select1_label'].'</option>';	
			foreach( $select_options as $s_option ){
				$out .= '<option value="'.$s_option.'" '.(  $output['input_select1'] == $s_option ? ' selected ' : ''  ).' >'.$s_option.'</option>';	
			}
			$out .= '
			</select>
		</div>';
	}

	if( $settings['_filed_select2'] == 'on' ){
		$select_options = explode( ',', esc_html( $settings['_filed_select2_options_label'] ) );
		$select_options = array_map('trim', $select_options);

		$out .= '
		<div class="form-group ">
		
			<label class="form-check-label" for="exampleCheck2">'.esc_html( $settings['_filed_select2_label'] ).'</label>
			<select id="input_select2"  class="form-control" name="input_select2" '.( $settings['_mandatory_select2'] == 'on' ? ' required ' : '' ).' >';

			$out .= '<option value="">'.$settings['_filed_select2_label'].'</option>';	
			foreach( $select_options as $s_option ){
				$out .= '<option value="'.$s_option.'" '.(  $output['input_select2'] == $s_option ? ' selected ' : ''  ).' >'.$s_option.'</option>';	
			}
			$out .= '
			</select>
		</div>';
	}

	

	$out .= '


	<button type="submit" class="btn btn-primary">'.esc_html( $settings_original['form_submit_label'] ).'</button>
	</form>
	</div>
	';

	return $out;
}
/**
 * myshares
 */
add_shortcode('myshares', 'wvp_myshares');
function wvp_myshares( $atts, $content ){
	global $wpdb, $current_user;

	$settings = get_option('wvp_options');

	// checke not online users
	$memberlistoffline = false;
	if( isset( $atts['memberlistoffline'] ) ){
		$memberlistoffline = $atts['memberlistoffline'];
	}
	

	$all = false;
	if( isset($atts['all']) ){
		$all = true;
	}
	
	$myshares = false;
	if( isset($atts['myshares']) ){
		$myshares = true;
	}

	/** PATCH  */
	$myshares = true;

	$org_total_shares = (int)$settings['organization_total_shares'];


	$output_blocks =  $settings['quorum_table'];
	$show_online_users =  $settings['show_online_users'];
	$show_offline_users =  $settings['show_offline_users'];

	//$time_limit = current_time('timestamp') - (int)$settings['user_online_lifetime']*60;
	if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
		$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
	}
	if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
		$time_limit = strtotime( $settings['from_timestamp'] );
	}

	$all_users_that_cant_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value != '1' " );

	$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
 
	// remove administartors
	$tmp_cnt = [];
	if( count($all_users_that_caN_vote) > 0 ){
		foreach( $all_users_that_caN_vote as $_user ){
		 
			$user_extra_data = get_userdata(  $_user  );
			if( in_array( 'administrator',  $user_extra_data->roles ) ){
				continue;
			}
			$tmp_cnt[] = $_user;
		}
		$all_users_that_caN_vote  = $tmp_cnt;
	}
 
	if( count($all_users_that_caN_vote) > 0 ){
		$all_online_usrs = $wpdb->get_results("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id  IN ( ".implode( ",", $all_users_that_caN_vote )." )");
	}else{
		$all_online_usrs = [];
	}
	
  
	$total_shares = 0;

	$total_proxies = 0;

	$total_own_plus_recieved = 0;

	$user_per_category_info = [];


	if(  count($all_online_usrs) > 0 )
	foreach( $all_online_usrs as $s_user ){
		$own_shares = get_own_shares( $s_user->ID ); 
		$total_shares = get_total_shares( $s_user->ID );   
		//$total_proxies = $total_proxies + $own_shares + $total_shares;
		$total_proxies = $total_proxies + $own_shares ; //+ $total_shares;
		
		//$total_own_plus_recieved = $total_own_plus_recieved + get_proxys_amount( $s_user->ID ) + get_own_shares( $s_user->ID );
		$total_own_plus_recieved = $total_own_plus_recieved + get_total_shares( $s_user->ID ) + get_own_shares( $s_user->ID );


		$cat_id = get_user_meta( $s_user->ID, USER_CATEGORY_META_KEY, true );

		if( $cat_id ){
			$user_per_category_info[$cat_id][] = $s_user->ID;
		}

	}

	 

	//$val_c = $total_proxies*100 / $org_total_shares;
	// patching
	$val_c = ( $total_own_plus_recieved / $org_total_shares ) *100;

	// customize poll outptu
	$output_options = get_option('wvp_options');
	$columns_data = $output_options['quorum_settings']['show_item'];
	$labels_data = $output_options['quorum_settings']['block_value'];
	 
 
	$columns_table_data = $output_options['quorum_settings_table']['show_item'];
	$labels_table_data = $output_options['quorum_settings_table']['block_value'];

	$decimal_amount = (int)$output_options['decimal_amount'];
	$show_decimal_amount =  $output_options['show_decimal_amount'];
 
	// getting new col value
	$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
	$tmp_total_proxies = 0;

	
	foreach( $all_users_that_caN_vote as $s_user ){
		$tmp_total_proxies = $tmp_total_proxies + get_proxys_amount( $s_user );

		
	}

	$ctn_val_1 = count($all_online_usrs);
	$ctn_val_2 = count($all_users_that_caN_vote) + $tmp_total_proxies ;

	$new_col_val =  $ctn_val_1*100 /  $ctn_val_2;

	// get user category

	$out = '
	<div class="tw-bs4">
		';
	 
/**
 *  online users list to show and filter by myshares
 * 
 */

		if( 1==1 ){
	 
			$out .= '	
			<table class="table sortable_table online_users_list ">
				<thead class="">
				<tr>
				<th  class="no-sort">'.__('No.', 'wvp').'</th>
				';
				if( $columns_data['col_1'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[0].'</th>';
				}

				if( $columns_data['col_2'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[1].'</th>';
				}
				if( $columns_data['col_7'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[6].'</th>';
				}
				if( $columns_data['col_3'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[2].'</th>';
				}
				if( $columns_data['col_4'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[3].'</th>';
				}
				if( $columns_data['col_5'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[4].'</th>';
				}
				/** v1.15 */
				if( $columns_data['col_9'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[8].'</th>';
				}
				if( $columns_data['col_10'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[9].'</th>';
				}
				/** v1.15 END */

				if( $columns_data['col_6'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[5].'</th>';
				}
				if( $columns_data['col_8'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[7].'</th>';
				}
				
				if( $columns_data['col_11'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[10].'</th>';
				}

				$out .= '
				</tr>
				</thead>
				<tbody class="">';
	
				$all_users = $all_online_usrs;
				$total_results = [];
			 
				$cnt = 0;
				foreach( $all_users as $s_user ){

				/** myshres patch */
				/*
				if( 1 == 1 ){
					if( $s_user->ID  != $current_user->ID ){ continue; }
				}
				*/
			 
				if( $myshares ){
					if( $s_user->ID  != $current_user->ID ){ continue; }
				}

				$cnt++;

				$val_a =  get_own_shares( $s_user->ID );  
				$val_b = get_total_shares( $s_user->ID );   
	
				$val_a_b = $val_a + $val_b;
	 
				$s_user = get_user_by( 'ID', $s_user->ID );

				$user_cat = get_user_meta( $s_user->ID, USER_CATEGORY_NAME_META_KEY, true );

				/** V 1.5 */
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
				/** V 1.5 END */
		 
				$out .= '
				<tr>
					<td  class="no-sort">'.$cnt.'</td>
				';
					if( $columns_data['col_1'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->first_name.'</td>';
					}
					if( $columns_data['col_2'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->last_name.'</td>';
					}
					if( $columns_data['col_7'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->user_login.'</td>';
					}
					if( $columns_data['col_3'] == 'on' || $all ){
						$out .= '<td class="text-center col_3">'.number_format( $val_a, 2).'</td>';
					}
					if( $columns_data['col_4'] == 'on' || $all ){
						$out .= '<td class="text-center col_4" data-sort-value="'.get_proxys_amount( $s_user->ID ).'">'.get_proxys_amount( $s_user->ID ).'</td>';
					}
					if( $columns_data['col_5'] == 'on' || $all ){
						$out .= '<td class="text-center col_5">'.number_format( $val_b, 2, '.',',').'</td>';
					}
					/** v 1.15 */
					if( $columns_data['col_9'] == 'on' || $all ){
						$out .= '<td class="text-center col_9">'.implode( ', ', $user_first_name  ).'</td>';
					}
					if( $columns_data['col_10'] == 'on' || $all ){
						$out .= '<td class="text-center col_10">'.implode( ', ', $user_last_name  ).'</td>';
					}
					/** v 1.15 END */
					if( $columns_data['col_6'] == 'on' || $all ){

						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}


						$out .= '<td class="text-center col_6" data-sort-value="'.(float)number_format( $val_a_b, $decimal_amount2show ).'">'.number_format( $val_a_b, $decimal_amount2show ).'</td>';
					}
					if( $columns_data['col_8'] == 'on' || $all ){
						$out .= '<td class="text-center col_8">'.$user_cat.'</td>';
					}

					if( $columns_data['col_11'] == 'on' || $all ){
						$recieved_proxies = get_proxys_amount( $s_user->ID ) + 1;
						$out .= '<td class="text-center col_11">'.$recieved_proxies.'</td>';
					}
					
					
		 $out .= '
				</tr>';
				}
				$out .= '
				</tbody>
		 
			</table>';
		}
		 

		 


	$out .= '
	</div>
	';


	return $out;
}

/*
* if user can_vote == 1
*/
add_shortcode('if_canvote_1', 'wvp_if_canvote_1');
function wvp_if_canvote_1( $atts, $content ){
	global $current_user;
	$can_vote = get_user_meta( $current_user->ID, 'can_vote', true );
	if( $can_vote == '1' ){
		return apply_filters( 'the_content', $content );
	}
}

/*
* if user can_vote == 0
*/
add_shortcode('if_canvote_0', 'wvp_if_canvote_0');
function wvp_if_canvote_0( $atts, $content ){
	global $current_user;
	$can_vote = get_user_meta( $current_user->ID, 'can_vote', true );
	if( $can_vote == '0'  ){
		return apply_filters( 'the_content', $content );
	}
}


// chrome view functionality
add_shortcode('showonly', 'wvp_showonly');
function wvp_showonly( $atts, $content ){
	$logged = $atts['logged'];
	 
	if( $logged == '1' ){
		if( is_user_logged_in() ){
			return apply_filters( 'the_content', $content );
		}
	}
	if( $logged == '0' ){
		if( !is_user_logged_in() ){
			return apply_filters( 'the_content', $content );
		}
	}
}

add_shortcode('showonly_logged', 'wvp_showonly_logged');
function wvp_showonly_logged( $atts, $content ){
 
		if( is_user_logged_in() ){
			return apply_filters( 'the_content', $content );
		}
}

add_shortcode('showonly_unlogged', 'wvp_showonly_unlogged');
function wvp_showonly_unlogged( $atts, $content ){
 
	if( !is_user_logged_in() ){
		return apply_filters( 'the_content', $content );
	}
}


// custom profiel fields
add_shortcode('voter_first_name', 'wvp_voter_first_name');
function wvp_voter_first_name( $atts, $content ){
	global $current_user;
	$out = $current_user->first_name;
	return $out;
}
add_shortcode('voter_last_name', 'wvp_voter_last_name');
function wvp_voter_last_name( $atts, $content ){
	global $current_user;
	$out = $current_user->last_name;
	return $out;
}
add_shortcode('voter_username', 'wvp_voter_username');
function wvp_voter_username( $atts, $content ){
	global $current_user;
	$out = $current_user->user_login;
	return $out;
}
add_shortcode('voter_password', 'wvp_voter_password');
function wvp_voter_password( $atts, $content ){
	global $current_user;
	$out = get_user_meta( $current_user->ID, 'password_to_send', true);
	return $out;
}
add_shortcode('voter_custom1', 'wvp_voter_custom1');
function wvp_voter_custom1( $atts, $content ){
	global $current_user;
	$out = get_user_meta( $current_user->ID, 'custom1', true);
	return $out;
}
add_shortcode('voter_custom2', 'wvp_voter_custom2');
function wvp_voter_custom2( $atts, $content ){
	global $current_user;
	$out = get_user_meta( $current_user->ID, 'custom2', true);
	return $out;
}
add_shortcode('voter_own_shares', 'wvp_voter_own_shares');
function wvp_voter_own_shares( $atts, $content ){
	global $current_user;
	$out = get_own_shares( $current_user->ID );
	return $out;
}
add_shortcode('voter_received_shares', 'wvp_voter_received_shares');
function wvp_voter_received_shares( $atts, $content ){
	global $current_user;
	$out = get_total_shares( $current_user->ID );
	return $out;
}
add_shortcode('voter_own_plus_received_shares', 'wvp_voter_own_plus_received_shares');
function wvp_voter_own_plus_received_shares( $atts, $content ){
	global $current_user;
	$out = get_own_shares( $current_user->ID ) + get_total_shares( $current_user->ID );
	return $out;
}

// chrome view functionality
add_shortcode('is_chrome_ff', 'wvp_is_chrome_ff');
function wvp_is_chrome_ff( $atts, $content ){
	$browser_data =  getBrowser();
	if( $browser_data['name'] == "Google Chrome"  || $browser_data['name'] == "Mozilla Firefox" ){
		return apply_filters( 'the_content', $content );
	}
}
add_shortcode('not_chrome_ff', 'wvp_not_chrome_ff');
function wvp_not_chrome_ff( $atts, $content ){
	$browser_data =  getBrowser();
	if( $browser_data['name'] == "Google Chrome"  || $browser_data['name'] == "Mozilla Firefox" ){
		
	}else{
		return apply_filters( 'the_content', $content );
	}
}



// raffle functionality
add_shortcode('raffle', 'raffle_processing');
function raffle_processing(){
	global $current_user, $wpdb;


	$out = '
	<div class="tw-bs4">
		<div class="row">
			<div class="col-6 step_1_block">
				<div class="form-group">
					<label for="exampleFormControlTextarea1" class="h5">'.__('Write/Paste here the possible options  (1 per line)', 'wvp').'</label>
					<textarea class="form-control" id="possible_raffle_options" rows="10"></textarea>
				</div>
			</div>
			<div class="col-6 step_1_block">
				<div class="form-group">
					<label for="exampleFormControlTextarea1"  class="h5">'.__('How many winners:', 'wvp').'</label>
					<input type="text"   class="form-control" id="winners_amount" value="1" >
				</div>
			</div>
			<div class="col-12 text-center  step_1_block">
				<button type="submit" class="btn btn-primary run_the_raffle">'.__('Run the raffle', 'wvp').'</button>
			</div>

			<div class="col-12 d-none  step_2_block results_list">
				 <div class="block_title h5" style="font-size:50px;">'.__('List of winners:', 'wvp').'</div>
				 <div class="raffle_results"  style="font-size:50px;">

				 </div>
			</div>

			<div class="col-12 text-center d-none  step_2_block">
				<div class="row">
					<div class="col-4">
						<button type="submit" class="btn btn-primary run_another_raffle">'.__('Run another raffle with empty options', 'wvp').'</button>
					</div><!-- /.col-4 -->
					<div class="col-4">
						<button type="submit" class="btn btn-primary run_another_raffle_last_option">'.__('Run another raffle with last options', 'wvp').'</button>
					</div><!-- /.col-4 -->
					<div class="col-4">
						<button type="submit" class="btn btn-primary run_another_raffle_remove_winners">'.__('Run another raffle with last options, removing winners', 'wvp').'</button>
					</div><!-- /.col-4 -->
				</div><!-- /.row -->
				
			</div>

			<div class="col-12 text-center d-none winner_number_error mt-4 mb-4">
				<div class="alert alert-warning">'.__('Number of winners needs to be lower than the list of options', 'wvp').'</div>
			</div>
		</div>
	</div>
	';
	return $out;
}


// show user poll answer results
add_shortcode('mypollanswers', 'mypollanswers_processing');
function mypollanswers_processing(){
	global $current_user, $wpdb;

	//error_reporting(E_ALL);
	//ini_set('display_errors', 'On');

	$user_id = $current_user->ID;

	$out = '
	<style>
	.userdata_list{
		font-size:16px;
	}
	</style>
	<div class="tw-bs4">
	<div class="text-center h3">'.__('Polls answers received for user:', 'wvp').'</div>';
	$out .= '<div class="userdata_list text-center h3 mb-3">'.$current_user->user_login.' '.$current_user->first_name.' '.$current_user->last_name.'</div>';

	$out .= '
	
	

				<table class="table">
					<thead>
		 				<tr>
							<th class="text-center">'.__('Poll title', 'wvp').'</th>
				<!--		<th class="text-center">'.__('Poll Category', 'wvp').'</th>-->
							<th class="text-center">'.__('Date – time of your answer', 'wvp').'</th>
							<th class="text-center">'.__('Your answer was', 'wvp').'</th>
						</tr>
					</thead>
					<tbody>';
						 
				 
						$all_polls = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = 'user_{$user_id}' ");

						$poll_info_out = [];

						foreach( $all_polls as $s_poll ){
							$poll_info = get_post( $s_poll->post_id );
							$all_cats = [];
							$poll_cats = wp_get_post_terms( $s_poll->post_id, 'poll_category' );
							$poll_variants = get_post_meta( $s_poll->post_id, 'poll_variants', true );
							$poll_type = get_post_meta( $s_poll->post_id, 'poll_type', true );
							$out_votes = [];
							
							if( $poll_type == 'multi' ){
								$all_votes = get_post_meta( $s_poll->post_id, 'user_'.$user_id );
								foreach( $all_votes as $s_vote ){
									$out_votes[] = $poll_variants[$s_vote];
								}

							}else{
								$user_vote = get_post_meta( $s_poll->post_id, 'user_'.$user_id, true );
								$out_votes[] = $poll_variants[$user_vote];
							}
							foreach( $poll_cats as $s_cat ){
								$all_cats[] = $s_cat->name;
							}

							$answer_date = get_post_meta( $s_poll->post_id, 'uservotetime_'.$user_id, true );
							
							$poll_info_out[] = [
								'title' => $poll_info->post_title,
								'answers' => $out_votes,
								'date' => $answer_date
							];
						}

						$date = [];
						$date  = array_column($poll_info_out, 'date');
				 
						array_multisort($date, SORT_DESC,  $poll_info_out);

						$already_showed = [];
						foreach( $poll_info_out as $s_poll ){
							/*
							if( in_array( $s_poll['date'], $already_showed) ){
								continue;
							}
							*/
							$out .= '
							<tr>
								<td class="text-center">'.$s_poll['title'].'</td>
								<!-- <td>'.implode(',', (array)$s_poll['title'] ).'</td> -->
								<td class="text-center">'.( $s_poll['date'] ?  date('Y-m-d H:i:s', $s_poll['date'] ) : '' ).'</td>
								<td class="text-center">'.implode(',', (array)$s_poll['answers']).'</td>
							</tr>';
							$already_showed[] = $s_poll['date'];
						}
					 
		$out .= '				
					</tbody>
				</table>

	</div>';

	return $out;
}


// fancy bloxks

//[polls_popup “link text here"],  [polls_answers_popup “link text here”] and [quorum_popup “link text here”] 
add_shortcode( 'openpollpopup', 'wvp_openpollpopup' );
function wvp_openpollpopup( $atts, $content = null ){
	global $current_user, $wpdb;

	$user_list = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1'" );

	if( !in_array( $current_user->ID, $user_list) ){
		return '';
	}

	$link = '';
	if( isset( $atts['link'] ) )
	$link = $atts['link'];

	$iframe_url = '';
	if( isset( $atts['iframe_url'] ) )
	$iframe_url = $atts['iframe_url'];
	//showallpolls
	return '
	<div class="ajax_showpoll_marker"></div>
	
	<a href="#" style="display:none;" class="shortcode_link is_hidden_link show_vote_popup_link" data-shortcode="" data-iframe="'.$iframe_url.'" >'.$link.'</a>';
}


add_shortcode( 'polls_popup', 'wvp_polls_popup' );
function wvp_polls_popup( $atts, $content = null ){
	
	$link = '';
	if( isset( $atts['link'] ) )
	$link = $atts['link'];

	$iframe_url = '';
	if( isset( $atts['iframe_url'] ) )
	$iframe_url = $atts['iframe_url'];
	//showallpolls
	return '<a href="#" class="shortcode_link" data-shortcode="" data-iframe="'.$iframe_url.'" >'.$link.'</a>';
}

add_shortcode( 'polls_answers_popup', 'wvp_polls_answers_popup' );
function wvp_polls_answers_popup( $atts, $content = null ){
	$link = '';
	if( isset( $atts['link'] ) )
	$link = $atts['link'];
	return '<a href="#" class="shortcode_link" data-shortcode="showallpollsanswers" >'.$link.'</a>';
}

add_shortcode( 'quorum_popup', 'wvp_quorum_popup' );
function wvp_quorum_popup( $atts, $content = null ){
	$link = '';
	if( isset( $atts['link'] ) )
	$link = $atts['link'];
	return '<a href="#" class="shortcode_link" data-shortcode="quorum" >'.$link.'</a>';
}

// currentusers
add_shortcode( 'currentusers', 'wvp_currentusers' );
function wvp_currentusers( $atts, $content = null ){
		global $wpdb, $post;


		$settings = get_option('wvp_options');
		//$time_limit = current_time('timestamp') - (int)$settings['user_online_lifetime']*60;
		if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
			$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
		}
		if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
			$time_limit = strtotime( $settings['from_timestamp'] );
		}

		// all who can vote
		$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );

		// all online users
		if( count($all_users_that_caN_vote) > 0 ){
			$all_online_usrs = $wpdb->get_col("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id  IN ( ".implode( ",", $all_users_that_caN_vote )." )");
		}else{
			$all_online_usrs = [];
		}
		
	 

		$all_offline_users = array_diff( $all_users_that_caN_vote, $all_online_usrs );

		$out .= '	
		<div class="currentusers_container tw-bs4">
			<div class="selector_cont text-right">
				<select id="user_type">
					<option value="all">'.__('All','wvp').'
					<option value="online">'.__('Only Online','wvp').'
					<option value="offline">'.__('Only Offline','wvp').'
				</select>
			</div>
		<table class="table sortable_table">
			<thead class="">
			<tr>';
		
				$out .= '
				<th class="">'.__('Name','wvp').'</th>';
			
				$out .= '
				<th class="">'.__('Last Name','wvp').'</th>';
			
				$out .= '
				<th class="">'.__('Username','wvp').'</th>';
			
				$out .= '
				<th class="">'.__('Own Shares (A), ','wvp').'</th>';
			
				$out .= '
				<th class="">'.__('Proxies Received','wvp').'</th>';
		
				$out .= '
				<th class="">'.__('Shares of proxies received (B), ','wvp').'</th>';
				$out .= '
				<th class="">'.__('Total Sshares (A+B)','wvp').'</th>';
		
			$out .= '
			</tr>
			</thead>
			<tbody class="">';

			$all_users = get_users();
			$total_results = [];
	 
			foreach( $all_users as $s_user ){

			// check if user is online or offline
			if( in_array( $s_user->ID, $all_online_usrs ) ){
				$user_status = ' user_is_online ';
			}
			if( in_array( $s_user->ID, $all_offline_users ) ){
				$user_status = ' user_is_offline ';
			}


			// show only can vote users
			$user_can_vote = get_user_meta( $s_user->ID, 'can_vote', true );
			if( $user_can_vote != '1' ){ continue; }

			$val_a = get_own_shares( $s_user->ID ); 
			$val_b = get_total_shares( $s_user->ID );  

			$val_a_b = $val_a + $val_b;
 
			$s_user = get_user_by( 'ID', $s_user->ID );
			$out .= '
			<tr class="single_user '.$user_status.'">';
				$out .= '<td class="">'.$s_user->first_name.'</td>';
	
				$out .= '<td class="">'.$s_user->last_name.'</td>';
				$out .= '<td class="">'.$s_user->user_login.'</td>';
			
				$out .= '<td class="">'.$val_a.'</td>';
			
				$out .= '<td class="">'.get_proxys_amount( $s_user->ID ).'</td>';
			
				$out .= '<td class="">'.$val_b.'</td>';
				
				$out .= '<td class="">'.$val_a_b.'</td>';
			

	 $out .= '
			</tr>';
			}
			$out .= '
			</tbody>
	 
		</table>

			<div class="selector_cont text-right">
				<a class="btn btn-success"  href="'.get_permalink( $post->ID ).'?exportcsv=currentusers">'.__('Export as CSV', 'wvp').'</a>
			</div>
		</div>';
	return $out;
}

// showallpolls
add_shortcode( 'showallpolls', 'wvp_showallpolls' );
function wvp_showallpolls( $atts, $content = null ){
 
	//get all ids
	global $wpdb, $current_user;

	$all_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'show_polls_shortcode' AND meta_value = 'on'");

	


	if( isset( $atts['ids'] ) ){
		$all_ids  =  explode(',', $atts['ids']);
	}

	// category
	if( isset($atts['category']) ){

		$args = array(
			'post_type' => 'poll',
			'tax_query' => array(
				array(
					'taxonomy' => 'poll_category',
					'field' => 'term_id',
					'terms' => $atts['category']
				)
			),
			'fields' => 'ids'
		);
		$all_ids = get_posts( $args );
		
	}

	$out = '<div class="all_polls_list">';
	if(count($all_ids) > 0){

		foreach( $all_ids as $s_id ){

			$show_polls_shortcode = get_post_meta( $s_id, 'show_polls_shortcode', true );
			if( $show_polls_shortcode != 'on' ){ continue; }
			// show only voters patch


			
			$voters_string = '';
			/*
			$show_only_voters = get_post_meta( $s_id, 'show_only_voters', true );
			$voters_string = '';
			if( $show_only_voters == 'on' ){

			}
			*/
			$out .= '<div class="single_poll_out">'.apply_filters('the_content',  "[show_poll id='".$s_id."' ".$voters_string." ]" ).'</div>';
		}
	}else{
		$out .= '
				<div class="alert alert-warning">'.__('There are no polls to show at this moment','wvp').'</div>
				';
	}
	$out .= '</div>';
	return $out;
}

add_shortcode( 'results_all', 'wvp_results_all' );
function wvp_results_all( $atts, $content = null ){

	if( isset($atts['showusersthatvoted']) ){
		$showusersthatvoted = ' showusersthatvoted="" ';
	}
	if( isset($atts['showmissingusers']) ){
		$showmissingusers = ' showmissingusers="" ';
	}
	if( isset($atts['showanswers']) ){
		$showanswers = ' showanswers="" ';
	}
	/*
	if( isset($atts['hidedatatable']) ){
		$hidedatatable = ' hidedatatable="" ';
	}
	*/

	return do_shortcode("[showallpollsanswers ".$showusersthatvoted." ".$showmissingusers." ".$showanswers." ".$hidedatatable." all='1' ]");
}

// showallpolls
add_shortcode( 'showallpollsanswers', 'wvp_showallpollsanswers' );
function wvp_showallpollsanswers( $atts, $content = null ){
	//get all ids
	global $wpdb;

	$all = false;
	if( isset($atts['all']) ){
		$all = true;

	}

	$is_pdf_patch = 0;
	if( isset($atts['ispdf']) ){
		$is_pdf_patch = 1;

	}

	$args = array(
		'post_type' => 'poll',
		'fields' => 'ids',
		'showposts' => -1
	);
	$all_ids = get_posts( $args );

	if( isset( $atts['ids'] ) ){
		$all_ids  =  explode(',', $atts['ids']);
	}

	if( isset($atts['showusersthatvoted']) ){
		$showusersthatvoted = ' showusersthatvoted="" ';
	}
	if( isset($atts['showmissingusers']) ){
		$showmissingusers = ' showmissingusers="" ';
	}
	if( isset($atts['showanswers']) ){
		$showanswers = ' showanswers="" ';
	}
	if( isset($atts['hidedatatable']) ){
		$hidedatatable = ' hidedatatable="" ';
	}

	// category
	if( isset($atts['category']) ){

		$args = array(
			'post_type' => 'poll',
			'tax_query' => array(
				array(
					'taxonomy' => 'poll_category',
					'field' => 'term_id',
					'terms' => $atts['category']
				)
			),
			'fields' => 'ids'
		);
		$all_ids = get_posts( $args );
		
	}
 
	$out = '';
	 
	if(count($all_ids) > 0  ){
		$count_show = 0;
		foreach( $all_ids as $s_id ){
		 
			$show_data_table = get_post_meta( $s_id, 'show_data_table', true );
			
			$hidedatatable = '';
			$showmissingusers = '';
			$showusersthatvoted = '';
			$showanswers = '';

			$show_poll = 0;
			if( $show_data_table == 'on' ){
				$hidedatatable = '   ';
				$show_poll = 1;
			}else{
				$hidedatatable = ' hidedatatable="" ';
				
			}
			$list_not_voted = get_post_meta( $s_id, 'list_not_voted', true );
			if( $list_not_voted == 'on' || $all ){
				$showmissingusers = ' showmissingusers="" ';
				$show_poll = 1;
			}

			$list_that_voted = get_post_meta( $s_id, 'list_that_voted', true );
			if( $list_that_voted == 'on' || $all ){
				$showusersthatvoted = ' showusersthatvoted="" ';
				$show_poll = 1;
			}

			$show_user_answers = get_post_meta( $s_id, 'show_user_answers', true );
			if( $show_user_answers == 'on' || $all ){
				$showanswers = ' showanswers="" ';
				$show_poll = 1;
			}
		
			if( $show_poll == 1 ){
				$count_show++;
				$out .= '<div class="single_poll_out">'.apply_filters('the_content',  "[show_poll_results ".( $is_pdf_patch == 1 ? ' ispdf="1" ' : ' ispdf="0" ' )." ".( $all ? ' all="1" ' : '' )."  id='".$s_id."'  ".$showusersthatvoted." ".$showmissingusers." ".$showanswers." ".$hidedatatable."  ]" ).'</div>';
			} 
			
		}

		if( $count_show == 0 ){
			$out .= '
				<div class="alert alert-warning">'.__('There are no polls to show at this moment','wvp').'</div>
				';
		}

	}else{
		$out .= '
		<div class="alert alert-warning">'.__('There are no polls to show at this moment','wvp').'</div>
		';
	}
	return $out;
}


// showallpolls
add_shortcode( 'showallpollsanswers_audit', 'wvp_showallpollsanswers_audit' );
function wvp_showallpollsanswers_audit( $atts, $content = null ){
	//get all ids
	global $wpdb;

	$showusersthatvoted = '';
	$showmissingusers = '';
	$showanswers = '';
	$hidedatatable = '';

	$all = false;
	if( isset($atts['all']) ){
		$all = true;
	}

	$all = true;

	$args = array(
		'post_type' => 'poll',
		'fields' => 'ids',
		'showposts' => -1,
		'meta_query' => [
			[
				'key' => 'show_polls_shortcode',
				'value' => 'on'
			]
		]
	);
	$all_ids = get_posts( $args );

	if( isset( $atts['ids'] ) ){
		$all_ids  =  explode(',', $atts['ids']);
	}

	if( isset($atts['showusersthatvoted']) ){
		$showusersthatvoted = ' showusersthatvoted="" ';
	}
	if( isset($atts['showmissingusers']) ){
		$showmissingusers = ' showmissingusers="" ';
	}
	if( isset($atts['showanswers']) ){
		$showanswers = ' showanswers="" ';
	}
	if( isset($atts['hidedatatable']) ){
		$hidedatatable = ' hidedatatable="" ';
	}
	/*
	// category
	if( isset($atts['category']) ){

		$args = array(
			'post_type' => 'poll',
			'tax_query' => array(
				array(
					'taxonomy' => 'poll_category',
					'field' => 'term_id',
					'terms' => $atts['category']
				)
			),
			'fields' => 'ids'
		);
		$all_ids = get_posts( $args );
		
	}
	*/
 
	$out = '';
	 
	if(count($all_ids) > 0  ){
		$count_show = 0;
		foreach( $all_ids as $s_id ){
			$show_poll = 1;
			
			/*
			$show_data_table = get_post_meta( $s_id, 'show_data_table', true );
			$show_poll = 0;
			if( $show_data_table == 'on' ){
				$hidedatatable = '   ';
				$show_poll = 1;
			}else{
				$hidedatatable = ' hidedatatable="" ';
				
			}
			*/
			
			/*
			***
			$list_not_voted = get_post_meta( $s_id, 'list_not_voted', true );
			if( $list_not_voted == 'on' || $all ){
				$showmissingusers = ' showmissingusers="" ';
				$show_poll = 1;
			}
			

			$list_that_voted = get_post_meta( $s_id, 'list_that_voted', true );
			if( $list_that_voted == 'on' || $all ){
				$showusersthatvoted = ' showusersthatvoted="" ';
				$show_poll = 1;
			}
			***
			*/
			/*
			$show_user_answers = get_post_meta( $s_id, 'show_user_answers', true );
			if( $show_user_answers == 'on' || $all ){
				$showanswers = ' showanswers="" ';
				$show_poll = 1;
			}
			*/
		
			if( $show_poll == 1 ){
				$count_show++;
				$out .= '<div class="single_poll_out">'.apply_filters('the_content',  "[show_poll_results ".( $all ? ' all="1" ' : '' )."  id='".$s_id."'  ".$showusersthatvoted." ".$showmissingusers." ".$showanswers." ".$hidedatatable."  ]" ).'</div>';
			} 
			
		}

		if( $count_show == 0 ){
			$out .= '
				<div class="alert alert-warning">'.__('There are no polls to show at this moment','wvp').'</div>
				';
		}

	}else{
		$out .= '
		<div class="alert alert-warning">'.__('There are no polls to show at this moment','wvp').'</div>
		';
	}
	return $out;
}

//. assign proxy
add_shortcode( 'assign_proxy', 'wvp_assign_proxy' );
function wvp_assign_proxy( $atts, $content = null ){
	global $current_user, $post, $wpdb;

	$date_limit = '';
	if( isset($atts['date']) ){
		$date_limit = $atts['date'];
	}
	
	$time_limit = '';
	if( isset( $atts['time'] ) ){
		$time_limit = $atts['time'];
	}
	
	$full_timestamp = strtotime( $date_limit.' '.$time_limit ); 

	$current_time = current_time('timestamp');

	// user data
	//OLD$can_vote = get_user_meta( $current_user->ID, 'can_vote', true );
	$can_vote = get_own_shares( $current_user->ID );  
 
	if( isset($_GET['max_amount_reached']) ){
		$wvp_data_options = get_option('wvp_data_options');
		$max_proxy_amount = (int)$wvp_data_options;

		$error_message = '
		<div class="tw-bs4">
			<div class="alert alert-warning mt-3 mb-3">'.str_replace( '%%', $max_proxy_amount, __('Sorry, this user has already reached the maximum number of proxies allowed by the system, which is %%. Please try another one', 'wvp') ).'</div>
		</div>
		';	
	}

	if( isset($_GET['email_exists']) ){
		$error_message = '
		<div class="tw-bs4">
			<div class="alert alert-warning mt-3 mb-3">'. __('Sorry, user already exists in system.', 'wvp') .'</div>
		</div>
		';	
	}

	if( (int)$can_vote == 0  && 1==2){
		return '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.__('Sorry, you not allowed to assign proxies', 'wvp').'</div>
		</div>
		';	
	}

	// check logged in
	if( !is_user_logged_in() ){
		return '
		<div class="tw-bs4">
			<div class="alert alert-success">'.__('You need to be logged in to vote.', 'wvp').'</div>
		</div>
		';
	}

	// add error messages
	$out = $error_message;

	// prosy data table
	$out .= '<div class="tw-bs4">';
	$out .= '<h2>'.__('Proxies that I have received:','wvp').'</h2>';

	$out .= '<table class="table">
		<thead>
		<tr>
			<th>'.__('Proxies given by','wvp').'</th>
			<th>'.__('Date when the proxy was assigned (YYYY-MM-DD)','wvp').'</th>';

			/*
		$out .= '
			<th>'.__('Current status (Accepted or Rejected)','wvp').'</th>
			<th>'.__('Action','wvp').'</th>';
		*/

	$out .= '
		</tr>
		</thead>
		<tbody>';
		$all_rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}usermeta WHERE meta_key = 'donate_recipient' AND meta_value = {$current_user->ID}");
 
		foreach( $all_rows as $s_row ){
			$user_data = get_user_by('ID', $s_row->user_id);
			$sending_date = get_user_meta( $s_row->user_id, 'assign_date', true );
			$out .= '
			<tr>
				<td>'.$user_data->first_name.' - '.$user_data->last_name.' - '.$user_data->user_login.'</td>
				<td>'.date('Y-m-d', $sending_date ).'</td>';

				/*
				$out .= '
				<td>Accepted</td>
				<td class="">[Accept proxy][Reject proxy]</td>';
				*/
				$out .= '
			</tr>';
		}
		

	$out .= '
		</tbody>';
	$out .= '</table>';
	$out .= '</div>';


	if( $current_time > $full_timestamp ){
		// time passed
		$out .= '<div class="tw-bs4">';
		$user_donated = get_user_meta( $current_user->ID, 'already_donated', true );
		
		if( $user_donated == 'yes' ){
			$out .= '<form action="" method="POST" class="">
			<input type="hidden" name="remove_proxy"  value="1" />
			<input type="hidden" name="parent_page"  value="'.$post->ID.'" />
			';
			$donate_recipient = get_user_meta( $current_user->ID, 'donate_recipient', true );
			$userdata = get_user_by('ID', $donate_recipient );
	 
			$out .= '
			<div class="alert alert-info">'.sprintf( __('You have given a proxy to: %s – %s – %s', 'wvp'), $userdata->first_name, $userdata->last_name, $userdata->user_login ) .'</div>
			';
			$out .= '<button class="btn btn-info" onclick="return confirm(\''.__('Are you sure? ', 'wvp').'\')" >'.__('Remove Given Proxy','wvp').'</button>';
			$out .= '</form>';
		}else{
			$out .= '
			<div class="alert alert-info">'.__('Time to share passed', 'wvp').'</div>
			';
		}
		$out .= '</div>';
	}else{
		// time not passed
	$out .= '<div class="tw-bs4">';
		// check if user gave his shares
		$user_donated = get_user_meta( $current_user->ID, 'already_donated', true );
	 
		if( $user_donated == 'yes' ){
			$out .= '<form action="" method="POST" class="">
			<input type="hidden" name="remove_proxy"  value="1" />
			<input type="hidden" name="parent_page"  value="'.$post->ID.'" />
			';
			$donate_recipient = get_user_meta( $current_user->ID, 'donate_recipient', true );
			$userdata = get_user_by('ID', $donate_recipient );
	 
			$out .= '
			<div class="alert alert-info">'.__('You have given a proxy to: '.$userdata->first_name.' – '.$userdata->last_name.' – '.$userdata->user_login.'', 'wvp').'</div>
			';
			$out .= '<button class="btn btn-info" onclick="return confirm(\''.__('Are you sure? ', 'wvp').'\')" >'.__('Remove Given Proxy','wvp').'</button>';
			$out .= '</form>';
		}else{
			$out .= '
			<form action="" method="POST" class="" id="assign_proxy_form" enctype="multipart/form-data">
			<input type="hidden" name="parent_page"  value="'.$post->ID.'" />
			<div class="alert alert-success">'.__('You have not given any proxy', 'wvp').'</div>
 
			<div class="form-group">
				<label>'.__('Assign proxy to external user', 'wvp').'</label>
				<select name="assign_proxies_to_external" id="assign_proxies_to_external"  class="form-control "  >
					<option value="no">'.__('No','wvp').'</option>
					<option value="yes">'.__('Yes','wvp').'</option>
				</select>
			</div>

			<div class="form-group for_external" style="display:none;">
				<label for="exampleFormControlFile1">'.__('Name','wvp').'</label>
				<input type="text" name="external_name"   class="form-control" id="external_name" >
			</div>

			<div class="form-group  for_external" style="display:none;">
				<label for="exampleFormControlFile1">'.__('Email','wvp').'</label>
				<input type="email" name="external_email"   class="form-control" id="external_email" >
			</div>


			<div class="form-group not_external">
				<label>'.__('Give a proxy with your shares to another user', 'wvp').'</label>
				<select name="assign_proxies"  class="form-control selectizer" required >
					<option>'.__('Select User','wvp').'</option>';
					$all_users = get_users(array(
						'role' => 'subscriber'
					));

					$wvp_data_options =  get_option('wvp_data_options');
					
					foreach( $all_users as $s_user ){
						if( $s_user->ID == $current_user->ID ){ continue; }

						// if user banned
						if( !$wvp_data_options['users_cant_recieve_proxies'] || $wvp_data_options['users_cant_recieve_proxies'] === null ){
							$wvp_data_options['users_cant_recieve_proxies'] = [];
						}
						if( in_array( $s_user->ID, $wvp_data_options['users_cant_recieve_proxies'] ) ){ continue; }

						$out .= '<option value="'.$s_user->ID.'">'.$s_user->first_name.' - '.$s_user->last_name.' - '.$s_user->user_login.'</option>';
					}

				$out .= '
				</select>
			</div>
			 
	

		
				<div class="form-group">
					<label for="exampleFormControlFile1">'.__('Attach documents','wvp').'</label>
					<input type="file" name="attached_documents[]" multiple class="form-control-file" id="exampleFormControlFile1">
				</div>
	

			<div class="form-group">
				<label for="exampleFormControlTextarea1">'.__('Message','wvp').'</label>
				<textarea class="form-control" name="_assign_message"  rows="5"></textarea>
			</div>

			<div class="form-group">
				<button class="btn btn-success">'.__('Assign Proxies','wvp').'</button>
			</div>
	 
	 
			</form>
			';

		}
		$out .= '</div>';

	}

	
	

	

	

	
	

	
	

	return $out;
}

add_shortcode( 'quorum_all', 'wvp_quorum_all' );
function wvp_quorum_all( $atts, $content = null ){
	return do_shortcode('[quorum all="1"]');
}

/**
 * Quorm shortcode functionality
 */
add_shortcode( 'quorum', 'wvp_quorum' );
function wvp_quorum( $atts, $content = null ){
	global $wpdb, $current_user;
 
	$settings = get_option('wvp_options');

	// checke not online users
	$memberlistoffline = false;
	if( isset( $atts['memberlistoffline'] ) ){
		$memberlistoffline = $atts['memberlistoffline'];
	}
	

	$all = false;
	if( isset($atts['all']) ){
		$all = true;
	}
	
	$myshares = false;
	if( isset($atts['myshares']) ){
		$myshares = true;
	}

	$org_total_shares = (int)$settings['organization_total_shares'];

	if( !isset( $settings['quorum_table'] ) ){
		$settings['quorum_table'] = '';
	}

	$output_blocks =  $settings['quorum_table'];
	$show_online_users =  $settings['show_online_users'];
	$show_offline_users =  $settings['show_offline_users'];

	//$time_limit = current_time('timestamp') - (int)$settings['user_online_lifetime']*60;
	if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
		$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
	}
	if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
		$time_limit = strtotime( $settings['from_timestamp'] );
	}

	$all_users_that_cant_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value != '1' " );

	$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
 
	// remove administartors
	$tmp_cnt = [];
	if( count($all_users_that_caN_vote) > 0 ){
		foreach( $all_users_that_caN_vote as $_user ){
		 
			$user_extra_data = get_userdata(  $_user  );
			if( in_array( 'administrator',  $user_extra_data->roles ) ){
				continue;
			}
			$tmp_cnt[] = $_user;
		}
		$all_users_that_caN_vote  = $tmp_cnt;
	}
 
	if( count($all_users_that_caN_vote) > 0 ){
		$all_online_usrs = $wpdb->get_results("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id  IN ( ".implode( ",", $all_users_that_caN_vote )." )");
	}else{
		$all_online_usrs = [];
	}
 	 
	$total_shares = 0;

	$total_proxies = 0;
	$total_recieved_proxies = 0;

	$total_own_plus_recieved = 0;

	$user_per_category_info = [];


	if(  count($all_online_usrs) > 0 )
	foreach( $all_online_usrs as $s_user ){
		$own_shares = get_own_shares( $s_user->ID ); 
		$total_shares = get_total_shares( $s_user->ID );   
		//$total_proxies = $total_proxies + $own_shares + $total_shares;
		$total_proxies = $total_proxies + $own_shares ; //+ $total_shares;
		
		//$total_own_plus_recieved = $total_own_plus_recieved + get_proxys_amount( $s_user->ID ) + get_own_shares( $s_user->ID );
		$total_own_plus_recieved = $total_own_plus_recieved + get_total_shares( $s_user->ID ) + get_own_shares( $s_user->ID );

		
		$total_recieved_proxies = $total_recieved_proxies + get_proxys_amount( $s_user->ID );
	 
		$cat_id = get_user_meta( $s_user->ID, USER_CATEGORY_META_KEY, true );

		if( $cat_id ){
			$user_per_category_info[$cat_id][] = $s_user->ID;
		}

	}

	 

	//$val_c = $total_proxies*100 / $org_total_shares;
	// patching
	if( $org_total_shares == 0 ){
		$val_c = 0;
	}else{
		$val_c = ( $total_own_plus_recieved / $org_total_shares ) *100;
	}
	

	// customize poll outptu
	$output_options = get_option('wvp_options');
	$columns_data = $output_options['quorum_settings']['show_item'];
	$labels_data = $output_options['quorum_settings']['block_value'];
	 
 
	$columns_table_data = $output_options['quorum_settings_table']['show_item'];
	$labels_table_data = $output_options['quorum_settings_table']['block_value'];
	$decimal_amount = (int)$output_options['decimal_amount'];
	$show_decimal_amount =  $output_options['show_decimal_amount'];
 
	// getting new col value
	$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );
	$tmp_total_proxies = 0;

	
	foreach( $all_users_that_caN_vote as $s_user ){
		$tmp_total_proxies = $tmp_total_proxies + get_proxys_amount( $s_user );

		
	}

	$ctn_val_1 = count($all_online_usrs);
	$ctn_val_2 = count($all_users_that_caN_vote) + $tmp_total_proxies ;

	$new_col_val =  $ctn_val_1*100 /  $ctn_val_2;

	// get user category

	$out = '
	<div class="tw-bs4">
		<table class="table quorum_data_table">
			<tbody>';
			if( $columns_table_data['col_1'] == 'on' || $all ){
			$out .= '
				<tr>
					<td class="">'.$labels_table_data[0].'</td>
					<td class="">'.count($all_online_usrs).'</td>
					
				</tr>';
			}
			if( $columns_table_data['col_2'] == 'on' || $all ){

				$decimal_amount2show = $decimal_amount;
				if( $show_decimal_amount == 'no' ){
					$decimal_amount2show = 0;
				}

			$out .= '
				<tr>
					<td class="">'.$labels_table_data[1].'</td>
					<td class="">'.number_format( $total_proxies, $decimal_amount2show ).'</td>
					
				</tr>';
			}
			if( $columns_table_data['col_3'] == 'on' || $all ){
			$out .= '
				<tr>
					<td class="">'.$labels_table_data[2].'</td>
					<td class="">'.number_format( $val_c, $decimal_amount ).'%</td>
				</tr>';
			}

			if( $columns_table_data['col_4'] == 'on' || $all ){

				$decimal_amount2show = $decimal_amount;
				if( $show_decimal_amount == 'no' ){
					$decimal_amount2show = 0;
				}

			$out .= '
				<tr>
					<td class="">'.$labels_table_data[3].'</td>
					<td class="">'.number_format( $new_col_val, $decimal_amount2show ).'%</td>
					
				</tr>';
			}
			if( $columns_table_data['col_5'] == 'on' || $all ){

				$decimal_amount2show = $decimal_amount;
				if( $show_decimal_amount == 'no' ){
					$decimal_amount2show = 0;
				}

				$out .= '
					<tr>
						<td class="">'.$labels_table_data[4].'</td>
						<td class="">'.number_format( $org_total_shares, $decimal_amount2show, '.', ',').'</td>
						
					</tr>';
				}
			if( $columns_table_data['col_6'] == 'on' || $all ){

				$decimal_amount2show = $decimal_amount;
				if( $show_decimal_amount == 'no' ){
					$decimal_amount2show = 0;
				}

				$out .= '
					<tr>
						<td class="">'.$labels_table_data[5].'</td>
						<td class="">'.number_format( $total_own_plus_recieved, $decimal_amount2show, '.', ',' ) .'</td>
							
					</tr>';
				}
			if( $columns_table_data['col_7'] == 'on' || $all ){

				//$user_per_category_info[$cat_id][] = $s_user->ID;
				$cat_info = [];
				foreach( $user_per_category_info as $term_id => $users ){
					$term = get_term( $term_id );
					$cat_info[] = $term->name.': '.count( $users ).'<br/>';
				}
				$out .= '
					<tr>
						<td class="">'.$labels_table_data[6].'</td>
						<td class="">'.implode( '', $cat_info ) .'</td>
							
					</tr>';
				}
			if( $columns_table_data['col_8'] == 'on' || $all ){

				//$user_per_category_info[$cat_id][] = $s_user->ID;
				$cat_info = [];
				foreach( $user_per_category_info as $term_id => $users ){
					$term = get_term( $term_id );

					//getting total
					$total_amount = 0;
		 
					foreach( $users as $s_user ){
					 
						$val_a =  get_own_shares( $s_user  );  
						$val_b = get_total_shares( $s_user  );   
			
						$val_a_b = $val_a + $val_b;

						$total_amount = $total_amount + $val_a_b;
				 
					}

					$cat_info[] = $term->name.': '.number_format( $total_amount, $decimal_amount).'<br/>';
				}
	 
				$out .= '
					<tr>
						<td class="">'.$labels_table_data[7].'</td>
						<td class="">'.implode( '', $cat_info ) .'</td>
							
					</tr>';
				}
				if( $columns_table_data['col_9'] == 'on' || $all ){

		
					$percent_of_shares = ( ( $total_own_plus_recieved ) * 100 ) /  $org_total_shares ;
				 
					$out .= '
						<tr>
							<td class="">'.$labels_table_data[8].'</td>
							<td class="">'.number_format( $percent_of_shares, $decimal_amount ).'</td>
								
						</tr>';
				}
				if( $columns_table_data['col_10'] == 'on' || $all ){

		 
					$online_user_plus_proxies = count($all_online_usrs) + $total_recieved_proxies;
				 
					$out .= '
						<tr>
							<td class="">'.$labels_table_data[9].'</td>
							<td class="">'.$online_user_plus_proxies.'</td>
								
						</tr>';
				}

				/*
				if( $columns_table_data['col_11'] == 'on' || $all ){

		 
					$own_shares =  get_own_shares( $s_user  ) + 1;  
					$out .= '
						<tr>
							<td class="">'.$labels_table_data[10].'</td>
							<td class="">'.$own_shares.'</td>
								
						</tr>';
				}
				*/
 
			$out .= '
			</tbody>
		</table>';
	 
/**
 *  online users list to show and filter by myshares
 * 
 */

		if( $show_online_users == 'yes' || $all ){
 
			$out .= '	
			<h3>'.stripslashes( $output_options['user_list_title'] ).'</h3> 
			<table class="table sortable_table online_users_list">
				<thead class="">
				<tr>
				<th  class="no-sort">'.__('No.', 'wvp').'</th>
				';
				if( $columns_data['col_1'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[0].'</th>';
				}

				if( $columns_data['col_2'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[1].'</th>';
				}
				if( $columns_data['col_7'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[6].'</th>';
				}
				if( $columns_data['col_3'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[2].'</th>';
				}
				if( $columns_data['col_4'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[3].'</th>';
				}
				if( $columns_data['col_5'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[4].'</th>';
				}
				/** v1.15 */
				if( $columns_data['col_9'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[8].'</th>';
				}
				if( $columns_data['col_10'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[9].'</th>';
				}
				/** v1.15 END */
				if( $columns_data['col_6'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[5].'</th>';
				}
				if( $columns_data['col_8'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[7].'</th>';
				}
				
				if( $columns_data['col_11'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[10].'</th>';
				}

				$out .= '
				</tr>
				</thead>
				<tbody class="">';
	
				$all_users = $all_online_usrs;
				$total_results = [];
			 
				$cnt = 0;
				foreach( $all_users as $s_user ){

				/** myshres patch */
		 
				if( $myshares ){
					if( $s_user->ID  != $current_user->ID ){ continue; }
				}

				$cnt++;

				$val_a =  get_own_shares( $s_user->ID );  
				$val_b = get_total_shares( $s_user->ID );   
	
				$val_a_b = $val_a + $val_b;
	 
				$s_user = get_user_by( 'ID', $s_user->ID );

				$user_cat = get_user_meta( $s_user->ID, USER_CATEGORY_NAME_META_KEY, true );

				/** V 1.5 */
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
				/** V 1.5 END */
		 
				$out .= '
				<tr>
					<td  class="no-sort">'.$cnt.'</td>
				';
					if( $columns_data['col_1'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->first_name.'</td>';
					}
					if( $columns_data['col_2'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->last_name.'</td>';
					}
					if( $columns_data['col_7'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->user_login.'</td>';
					}
					if( $columns_data['col_3'] == 'on' || $all ){
						##FIX
						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}

						$out .= '<td class="text-center col_3">'.number_format( $val_a, $decimal_amount2show ).'</td>';
					}
					if( $columns_data['col_4'] == 'on' || $all ){
						$out .= '<td class="text-center col_4" data-sort-value="'.get_proxys_amount( $s_user->ID ).'">'.get_proxys_amount( $s_user->ID ).'</td>';
					}
					if( $columns_data['col_5'] == 'on' || $all ){
						##FIX
						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}
						
						$out .= '<td class="text-center col_5">'.number_format( $val_b, $decimal_amount2show, '.',',').'</td>';
					}
					/** v 1.15 */
					if( $columns_data['col_9'] == 'on' || $all ){
						$out .= '<td class="text-center col_9">'.implode( ', ', $user_first_name  ).'</td>';
					}
					if( $columns_data['col_10'] == 'on' || $all ){
						$out .= '<td class="text-center col_10">'.implode( ', ', $user_last_name  ).'</td>';
					}
					/** v 1.15 END */
					if( $columns_data['col_6'] == 'on' || $all ){

						##FIX
						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}

						$out .= '<td class="text-center col_6 " data-sort-value="'.(float)number_format( $val_a_b, $decimal_amount2show).'">'.number_format( $val_a_b, $decimal_amount2show).'</td>';
					}
					if( $columns_data['col_8'] == 'on' || $all ){
						$out .= '<td class="text-center col_8">'.$user_cat.'</td>';
					}
					if( $columns_data['col_11'] == 'on' || $all ){
						$recieved_proxies = get_proxys_amount( $s_user->ID ) + 1;
						$out .= '<td class="text-center col_11">'.$recieved_proxies.'</td>';
					}

		 $out .= '
				</tr>';
				}
				$out .= '
				</tbody>
		 
			</table>';
		}
		//$memberlistoffline
	 
		if( isset($atts['memberlistoffline'] ) || $show_offline_users == 'yes'  || $all ){
			$out .= '	
			<h3>'.stripslashes( $output_options['offline_user_list_title'] ).'</h3>
			<table class="table sortable_table offline_users_list"> 
				<thead class="">
				<tr>
					<th class="no-sort">'.__('No.', 'wvp').'</th>
				';
				if( $columns_data['col_1'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[0].'</th>';
				}

				if( $columns_data['col_2'] == 'on' || $all ){
					$out .= '
					<th class="col_2 ">'.$labels_data[1].'</th>';
				}
				if( $columns_data['col_7'] == 'on' || $all ){
					$out .= '
					<th class="col_7 ">'.$labels_data[6].'</th>';
				}
				if( $columns_data['col_3'] == 'on' || $all ){
					$out .= '
					<th class="col_3 number">'.$labels_data[2].'</th>';
				}
				if( $columns_data['col_4'] == 'on' || $all ){
					$out .= '
					<th class="col_4  number">'.$labels_data[3].'</th>';
				}
				if( $columns_data['col_5'] == 'on' || $all ){
					$out .= '
					<th class="col_5  number">'.$labels_data[4].'</th>';
				}
				/** v1.15 */
				if( $columns_data['col_9'] == 'on' || $all ){
					$out .= '
					<th class="col_9 ">'.$labels_data[8].'</th>';
				}
				if( $columns_data['col_10'] == 'on' || $all ){
					$out .= '
					<th class="col_10 ">'.$labels_data[9].'</th>';
				}
				/** v1.15 END */
				if( $columns_data['col_6'] == 'on' || $all ){
					$out .= '
					<th class="col_6  number">'.$labels_data[5].'</th>';
				}
				if( $columns_data['col_8'] == 'on' || $all ){
					$out .= '
					<th class="col_8 ">'.$labels_data[7].'</th>';
				}
				if( $columns_data['col_11'] == 'on' || $all ){
					$out .= '
					<th class="">'.$labels_data[10].'</th>';
				}
				$out .= '
				</tr>
				</thead>
				<tbody class="">';
	
 
				$tmp_online_users = [];
				if( count($all_online_usrs) > 0 ){
					foreach( $all_online_usrs as $s_user ){
						$tmp_online_users[] = $s_user->ID;
					}
				}

				$user_diff = array_diff( $all_users_that_caN_vote, $tmp_online_users );


				$all_users = $all_online_usrs;
				$total_results = [];
			 
				$cnt = 0;
				foreach( $user_diff as $s_user ){
				$cnt++;

				$val_a =  get_own_shares( $s_user  );  
				$val_b = get_total_shares( $s_user );  
	

			 
			 	/**  V1.15 BEGIN */
				$user_first_name = [];
				$user_last_name = [];
				$all_rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}usermeta WHERE meta_key = 'donate_recipient' AND meta_value = {$s_user}");
 
				foreach( $all_rows as $s_row ){
					$user_data = get_user_by('ID', $s_row->user_id);
					$user_first_name[] = $user_data->first_name;
					$user_last_name[] = $user_data->last_name;
				}
				$user_first_name = array_filter( $user_first_name );
				$user_last_name = array_filter( $user_last_name );
				/**  V1.15 END */
			 
				$val_a_b = $val_a + $val_b;
			 
				$s_user = get_user_by( 'ID', $s_user  );
				$user_cat = get_user_meta( $s_user->ID, USER_CATEGORY_NAME_META_KEY, true );


				

				$out .= '
				<tr>
					<td class="no-sort">'.$cnt.'</td>
				';
					if( $columns_data['col_1'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->first_name.'</td>';
					}
					if( $columns_data['col_2'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->last_name.'</td>';
					}
					if( $columns_data['col_7'] == 'on' || $all ){
						$out .= '<td class="">'.$s_user->user_login.'</td>';
					}
					if( $columns_data['col_3'] == 'on' || $all ){
						##FIX

						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}

						$out .= '<td class="text-center" data-sort-value="'.$val_a.'" >'.number_format( $val_a, $decimal_amount2show, '.', ',' ).'</td>';
					}
					if( $columns_data['col_4'] == 'on' || $all ){
						$out .= '<td class="text-center" data-sort-value="'.get_proxys_amount( $s_user->ID ).'">'.get_proxys_amount( $s_user->ID ).'</td>';
					}
					if( $columns_data['col_5'] == 'on' || $all ){
						##FIX

						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}

						$out .= '<td class="text-center"  data-sort-value="'.$val_b.'">'.number_format( $val_b, $decimal_amount2show, '.', ',' ).'</td>';
					}
					/** v 1.15 */
					if( $columns_data['col_9'] == 'on' || $all ){
						$out .= '<td class="text-center">'.implode( ', ',  $user_first_name  ).'</td>';
					}
					if( $columns_data['col_10'] == 'on' || $all ){
						$out .= '<td class="text-center">'.implode( ', ',  $user_last_name  ).'</td>';
					}
					/** v 1.15 END */
					if( $columns_data['col_6'] == 'on' || $all ){
						##FIX

						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}

						$out .= '<td class="text-center"   data-sort-value="'.$val_a_b.'">'.number_format( $val_a_b, $decimal_amount2show, '.', ',' ).'</td>';
					}
					if( $columns_data['col_8'] == 'on' || $all ){
						$out .= '<td class="text-center">'.$user_cat.'</td>';
					}
					if( $columns_data['col_11'] == 'on' || $all ){
						$recieved_proxies = get_proxys_amount( $s_user->ID ) + 1;
						$out .= '<td class="text-center col_11">'.$recieved_proxies.'</td>';
					}
		 $out .= '
				</tr>';
				}
				$out .= '
				</tbody>
		 
			</table>';
		}

		 


	$out .= '
	</div>
	';


	return $out;
}


add_shortcode( 'show_poll', 'wvp_show_poll' );
function wvp_show_poll( $atts, $content = null ){
	global $current_user, $post, $wpdb;

	/* limit */
	/*
	$all_posts = get_posts(array(
		'post_type' => 'poll',
		'post_status' => 'any',
		'showposts' => -1
	));
	$polls_num = 2;
	if( count($all_posts) > $polls_num ){
		$msg = sprintf( __( 'You current version of the plugin does not allow having more than %d polls created', 'wvp'), $polls_num ) ;
		return '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.$msg.'</div>
		</div>
		';
	}
	*/


	$post_id = $atts['id'];
	$this_post = get_post( $post_id );

	$poll_message = get_post_meta( $post_id, 'poll_message', true );
	$make_msg_mandatory = get_post_meta( $post_id, 'make_msg_mandatory', true );
	$show_messages_in_shortcodes = get_post_meta( $post_id, 'show_messages_in_shortcodes', true );
 
 
	if( !$this_post ){
		return '
		<div class="tw-bs4">
			<div class="alert alert-success">'.sprintf( __('Sorry, poll with this ID %d not exists', 'wvp'), $post_id ).'</div>
		</div>
		';
	}

	// filter posll by allow_only_assigned_users_to_vote
	$user_associated_category = get_user_meta( $current_user->ID, USER_CATEGORY_META_KEY, true );
	// get  poll assiciation value
	$is_assiged_to_cat = get_post_meta( $post_id, 'allow_only_assigned_users_to_vote', true );
	if( $is_assiged_to_cat == 'yes' ){
		$poll_cats = wp_get_post_terms( $post_id, USER_CATEGORY_NAME );
		$all_cat_ids = [];
		foreach( $poll_cats as $single_cat ){
			$all_cat_ids[] = $single_cat->term_id;
		}
		if( !in_array( $user_associated_category, $all_cat_ids ) ){
			//continue;
			return '
			<div class="tw-bs4">
				<div class="alert alert-warning">'.__('Sorry, you not allowed to vote', 'wvp').'</div>
			</div>
			';
		}
	}

	$poll_variants = (array) get_post_meta( $post_id, 'poll_variants', true );

	$user_id = $current_user->ID;

	// user data
	$can_vote = get_user_meta( $user_id, 'can_vote', true );
	$own_shares = get_own_shares( $user_id  );    
	$proxys_amount = get_proxys_amount( $user_id );
	$total_shares = get_total_shares( $user_id ); 

	// iа opened
	$vote_is_open = get_post_meta( $post_id, 'vote_is_open', true );

	$poll_type = get_post_meta( $post_id, 'poll_type', true );
	$min_variants = get_post_meta( $post_id, 'min_variants', true );
	$max_variants = get_post_meta( $post_id, 'max_variants', true );

	$after_vote_message = get_post_meta( $post_id, 'after_vote_message', true );
	if( !$after_vote_message || $after_vote_message =='' ){
		$wvp_variants = get_option('wvp_variants_options');
		$after_vote_message = $wvp_variants['default_submit_message'];
	}
 

	// check logged in
	if( !is_user_logged_in() ){
		return '
		<div class="tw-bs4">
			<div class="alert alert-success">'.__('You need to be logged in to vote.', 'wvp').'</div>
		</div>
		';
	}

	 
	if( (int)$can_vote != 1 && !current_user_can('administrator') ){
		return '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.__('Sorry, you not allowed to vote', 'wvp').'</div>
		</div>
		';	
	}

	


	// is submit
if( isset( $_GET['submit']  ) )
	if( $_GET['submit'] == 'true' && $_GET['id'] == $post_id ){
 
		return '
		<div class="tw-bs4">
			<div class="alert alert-success">'.$after_vote_message.'</div>
		</div>
		';
		/*
		$after_vote_message_var =  '
		<div class="tw-bs4">
			<div class="alert alert-success">'.$after_vote_message.'</div>
		</div>
		';
		*/
	}

	// if more variants
	if( isset( $_GET['submit']  ) )
	if( $_GET['submit'] == 'false' && $_GET['id'] == $post_id ){
		$error_message = '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.__('Results submit error. Try Again.', 'wvp').'</div></br>
		</div>
		';
	}

	// if wrong category
	if( isset( $_REQUEST['error']   ) )
	if( $_REQUEST['error'] == 'not_allowed'  ){
		$error_message = '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.__('Sorry, user need to be in poll category to vote', 'wvp').'</div></br>
		</div>
		';
	}

	//. checking if voted

	$voted = get_post_meta( $post_id, 'user_'.$current_user->ID,  true );
	
	if( isset( $voted ) && $voted != '' ){
		return '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.sprintf( __('You already vote in this vote – %s', 'wvp'), $this_post->post_title ).'</div>
		</div>
		';
	}

 
	//process timing delay
	$vote_delay_class = '';
	$wvp_extra_options = get_option('wvp_extra_options');

	$all_user_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->prefix}users");
	$splitted_users_arr = array_chunk ( $all_user_ids, 100 );

	$array_item_cnt = 1;
	$selected_offset = 0;
	foreach( $splitted_users_arr as $single_array ){
		if( in_array( $current_user->ID, $single_array) ){
			$selected_offset = $array_item_cnt;
		}
		$array_item_cnt++;
	}
	 
	if( $wvp_extra_options['enable_timer'] == 'yes' && $selected_offset > 1 ){
		$timeout  = $wvp_extra_options['interval_in_sec'];

		$vote_delay_class = ' vote_delay_class ';

		$out .= '
		<style>
		.vote_delay_class{
			display: none;
		}
		</style>
		';
		// full time offset
		$full_time_offset = $timeout * $selected_offset;

		$out .= '
		<script>
		jQuery(document).ready( function($){
			if( $(".vote_delay_class").length > 0 ){
				var total_time = '.$full_time_offset.';
				var interval_var = setInterval(function(){
					total_time--;
					$(".time_placeholder").html(total_time);
					if( total_time <= 1 ){
						clearInterval( interval_var );
						$(".vote_delay_class").fadeIn();
						$(".countdown_container").fadeOut();
					}
				}, 1000 )
			}
		})
		</script>
		<div class="tw-bs4 countdown_container">
			<div class="alert alert-warning">'.str_replace( 'XX', '<span class="time_placeholder">'.$full_time_offset.'</span>', __('Please wait XX seconds to enter your vote.','wvp') ).'</div>
		</div>
		';
	}
 
	// assigned proxies 
	$all_donors_list = [];
	/* v14#4
	// multiuser vote check tif assigned proxy users exists
	$all_donors_list = $wpdb->get_results("SELECT *  FROM {$wpdb->usermeta} WHERE meta_key = 'donate_recipient' AND meta_value = {$current_user->ID} " );
	if( count($all_donors_list) > 0 ){

		
		 $voters_amount = 0;
		
		$is_assiged_to_cat = get_post_meta( $post_id, 'allow_only_assigned_users_to_vote', true );

		$who_vote_selector = '<select name="donor_selector" required>
			<option value="" />'.__('Select here your name and the names of your assigned proxies to vote per each one. Please do it for all of your proxies', 'wvp').'
		';
		$voted = get_post_meta( $post_id, 'user_'.$current_user->ID,  true );
		if( $voted == '' ){
			unset($voted);
		}
 
		if( !isset( $voted ) ){
			$who_vote_selector .= '<option value="'.$current_user->ID.'">'.$current_user->user_login.' '.$current_user->first_name.' '.$current_user->last_name;
			$voters_amount++;
	 
		}
		foreach( $all_donors_list as $s_donor ){
			$userdata = get_user_by('ID', $s_donor->user_id );

			// filter posll by allow_only_assigned_users_to_vote
			$user_associated_category = get_user_meta( $s_donor->user_id, USER_CATEGORY_META_KEY, true );

			// get  poll assiciation value	
			if( $is_assiged_to_cat == 'yes' ){
				$poll_cats = wp_get_post_terms( $post_id, USER_CATEGORY_NAME );
				$all_cat_ids = [];
				foreach( $poll_cats as $single_cat ){
					$all_cat_ids[] = $single_cat->term_id;
				}

				//check if user already voted
				$voted = get_post_meta( $post_id, 'user_'.$s_donor->user_id,  true );
			 
				if( $voted == '' ){
					unset($voted);
				}
			
				if( in_array( $user_associated_category, $all_cat_ids ) && !isset( $voted ) ){
					$who_vote_selector .= '<option value="'.$s_donor->user_id.'">'.$userdata->user_login.' '.$userdata->first_name.' '.$userdata->last_name;
					$voters_amount++;
			 
				}
			}else{
				$who_vote_selector .= '<option value="'.$s_donor->user_id.'">'.$userdata->user_login.' '.$userdata->first_name.' '.$userdata->last_name;
				$voters_amount++;
	 
			}
			
		}
		$who_vote_selector .= '</select>';
	}
	*/
 
	// multiuser vote patch
	if( $voters_amount == 0 && count($all_donors_list) > 0 ){
		return '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.sprintf( __('You already vote in this vote – %s', 'wvp'), $this_post->post_title ).'</div>
		</div>
		';
	}
 
	$out .= '
	<div class="tw-bs4 '.$vote_delay_class.'">'.$after_vote_message_var.$error_message.'
		<h3>'.$this_post->post_title.'</h3>
		<form action="" method="POST" class="submission_vote_form">
		<input type="hidden" name="parent_vote" value="'.(int)$post_id.'">
		<input type="hidden" name="current_page" value="'.(int)$post->ID.'">
		<input type="hidden" id="poll_type" value="'.$poll_type.'">
		<input type="hidden" id="min_variants" value="'.$min_variants.'">
		<input type="hidden" id="max_variants" value="'.$max_variants.'">
		<table class="table">
			<tbody class="">';
			$poll_variants_check = (array) get_post_meta( $post_id, 'poll_variants_check', true );
 
			$cnt = 0;
			foreach( $poll_variants as $s_var ){
				$out .= '
				<tr>
					<td class="selector">
					<label>';
					if( $poll_type == 'multi' ){
						$out .= '<input type="checkbox"   name="user_selection[]" class="multi_user_check mr-4" data-status="'.$poll_variants_check[$cnt].'" value="'.$cnt.'" >';
					}else{
						$out .= '<input type="radio" required name="user_selection" class="mr-4" value="'.$cnt.'" >';
					}
					
					$out .= '
					'.$s_var.'
					</label>
					</td>
					<!-- <td class="">'.$s_var.'</td> -->
				</tr>
				';
				$cnt++;
			}

			$out .= '
			</tbody>
		</table>';

	if( $poll_message == 'yes' ){
		$out .= '<br/>
			<div class="">
			<textarea name="user_message" class="form-control" '.( $make_msg_mandatory == 'yes' ? ' required ' : '' ).' placeholder="'.__('Please, enter your message', 'wvp').'"></textarea><br/>
			</div>
			';
	}


	if( $vote_is_open == 'on' ){
		// admins cant vote
		if( current_user_can('administrator') ) {
			$out .= '
			<div class="tw-bs4">
				<div class="alert alert-warning">'.__('You are an admin and administrators can\'t vote', 'wvp').'</div>
			</div>
			';
		}else{
			$out .=  /* v14#4 $who_vote_selector. */ '
			<button class="btn btn-success">'.__('Submit', 'wvp').'</button>';
		}
	}else{
		$out .= '
			<div class="tw-bs4">
				<div class="alert alert-warning">'.__('This poll is closed for voting', 'wvp').'</div>
			</div>
			';
	}


	

		
	
		$out .= '
		</form>
	</div>
	';
	
	return $out;	
}
 
 
add_shortcode( 'show_poll_results', 'wvp_show_poll_results' );
function wvp_show_poll_results( $atts, $content = null ){
	global $wpdb;

 
	$settings_wvp_options = get_option('wvp_options');
	$org_total_shares = (int)$settings_wvp_options['organization_total_shares'];
 
	$decimal_amount = (int)$settings_wvp_options['decimal_amount'];
	

	

	/* limit */
	/*
	$all_posts = get_posts(array(
		'post_type' => 'poll',
		'post_status' => 'any',
		'showposts' => -1
	));
	$polls_num = 2;
	if( count($all_posts) > $polls_num ){
		$msg = sprintf( __( 'You current version of the plugin does not allow having more than %d polls created', 'wvp'), $polls_num ) ;
		return '
		<div class="tw-bs4">
			<div class="alert alert-warning">'.$msg.'</div>
		</div>
		';
	}
	*/

	$all = false;
	if( isset($atts['all']) ){
		$all = true;

	}
	$align_patch = '';
	$is_pdf = false;
	if( isset($atts['ispdf']) ){
		if( $atts['ispdf'] == '1' ){
			$align_patch = ' style="text-align:center;"';

		}
	}

	$post_id = $atts['id'];

 
	$post = get_post( $post_id );


	// iа opened
	$vote_is_open = get_post_meta( $post_id, 'vote_is_open', true );

	if( !$post ){
		return '
		<div class="tw-bs4">
			<div class="alert alert-success">'.sprintf( __('Sorry, poll with this ID %d not exists', 'wvp'), $post_id ).'</div>
		</div>
		';
	}

	$poll_variants = (array) get_post_meta( $post->ID, 'poll_variants', true );
	$poll_type = get_post_meta( $post->ID, 'poll_type', true );

	$pols_count = count( $poll_variants );
	

	$all_results = [];
 
	$all_shares_and_proxies = [];
	$all_votes = [];
	$total_votes_count = 0;
	$totla_count = 0;
	$col_5_val_cal_total = 0;
	$col_6_val_cal_total = 0;
	$col_7_val_cal_total = 0;

	for( $i=0; $i<$pols_count; $i++ ){
		//$all_votes = $wpdb->get_results("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = {$post->ID} AND meta_key LIKE '%user_%' AND meta_value = {%i}");
		$total_votes = $wpdb->get_col("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\\_%' AND  meta_value = {$i} AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%' AND  meta_value = {$i}  )");
		 
		//var_dump( "SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%' AND  meta_value = {$i} AND post_id NOT IN ( SELECT post_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%' AND  meta_value = {$i}  )" );
		 
		$total_votes = array_unique( $total_votes );
		
		$all_votes[$i] = count( $total_votes );

		$total_votes_count = $total_votes_count + count( $total_votes );

		$all_results[$i] = 0;
		$all_shares_and_proxies[$i] = 0;

		if( count( $total_votes ) == 0 ){
			$me_plus_proxies[$i] = 0;
		}else{
			//$me_plus_proxies[$i] = 1;
			/** patch 2.0.13 */
			$me_plus_proxies[$i] = count( $total_votes );
		}
		

		//if( count( $total_votes ) > 0 )
		foreach( $total_votes as $s_vote ){
			
			$tmp = explode('_', $s_vote);
			$user_shares = get_own_shares( $tmp[1]  ); 
			$total_shares = get_total_shares( $tmp[1] );
			$all_results[$i] = $all_results[$i] + $user_shares + $total_shares;
		 
			$all_shares_and_proxies[$i] = $all_shares_and_proxies[$i] + get_own_shares( $tmp[1]  ) + get_proxys_amount( $tmp[1]  );
			// patch 1.1.6 last edition
			$me_plus_proxies[$i] = $me_plus_proxies[$i] + get_proxys_amount( $tmp[1]  );
		}
		 
		//$all_results[$i] = $votes_count;
		$totla_count = $totla_count + $all_results[$i];
	}

 
	// customize poll outptu
	$output_options = get_option('wvp_extra_options');
	$columns_data = $output_options['poll_settings']['show_item'];
	$labels_data = $output_options['poll_settings']['block_value'];
	$show_decimal_amount =  $settings_wvp_options['show_decimal_amount'];
 
	
	$out = '
	<h2>'.$post->post_title.'</h2>';
	if( !isset( $atts['hidedatatable'] )  || $all ){
	$out .= '
	<table class="table text-center layout-fixd">
		<thead class="">
			<tr>';
			if( $columns_data['col_1'] == 'on' || $all ){
				$out .= '<th class="" '.$align_patch.'>'.$labels_data[0].'</th>';
			}
			if( $columns_data['col_2'] == 'on' || $all  ){
				$out .= '<th class="" '.$align_patch.'>'.$labels_data[1].'</th>';
			}
			if( $columns_data['col_3'] == 'on' || $all ){
				$out .= '<th class="" '.$align_patch.'>'.$labels_data[2].'</th>';
			}
			if( $columns_data['col_4'] == 'on' || $all ){
				$out .= '<th class="" '.$align_patch.'>'.$labels_data[3].'</th>';
			}
			if( $columns_data['col_5'] == 'on' || $all ){
				$out .= '<th class="" '.$align_patch.'>'.$labels_data[4].'</th>';
			}
			if( $columns_data['col_6'] == 'on' || $all ){
				$out .= '<th class="" '.$align_patch.'>'.$labels_data[5].'</th>';
			}
			if( $columns_data['col_7'] == 'on' || $all ){
				$out .= '<th class="" '.$align_patch.'>'.$labels_data[6].'</th>';
			}
	$out .= '</tr>
		</thead>
		<tbody class="">';
		
		// order poll variants by count
		//if( $poll_type == 'single' ){
			asort( $all_results );
 
		//}
		
		
		 
			//var_dump( $all_results );
		$out_results = [];
		$ans_count = 1;
		if( count($poll_variants) > 0 ){
			//$cnt = 0;
			
			foreach( $poll_variants as $index =>  $s_var ){

				$cnt = $index;

				// count percentage 
				if( $totla_count && $totla_count != 0 ){
					$percent = $all_results[$cnt]*100 / $totla_count;
				}else{
					$percent = 0;
				}
		 
				$out_row[ $cnt ]  = '
				<tr>';
				if( $columns_data['col_1'] == 'on' || $all ){
					$out_row[ $cnt ] .= '
					<td class="" '.$align_patch.'>%number%. '.$s_var.'
					<!--
					<div class="progress" '.$align_patch.'>
						<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0"   style="width: '.number_format( $percent, 2 ).'%"></div>
					</div>
					-->
 
					<div class="new_progress">
						<div class="progressbar_inner"   style="width: '.number_format( $percent, 2 ).'%"></div>
					</div>
				 

					</td>';
				}
				if( $columns_data['col_2'] == 'on' || $all ){
					$out_row[ $cnt ] .= '
					<td class="text-center" '.$align_patch.'>'.$all_votes[$cnt].'</td>';
				}
				if( $columns_data['col_3'] == 'on' || $all ){
					 
					$decimal_amount2show = $decimal_amount;
					if( $show_decimal_amount == 'no' ){
						$decimal_amount2show = 0;
					}

					$out_row[ $cnt ] .= '
					<td class="text-center" '.$align_patch.'>'.number_format( $all_results[$cnt], $decimal_amount2show ).'</td>';
				}
				if( $columns_data['col_4'] == 'on' || $all ){
					$out_row[ $cnt ] .= '
					<td class="text-center" '.$align_patch.'>'.number_format( $percent, $decimal_amount ).'</td>';
				}
				if( $columns_data['col_5'] == 'on' || $all ){

					// getting quorum on close time
					$user_closed_times = get_post_meta( $post->ID, 'vote_close_time', true );
					$last_quorum = 0;
					//foreach( $user_closed_times as $index => $value ){
						if( isset( $user_closed_times['shares'] ) ){
							$last_quorum = $user_closed_times['shares'];
						}
						else{
							$last_quorum = 0;
						}
					//}

					
				 
					if( $last_quorum == 0 ){
						$col_5_val_cal = 0;
					}else{
						$col_5_val_cal = ($all_results[$cnt]*100) / $last_quorum;
					}
					$col_5_val_cal_total = $col_5_val_cal_total + $col_5_val_cal;
					$out_row[ $cnt ] .= '
					<td class="text-center is_col_5" '.$align_patch.'>'.number_format( $col_5_val_cal, $decimal_amount ).'</td>';
				}

				if( $columns_data['col_6'] == 'on' || $all ){

					 //var_dump( $me_plus_proxies );
				 

					//$col_6_val_cal_total = $col_6_val_cal_total + $all_shares_and_proxies[$cnt];
					// patch 2.0.13
					$col_6_val_cal_total = $col_6_val_cal_total + $me_plus_proxies[$cnt];
					//$out_row[ $cnt ] .= '<td class="text-center" '.$align_patch.'>'.number_format( $all_shares_and_proxies[$cnt], 2 ).'</td>';
					// patch 1.16 overview
					$out_row[ $cnt ] .= '<td class="text-center is_col_6" '.$align_patch.'>'.$me_plus_proxies[$cnt].'</td>';
				}
				if( $columns_data['col_7'] == 'on' || $all ){
					####
					###  ????????????
 

					// patch 2.0.13
					$col_7_val_cal = ($all_results[$cnt]*100) / $org_total_shares;
			
					$col_7_val_cal_total = $col_7_val_cal_total + $col_7_val_cal;

					// patch 1.16 overview
					$out_row[ $cnt ] .= '<td class="text-center is_col_7" '.$align_patch.'>'.number_format( $col_7_val_cal, $decimal_amount ).'</td>';
				}

				$out_row[ $cnt ] .= '
				</tr>';
				//$cnt++;
				$out_results[] = array( 'text' => $out_row[ $cnt ] , 'votes' => $all_results[$cnt] );
				$ans_count++;
			}
			
		}
		 
			// order poll variants by count
		//if( $poll_type == 'single' ){
			$votes  = array_column($out_results, 'votes');
			array_multisort($votes, SORT_DESC,   $out_results);
 
		//} 
			
			$number = 1;
			foreach( $out_results as $k => $v ){
				$out .=  str_replace( '%number%', $number, $v['text'] );
				$number++;
			}
			

			$out .= '
			<tr>';
			if( $columns_data['col_1'] == 'on' || $all ){
				$out .= '<td class="" '.$align_patch.'>'.__('Total', 'wvp').'</td>';
			}
			if( $columns_data['col_2'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>'.$total_votes_count.'</td>';
			}
			if( $columns_data['col_3'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>'.number_format( $totla_count, $decimal_amount ).'</td>';
			}
			if( $columns_data['col_4'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>100.00%</td>';
			}
			if( $columns_data['col_5'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>'.number_format( $col_5_val_cal_total, $decimal_amount ).'</td>';
			}
			if( $columns_data['col_6'] == 'on' || $all ){
				$out .= '<td class="text-center" '.$align_patch.'>'.$col_6_val_cal_total.'</td>';
			}
			if( $columns_data['col_7'] == 'on' || $all ){

				$decimal_amount2show = $decimal_amount;
				if( $show_decimal_amount == 'no' ){
					$decimal_amount2show = 0;
				}		

				$out .= '<td class="text-center" '.$align_patch.'>'.number_format( $col_7_val_cal_total, $decimal_amount2show ).'</td>';
			}
			$out .= '
			</tr>';
	 
		
		
		$out .= '
		</tbody>
	</table>
	';


	// show quorum data saved time

	$output_options = get_option('wvp_extra_options');
	$columns_data = $output_options['poll_open_stats']['show_item'];
	$labels_data = $output_options['poll_open_stats']['block_value'];
 
	$out .= '
	<table>
			<tbody>';
			
			if( $vote_is_open == 'on' ){
 
				if( $columns_data['col_1'] == 'on' || $all ){
					$out .= '
					<tr>
						<th  class="text-left">'.$labels_data[0].'</th>
						<th class="text-left">'.__('Poll is open at this moment', 'wvp').'</th>
					</tr>';
				}
				if( $columns_data['col_2'] == 'on' || $all ){
					$out .= '
					<tr>
						<th class="text-left">'.$labels_data[1].'</th>
						<th class="text-left">'.__('Poll is open at this moment', 'wvp').'</th>
					</tr>';
				}
				
			}else{
				$user_closed_times = [];
				$user_closed_times[] = (array)get_post_meta( $post_id, 'vote_close_time', true );
			 
				foreach( $user_closed_times as $index => $value ){
			 
					if( $value == '' ){ continue; }

					if( $poll_type == 'single' ){
						$all_voted = $wpdb->get_var("SELECT count(meta_key) FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\\_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'    )");
						//echo $all_voted;
					}
					if( $poll_type == 'multi' ){
						$all_voted = $wpdb->get_var("SELECT  count(DISTINCT(meta_key)) FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\\_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'    )");
						//echo $all_voted;
					}


					$poll_type = get_post_meta($post_id, 'poll_type', true);
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
						 
						//echo $total;
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
							 
							//echo $total;
						}
					if( $columns_data['col_1'] == 'on' || $all ){
					$out .= '
					<tr>
						<th class="text-left">'.$labels_data[0].'</th>
						<th class="text-left">'.date('Y/m/d H:i:s', $value['time']).'</th>
					</tr>';
					}

					if( $columns_data['col_2'] == 'on' || $all ){

					$decimal_amount2show = 2;
					if( $show_decimal_amount == 'no' ){
						$decimal_amount2show = 0;
					}

					$out .= '
					<tr>
						<th class="text-left">'.$labels_data[1].'</th>
						<th class="text-left">'.number_format( $value['shares'], $decimal_amount2show ).'</th>
					</tr>';
					}

					if( $columns_data['col_3'] == 'on' || $all ){
					$out .= '
					<tr>
						<th class="text-left">'.$labels_data[2].'</th>
						<th class="text-left">'. /* hard patch $all_voted */ $total_votes_count.'</th>
					</tr>';
					}

					if( $columns_data['col_4'] == 'on' || $all ){
					$out .= '
					<tr>
						<th class="text-left">'.$labels_data[3].'</th>
						<th class="text-left">'.number_format( $total, $decimal_amount, '.', ',').'</th>
					</tr>';
					}
					if( $columns_data['col_5'] == 'on' || $all ){
						//Shares that didn’t vote:
						// Total shares in this answer - Organization total shares  
						//$calculated_value = $value['shares'] - $total;
						$calculated_value = $org_total_shares - $totla_count;
						$out .= '
						<tr>
							<th class="text-left">'.$labels_data[4].'</th>
							<th class="text-left">'.number_format( $calculated_value, $decimal_amount ).'</th>
						</tr>';
					}
					if( $columns_data['col_6'] == 'on' || $all ){
						// Shares that didn’t vote minus Quorum at the closing time :
						//$calculated_value = $value['shares'] - $total;
						//$calculated_value = $org_total_shares - $totla_count - (float)$value['shares'];
						$calculated_value = $value['shares'] - $total ;

						$decimal_amount2show = $decimal_amount;
						if( $show_decimal_amount == 'no' ){
							$decimal_amount2show = 0;
						}

						$out .= '
						<tr>
							<th class="text-left">'.$labels_data[5].'</th>
							<th class="text-left">'.number_format( $calculated_value, $decimal_amount2show ).'</th>
						</tr>';
					}
				}
				
			}
			
			$out .= '
			</tbody>
	</table>
	<br/>
	';
	}// if is hidetable set
	// process extra parameters
	 
	if( isset( $atts['showmissingusers'] ) ){
		//  all users that can vote
		$all_can_vote = $wpdb->get_col("SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = 'can_vote' AND meta_value = '1'");
		// all that voted

		$all_voted = $wpdb->get_col("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'    )");


		/* All onlione */
		$settings = get_option('wvp_options');
		//$time_limit = current_time('timestamp') - (int)$settings['user_online_lifetime']*60;
		if( $settings['user_online_lifetime_type'] == 'prev_online_minutes' ){
			$time_limit = current_time('timestamp') - (int)$settings['prev_online_minutes']*60;
		}
		if( $settings['user_online_lifetime_type'] == 'from_timestamp' ){
			$time_limit = strtotime( $settings['from_timestamp'] );
		}

		// all who can vote
		$all_users_that_caN_vote = $wpdb->get_col("SELECT user_id   FROM {$wpdb->usermeta} WHERE meta_key = 'can_vote' AND meta_value = '1' " );

		// all online users
		if( count($all_users_that_caN_vote) > 0 ){
			$all_online_usrs = $wpdb->get_col("SELECT DISTINCT user_id as ID FROM {$wpdb->usermeta} WHERE meta_key = 'online_activity' AND meta_value > {$time_limit} AND user_id  IN ( ".implode( ",", $all_users_that_caN_vote )." )");
		}else{
			$all_online_usrs = [];
		}
		
		/* all online end */

		$all_voted_ids = [];
		foreach( $all_voted as $s_vote ){
			$tmp = explode('_', $s_vote);
			$all_voted_ids[] = $tmp[1];
		}

		//var_dump( $all_can_vote );
		//var_dump( $all_voted_ids );

		$all_not_voted = array_diff( $all_can_vote, $all_voted_ids );

		//var_dump( $all_not_voted );
		if( count( $all_not_voted ) > 0 ){
			$out .= '<h3>'.__('Not voted:', 'wvp').'</h3>';
			//$out .= '<ol>';
			$not_voted_users_list = [];
			foreach( $all_not_voted as $s_id ){

				//if( !in_array( $s_id, $all_online_usrs ) ){ continue; }

				$userdata = get_user_by( 'ID', $s_id );
				//$out .= '<li>'.$userdata->first_name.' '.$userdata->last_name.' '.$userdata->user_login.'</li>';

				$not_voted_users_list[] = [ 'first_name' => $userdata->first_name, 'last_name' => $userdata->last_name, 'user_login' => $userdata->user_login ];
			}
			//$out .= '</ol>';

			// table output

			$columns_data = $output_options['vote_users_table']['show_item'];
			$labels_data = $output_options['vote_users_table']['block_value'];

			$out .= '<table class="table text-center layout-fixd sortable_table">
				<thead>
					<tr>
						<th>#</th>';
						/* OLD OUTPUT
						if( $columns_data['col_1'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[0].'</th>';
						}
						if( $columns_data['col_2'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[1].'</th>';
						}
						if( $columns_data['col_3'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[2].'</th>';
						}
						*/
						if( $columns_data['col_1'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[0].'</th>';
						}
						if( $columns_data['col_2'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[1].'</th>';
						}
						if( $columns_data['col_3'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[2].'</th>';
						}
						if( $columns_data['col_6'] == 'on' || $all ){
							$out .= '<th class="col_6 number">'.$labels_data[5].'</th>';
						}
						if( $columns_data['col_4'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[3].'</th>';
						}
						if( $columns_data['col_5'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[4].'</th>';
						}
						if( $columns_data['col_7'] == 'on' || $all ){
							$out .= '<th class="col_7 number">'.$labels_data[6].'</th>';
						}
						if( $columns_data['col_8'] == 'on' || $all ){
							$out .= '<th class="col_8 number">'.$labels_data[7].'</th>';
						}
						if( $columns_data['col_9'] == 'on' || $all ){
							$out .= '<th class="col_9 number">'.$labels_data[8].'</th>';
						}
			$out .= '
					</tr>
				</thead>
				<tbody>';
				$cnt = 1;
				foreach( $not_voted_users_list as $s_user ){

					$out .= '
					<tr>
						<td>'.$cnt.'</td>';
						if( $columns_data['col_1'] == 'on' || $all ){
							$out .= '<td>'.$s_user['first_name'].'</td>';
						}
						if( $columns_data['col_2'] == 'on' || $all ){
							$out .= '<td>'.$s_user['last_name'].'</td>';
						}
						if( $columns_data['col_3'] == 'on' || $all ){
							$out .= '<td>'.$s_user['user_login'].'</td>';
						}
						if( $columns_data['col_6'] == 'on' || $all ){
							/**  Total own shares + received in proxies shares (A+B) */
							$user_data_inner = get_user_by( 'login', $s_user['user_login'] );
							$own_shares = get_own_shares( $user_data_inner->ID );
							$total_shares = get_total_shares( $user_data_inner->ID );
							$proxys_amount = get_proxys_amount( $user_data_inner->ID );

							$inner_total = $own_shares + $total_shares;
							$out .= '<td class="col_6" data-sort-value="'.$inner_total.'">'.number_format( $inner_total, $decimal_amount ).'</td>';
						}
						if( $columns_data['col_4'] == 'on' || $all ){
							$out .= '<td class="col_4">'.$s_user['answer_variants'].'</td>';
						}
						if( $columns_data['col_5'] == 'on' || $all ){
							$out .= '<td class="col_5">'.( $s_user['message'] != '' ? $s_user['message'] : '' ).'</td>';
						}
						if( $columns_data['col_7'] == 'on' || $all ){
							$out .= '<td class="col_7" data-sort-value="'.get_own_shares( $s_user['ID'] ).'">'.number_format( get_own_shares( $s_user['ID'] ), $decimal_amount ).'</td>';
						}
						if( $columns_data['col_8'] == 'on' || $all ){
							$out .= '<td class="col_8" data-sort-value="'.get_total_shares( $s_user['ID'] ).'">'.number_format( get_total_shares( $s_user['ID'] ), $decimal_amount ).'</td>';
						}
				 
						if( $columns_data['col_9'] == 'on' || $all ){
							$date_timestamp = (int)get_post_meta( $post_id, 'uservotetime_'.$s_user['ID'], true );
							$vote_date = date( 'Y/m/d H:i:s', $date_timestamp );
							$out .= '<td class="col_9" data-sort-value="'.$date_timestamp.'">'.$vote_date.'</td>';
						}
					$out .= '
					</tr>';

					/* OLD OUTPUT
					$out .= '
					<tr>
						<td>'.$cnt.'</td>';
						if( $columns_data['col_1'] == 'on' || $all ){
							$out .= '<td>'.$s_user['first_name'].'</td>';
						}
						if( $columns_data['col_2'] == 'on' || $all ){
							$out .= '<td>'.$s_user['last_name'].'</td>';
						}
						if( $columns_data['col_3'] == 'on' || $all ){
							$out .= '<td>'.$s_user['user_login'].'</td>';
						}
				$out .= '
					</tr>';
					*/
					$cnt++;
				}
				
				$out .= '
				</tbody>
			</table>';
		}

	}
	// users that vote
	 
	if( isset( $atts['showusersthatvoted'] ) ){
 
		// all that voted
 
		//$all_voted = $wpdb->get_col("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%'");

		$all_voted = $wpdb->get_col("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user\_%'   AND meta_id NOT IN ( SELECT meta_id FROM {$wpdb->prefix}postmeta WHERE post_id = {$post_id} AND meta_key LIKE 'user_%_ip%'    )");
 

		$all_voted_ids = [];
 

		foreach( $all_voted as $s_vote ){
			$tmp = explode('_', $s_vote);
			$all_voted_ids[] = $tmp[1];
		}
		$all_voted_ids = array_unique( $all_voted_ids );
		//$all_voted = array_merge( $all_can_vote, $all_voted_ids );

		if( count( $all_voted_ids ) > 0 ){
			$out .= '<br/><h3>'.__('All users, that voted::', 'wvp').'</h3>';
			//$out .= '<ol>';
			$all_voted_users_list = [];
			
			foreach( $all_voted_ids as $s_id ){
				$answers_variants = '';
				$userdata = get_user_by( 'ID', $s_id );
				//$out .= '<li>'.$userdata->first_name.' '.$userdata->last_name.' '.$userdata->user_login;
				if( isset( $atts['showanswers'] ) ){
					// patch for multipol
					
					if( $poll_type == 'multi' ){
						$all_votes = get_post_meta( $post_id, 'user_'.$s_id );
						$out_votes = [];
						foreach( $all_votes as $s_vote ){
							$out_votes[] = $poll_variants[$s_vote];

						}
						//$out .= ' ( '.implode( ', ', $out_votes ).' )';
						$answers_variants = implode( ', ', $out_votes );
					}else{
						
						//$out .= ' ( '.$poll_variants[get_post_meta( $post_id, 'user_'.$s_id, true )].' )';
						$answers_variants = $poll_variants[get_post_meta( $post_id, 'user_'.$s_id, true )];
					}
					
				}
			 
				// add message oif exists
				$show_messages_in_shortcodes = get_post_meta( $post_id, 'show_messages_in_shortcodes', true );
		
				$message = get_post_meta( $post_id, 'usermessage_'.$s_id, true );
				if( $show_messages_in_shortcodes == 'yes' ){
					//$out .= '  '.__('Message: ', 'wvp').$message;	
				}

				$all_voted_users_list[] = [ 'ID' => $userdata->ID,  'first_name' => $userdata->first_name, 'last_name' => $userdata->last_name, 'user_login' => $userdata->user_login, 'message' => $message, 'answer_variants' => $answers_variants ];
				//$out .= '</li>';
			}
			//$out .= '</ol>';

			$columns_data = $output_options['vote_users_table']['show_item'];
			$labels_data = $output_options['vote_users_table']['block_value'];
			$out .= '<table class="table  asas text-center layout-fixd sortable_table">
				<thead>
					<tr>
						<th>#</th>';
						if( $columns_data['col_1'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[0].'</th>';
						}
						if( $columns_data['col_2'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[1].'</th>';
						}
						if( $columns_data['col_3'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[2].'</th>';
						}
						if( $columns_data['col_6'] == 'on' || $all ){
							$out .= '<th class="col_6 number">'.$labels_data[5].'</th>';
						}
						if( $columns_data['col_4'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[3].'</th>';
						}
						if( $columns_data['col_5'] == 'on' || $all ){
							$out .= '<th>'.$labels_data[4].'</th>';
						}
						if( $columns_data['col_7'] == 'on' || $all ){
							$out .= '<th class="col_7 number">'.$labels_data[6].'</th>';
						}
						if( $columns_data['col_8'] == 'on' || $all ){
							$out .= '<th class="col_8 number">'.$labels_data[7].'</th>';
						}
						if( $columns_data['col_9'] == 'on' || $all ){
							$out .= '<th class="col_9 number">'.$labels_data[8].'</th>';
						}
			$out .= '
					</tr>
				</thead>
				<tbody>';
				$cnt = 1;
				foreach( $all_voted_users_list as $s_user ){
		 
					$out .= '
					<tr>
						<td>'.$cnt.'</td>';
						if( $columns_data['col_1'] == 'on' || $all ){
							$out .= '<td>'.$s_user['first_name'].'</td>';
						}
						if( $columns_data['col_2'] == 'on' || $all ){
							$out .= '<td>'.$s_user['last_name'].'</td>';
						}
						if( $columns_data['col_3'] == 'on' || $all ){
							$out .= '<td>'.$s_user['user_login'].'</td>';
						}
						if( $columns_data['col_6'] == 'on' || $all ){
							/**  Total own shares + received in proxies shares (A+B) */
							$user_data_inner = get_user_by( 'login', $s_user['user_login'] );
							$own_shares = get_own_shares( $user_data_inner->ID );
							$total_shares = get_total_shares( $user_data_inner->ID );
							$proxys_amount = get_proxys_amount( $user_data_inner->ID );

							$inner_total = $own_shares + $total_shares;
							$out .= '<td class="col_6" data-sort-value="'.$inner_total.'">'.number_format( $inner_total, $decimal_amount ).'</td>';
						}
						if( $columns_data['col_4'] == 'on' || $all ){
							$out .= '<td class="col_4">'.$s_user['answer_variants'].'</td>';
						}
						if( $columns_data['col_5'] == 'on' || $all ){
							$out .= '<td class="col_5">'.( $s_user['message'] != '' ? $s_user['message'] : '' ).'</td>';
						}
						if( $columns_data['col_7'] == 'on' || $all ){
							$out .= '<td class="col_7" data-sort-value="'.get_own_shares( $s_user['ID'] ).'">'.number_format( get_own_shares( $s_user['ID'] ), $decimal_amount ).'</td>';
						}
						if( $columns_data['col_8'] == 'on' || $all ){
							$out .= '<td class="col_8" data-sort-value="'.get_total_shares( $s_user['ID'] ).'">'.number_format( get_total_shares( $s_user['ID'] ), $decimal_amount ).'</td>';
						}
				 
						if( $columns_data['col_9'] == 'on' || $all ){
							$date_timestamp = (int)get_post_meta( $post_id, 'uservotetime_'.$s_user['ID'], true );
							$vote_date = date( 'Y/m/d H:i:s', $date_timestamp );
							$out .= '<td class="col_9" data-sort-value="'.$date_timestamp.'">'.$vote_date.'</td>';
						}
					$out .= '
					</tr>';
					$cnt++;
				}
				
				$out .= '
				</tbody>
			</table>';
		}

	}

	return '<div class="tw-bs4">'.$out.'</div>';
}
 
?>